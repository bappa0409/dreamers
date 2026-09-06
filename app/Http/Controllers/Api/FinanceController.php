<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Services\ApprovalService;
use App\Services\JournalService;
use App\Services\ReceiptPdfService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FinanceController extends Controller
{
    public function __construct(
        private JournalService $journalService,
        private ApprovalService $approvalService,
        private ReceiptPdfService $receiptPdfService
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

    public function voucher(Transaction $transaction)
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

        return $this->receiptPdfService->downloadJournalVoucher(
            $this->receiptPdfService->branding(),
            [
                'title'=>'Journal Voucher',
                'voucher_no'=>$transaction->transaction_no,
                'status'=>ucfirst($transaction->status),
                'date'=>$transaction->transaction_date
                    ?->format('d M, Y'),
                'type'=>ucfirst((string)$transaction->type),
                'source'=>$transaction->source_module
                    ?ucfirst(str_replace('_',' ',$transaction->source_module))
                    :'—',
                'posted_at'=>$transaction->posted_at
                    ?->format('d M, Y h:i A'),
                'reference'=>$this->resolveReferenceNo($transaction),
                'description'=>$transaction->description,
                'entries'=>$transaction->entries->map(fn($entry)=>[
                    'account'=>trim(
                        ($entry->account->code??'')
                        .' - '
                        .($entry->account->name??'')
                    ),
                    'description'=>$entry->description,
                    'debit'=>(float)$entry->debit,
                    'credit'=>(float)$entry->credit,
                ])->all(),
                'total_debit'=>(float)$transaction->total_debit,
                'total_credit'=>(float)$transaction->total_credit,
                'prepared_by'=>$transaction->creator?->name??'System',
                'authorized_by'=>$transaction->poster?->name??'System',
            ],
            'journal-voucher-'.$transaction->transaction_no
        );
    }

    /**
     * Best-effort human-readable reference number for the record this
     * journal came from (loan_no, share_no, payment_no, etc). Any
     * failure here just hides the "Reference" line on the voucher -
     * it never breaks the download itself.
     */
    protected function resolveReferenceNo(Transaction $transaction): ?string
    {
        if(
            !$transaction->reference_type||
            !$transaction->reference_id
        ){
            return null;
        }

        try{
            $model=$transaction->reference_type::find(
                $transaction->reference_id
            );

            if(!$model){
                return null;
            }

            foreach([
                'loan_no',
                'share_no',
                'payment_no',
                'charge_no',
                'asset_no',
                'invoice_no',
                'voucher_no',
                'reference_no',
                'code',
            ] as $field){
                if(!empty($model->{$field}??null)){
                    return(string)$model->{$field};
                }
            }
        }catch(\Throwable){
            return null;
        }

        return null;
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