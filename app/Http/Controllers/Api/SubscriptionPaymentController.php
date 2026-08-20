<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPayment;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;

class SubscriptionPaymentController extends Controller
{
    public function __construct(
        protected SubscriptionService $subscriptionService
    ){}

    public function index(Request $request)
    {
        $validated=$request->validate([
            'status'=>'nullable|in:pending,verified,rejected',
            'payment_method'=>'nullable|in:cash,bank,mobile_banking,online',
            'search'=>'nullable|string|max:150',
            'from_date'=>'nullable|date',
            'to_date'=>'nullable|date|after_or_equal:from_date',
            'per_page'=>'nullable|integer|min:5|max:100'
        ]);

        $query=SubscriptionPayment::query()
            ->with([
                'member:id,user_id,member_code',
                'member.user:id,name,email,mobile',
                'due:id,member_subscription_id,year,month,base_amount,fine_amount,amount,paid_amount,due_date,status',
                'verifier:id,name,email'
            ])
            ->latest('id');

        if(!empty($validated['status'])){
            $query->where('status',$validated['status']);
        }

        if(!empty($validated['payment_method'])){
            $query->where(
                'payment_method',
                $validated['payment_method']
            );
        }

        if(!empty($validated['from_date'])){
            $query->whereDate(
                'paid_at',
                '>=',
                $validated['from_date']
            );
        }

        if(!empty($validated['to_date'])){
            $query->whereDate(
                'paid_at',
                '<=',
                $validated['to_date']
            );
        }

        if(!empty($validated['search'])){
            $search=trim($validated['search']);

            $query->where(function($q)use($search){
                $q->where(
                    'payment_no',
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
            });
        }

        $perPage=min(
            (int)($validated['per_page']??15),
            100
        );

        return response()->json([
            'success'=>true,
            'data'=>$query->paginate($perPage)
        ]);
    }

    public function show(
        SubscriptionPayment $subscriptionPayment
    ){
        $subscriptionPayment->load([
            'member:id,user_id,member_code,status',
            'member.user:id,name,email,mobile',
            'due.subscription.plan',
            'verifier:id,name,email',
            'financeTransaction.entries.account'
        ]);

        return response()->json([
            'success'=>true,
            'data'=>$subscriptionPayment
        ]);
    }

    public function verify(
        Request $request,
        SubscriptionPayment $subscriptionPayment
    ){
        $validated=$request->validate([
            'note'=>'nullable|string|max:2000'
        ]);

        $payment=$this->subscriptionService
            ->verifyPayment(
                $subscriptionPayment,
                $request->user()->id,
                $validated['note']??null
            );

        return response()->json([
            'success'=>true,
            'message'=>'Subscription payment verified successfully.',
            'data'=>$payment
        ]);
    }

    public function reject(
        Request $request,
        SubscriptionPayment $subscriptionPayment
    ){
        $validated=$request->validate([
            'reason'=>'required|string|max:2000'
        ]);

        $payment=$this->subscriptionService
            ->rejectPayment(
                $subscriptionPayment,
                $request->user()->id,
                $validated['reason']
            );

        return response()->json([
            'success'=>true,
            'message'=>'Subscription payment rejected successfully.',
            'data'=>$payment
        ]);
    }
}