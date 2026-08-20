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

class MemberSubscriptionAdminController extends Controller
{
    public function __construct(
        protected SubscriptionService $subscriptionService
    ){}

    public function summary(Request $request)
    {
        $year=(int)($request->year?:now()->year);
        $month=(int)($request->month?:now()->month);

        $query=SubscriptionDue::query()
            ->where('year',$year)
            ->where('month',$month);

        $totalDue=(float)(clone $query)->sum('amount');
        $totalPaid=(float)(clone $query)->sum('paid_amount');
        $outstanding=max($totalDue-$totalPaid,0);

        return response()->json([
            'success'=>true,
            'data'=>[
                'total_due'=>round($totalDue,2),
                'total_paid'=>round($totalPaid,2),
                'outstanding'=>round($outstanding,2),
            ],
        ]);
    }

    public function index(Request $request)
    {
        $year=(int)($request->year?:now()->year);
        $month=(int)($request->month?:now()->month);
        $search=trim((string)$request->search);

        $subscriptions=MemberSubscription::query()
            ->with([
                'member.user:id,name,email,mobile',
                'plan:id,name,amount,due_day',
            ])
            ->with([
                'dues'=>function($query)use($year,$month){
                    $query->where('year',$year)
                        ->where('month',$month)
                        ->select([
                            'id',
                            'member_subscription_id',
                            'year',
                            'month',
                            'amount',
                            'paid_amount',
                            'due_date',
                            'status',
                            'finance_transaction_id',
                        ]);
                }
            ])
            ->when($search!=='',function($query)use($search){
                $query->whereHas('member',function($memberQuery)use($search){
                    $memberQuery
                        ->where('member_code','like',"%{$search}%")
                        ->orWhereHas('user',function($userQuery)use($search){
                            $userQuery
                                ->where('name','like',"%{$search}%")
                                ->orWhere('email','like',"%{$search}%")
                                ->orWhere('mobile','like',"%{$search}%");
                        });
                });
            })
            ->when($request->filled('status'),function($query)use($request){
                $query->where('is_active',$request->status==='active');
            })
            ->latest('id')
            ->paginate(20);

        return response()->json([
            'success'=>true,
            'data'=>$subscriptions,
        ]);
    }

    public function members(Request $request)
    {
        $search=trim((string)$request->search);

        $members=Member::query()
            ->with('user:id,name,email,mobile')
            ->where('status','active')
            ->when($search!=='',function($query)use($search){
                $query->where(function($memberQuery)use($search){
                    $memberQuery
                        ->where('member_code','like',"%{$search}%")
                        ->orWhereHas('user',function($userQuery)use($search){
                            $userQuery
                                ->where('name','like',"%{$search}%")
                                ->orWhere('email','like',"%{$search}%")
                                ->orWhere('mobile','like',"%{$search}%");
                        });
                });
            })
            ->orderBy('member_code')
            ->limit(50)
            ->get([
                'id',
                'user_id',
                'member_code',
                'status',
            ]);

        return response()->json([
            'success'=>true,
            'data'=>$members,
        ]);
    }

    public function store(Request $request)
    {
        $validated=$request->validate([
            'member_id'=>'required|exists:members,id',
            'subscription_plan_id'=>'required|exists:subscription_plans,id',
            'start_date'=>'required|date',
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

        $subscription=DB::transaction(function()use($validated){
            MemberSubscription::query()
                ->where('member_id',$validated['member_id'])
                ->where('is_active',true)
                ->lockForUpdate()
                ->update([
                    'is_active'=>false,
                    'end_date'=>now()->toDateString(),
                ]);

            return MemberSubscription::create([
                'member_id'=>$validated['member_id'],
                'subscription_plan_id'=>$validated['subscription_plan_id'],
                'start_date'=>$validated['start_date'],
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

    public function deactivate(MemberSubscription $memberSubscription)
    {
        if(!$memberSubscription->is_active){
            throw ValidationException::withMessages([
                'subscription'=>[
                    'This subscription is already inactive.'
                ],
            ]);
        }

        $memberSubscription->update([
            'is_active'=>false,
            'end_date'=>now()->toDateString(),
        ]);

        return response()->json([
            'success'=>true,
            'message'=>'Subscription deactivated successfully.',
        ]);
    }

    public function generateDue(
        Request $request,
        MemberSubscription $memberSubscription
    ){
        $validated=$request->validate([
            'year'=>'required|integer|min:2000|max:2100',
            'month'=>'required|integer|min:1|max:12',
        ]);

        $due=$this->subscriptionService->generateDue(
            $memberSubscription,
            $validated['year'],
            $validated['month'],
            $request->user()->id
        );

        return response()->json([
            'success'=>true,
            'message'=>'Monthly due generated successfully.',
            'data'=>$due,
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
            ->with([
                'plan',
                'member.user',
            ])
            ->chunkById(200,function($subscriptions)use(
                $validated,
                $request,
                &$created,
                &$existing,
                &$failed
            ){
                foreach($subscriptions as $subscription){
                    try{
                        $exists=SubscriptionDue::query()
                            ->where(
                                'member_subscription_id',
                                $subscription->id
                            )
                            ->where('year',$validated['year'])
                            ->where('month',$validated['month'])
                            ->exists();

                        $this->subscriptionService->generateDue(
                            $subscription,
                            $validated['year'],
                            $validated['month'],
                            $request->user()->id
                        );

                        $exists
                            ?$existing++
                            :$created++;
                    }catch(\Throwable $e){
                        report($e);
                        $failed++;
                    }
                }
            });

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