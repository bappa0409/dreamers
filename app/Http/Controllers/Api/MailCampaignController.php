<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MailCampaign;
use App\Models\MailRecipient;
use App\Models\User;
use App\Services\MailCampaignService;
use Illuminate\Http\Request;

class MailCampaignController extends Controller
{
    public function __construct(
        protected MailCampaignService $mailCampaignService
    ){}

    public function index(Request $request)
    {
        $validated=$request->validate([
            'search'=>'nullable|string|max:150',
            'status'=>'nullable|in:draft,sending,completed,completed_with_errors,cancelled',
            'per_page'=>'nullable|integer|min:5|max:100'
        ]);

        $query=MailCampaign::query()
            ->with('creator:id,name,email')
            ->latest('id');

        if(!empty($validated['search'])){
            $search=$validated['search'];

            $query->where(function($q)use($search){
                $q->where('subject','like',"%{$search}%")
                    ->orWhere('body','like',"%{$search}%");
            });
        }

        if(!empty($validated['status'])){
            $query->where(
                'status',
                $validated['status']
            );
        }

        return response()->json([
            'success'=>true,
            'data'=>$query->paginate(
                min(
                    (int)($validated['per_page']??15),
                    100
                )
            )
        ]);
    }

    public function store(Request $request)
    {
        $validated=$request->validate([
            'subject'=>'required|string|max:255',
            'body'=>'required|string|max:20000'
        ]);

        $campaign=$this->mailCampaignService
            ->create(
                $validated,
                $request->user()->id
            );

        return response()->json([
            'success'=>true,
            'message'=>'Mail campaign created successfully.',
            'data'=>$campaign
        ],201);
    }

    public function show(MailCampaign $mailCampaign)
    {
        return response()->json([
            'success'=>true,
            'data'=>$mailCampaign->load([
                'creator:id,name,email',
                'recipients'=>function($query){
                    $query->latest('id');
                }
            ])
        ]);
    }

    public function update(
        Request $request,
        MailCampaign $mailCampaign
    ){
        $validated=$request->validate([
            'subject'=>'sometimes|required|string|max:255',
            'body'=>'sometimes|required|string|max:20000'
        ]);

        $campaign=$this->mailCampaignService
            ->update(
                $mailCampaign,
                $validated
            );

        return response()->json([
            'success'=>true,
            'message'=>'Campaign updated successfully.',
            'data'=>$campaign
        ]);
    }

    public function recipients(Request $request)
    {
        $search=trim(
            (string)$request->input('search','')
        );

        $users=User::query()
            ->where('is_active',true)
            ->whereHas('member',function($q){
                $q->where('status','active');
            })
            ->when($search,function($query)use($search){
                $query->where(function($q)use($search){
                    $q->where('name','like',"%{$search}%")
                        ->orWhere('email','like',"%{$search}%")
                        ->orWhere('mobile','like',"%{$search}%");
                });
            })
            ->orderBy('name')
            ->limit(50)
            ->get([
                'id',
                'name',
                'email',
                'mobile'
            ]);

        return response()->json([
            'success'=>true,
            'data'=>$users
        ]);
    }

    public function addRecipients(
        Request $request,
        MailCampaign $mailCampaign
    ){
        $validated=$request->validate([
            'audience_type'=>'required|in:all_active_members,selected_members,manual',
            'user_ids'=>'nullable|required_if:audience_type,selected_members|array',
            'user_ids.*'=>'integer|distinct|exists:users,id',
            'manual_emails'=>'nullable|required_if:audience_type,manual|array|min:1',
            'manual_emails.*.name'=>'nullable|string|max:255',
            'manual_emails.*.email'=>'required|email|max:255'
        ]);

        $this->mailCampaignService
            ->addRecipients(
                $mailCampaign,
                $validated
            );

        return response()->json([
            'success'=>true,
            'message'=>'Recipients added successfully.'
        ]);
    }

    public function removeRecipient(
        MailCampaign $mailCampaign,
        MailRecipient $recipient
    ){
        $this->mailCampaignService
            ->removeRecipient(
                $mailCampaign,
                $recipient
            );

        return response()->json([
            'success'=>true,
            'message'=>'Recipient removed successfully.'
        ]);
    }

    public function send(MailCampaign $mailCampaign)
    {
        $campaign=$this->mailCampaignService
            ->send($mailCampaign);

        return response()->json([
            'success'=>true,
            'message'=>'Campaign processed successfully.',
            'data'=>$campaign
        ]);
    }

    public function destroy(MailCampaign $mailCampaign)
    {
        $this->mailCampaignService
            ->delete($mailCampaign);

        return response()->json([
            'success'=>true,
            'message'=>'Campaign deleted successfully.'
        ]);
    }
}