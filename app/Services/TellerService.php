<?php

namespace App\Services;

use App\Models\TellerTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TellerService
{
    public function __construct(
        private FinancePostingService $financePostingService
    ) {}

    public function receive(array $data, int $tellerId): TellerTransaction
    {
        return DB::transaction(function () use ($data, $tellerId) {

            $transaction = $this->createTransaction(
                data: $data,
                tellerId: $tellerId,
                type: 'receive'
            );

            $financeTransaction = $this->financePostingService
                ->postTellerReceive(
                    amount: (float) $transaction->amount,
                    userId: $tellerId,
                    description: $transaction->description
                        ?: $transaction->purpose
                        ?: 'Teller cash receive',
                    referenceType: TellerTransaction::class,
                    referenceId: $transaction->id
                );

            $transaction->update([
                'finance_transaction_id' => $financeTransaction->id,
            ]);

            return $transaction->fresh()
                ->load('member', 'financeTransaction');
        });
    }

    public function payment(array $data, int $tellerId): TellerTransaction
    {
        return DB::transaction(function () use ($data, $tellerId) {

            $balance = $this->balance($tellerId);

            if ($data['amount'] > $balance['balance']) {
                throw ValidationException::withMessages([
                    'amount' => 'Insufficient teller balance.',
                ]);
            }

            $transaction = $this->createTransaction(
                data: $data,
                tellerId: $tellerId,
                type: 'payment'
            );

            $financeTransaction = $this->financePostingService
                ->postTellerPayment(
                    amount: (float) $transaction->amount,
                    userId: $tellerId,
                    description: $transaction->description
                        ?: $transaction->purpose
                        ?: 'Teller cash payment',
                    referenceType: TellerTransaction::class,
                    referenceId: $transaction->id
                );

            $transaction->update([
                'finance_transaction_id' => $financeTransaction->id,
            ]);

            return $transaction->fresh()
                ->load('member', 'financeTransaction');
        });
    }

    private function createTransaction(
        array $data,
        int $tellerId,
        string $type
    ): TellerTransaction {

        $lastTransaction = TellerTransaction::latest('id')->first();

        $nextNumber = $lastTransaction
            ? $lastTransaction->id + 1
            : 1;

        $transactionNo = 'TELLER-' . str_pad(
            $nextNumber,
            6,
            '0',
            STR_PAD_LEFT
        );

        return TellerTransaction::create([
            'transaction_no' => $transactionNo,
            'teller_id' => $tellerId,
            'member_id' => $data['member_id'] ?? null,
            'type' => $type,
            'amount' => $data['amount'],
            'purpose' => $data['purpose'] ?? null,
            'description' => $data['description'] ?? null,
            'transaction_date' => $data['transaction_date']
                ?? now()->toDateString(),
            'status' => 'completed',
        ]);
    }

    public function balance(int $tellerId): array
    {
        $receive = TellerTransaction::where('teller_id', $tellerId)
            ->where('type', 'receive')
            ->where('status', 'completed')
            ->sum('amount');

        $payment = TellerTransaction::where('teller_id', $tellerId)
            ->where('type', 'payment')
            ->where('status', 'completed')
            ->sum('amount');

        return [
            'total_received' => (float) $receive,
            'total_paid' => (float) $payment,
            'balance' => (float) ($receive - $payment),
        ];
    }
}