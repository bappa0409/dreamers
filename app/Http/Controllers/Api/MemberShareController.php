<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Models\MemberShare;
use App\Services\MemberShareService;
use Illuminate\Http\Request;

class MemberShareController extends Controller
{
    public function __construct(
        protected MemberShareService $memberShareService
    ){}

    public function index(Member $member)
    {
        return response()->json([
            'success'=>true,
            'data'=>[
                'summary'=>$this->memberShareService
                    ->summary($member),
                'shares'=>$this->memberShareService
                    ->memberShares($member),
            ],
        ]);
    }

    public function store(
        Request $request,
        Member $member
    ){
        $validated=$this->validatePurchase($request);

        $share=$this->memberShareService->issue(
            $member,
            $validated,
            $request->user()->id
        );

        return response()->json([
            'success'=>true,
            'message'=>'Share purchase submitted for verification.',
            'data'=>$share,
        ],201);
    }

    public function myShares(Request $request)
    {
        $member=$request->user()->member;

        abort_unless(
            $member&&$member->status==='active',
            403,
            'Active membership is required.'
        );

        abort_unless(
            filter_var(
                setting('share_enabled',false),
                FILTER_VALIDATE_BOOLEAN
            ),
            403,
            'Share purchasing is currently disabled.'
        );

        return response()->json([
            'success'=>true,
            'data'=>[
                'summary'=>$this->memberShareService
                    ->summary($member),
                'shares'=>$this->memberShareService
                    ->memberShares($member),
                'settings'=>[
                    'share_enabled'=>true,
                    'default_share_value'=>(float)setting(
                        'default_share_value',
                        0
                    ),
                    'minimum_share_purchase_amount'=>(float)setting(
                        'minimum_share_purchase_amount',
                        0
                    ),
                ],
            ],
        ]);
    }

    public function purchase(Request $request)
    {
        $member=$request->user()->member;

        abort_unless(
            $member&&$member->status==='active',
            403,
            'Active membership is required.'
        );

        $validated=$this->validatePurchase($request);

        $share=$this->memberShareService->issue(
            $member,
            $validated,
            $request->user()->id
        );

        return response()->json([
            'success'=>true,
            'message'=>'Share purchase submitted for verification.',
            'data'=>$share,
        ],201);
    }

    public function verify(
        Request $request,
        MemberShare $memberShare
    ){
        $validated=$request->validate([
            'note'=>'nullable|string|max:3000',
        ]);

        $share=$this->memberShareService->verify(
            $memberShare,
            $request->user()->id,
            $validated['note']??null
        );

        return response()->json([
            'success'=>true,
            'message'=>'Share purchase verified successfully.',
            'data'=>$share,
        ]);
    }

    public function reject(
        Request $request,
        MemberShare $memberShare
    ){
        $validated=$request->validate([
            'note'=>'required|string|max:3000',
        ]);

        $share=$this->memberShareService->reject(
            $memberShare,
            $request->user()->id,
            $validated['note']
        );

        return response()->json([
            'success'=>true,
            'message'=>'Share purchase rejected.',
            'data'=>$share,
        ]);
    }

    protected function validatePurchase(
        Request $request
    ): array{
        return $request->validate([
            'purchase_amount'=>'required|numeric|min:0.01',
            'payment_method'=>'required|in:cash,bank,mobile_banking,online',
            'transaction_reference'=>'nullable|string|max:255',
            'notes'=>'nullable|string|max:3000',
        ]);
    }

    public function adminIndex(Request $request)
{
    $validated=$request->validate([
        'status'=>'nullable|in:pending,active,rejected,cancelled,transferred,retired',
        'search'=>'nullable|string|max:150',
        'per_page'=>'nullable|integer|min:5|max:100',
    ]);

    $query=MemberShare::query()
        ->with([
            'member.user:id,name,email,mobile',
            'creator:id,name,email',
            'verifier:id,name,email',
        ])
        ->latest('id');

    if(!empty($validated['status'])){
        $query->where(
            'status',
            $validated['status']
        );
    }

    if(!empty($validated['search'])){
        $search=trim($validated['search']);

        $query->where(function($query)use($search){
            $query
                ->where(
                    'share_no',
                    'like',
                    "%{$search}%"
                )
                ->orWhere(
                    'transaction_reference',
                    'like',
                    "%{$search}%"
                )
                ->orWhereHas(
                    'member',
                    function($memberQuery)use($search){
                        $memberQuery
                            ->where(
                                'member_code',
                                'like',
                                "%{$search}%"
                            )
                            ->orWhereHas(
                                'user',
                                function($userQuery)use($search){
                                    $userQuery
                                        ->where(
                                            'name',
                                            'like',
                                            "%{$search}%"
                                        )
                                        ->orWhere(
                                            'email',
                                            'like',
                                            "%{$search}%"
                                        )
                                        ->orWhere(
                                            'mobile',
                                            'like',
                                            "%{$search}%"
                                        );
                                }
                            );
                    }
                );
        });
    }

    return response()->json([
        'success'=>true,
        'data'=>$query->paginate(
            min(
                (int)($validated['per_page']??20),
                100
            )
        ),
    ]);
}

public function show(MemberShare $memberShare)
{
    return response()->json([
        'success'=>true,
        'data'=>$memberShare->load([
            'member.user:id,name,email,mobile',
            'creator:id,name,email',
            'verifier:id,name,email',
            'financeTransaction.entries.account',
        ]),
    ]);
}
}