<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MailCampaign;
use App\Models\User;
use App\Services\MailCampaignService;
use Illuminate\Http\Request;

class MailCampaignController extends Controller
{
    public function index()
    {
        $campaigns = MailCampaign::with('creator')
            ->latest()
            ->paginate(10);

        return response()->json($campaigns);
    }

    public function store(
        Request $request,
        MailCampaignService $service
    ) {
        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        $campaign = $service->createCampaign(
            $validated,
            $request->user()
        );

        return response()->json([
            'message' => 'Mail campaign created successfully.',
            'campaign' => $campaign,
        ], 201);
    }

    public function show(MailCampaign $mailCampaign)
    {
        return response()->json(
            $mailCampaign->load([
                'creator',
                'recipients',
            ])
        );
    }

    public function addRecipient(
        Request $request,
        MailCampaign $mailCampaign,
        MailCampaignService $service
    ) {
        $validated = $request->validate([
            'email' => 'required|email',
            'name' => 'nullable|string|max:255',
            'user_id' => 'nullable|exists:users,id',
            'member_id' => 'nullable|exists:members,id',
        ]);

        $recipient = $service->addRecipient(
            $mailCampaign,
            $validated['email'],
            $validated['name'] ?? null,
            $validated['user_id'] ?? null,
            $validated['member_id'] ?? null
        );

        $mailCampaign->update([
            'total_recipients' =>
                $mailCampaign->recipients()->count(),
        ]);

        return response()->json([
            'message' => 'Recipient added successfully.',
            'recipient' => $recipient,
        ], 201);
    }

    public function send(
        MailCampaign $mailCampaign,
        MailCampaignService $service
    ) {
        if (
            $mailCampaign->status === 'completed'
        ) {
            return response()->json([
                'message' => 'Campaign has already been sent.'
            ], 422);
        }

        if (
            $mailCampaign->recipients()->count() === 0
        ) {
            return response()->json([
                'message' => 'Campaign has no recipients.'
            ], 422);
        }

        $service->sendCampaign($mailCampaign);

        return response()->json([
            'message' => 'Campaign sending completed.',
            'campaign' => $mailCampaign->fresh(),
        ]);
    }

    public function destroy(MailCampaign $mailCampaign)
    {
        $mailCampaign->delete();

        return response()->json([
            'message' => 'Campaign deleted successfully.',
        ]);
    }
}