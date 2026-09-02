<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Services\ApprovalService;
use App\Services\JournalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FinanceController extends Controller
{
    public function __construct(
        private JournalService $journalService,
        private ApprovalService $approvalService
    ){}

    public function index(Request $request)
    {
        $validated=$request->validate([
            'search'=>'nullable|string|max:150',
            'type'=>'nullable|string|max:50',
            'source_module'=>'nullable|string|max:100',
            'status'=>'nullable|in:draft,posted,cancelled',
            'from'=>'nullable|date',
            'to'=>'nullable|date|after_or_equal:from',
            'per_page'=>'nullable|integer|min:5|max:100',
        ]);

        return response()->json([
            'success'=>true,
            'data'=>$this->journalService->paginate(
                $validated
            ),
        ]);
    }

    public function show(Transaction $transaction)
    {
        $transaction->load([
            'creator:id,name,email',
            'poster:id,name,email',
            'entries.account:id,code,name,type,sub_type',
        ])->loadSum(
            'entries as total_debit',
            'debit'
        )->loadSum(
            'entries as total_credit',
            'credit'
        );

        return response()->json([
            'success'=>true,
            'data'=>$transaction,
        ]);
    }

    public function options()
    {
        return response()->json([
            'success'=>true,
            'data'=>$this->journalService->options(),
        ]);
    }

    public function summary(Request $request)
    {
        $validated=$request->validate([
            'from'=>'nullable|date',
            'to'=>'nullable|date|after_or_equal:from',
        ]);

        return response()->json([
            'success'=>true,
            'data'=>$this->journalService->summary(
                $validated
            ),
        ]);
    }

    public function store(Request $request)
    {
        $validated=$request->validate([
            'transaction_date'=>'required|date',
            'description'=>'required|string|max:3000',

            'entries'=>
                'required|array|min:2|max:100',

            'entries.*.account_id'=>
                'required|integer|exists:accounts,id',

            'entries.*.debit'=>
                'nullable|numeric|min:0|max:9999999999999.99',

            'entries.*.credit'=>
                'nullable|numeric|min:0|max:9999999999999.99',

            'entries.*.description'=>
                'nullable|string|max:500',
        ]);

        $transaction=DB::transaction(function()use($validated,$request){
            $transaction=$this->journalService
                ->createManual(
                    $validated,
                    $request->user()->id
                );

            $this->approvalService->createRequest(
                $transaction,
                'JournalEntry',
                'create',
                $request->user()->id,
                'New manual journal entry requires approval before posting.'
            );

            return $transaction;
        });

        return response()->json([
            'success'=>true,
            'message'=>'Journal entry recorded and sent for approval.',
            'data'=>$transaction,
        ],201);
    }

    public function reverse(
        Request $request,
        Transaction $transaction
    ){
        $validated=$request->validate([
            'transaction_date'=>'nullable|date',
            'reason'=>'required|string|min:3|max:1000',
        ]);

        $reversal=$this->journalService
            ->reverse(
                $transaction,
                $validated,
                $request->user()->id
            );

        return response()->json([
            'success'=>true,
            'message'=>'Journal reversed successfully.',
            'data'=>$reversal,
        ]);
    }
}