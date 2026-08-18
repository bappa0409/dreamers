<?php

namespace App\Services;

use App\Models\Investment;
use Illuminate\Support\Facades\DB;
use App\Services\ApprovalService;

class InvestmentService
{
    protected ApprovalService $approvalService;

    public function __construct(
        ApprovalService $approvalService
    ) {
        $this->approvalService = $approvalService;
    }

    public function create(array $data): Investment
    {
        return DB::transaction(function () use ($data) {

            $lastInvestment = Investment::latest('id')->first();

            $nextNumber = $lastInvestment
                ? $lastInvestment->id + 1
                : 1;

            $investmentNo = 'INV-' . str_pad(
                $nextNumber,
                6,
                '0',
                STR_PAD_LEFT
            );

            $investment = Investment::create([
                'investment_no' => $investmentNo,
                'member_id' => $data['member_id'],
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'amount' => $data['amount'],
                'expected_return' => $data['expected_return'] ?? 0,
                'investment_date' => $data['investment_date'],
                'maturity_date' => $data['maturity_date'] ?? null,
                'status' => $data['status'] ?? 'pending',
            ]);
            
            $this->approvalService->createRequest(
                $investment,
                'Investment',
                'create',
                auth()->id(),
                'New investment requires approval.'
            );

            return $investment;
        });
    }
}