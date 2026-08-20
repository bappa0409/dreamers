<?php

namespace App\Services;

use App\Mail\CampaignMail;
use App\Models\MailCampaign;
use App\Models\MailRecipient;
use App\Models\Member;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class MailCampaignService
{
    public function create(array $data,int $userId): MailCampaign
    {
        return DB::transaction(function()use($data,$userId){
            return MailCampaign::create([
                'subject'=>$data['subject'],
                'body'=>$data['body'],
                'status'=>'draft',
                'created_by'=>$userId,
                'recipients_count'=>0,
                'sent_count'=>0,
                'failed_count'=>0
            ]);
        });
    }

    public function update(
        MailCampaign $campaign,
        array $data
    ): MailCampaign{
        if($campaign->status!=='draft'){
            throw ValidationException::withMessages([
                'campaign'=>[
                    'Only draft campaigns can be edited.'
                ]
            ]);
        }

        $campaign->update([
            'subject'=>$data['subject']??$campaign->subject,
            'body'=>$data['body']??$campaign->body
        ]);

        return $campaign->fresh();
    }

    public function addRecipients(
        MailCampaign $campaign,
        array $data
    ): void{
        if($campaign->status!=='draft'){
            throw ValidationException::withMessages([
                'campaign'=>[
                    'Recipients cannot be changed after sending starts.'
                ]
            ]);
        }

        DB::transaction(function()use($campaign,$data){
            $audience=$data['audience_type'];

            if($audience==='all_active_members'){
                $users=User::query()
                    ->where('is_active',true)
                    ->whereHas('member',function($q){
                        $q->where('status','active');
                    })
                    ->get(['id','name','email']);

                foreach($users as $user){
                    $this->attachRecipient(
                        $campaign,
                        $user->id,
                        $user->name,
                        $user->email
                    );
                }
            }

            if($audience==='selected_members'){
                $users=User::query()
                    ->whereIn(
                        'id',
                        $data['user_ids']??[]
                    )
                    ->get(['id','name','email']);

                foreach($users as $user){
                    $this->attachRecipient(
                        $campaign,
                        $user->id,
                        $user->name,
                        $user->email
                    );
                }
            }

            if($audience==='manual'){
                foreach($data['manual_emails']??[] as $item){
                    $this->attachRecipient(
                        $campaign,
                        null,
                        $item['name']??null,
                        $item['email']
                    );
                }
            }

            $this->refreshRecipientCount(
                $campaign
            );
        });
    }

    protected function attachRecipient(
        MailCampaign $campaign,
        ?int $userId,
        ?string $name,
        string $email
    ): void{
        $email=strtolower(trim($email));

        if($email===''){
            return;
        }

        MailRecipient::firstOrCreate(
            [
                'mail_campaign_id'=>$campaign->id,
                'email'=>$email
            ],
            [
                'user_id'=>$userId,
                'name'=>$name,
                'status'=>'pending'
            ]
        );
    }

    public function removeRecipient(
        MailCampaign $campaign,
        MailRecipient $recipient
    ): void{
        if($campaign->status!=='draft'){
            throw ValidationException::withMessages([
                'campaign'=>[
                    'Recipients cannot be removed after sending starts.'
                ]
            ]);
        }

        if($recipient->mail_campaign_id!==$campaign->id){
            abort(404);
        }

        $recipient->delete();

        $this->refreshRecipientCount(
            $campaign
        );
    }

    public function send(MailCampaign $campaign): MailCampaign
    {
        if($campaign->status!=='draft'){
            throw ValidationException::withMessages([
                'campaign'=>[
                    'This campaign has already been processed.'
                ]
            ]);
        }

        $recipients=$campaign
            ->recipients()
            ->where('status','pending')
            ->get();

        if($recipients->isEmpty()){
            throw ValidationException::withMessages([
                'recipients'=>[
                    'Add at least one recipient before sending.'
                ]
            ]);
        }

        $campaign->update([
            'status'=>'sending'
        ]);

        $sent=0;
        $failed=0;

        foreach($recipients as $recipient){
            try{
                Mail::to($recipient->email)
                    ->send(
                        new CampaignMail(
                            subject:$campaign->subject,
                            body:$campaign->body
                        )
                    );

                $recipient->update([
                    'status'=>'sent',
                    'sent_at'=>now(),
                    'error_message'=>null
                ]);

                $sent++;

            }catch(\Throwable $e){
                $recipient->update([
                    'status'=>'failed',
                    'error_message'=>mb_substr(
                        $e->getMessage(),
                        0,
                        2000
                    )
                ]);

                $failed++;
            }
        }

        $campaign->update([
            'status'=>$failed>0
                ?'completed_with_errors'
                :'completed',
            'sent_at'=>now(),
            'sent_count'=>$sent,
            'failed_count'=>$failed,
            'recipients_count'=>$campaign
                ->recipients()
                ->count()
        ]);

        return $campaign->fresh();
    }

    public function delete(MailCampaign $campaign): void
    {
        if(!in_array($campaign->status,['draft','cancelled'],true)){
            throw ValidationException::withMessages([
                'campaign'=>[
                    'Only draft or cancelled campaigns can be deleted.'
                ]
            ]);
        }

        $campaign->delete();
    }

    protected function refreshRecipientCount(
        MailCampaign $campaign
    ): void{
        $campaign->update([
            'recipients_count'=>
                $campaign->recipients()->count()
        ]);
    }
}