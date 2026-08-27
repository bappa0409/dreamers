<?php

namespace App\Services;

use App\Jobs\SendCampaignMailJob;
use App\Models\MailCampaign;
use App\Models\MailRecipient;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MailCampaignService
{
    public function create(array $data, int $userId): MailCampaign
    {
        return DB::transaction(function () use ($data, $userId) {
            return MailCampaign::create([
                'subject' => $data['subject'],
                'body' => $data['body'],
                'status' => 'draft',
                'created_by' => $userId,
                'recipients_count' => 0,
                'sent_count' => 0,
                'failed_count' => 0,
            ]);
        });
    }

    public function update(MailCampaign $campaign, array $data): MailCampaign
    {
        return DB::transaction(function () use ($campaign, $data) {
            $campaign = MailCampaign::query()
                ->whereKey($campaign->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($campaign->status !== 'draft') {
                throw ValidationException::withMessages([
                    'campaign' => ['Only draft campaigns can be edited.'],
                ]);
            }

            $campaign->update([
                'subject' => $data['subject'] ?? $campaign->subject,
                'body' => $data['body'] ?? $campaign->body,
            ]);

            return $campaign->fresh();
        });
    }

    public function addRecipients(MailCampaign $campaign, array $data): void
    {
        DB::transaction(function () use ($campaign, $data) {
            $campaign = MailCampaign::query()
                ->whereKey($campaign->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($campaign->status !== 'draft') {
                throw ValidationException::withMessages([
                    'campaign' => ['Recipients cannot be changed after sending starts.'],
                ]);
            }

            $audience = $data['audience_type'];

            if ($audience === 'all_active_members') {
                User::query()
                    ->where('is_active', true)
                    ->whereNotNull('email')
                    ->whereHas('member', fn ($q) => $q->where('status', 'active'))
                    ->select('id', 'name', 'email')
                    ->orderBy('id')
                    ->chunkById(500, function ($users) use ($campaign) {
                        $this->attachRecipientRows(
                            $campaign,
                            $users->map(fn ($user) => [
                                'user_id' => $user->id,
                                'name' => $user->name,
                                'email' => $user->email,
                            ])->all()
                        );
                    });
            }

            if ($audience === 'selected_members') {
                $requestedUserIds = collect($data['user_ids'] ?? [])
                    ->map(fn ($id) => (int) $id)
                    ->unique()
                    ->values();

                $eligibleCount = User::query()
                    ->whereIn('id', $requestedUserIds->all())
                    ->where('is_active', true)
                    ->whereNotNull('email')
                    ->whereHas('member', fn ($q) => $q->where('status', 'active'))
                    ->count();

                if ($eligibleCount !== $requestedUserIds->count()) {
                    throw ValidationException::withMessages([
                        'user_ids' => ['Every selected recipient must be an active member with an email address.'],
                    ]);
                }

                User::query()
                    ->whereIn('id', $requestedUserIds->all())
                    ->where('is_active', true)
                    ->whereNotNull('email')
                    ->whereHas('member', fn ($q) => $q->where('status', 'active'))
                    ->select('id', 'name', 'email')
                    ->orderBy('id')
                    ->chunkById(500, function ($users) use ($campaign) {
                        $this->attachRecipientRows(
                            $campaign,
                            $users->map(fn ($user) => [
                                'user_id' => $user->id,
                                'name' => $user->name,
                                'email' => $user->email,
                            ])->all()
                        );
                    });
            }

            if ($audience === 'manual') {
                $this->attachRecipientRows(
                    $campaign,
                    collect($data['manual_emails'] ?? [])
                        ->map(fn ($item) => [
                            'user_id' => null,
                            'name' => $item['name'] ?? null,
                            'email' => $item['email'] ?? '',
                        ])
                        ->all()
                );
            }

            $this->refreshRecipientCount($campaign);
        });
    }

    protected function attachRecipientRows(
        MailCampaign $campaign,
        array $rows
    ): void {
        $rows = collect($rows)
            ->map(function (array $row) {
                $email = strtolower(trim((string) ($row['email'] ?? '')));

                if ($email === '') {
                    return null;
                }

                return [
                    'user_id' => $row['user_id'] ?? null,
                    'name' => $row['name'] ?? null,
                    'email' => $email,
                ];
            })
            ->filter()
            ->unique('email')
            ->values();

        if ($rows->isEmpty()) {
            return;
        }

        $existing = $campaign->recipients()
            ->whereIn('email', $rows->pluck('email')->all())
            ->pluck('email')
            ->map(fn ($email) => strtolower((string) $email))
            ->flip();

        $now = now();

        $insert = $rows
            ->reject(fn ($row) => $existing->has($row['email']))
            ->map(fn ($row) => [
                'mail_campaign_id' => $campaign->id,
                'user_id' => $row['user_id'],
                'member_id' => null,
                'email' => $row['email'],
                'name' => $row['name'],
                'status' => 'pending',
                'sent_at' => null,
                'error_message' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ])
            ->values()
            ->all();

        if ($insert) {
            DB::table('mail_recipients')->insert($insert);
        }
    }

    public function removeRecipient(
        MailCampaign $campaign,
        MailRecipient $recipient
    ): void {
        DB::transaction(function () use ($campaign, $recipient) {
            $campaign = MailCampaign::query()
                ->whereKey($campaign->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($campaign->status !== 'draft') {
                throw ValidationException::withMessages([
                    'campaign' => ['Recipients cannot be removed after sending starts.'],
                ]);
            }

            if ((int) $recipient->mail_campaign_id !== (int) $campaign->id) {
                abort(404);
            }

            $recipient = MailRecipient::query()
                ->whereKey($recipient->id)
                ->where('mail_campaign_id', $campaign->id)
                ->lockForUpdate()
                ->firstOrFail();

            $recipient->delete();
            $this->refreshRecipientCount($campaign);
        });
    }

    public function send(MailCampaign $campaign): MailCampaign
    {
        $campaignId = (int) $campaign->id;

        DB::transaction(function () use ($campaignId) {
            $campaign = MailCampaign::query()
                ->whereKey($campaignId)
                ->lockForUpdate()
                ->firstOrFail();

            if ($campaign->status !== 'draft') {
                throw ValidationException::withMessages([
                    'campaign' => ['This campaign has already been processed.'],
                ]);
            }

            $recipientCount = $campaign->recipients()
                ->where('status', 'pending')
                ->count();

            if ($recipientCount < 1) {
                throw ValidationException::withMessages([
                    'recipients' => ['Add at least one recipient before sending.'],
                ]);
            }

            $campaign->update([
                'status' => 'sending',
                'sent_at' => null,
                'sent_count' => 0,
                'failed_count' => 0,
                'recipients_count' => $recipientCount,
            ]);
        });

        // Dispatch in bounded chunks instead of loading every recipient id into
        // memory. The campaign row was already atomically moved out of draft,
        // so a concurrent second Send request cannot dispatch the same campaign.
        MailRecipient::query()
            ->where('mail_campaign_id', $campaignId)
            ->where('status', 'pending')
            ->select('id')
            ->orderBy('id')
            ->chunkById(500, function ($recipients) use ($campaign) {
                foreach ($recipients as $recipient) {
                    SendCampaignMailJob::dispatch(
                        (int) $recipient->id,
                        $campaign->subject,
                        (string) $campaign->body
                    );
                }
            });

        return MailCampaign::findOrFail($campaignId);
    }

    /**
     * Manual reconciliation helper. Queue jobs also update counts and final
     * status automatically, so this is safe to call from maintenance code.
     */
    public function refreshSendStatus(MailCampaign $campaign): MailCampaign
    {
        if ($campaign->status !== 'sending') {
            return $campaign;
        }

        $pending = $campaign->recipients()->where('status', 'pending')->count();
        $sent = $campaign->recipients()->where('status', 'sent')->count();
        $failed = $campaign->recipients()->where('status', 'failed')->count();

        $campaign->update([
            'sent_count' => $sent,
            'failed_count' => $failed,
            'recipients_count' => $campaign->recipients()->count(),
        ]);

        if ($pending === 0) {
            $campaign->update([
                'status' => $failed > 0 ? 'failed' : 'completed',
                'sent_at' => now(),
            ]);
        }

        return $campaign->fresh();
    }

    public function delete(MailCampaign $campaign): void
    {
        DB::transaction(function () use ($campaign) {
            $campaign = MailCampaign::query()
                ->whereKey($campaign->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (!in_array($campaign->status, ['draft', 'cancelled'], true)) {
                throw ValidationException::withMessages([
                    'campaign' => ['Only draft or cancelled campaigns can be deleted.'],
                ]);
            }

            $campaign->delete();
        });
    }

    protected function refreshRecipientCount(MailCampaign $campaign): void
    {
        $campaign->update([
            'recipients_count' => $campaign->recipients()->count(),
        ]);
    }
}
