<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Models\MemberShare;
use App\Services\ApprovalService;
use App\Services\MemberShareService;
use App\Services\ReceiptPdfService;
use Illuminate\Http\Request;

class MemberShareController extends Controller
{
    public function __construct(
        protected MemberShareService $memberShareService,
        protected ApprovalService $approvalService,
        protected ReceiptPdfService $receiptPdfService
    ) {}

    public function index(Member $member)
    {
        return response()->json([
            'success' => true,
            'data' => [
                'summary' => $this->memberShareService
                    ->summary($member),
                'shares' => $this->memberShareService
                    ->memberShares($member),
            ],
        ]);
    }

    public function store(
        Request $request,
        Member $member
    ) {
        $validated = $this->validatePurchase($request);

        // Only this admin-only, Finance.create-gated endpoint may flag a
        // share as the member's initial share (exempt from the fixed share
        // price check). The member self-purchase endpoint below never
        // reads this flag from user input.
        $validated['is_initial'] = $request->boolean('is_initial');

        $share = $this->memberShareService->issue(
            $member,
            $validated,
            $request->user()->id
        );

        $approval = $this->approvalService->createRequest(
            $share,
            'MemberShare',
            'request',
            $request->user()->id,
            'New share purchase requires approval.'
        );

        return response()->json([
            'success' => true,
            'message' => 'Share purchase submitted for verification.',
            'data' => $share,
            'approval' => $approval,
        ], 201);
    }

    public function myShares(Request $request)
    {
        $member = $request->user()->member;

        abort_unless(
            $member && $member->status === 'active',
            403,
            'Active membership is required.'
        );

        $validated = $request->validate([
            'status' => 'nullable|in:pending,active,rejected,cancelled,transferred,retired',
            'search' => 'nullable|string|max:150',
            'per_page' => 'nullable|integer|min:5|max:50',
        ]);

        $shareEnabled = filter_var(
            setting('share_enabled', false),
            FILTER_VALIDATE_BOOLEAN
        );

        return response()->json([
            'success' => true,
            'data' => [
                // Summary intentionally remains global, not status-filtered.
                'summary' => $this->memberShareService->summary($member),

                // Only one page of history is transferred to the app.
                'shares' => $this->memberShareService->memberShares($member),

                'settings' => [
                    'share_enabled' => $shareEnabled,
                    'default_share_value' => round(
                        (float) setting('default_share_value', 0),
                        2
                    ),
                    'minimum_share_purchase_amount' => round(
                        (float) setting(
                            'minimum_share_purchase_amount',
                            0
                        ),
                        2
                    ),
                ],
            ],
        ]);
    }

    public function purchase(Request $request)
    {
        $member = $request->user()->member;

        abort_unless(
            $member && $member->status === 'active',
            403,
            'Active membership is required.'
        );

        $validated = $this->validatePurchase($request);

        $share = $this->memberShareService->issue(
            $member,
            $validated,
            $request->user()->id
        );

        $approval = $this->approvalService->createRequest(
            $share,
            'MemberShare',
            'request',
            $request->user()->id,
            'New share purchase requires approval.'
        );

        return response()->json([
            'success' => true,
            'message' => 'Share purchase submitted for verification.',
            'data' => $share,
            'approval' => $approval,
        ], 201);
    }

    public function verify(
        Request $request,
        MemberShare $memberShare
    ) {
        $validated = $request->validate([
            'note' => 'nullable|string|max:3000',
        ]);

        $approvalRequest = $this->approvalService->findPendingRequestFor(
            $memberShare,
            'MemberShare',
            'request'
        );

        $this->approvalService->approve(
            $approvalRequest,
            $request->user()->id,
            $validated['note'] ?? null,
            $validated['note'] ? ['note' => $validated['note']] : []
        );

        return response()->json([
            'success' => true,
            'message' => 'Share verification step completed successfully.',
            'data' => $memberShare->fresh([
                'member.user',
                'creator',
                'verifier',
                'financeTransaction.entries.account',
            ]),
        ]);
    }

    public function receipt(Request $request, MemberShare $memberShare)
    {
        $member = $request->user()->member;

        if (!$request->user()->hasPermission('Finance.view')) {
            abort_unless(
                $member && $member->status === 'active',
                403,
                'Active membership is required.'
            );

            abort_unless(
                $memberShare->member_id === $member->id,
                404,
                'Share purchase not found.'
            );
        }

        abort_unless(
            $memberShare->status === 'active',
            422,
            'Receipt is only available for verified share purchases.'
        );

        $memberShare->load([
            'member:id,user_id,member_code',
            'member.user:id,name,email,mobile',
            'verifier:id,name',
        ]);

        return $this->receiptPdfService->download(
            $this->receiptPdfService->branding(),
            $memberShare->toReceiptData(),
            'share-purchase-'.$memberShare->share_no
        );
    }

