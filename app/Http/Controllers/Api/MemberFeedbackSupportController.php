<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FeedbackSupport;
use App\Models\FeedbackSupportAttachment;
use App\Models\FeedbackSupportCategory;
use App\Services\FeedbackSupportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MemberFeedbackSupportController extends Controller
{
    public function __construct(
        protected FeedbackSupportService $service
    ){}

    public function index(Request $request)
    {
        $member=$request->user()->member;

        abort_unless($member,403);

        return response()->json([
            'success'=>true,
            'data'=>[
                'categories'=>FeedbackSupportCategory::query()
                    ->where('is_active',true)
                    ->orderBy('sort_order')
                    ->get([
                        'id',
                        'name',
                        'description'
                    ]),

                'tickets'=>$member->feedbackSupports()
                    ->with([
                        'category:id,name',
                        'assignee:id,name',
                        'attachments:id,feedback_support_id,original_name,mime_type,file_size',

                        'updates'=>fn($q)=>
                            $q->whereIn(
                                'type',
                                [
                                    'support_response',
                                    'member_follow_up'
                                ]
                            )
                            ->with('user:id,name')
                            ->oldest(),

                        'histories'=>fn($q)=>
                            $q->select([
                                'id',
                                'feedback_support_id',
                                'from_status',
                                'to_status',
                                'created_at'
                            ])
                            ->oldest()
                    ])
                    ->latest('id')
                    ->get()
            ]
        ]);
    }

    public function store(Request $request)
    {
        $member=$request->user()->member;

        abort_unless($member,403);

        $data=$request->validate([
            'feedback_support_category_id'=>
                'required|integer|exists:feedback_support_categories,id',

            'type'=>
                'required|in:feedback,support_request,complaint,suggestion,service_issue,other',

            'subject'=>'required|string|max:200',
            'description'=>'required|string|max:20000',

            'priority'=>
                'nullable|in:low,normal,high,urgent',

            'is_confidential'=>'nullable|boolean'
        ]);

        return response()->json([
            'success'=>true,
            'message'=>'Feedback & Support request submitted.',
            'data'=>$this->service->create(
                $member,
                $data,
                $request->user()->id
            )
        ],201);
    }

    public function followUp(
        Request $request,
        FeedbackSupport $feedbackSupport
    ){
        $this->ownership(
            $request,
            $feedbackSupport
        );

        $data=$request->validate([
            'message'=>'required|string|max:10000'
        ]);

        return response()->json([
            'success'=>true,
            'message'=>'Follow-up added.',
            'data'=>$this->service->memberFollowUp(
                $feedbackSupport,
                $data['message'],
                $request->user()->id
            )
        ],201);
    }

    public function upload(
        Request $request,
        FeedbackSupport $feedbackSupport
    ){
        $this->ownership(
            $request,
            $feedbackSupport
        );

        $request->validate([
            'file'=>
                'required|file|mimes:pdf,jpg,jpeg,png,webp,doc,docx|max:10240'
        ]);

        return response()->json([
            'success'=>true,
            'message'=>'Attachment uploaded.',
            'data'=>$this->service->upload(
                $feedbackSupport,
                $request->file('file'),
                $request->user()->id
            )
        ],201);
    }

    public function cancel(
        Request $request,
        FeedbackSupport $feedbackSupport
    ){
        $this->ownership(
            $request,
            $feedbackSupport
        );

        return response()->json([
            'success'=>true,
            'message'=>'Feedback & Support request cancelled.',
            'data'=>$this->service->cancel(
                $feedbackSupport,
                $request->user()->id
            )
        ]);
    }

    public function document(
        Request $request,
        FeedbackSupportAttachment $attachment
    ){
        $attachment->loadMissing(
            'feedbackSupport'
        );

        $this->ownership(
            $request,
            $attachment->feedbackSupport
        );

        abort_unless(
            Storage::disk('local')
                ->exists($attachment->file_path),
            404
        );

        return Storage::disk('local')->download(
            $attachment->file_path,
            $attachment->original_name
        );
    }

    protected function ownership(
        Request $request,
        FeedbackSupport $ticket
    ): void{
        abort_unless(
            $ticket->member_id===
            $request->user()->member?->id,
            403
        );
    }
}