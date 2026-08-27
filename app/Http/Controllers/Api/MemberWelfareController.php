<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WelfareDocument;
use App\Models\WelfareFund;
use App\Models\WelfareRequest;
use App\Services\WelfareService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MemberWelfareController extends Controller
{
    public function __construct(
        protected WelfareService $service
    ){}

    public function index(Request $request)
    {
        $member=$request->user()->member;

        abort_unless($member,403);

        $validated=$request->validate([
            'search'=>'nullable|string|max:150',
            'status'=>'nullable|in:submitted,under_review,approved,rejected,completed,cancelled,reversed',
            'per_page'=>'nullable|integer|min:5|max:50',
        ]);

        $today=now()->toDateString();

        $base=$member->welfareRequests();

        $summary=(clone $base)
            ->selectRaw("
                COUNT(*) AS total,
                SUM(CASE WHEN status IN ('submitted','under_review','approved') THEN 1 ELSE 0 END) AS pending,
                SUM(CASE WHEN status='completed' THEN 1 ELSE 0 END) AS completed,
                COALESCE(SUM(CASE WHEN status IN ('approved','completed') THEN approved_amount ELSE 0 END),0) AS total_approved
            ")
            ->first();

        $requests=$base
            ->with([
                'fund:id,code,name',
                'documents:id,welfare_request_id,document_type,original_name',
                'histories.user:id,name'
            ])
            ->when(
                $validated['status']??null,
                fn($q,$status)=>$q->where('status',$status)
            )
            ->when(
                $validated['search']??null,
                function($q,$search){
                    $search=trim($search);

                    $q->where(function($q)use($search){
                        $q->where('request_no','like',"%{$search}%")
                            ->orWhere('reason','like',"%{$search}%")
                            ->orWhereHas(
                                'fund',
                                fn($fund)=>$fund->where('name','like',"%{$search}%")
                            );
                    });
                }
            )
            ->latest('id')
            ->paginate($validated['per_page']??10)
            ->withQueryString();

        return response()->json([
            'success'=>true,
            'data'=>[
                'funds'=>WelfareFund::query()
                    ->where('is_active',true)
                    ->where(function($q)use($today){
                        $q->whereNull('start_date')
                            ->orWhereDate('start_date','<=',$today);
                    })
                    ->where(function($q)use($today){
                        $q->whereNull('end_date')
                            ->orWhereDate('end_date','>=',$today);
                    })
                    ->orderBy('name')
                    ->get([
                        'id',
                        'code',
                        'name',
                        'description'
                    ]),

                'requests'=>$requests,

                'summary'=>[
                    'total'=>(int)($summary->total??0),
                    'pending'=>(int)($summary->pending??0),
                    'completed'=>(int)($summary->completed??0),
                    'total_approved'=>round((float)($summary->total_approved??0),2),
                ],
            ]
        ]);
    }

    public function store(Request $request)
    {
        $member=$request->user()->member;

        abort_unless(
            $member&&$member->status==='active',
            403
        );

        $data=$request->validate([
            'welfare_fund_id'=>
                'required|integer|exists:welfare_funds,id',

            'assistance_type'=>
                'required|in:illness,accident,death,natural_disaster,emergency,financial_hardship,other',

            'requested_amount'=>
                'required|numeric|min:0.01',

            'reason'=>'required|string|max:5000',
            'notes'=>'nullable|string|max:5000'
        ]);

        $data['member_id']=$member->id;
        $data['request_date']=now()->toDateString();

        return response()->json([
            'success'=>true,
            'message'=>'Welfare request submitted.',
            'data'=>$this->service->createRequest(
                $data,
                $request->user()->id
            )
        ],201);
    }

    public function upload(
        Request $request,
        WelfareRequest $welfareRequest
    ){
        abort_unless(
            $welfareRequest->member_id===
            $request->user()->member?->id,
            403
        );

        abort_if(
            in_array(
                $welfareRequest->status,
                ['rejected','completed','cancelled','reversed'],
                true
            ),
            422
        );

        $data=$request->validate([
            'document_type'=>
                'required|string|max:80',

            'file'=>
                'required|file|mimes:pdf,jpg,jpeg,png,webp|max:10240'
        ]);

        return response()->json([
            'success'=>true,
            'message'=>'Document uploaded.',
            'data'=>$this->service->uploadDocument(
                $welfareRequest,
                $request->file('file'),
                $data['document_type'],
                $request->user()->id
            )
        ],201);
    }

    public function cancel(
        Request $request,
        WelfareRequest $welfareRequest
    ){
        abort_unless(
            $welfareRequest->member_id===
            $request->user()->member?->id,
            403
        );

        return response()->json([
            'success'=>true,
            'message'=>'Request cancelled.',
            'data'=>$this->service->cancel(
                $welfareRequest,
                $request->user()->id
            )
        ]);
    }

    public function document(
        Request $request,
        WelfareDocument $document
    ){
        $document->loadMissing('request');

        abort_unless(
            $document->request?->member_id===
            $request->user()->member?->id,
            403
        );

        abort_unless(
            Storage::disk('local')
                ->exists($document->file_path),
            404
        );

        return Storage::disk('local')->download(
            $document->file_path,
            $document->original_name
        );
    }
}