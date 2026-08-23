<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FeedbackSupport;
use App\Models\FeedbackSupportAttachment;
use App\Models\FeedbackSupportCategory;
use App\Models\Member;
use App\Models\User;
use App\Services\FeedbackSupportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FeedbackSupportController extends Controller
{
    public function __construct(
        protected FeedbackSupportService $service
    ){}

    public function statistics()
    {
        return response()->json([
            'success'=>true,
            'data'=>$this->service->statistics()
        ]);
    }

    public function options()
    {
        return response()->json([
            'success'=>true,
            'data'=>[
                'categories'=>FeedbackSupportCategory::query()
                    ->where('is_active',true)
                    ->orderBy('sort_order')
                    ->get(['id','name']),

                'members'=>Member::query()
                    ->select(['id','user_id','member_code'])
                    ->with('user:id,name')
                    ->whereNotIn(
                        'status',
                        ['rejected','exited','deceased']
                    )
                    ->limit(50)
                    ->get(),

                'users'=>User::query()
                    ->where('is_active',true)
                    ->orderBy('name')
                    ->get(['id','name','email'])
            ]
        ]);
    }

    public function index(Request $request)
    {
        $data=$request->validate([
            'search'=>'nullable|string|max:150',
            'status'=>'nullable|string|max:40',
            'type'=>'nullable|in:feedback,support_request,complaint,suggestion,service_issue,other',
            'priority'=>'nullable|in:low,normal,high,urgent',
            'per_page'=>'nullable|integer|min:5|max:100'
        ]);

        $query=FeedbackSupport::query()->with([
            'category:id,name',
            'member:id,user_id,member_code',
            'member.user:id,name',
            'assignee:id,name'
        ]);

        if(
            !$request->user()
                ->hasPermission('FeedbackSupport.confidential')
        ){
            $query->where('is_confidential',false);
        }

        $query
            ->when(
                $data['status']??null,
                fn($q,$v)=>$q->where('status',$v)
            )
            ->when(
                $data['type']??null,
                fn($q,$v)=>$q->where('type',$v)
            )
            ->when(
                $data['priority']??null,
                fn($q,$v)=>$q->where('priority',$v)
            )
            ->when(
                $data['search']??null,
                function($q,$search){
                    $q->where(function($q)use($search){
                        $q->where(
                            'ticket_no',
                            'like',
                            "%{$search}%"
                        )
                        ->orWhere(
                            'subject',
                            'like',
                            "%{$search}%"
                        )
                        ->orWhereHas(
                            'member',
                            fn($m)=>$m
                                ->where(
                                    'member_code',
                                    'like',
                                    "%{$search}%"
                                )
                                ->orWhereHas(
                                    'user',
                                    fn($u)=>$u->where(
                                        'name',
                                        'like',
                                        "%{$search}%"
                                    )
                                )
                        );
                    });
                }
            )
            ->latest('id');

        return response()->json([
            'success'=>true,
            'data'=>$query->paginate(
                $data['per_page']??15
            )
        ]);
    }

    public function store(Request $request)
    {
        $data=$this->rules($request);

        return response()->json([
            'success'=>true,
            'message'=>'Feedback & Support request created.',
            'data'=>$this->service->create(
                Member::findOrFail(
                    $data['member_id']
                ),
                $data,
                $request->user()->id
            )
        ],201);
    }

    public function show(
        Request $request,
        FeedbackSupport $feedbackSupport
    ){
        $this->confidential(
            $request,
            $feedbackSupport
        );

        return response()->json([
            'success'=>true,
            'data'=>$feedbackSupport->load([
                'category',
                'member.user',
                'assignee:id,name,email',
                'assigner:id,name',
                'attachments.uploader:id,name',
                'updates.user:id,name',
                'histories.user:id,name'
            ])
        ]);
    }

    public function review(
        Request $request,
        FeedbackSupport $feedbackSupport
    ){
        $this->confidential($request,$feedbackSupport);

        $data=$request->validate([
            'note'=>'nullable|string|max:5000'
        ]);

        return response()->json([
            'success'=>true,
            'message'=>'Request moved to review.',
            'data'=>$this->service->review(
                $feedbackSupport,
                $data['note']??null,
                $request->user()->id
            )
        ]);
    }

    public function assign(
        Request $request,
        FeedbackSupport $feedbackSupport
    ){
        $this->confidential($request,$feedbackSupport);

        $data=$request->validate([
            'assigned_to'=>'required|exists:users,id',
            'note'=>'nullable|string|max:5000'
        ]);

        return response()->json([
            'success'=>true,
            'message'=>'Feedback & Support request assigned.',
            'data'=>$this->service->assign(
                $feedbackSupport,
                User::findOrFail($data['assigned_to']),
                $data['note']??null,
                $request->user()->id
            )
        ]);
    }

    public function progress(
        Request $request,
        FeedbackSupport $feedbackSupport
    ){
        $this->confidential($request,$feedbackSupport);

        $data=$request->validate([
            'note'=>'nullable|string|max:5000'
        ]);

        return response()->json([
            'success'=>true,
            'message'=>'Request marked in progress.',
            'data'=>$this->service->startProgress(
                $feedbackSupport,
                $data['note']??null,
                $request->user()->id
            )
        ]);
    }

    public function internalNote(
        Request $request,
        FeedbackSupport $feedbackSupport
    ){
        $this->confidential($request,$feedbackSupport);

        $data=$request->validate([
            'message'=>'required|string|max:10000'
        ]);

        return response()->json([
            'success'=>true,
            'message'=>'Internal note added.',
            'data'=>$this->service->internalNote(
                $feedbackSupport,
                $data['message'],
                $request->user()->id
            )
        ],201);
    }

    public function response(
        Request $request,
        FeedbackSupport $feedbackSupport
    ){
        $this->confidential($request,$feedbackSupport);

        $data=$request->validate([
            'message'=>'required|string|max:10000'
        ]);

        return response()->json([
            'success'=>true,
            'message'=>'Support response added.',
            'data'=>$this->service->supportResponse(
                $feedbackSupport,
                $data['message'],
                $request->user()->id
            )
        ],201);
    }

    public function resolve(
        Request $request,
        FeedbackSupport $feedbackSupport
    ){
        $this->confidential($request,$feedbackSupport);

        $data=$request->validate([
            'resolution'=>'required|string|max:10000'
        ]);

        return response()->json([
            'success'=>true,
            'message'=>'Feedback & Support request resolved.',
            'data'=>$this->service->resolve(
                $feedbackSupport,
                $data['resolution'],
                $request->user()->id
            )
        ]);
    }

    public function close(
        Request $request,
        FeedbackSupport $feedbackSupport
    ){
        $this->confidential($request,$feedbackSupport);

        $data=$request->validate([
            'note'=>'nullable|string|max:5000'
        ]);

        return response()->json([
            'success'=>true,
            'message'=>'Feedback & Support request closed.',
            'data'=>$this->service->close(
                $feedbackSupport,
                $data['note']??null,
                $request->user()->id
            )
        ]);
    }

    public function document(
        Request $request,
        FeedbackSupportAttachment $attachment
    ){
        $attachment->loadMissing('feedbackSupport');

        $this->confidential(
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

    protected function confidential(
        Request $request,
        FeedbackSupport $ticket
    ): void{
        if(
            $ticket->is_confidential&&
            !$request->user()
                ->hasPermission(
                    'FeedbackSupport.confidential'
                )
        ){
            abort(403);
        }
    }

    protected function rules(Request $request): array
    {
        return $request->validate([
            'member_id'=>
                'required|integer|exists:members,id',

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
    }
}