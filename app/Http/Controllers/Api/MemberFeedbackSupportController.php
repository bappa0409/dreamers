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

        $validated=$request->validate([
            'search'=>'nullable|string|max:150',
            'status'=>'nullable|in:submitted,under_review,assigned,in_progress,resolved,closed,rejected,cancelled',
            'type'=>'nullable|in:feedback,support_request,complaint,suggestion,service_issue,other',
            'priority'=>'nullable|in:low,normal,high,urgent',
            'per_page'=>'nullable|integer|min:5|max:50',
        ]);

        $base=$member->feedbackSupports();

        $summary=(clone $base)
            ->selectRaw("
                COUNT(*) AS total,
                SUM(CASE WHEN status IN ('submitted','under_review','assigned','in_progress') THEN 1 ELSE 0 END) AS open_count,
                SUM(CASE WHEN priority='urgent' AND status NOT IN ('closed','cancelled','rejected') THEN 1 ELSE 0 END) AS urgent,
                SUM(CASE WHEN status='resolved' THEN 1 ELSE 0 END) AS resolved,
                SUM(CASE WHEN status='closed' THEN 1 ELSE 0 END) AS closed_count
            ")
            ->first();

        $tickets=$base
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
            ->when(
                $validated['status']??null,
                fn($q,$status)=>$q->where('status',$status)
            )
            ->when(
                $validated['type']??null,
                fn($q,$type)=>$q->where('type',$type)
            )
            ->when(
                $validated['priority']??null,
                fn($q,$priority)=>$q->where('priority',$priority)
            )
            ->when(
                $validated['search']??null,
                function($q,$search){
                    $search=trim($search);

                    $q->where(function($q)use($search){
                        $q->where('ticket_no','like',"%{$search}%")
                            ->orWhere('subject','like',"%{$search}%")
                            ->orWhere('description','like',"%{$search}%");
                    });
                }
            )
            ->latest('id')
            ->paginate($validated['per_page']??10)
            ->withQueryString();

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

                'tickets'=>$tickets,

                'summary'=>[
                    'total'=>(int)($summary->total??0),
                    'open'=>(int)($summary->open_count??0),
                    'urgent'=>(int)($summary->urgent??0),
                    'resolved'=>(int)($summary->resolved??0),
                    'closed'=>(int)($summary->closed_count??0),
                    'resolved_closed'=>(int)($summary->resolved??0)+(int)($summary->closed_count??0),
                ],
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