    public function reject(
        Request $request,
        MemberShare $memberShare
    ) {
        $validated = $request->validate([
            'note' => 'required|string|max:3000',
        ]);

        $approvalRequest = $this->approvalService->findPendingRequestFor(
            $memberShare,
            'MemberShare',
            'request'
        );

        $this->approvalService->reject(
            $approvalRequest,
            $request->user()->id,
            $validated['note']
        );

        return response()->json([
            'success' => true,
            'message' => 'Share purchase rejected.',
            'data' => $memberShare->fresh([
                'member.user',
                'creator',
                'verifier',
            ]),
        ]);
    }

    protected function validatePurchase(
        Request $request
    ): array {
        return $request->validate([
            'purchase_amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|in:cash,bank,mobile_banking,online',
            'transaction_reference' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:3000',
        ]);
    }

    public function adminIndex(Request $request)
    {
        $validated = $request->validate([
            'status' => 'nullable|in:pending,active,rejected,cancelled,transferred,retired',
            'search' => 'nullable|string|max:150',
            'per_page' => 'nullable|integer|min:5|max:100',
        ]);

        $baseQuery = MemberShare::query()
            ->when(
                !empty($validated['search']),
                function ($query) use ($validated) {
                    $search = trim($validated['search']);

                    $query->where(function ($q) use ($search) {
                        $q->where('share_no', 'like', "%{$search}%")
                            ->orWhere('transaction_reference', 'like', "%{$search}%")
                            ->orWhereHas('member', function ($memberQuery) use ($search) {
                                $memberQuery
                                    ->where('member_code', 'like', "%{$search}%")
                                    ->orWhereHas('user', function ($userQuery) use ($search) {
                                        $userQuery
                                            ->where('name', 'like', "%{$search}%")
                                            ->orWhere('email', 'like', "%{$search}%")
                                            ->orWhere('mobile', 'like', "%{$search}%");
                                    });
                            });
                    });
                }
            );

        $summary = (clone $baseQuery)
            ->selectRaw("
            COUNT(*) total,
            SUM(CASE WHEN status='pending' THEN 1 ELSE 0 END) pending,
            SUM(CASE WHEN status='active' THEN 1 ELSE 0 END) active,
            SUM(CASE WHEN status='rejected' THEN 1 ELSE 0 END) rejected,
            SUM(CASE WHEN status='cancelled' THEN 1 ELSE 0 END) cancelled,
            SUM(CASE WHEN status='transferred' THEN 1 ELSE 0 END) transferred,
            SUM(CASE WHEN status='retired' THEN 1 ELSE 0 END) retired,
            COALESCE(SUM(CASE WHEN status='active' THEN purchase_amount ELSE 0 END),0) active_value,
            COALESCE(SUM(CASE WHEN status='pending' THEN purchase_amount ELSE 0 END),0) pending_value
        ")
            ->first();

        $query = (clone $baseQuery)
            ->with([
                'member:id,user_id,member_code,status',
                'member.user:id,name,email,mobile',
                'creator:id,name,email',
                'verifier:id,name,email',
            ])
            ->when(
                !empty($validated['status']),
                fn($q) => $q->where(
                    'status',
                    $validated['status']
                )
            )
            ->latest('id');

        $perPage = min(
            max(
                (int)($validated['per_page'] ?? 20),
                5
            ),
            100
        );

        return response()->json([
            'success' => true,
            'data' => $query
                ->paginate($perPage)
                ->withQueryString(),
            'summary' => [
                'total' => (int)($summary->total ?? 0),
                'pending' => (int)($summary->pending ?? 0),
                'active' => (int)($summary->active ?? 0),
                'rejected' => (int)($summary->rejected ?? 0),
                'cancelled' => (int)($summary->cancelled ?? 0),
                'transferred' => (int)($summary->transferred ?? 0),
                'retired' => (int)($summary->retired ?? 0),
                'active_value' => round(
                    (float)($summary->active_value ?? 0),
                    2
                ),
                'pending_value' => round(
                    (float)($summary->pending_value ?? 0),
                    2
                ),
            ],
        ]);
    }

    public function show(MemberShare $memberShare)
    {
        return response()->json([
            'success' => true,
            'data' => $memberShare->load([
                'member.user:id,name,email,mobile',
                'creator:id,name,email',
                'verifier:id,name,email',
                'financeTransaction.entries.account',
            ]),
        ]);
    }
}