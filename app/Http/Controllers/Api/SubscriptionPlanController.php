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
        $validated=$request->validate([
            'search'=>'nullable|string|max:100',
            'status'=>'nullable|in:active,inactive',
        ]);

        $plans=SubscriptionPlan::query()
            ->when(
                !empty($validated['search']),
                function($query)use($validated){
                    $search=trim(
                        $validated['search']
                    );

                    $query->where(
                        'name',
                        'like',
                        "%{$search}%"
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
            ->orderByDesc('is_default')
            ->orderByDesc('is_active')
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
            'name'=>[
                'required',
                'string',
                'max:100',
                Rule::unique(
                    'subscription_plans',
                    'name'
                ),
            ],
            'amount'=>
                'required|numeric|min:0.01|max:999999999999.99',
            'due_day'=>
                'required|integer|min:1|max:31',
            'fine_type'=>[
                'nullable',
                Rule::in([
                    'none',
                    'fixed',
                    'percentage',
                ]),
            ],
            'fine_value'=>
                'nullable|numeric|min:0|max:999999999999.99',
            'grace_days'=>
                'nullable|integer|min:0|max:365',
            'is_default'=>'nullable|boolean',
            'is_active'=>'nullable|boolean',
            'description'=>'nullable|string|max:1000',
        ]);

        $plan=DB::transaction(function()use(
            $validated
        ){
            /*
             * Lock existing plans while deciding
             * which plan is default.
             */
            SubscriptionPlan::query()
                ->lockForUpdate()
                ->get(['id']);

            $hasDefault=SubscriptionPlan::query()
                ->where('is_default',true)
                ->exists();

            $isDefault=array_key_exists(
                'is_default',
                $validated
            )
                ?(bool)$validated['is_default']
                :!$hasDefault;

            $isActive=array_key_exists(
                'is_active',
                $validated
            )
                ?(bool)$validated['is_active']
                :true;

            if(
                $isDefault&&
                !$isActive
            ){
                throw ValidationException::withMessages([
                    'is_active'=>[
                        'Default subscription plan must remain active.'
                    ],
                ]);
            }

            $fineType=
                $validated['fine_type']
                ??'none';

            $fineValue=round(
                (float)(
                    $validated['fine_value']
                    ??0
                ),
                2
            );

            $graceDays=(int)(
                $validated['grace_days']
                ??0
            );

            $this->validateFine(
                $fineType,
                $fineValue
            );

            if($isDefault){
                SubscriptionPlan::query()
                    ->where('is_default',true)
                    ->update([
                        'is_default'=>false,
                    ]);
            }

            return SubscriptionPlan::create([
                'name'=>trim(
                    $validated['name']
                ),
                'amount'=>round(
                    (float)$validated['amount'],
                    2
                ),
                'due_day'=>
                    (int)$validated['due_day'],
                'fine_type'=>$fineType,
                'fine_value'=>$fineValue,
                'grace_days'=>$graceDays,
                'is_default'=>$isDefault,
                'is_active'=>$isActive,
                'description'=>$this->nullableString(
                    $validated['description']
                    ??null
                ),
            ]);
        });

        return response()->json([
            'success'=>true,
            'message'=>
                'Subscription plan created successfully.',
            'data'=>$plan,
        ],201);
    }

    public function update(
        Request $request,
        SubscriptionPlan $subscriptionPlan
    ){
        $validated=$request->validate([
            'name'=>[
                'sometimes',
                'required',
                'string',
                'max:100',
                Rule::unique(
                    'subscription_plans',
                    'name'
                )->ignore(
                    $subscriptionPlan->id
                ),
            ],
            'amount'=>
                'sometimes|required|numeric|min:0.01|max:999999999999.99',
            'due_day'=>
                'sometimes|required|integer|min:1|max:31',
            'fine_type'=>[
                'sometimes',
                'required',
                Rule::in([
                    'none',
                    'fixed',
                    'percentage',
                ]),
            ],
            'fine_value'=>
                'sometimes|nullable|numeric|min:0|max:999999999999.99',
            'grace_days'=>
                'sometimes|nullable|integer|min:0|max:365',
            'is_default'=>
                'sometimes|required|boolean',
            'is_active'=>
                'sometimes|required|boolean',
            'description'=>
                'sometimes|nullable|string|max:1000',
        ]);

        $plan=DB::transaction(function()use(
            $subscriptionPlan,
            $validated
        ){
            /*
             * Serialize plan/default changes.
             */
            SubscriptionPlan::query()
                ->lockForUpdate()
                ->get(['id']);

            $subscriptionPlan=
                SubscriptionPlan::query()
                    ->whereKey(
                        $subscriptionPlan->id
                    )
                    ->firstOrFail();

            $isDefault=array_key_exists(
                'is_default',
                $validated
            )
                ?(bool)$validated['is_default']
                :(bool)$subscriptionPlan->is_default;

            $isActive=array_key_exists(
                'is_active',
                $validated
            )
                ?(bool)$validated['is_active']
                :(bool)$subscriptionPlan->is_active;

            if(
                $isDefault&&
                !$isActive
            ){
                throw ValidationException::withMessages([
                    'is_active'=>[
                        'Default subscription plan must remain active.'
                    ],
                ]);
            }

            /*
             * Existing default cannot simply be removed
             * unless another default already exists.
             */
            if(
                $subscriptionPlan->is_default&&
                !$isDefault
            ){
                $otherDefault=
                    SubscriptionPlan::query()
                        ->where(
                            'id',
                            '!=',
                            $subscriptionPlan->id
                        )
                        ->where(
                            'is_default',
                            true
                        )
                        ->where(
                            'is_active',
                            true
                        )
                        ->exists();

                if(!$otherDefault){
                    throw ValidationException::withMessages([
                        'is_default'=>[
                            'Set another active default plan before removing this default.'
                        ],
                    ]);
                }
            }

            /*
             * New default automatically replaces
             * previous default.
             */
            if($isDefault){
                SubscriptionPlan::query()
                    ->where(
                        'id',
                        '!=',
                        $subscriptionPlan->id
                    )
                    ->where(
                        'is_default',
                        true
                    )
                    ->update([
                        'is_default'=>false,
                    ]);
            }

            $fineType=
                $validated['fine_type']
                ??$subscriptionPlan->fine_type;

            $fineValue=round(
                (float)(
                    array_key_exists(
                        'fine_value',
                        $validated
                    )
                        ?($validated['fine_value']??0)
                        :$subscriptionPlan->fine_value
                ),
                2
            );

            $this->validateFine(
                $fineType,
                $fineValue
            );

            $update=[];

            foreach([
                'name',
                'amount',
                'due_day',
                'fine_type',
                'fine_value',
                'grace_days',
                'is_default',
                'is_active',
                'description',
            ] as $field){
                if(
                    array_key_exists(
                        $field,
                        $validated
                    )
                ){
                    $update[$field]=
                        $validated[$field];
                }
            }

            if(
                array_key_exists(
                    'name',
                    $update
                )
            ){
                $update['name']=trim(
                    $update['name']
                );
            }

            if(
                array_key_exists(
                    'amount',
                    $update
                )
            ){
                $update['amount']=round(
                    (float)$update['amount'],
                    2
                );
            }

            if(
                array_key_exists(
                    'due_day',
                    $update
                )
            ){
                $update['due_day']=
                    (int)$update['due_day'];
            }

            if(
                array_key_exists(
                    'fine_value',
                    $update
                )
            ){
                $update['fine_value']=
                    $fineValue;
            }

            if(
                array_key_exists(
                    'grace_days',
                    $update
                )
            ){
                $update['grace_days']=
                    (int)($update['grace_days']??0);
            }

            if(
                array_key_exists(
                    'is_default',
                    $update
                )
            ){
                $update['is_default']=
                    $isDefault;
            }

            if(
                array_key_exists(
                    'is_active',
                    $update
                )
            ){
                $update['is_active']=
                    $isActive;
            }

            if(
                array_key_exists(
                    'description',
                    $update
                )
            ){
                $update['description']=
                    $this->nullableString(
                        $update['description']
                    );
            }

            /*
             * A "none" fine must never retain
             * a stale fine value.
             */
            if($fineType==='none'){
                $update['fine_type']='none';
                $update['fine_value']=0;
            }

            $subscriptionPlan->update(
                $update
            );

            return $subscriptionPlan->fresh();
        });

        return response()->json([
            'success'=>true,
            'message'=>
                'Subscription plan updated successfully.',
            'data'=>$plan,
        ]);
    }

    public function destroy(
        SubscriptionPlan $subscriptionPlan
    ){
        DB::transaction(function()use(
            $subscriptionPlan
        ){
            $subscriptionPlan=
                SubscriptionPlan::query()
                    ->whereKey(
                        $subscriptionPlan->id
                    )
                    ->lockForUpdate()
                    ->firstOrFail();

            if($subscriptionPlan->is_default){
                throw ValidationException::withMessages([
                    'plan'=>[
                        'Default subscription plan cannot be deleted.'
                    ],
                ]);
            }

            if(
                $subscriptionPlan
                    ->subscriptions()
                    ->exists()
            ){
                throw ValidationException::withMessages([
                    'plan'=>[
                        'This plan is already assigned to members and cannot be deleted. Deactivate it instead.'
                    ],
                ]);
            }

            $subscriptionPlan->delete();
        });

        return response()->json([
            'success'=>true,
            'message'=>
                'Subscription plan deleted successfully.',
        ]);
    }

    protected function validateFine(
        string $type,
        float $value
    ): void{
        if($type==='none'){
            return;
        }

        if($value<=0){
            throw ValidationException::withMessages([
                'fine_value'=>[
                    'Fine value must be greater than zero when a fine is enabled.'
                ],
            ]);
        }

        if(
            $type==='percentage'&&
            $value>100
        ){
            throw ValidationException::withMessages([
                'fine_value'=>[
                    'Percentage fine cannot exceed 100%.'
                ],
            ]);
        }
    }

    protected function nullableString(
        mixed $value
    ): ?string{
        if($value===null){
            return null;
        }

        $value=trim(
            (string)$value
        );

        return $value===''
            ?null
            :$value;
    }
}