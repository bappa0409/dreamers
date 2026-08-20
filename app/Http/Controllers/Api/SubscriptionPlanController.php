<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class SubscriptionPlanController extends Controller
{
    public function index(Request $request)
    {
        $plans=SubscriptionPlan::query()
            ->when($request->filled('search'),function($query)use($request){
                $search=trim($request->search);
                $query->where('name','like',"%{$search}%");
            })
            ->orderByDesc('is_default')
            ->orderBy('name')
            ->get();

        return response()->json([
            'success'=>true,
            'data'=>$plans,
        ]);
    }

    public function store(Request $request)
    {
        $validated=$request->validate([
            'name'=>'required|string|max:100|unique:subscription_plans,name',
            'amount'=>'required|numeric|min:0.01|max:999999999999.99',
            'due_day'=>'required|integer|min:1|max:31',
            'is_default'=>'nullable|boolean',
            'is_active'=>'nullable|boolean',
            'description'=>'nullable|string|max:1000',
        ]);

        $plan=DB::transaction(function()use($validated){
            $isDefault=(bool)($validated['is_default']??false);

            if($isDefault){
                SubscriptionPlan::where('is_default',true)->update([
                    'is_default'=>false,
                ]);
            }

            return SubscriptionPlan::create([
                'name'=>$validated['name'],
                'amount'=>$validated['amount'],
                'due_day'=>$validated['due_day'],
                'is_default'=>$isDefault,
                'is_active'=>(bool)($validated['is_active']??true),
                'description'=>$validated['description']??null,
            ]);
        });

        return response()->json([
            'success'=>true,
            'message'=>'Subscription plan created successfully.',
            'data'=>$plan,
        ],201);
    }

    public function update(Request $request,SubscriptionPlan $subscriptionPlan)
    {
        $validated=$request->validate([
            'name'=>[
                'required',
                'string',
                'max:100',
                Rule::unique('subscription_plans','name')
                    ->ignore($subscriptionPlan->id),
            ],
            'amount'=>'required|numeric|min:0.01|max:999999999999.99',
            'due_day'=>'required|integer|min:1|max:31',
            'is_default'=>'required|boolean',
            'is_active'=>'required|boolean',
            'description'=>'nullable|string|max:1000',
        ]);

        DB::transaction(function()use($subscriptionPlan,$validated){
            if($validated['is_default']){
                SubscriptionPlan::where('id','!=',$subscriptionPlan->id)
                    ->where('is_default',true)
                    ->update(['is_default'=>false]);
            }

            if(
                $subscriptionPlan->is_default&&
                !$validated['is_default']
            ){
                $otherDefault=SubscriptionPlan::query()
                    ->where('id','!=',$subscriptionPlan->id)
                    ->where('is_default',true)
                    ->exists();

                if(!$otherDefault){
                    throw ValidationException::withMessages([
                        'is_default'=>[
                            'Set another default plan before removing this default.'
                        ],
                    ]);
                }
            }

            $subscriptionPlan->update($validated);
        });

        return response()->json([
            'success'=>true,
            'message'=>'Subscription plan updated successfully.',
            'data'=>$subscriptionPlan->fresh(),
        ]);
    }

    public function destroy(SubscriptionPlan $subscriptionPlan)
    {
        if($subscriptionPlan->subscriptions()->exists()){
            throw ValidationException::withMessages([
                'plan'=>[
                    'This plan is already assigned to members and cannot be deleted. Deactivate it instead.'
                ],
            ]);
        }

        $subscriptionPlan->delete();

        return response()->json([
            'success'=>true,
            'message'=>'Subscription plan deleted successfully.',
        ]);
    }
}