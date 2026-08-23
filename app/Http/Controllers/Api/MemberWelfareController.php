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

        return response()->json([
            'success'=>true,

            'data'=>[
                'funds'=>WelfareFund::query()
                    ->where('is_active',true)
                    ->get([
                        'id',
                        'code',
                        'name',
                        'description'
                    ]),

                'requests'=>$member
                    ->welfareRequests()
                    ->with([
                        'fund:id,code,name',
                        'documents:id,welfare_request_id,document_type,original_name',
                        'histories.user:id,name'
                    ])
                    ->latest('id')
                    ->get()
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
                ['completed','cancelled','reversed'],
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