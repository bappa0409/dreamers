<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Models\MemberSubscription;
use App\Models\SubscriptionDue;
use App\Models\SubscriptionPlan;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

class MemberSubscriptionAdminController extends Controller
{
    public function __construct(
        protected SubscriptionService $subscriptionService
    ) {}

    public function summary(Request $request)
    {
        $validated = $request->validate([
            'year' => 'nullable|integer|min:2000|max:2100',
            'month' => 'nullable|integer|min:1|max:12'
        ]);

        $year = (int)($validated['year'] ?? now()->year);
        $month = (int)($validated['month'] ?? now()->month);

        $row = SubscriptionDue::query()
            ->where('year', $year)
            ->where('month', $month)
            ->selectRaw("
            COALESCE(SUM(amount),0) total_due,
            COALESCE(SUM(paid_amount),0) total_paid,
            COUNT(*) total_records,
            SUM(CASE WHEN status='paid' THEN 1 ELSE 0 END) paid_count,
            SUM(CASE WHEN status='partial' THEN 1 ELSE 0 END) partial_count,
            SUM(CASE WHEN status IN ('unpaid','overdue') THEN 1 ELSE 0 END) unpaid_count
        ")
            ->first();

        $due = (float)$row->total_due;
        $paid = (float)$row->total_paid;

        return response()->json([
            'success' => true,
            'data' => [
                'total_due' => round($due, 2),
                'total_paid' => round($paid, 2),
                'outstanding' => round(
                    max($due - $paid, 0),
                    2
                ),
                'total_records' => (int)$row->total_records,
                'paid_count' => (int)$row->paid_count,
                'partial_count' => (int)$row->partial_count,
                'unpaid_count' => (int)$row->unpaid_count
            ]
        ]);
    }

    public function index(Request $request)
    {
        $validated=$request->validate([
            'year'=>'nullable|integer|min:2000|max:2100',
            'month'=>'nullable|integer|min:1|max:12',
            'search'=>'nullable|string|max:150',
            'status'=>'nullable|in:active,inactive',
            'per_page'=>'nullable|integer|min:5|max:100',
        ]);

        $year=(int)(
            $validated['year']
            ??now()->year
        );

        $month=(int)(
            $validated['month']
            ??now()->month
        );

        $search=trim(
            (string)($validated['search']??'')
        );

        $perPage=min(
            max(
                (int)($validated['per_page']??20),
                5
            ),
            100
        );

        $subscriptions=MemberSubscription::query()
            ->with([
                'member.user:id,name,email,mobile',
                'plan:id,name,amount,due_day',
                'dues'=>function($query)use(
                    $year,
                    $month
                ){
                    $query
                        ->where('year',$year)
                        ->where('month',$month)
                        ->select([
                            'id',
                            'member_subscription_id',
                            'year',
                            'month',
                            'base_amount',
                            'share_count',
                            'fine_amount',
                            'amount',
                            'paid_amount',
                            'due_date',
                            'fine_applied_at',
                            'status',
                            'finance_transaction_id',
                        ]);
                },
            ])
            ->when(
                $search!=='',
                function($query)use($search){
                    $query->whereHas(
                        'member',
                        function($member)use($search){
                            $member
                                ->where(
                                    'member_code',
                                    'like',
                                    "%{$search}%"
                                )
                                ->orWhereHas(
                                    'user',
                                    function($user)use($search){
                                        $user
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
                }
            )
            ->when(
                !empty($validated['status']),
                fn($query)=>$query->where(
                    'is_active',
                    $validated['status']==='active'
                )
            )
            ->latest('id')
            ->paginate($perPage)
            ->withQueryString();

        return response()->json([
            'success'=>true,
            'data'=>$subscriptions,
        ]);
    }

    public function members(Request $request)
    {
        $validated = $request->validate([
            'search' => 'nullable|string|max:150'
        ]);

        $search = trim(
            (string)($validated['search'] ?? '')
        );

        $members = Member::query()
            ->select([
                'id',
                'user_id',
                'member_code',
                'status'
            ])
            ->with([
                'user:id,name,email,mobile'
            ])
            ->where('status', 'active')
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where(
                        'member_code',
                        'like',
                        "%{$search}%"
                    )->orWhereHas(
                        'user',
                        function ($uq) use ($search) {
                            $uq->where(
                                'name',
                                'like',
                                "%{$search}%"
                            )->orWhere(
                                'email',
                                'like',
                                "%{$search}%"
                            )->orWhere(
                                'mobile',
                                'like',
                                "%{$search}%"
                            );
                        }
                    );
                });
            })
            ->orderBy('member_code')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $members
        ]);
    }

    public function store(Request $request)
    {
        $validated=$request->validate([
            'member_id'=>'required|integer|exists:members,id',
            'subscription_plan_id'=>'required|integer|exists:subscription_plans,id',
            'start_date'=>'required|date_format:Y-m-d',
        ]);

        $plan=SubscriptionPlan::query()
            ->whereKey($validated['subscription_plan_id'])
            ->where('is_active',true)
            ->first();

        if(!$plan){
            throw ValidationException::withMessages([
                'subscription_plan_id'=>[
                    'The selected subscription plan is inactive.'
                ],
            ]);
        }

        $member=Member::query()
            ->whereKey($validated['member_id'])
            ->where('status','active')
            ->first();

        if(!$member){
            throw ValidationException::withMessages([
                'member_id'=>[
                    'Subscription can only be assigned to an active member.'
                ],
            ]);
        }

        $startDate=Carbon::parse(
            $validated['start_date']
        )->startOfDay();

        $subscription=DB::transaction(function()use(
            $validated,
            $startDate
        ){
            $member=Member::query()
                ->whereKey($validated['member_id'])
                ->lockForUpdate()
                ->firstOrFail();

            if($member->status!=='active'){
                throw ValidationException::withMessages([
                    'member_id'=>[
                        'Subscription can only be assigned to an active member.'
                    ],
                ]);
            }

            $active=MemberSubscription::query()
                ->where(
                    'member_id',
                    $member->id
                )
                ->where(
                    'is_active',
                    true
                )
                ->lockForUpdate()
                ->first();

            if($active){
                if(
                    $startDate->lte(
                        $active->start_date->copy()->startOfDay()
                    )
                ){
                    throw ValidationException::withMessages([
                        'start_date'=>[
                            'New subscription start date must be after the current subscription start date.'
                        ],
                    ]);
                }

                $endDate=$startDate
                    ->copy()
                    ->subDay();

                $active->update([
                    'is_active'=>false,
                    'end_date'=>$endDate->toDateString(),
                ]);
            }

            return MemberSubscription::create([
                'member_id'=>$member->id,
                'subscription_plan_id'=>
                    $validated['subscription_plan_id'],
                'start_date'=>$startDate->toDateString(),
                'end_date'=>null,
                'is_active'=>true,
            ]);
        });

        return response()->json([
            'success'=>true,
            'message'=>'Subscription assigned successfully.',
            'data'=>$subscription->load([
                'member.user',
                'plan',
            ]),
        ],201);
    }

    public function deactivate(
    MemberSubscription $memberSubscription
){
    $subscription=DB::transaction(function()use(
        $memberSubscription
    ){
        $subscription=MemberSubscription::query()
            ->whereKey($memberSubscription->id)
            ->lockForUpdate()
            ->firstOrFail();

        if(!$subscription->is_active){
            throw ValidationException::withMessages([
                'subscription'=>[
                    'This subscription is already inactive.'
                ],
            ]);
        }

        /*
         * Existing generated dues remain historical.
         * Deactivation only prevents future due generation.
         */
        $endDate=now()->startOfDay();

        if(
            $endDate->lt(
                $subscription
                    ->start_date
                    ->copy()
                    ->startOfDay()
            )
        ){
            throw ValidationException::withMessages([
                'subscription'=>[
                    'Subscription cannot end before its start date.'
                ],
            ]);
        }

        $subscription->update([
            'is_active'=>false,
            'end_date'=>$endDate->toDateString(),
        ]);

        return $subscription->fresh([
            'member.user',
            'plan',
        ]);
    });

    return response()->json([
        'success'=>true,
        'message'=>'Subscription deactivated successfully.',
        'data'=>$subscription,
    ]);
}

    public function generateDue(
        Request $request,
        MemberSubscription $memberSubscription
    ) {
        $validated = $request->validate([
            'year' => 'required|integer|min:2000|max:2100',
            'month' => 'required|integer|min:1|max:12',
        ]);

        $due = $this->subscriptionService->generateDue(
            $memberSubscription,
            $validated['year'],
            $validated['month'],
            $request->user()->id
        );

        return response()->json([
            'success' => true,
            'message' => 'Monthly due generated successfully.',
            'data' => $due,
        ]);
    }

   public function generateBulk(Request $request)
{
    $validated=$request->validate([
        'year'=>'required|integer|min:2000|max:2100',
        'month'=>'required|integer|min:1|max:12',
    ]);

    $created=0;
    $existing=0;
    $failed=0;

    MemberSubscription::query()
        ->where('is_active',true)
        ->select([
            'id',
            'member_id',
            'subscription_plan_id',
            'start_date',
            'end_date',
            'is_active',
        ])
        ->chunkById(
            200,
            function($subscriptions)use(
                $validated,
                $request,
                &$created,
                &$existing,
                &$failed
            ){
                foreach($subscriptions as $subscription){
                    try{
                        $beforeExists=
                            SubscriptionDue::query()
                                ->where(
                                    'member_subscription_id',
                                    $subscription->id
                                )
                                ->where(
                                    'year',
                                    $validated['year']
                                )
                                ->where(
                                    'month',
                                    $validated['month']
                                )
                                ->exists();

                        $this
                            ->subscriptionService
                            ->generateDue(
                                $subscription,
                                $validated['year'],
                                $validated['month'],
                                $request->user()->id
                            );

                        if($beforeExists){
                            $existing++;
                        }else{
                            $created++;
                        }
                    }catch(
                        \Illuminate\Database\QueryException $exception
                    ){
                        /*
                         * If another concurrent process created
                         * the same due first, count as existing.
                         */
                        if(
                            in_array(
                                (string)$exception->getCode(),
                                [
                                    '23000',
                                    '23505',
                                ],
                                true
                            )
                        ){
                            $existing++;
                            continue;
                        }

                        report($exception);
                        $failed++;
                    }catch(\Throwable $exception){
                        report($exception);
                        $failed++;
                    }
                }
            }
        );

    return response()->json([
        'success'=>true,
        'message'=>'Monthly due generation completed.',
        'data'=>[
            'created'=>$created,
            'existing'=>$existing,
            'failed'=>$failed,
        ],
    ]);
}
}
