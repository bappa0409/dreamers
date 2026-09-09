<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $validated=$request->validate([
            'search'=>'nullable|string|max:150',
            'status'=>'nullable|in:active,inactive',
            'per_page'=>'nullable|integer|min:5|max:100',
        ]);

        $search=trim($validated['search']??'');
        $status=$validated['status']??null;
        $perPage=min((int)($validated['per_page']??20),100);

        $query=User::query()
            ->select('id','name','email','mobile','is_active','created_at')
            ->with([
                'member:id,user_id,profile_photo,member_code,status',
                'roles:id,name,display_name'
            ]);

        if($search!==''){
            $like="%{$search}%";

            $query->where(function($q)use($like){
                $q->where('name','like',$like)
                    ->orWhere('email','like',$like)
                    ->orWhere('mobile','like',$like)
                    ->orWhereHas('member',fn($mq)=>$mq->where('member_code','like',$like));
            });
        }

        if($status==='active')$query->where('is_active',true);
        if($status==='inactive')$query->where('is_active',false);

        return response()->json([
            'success'=>true,
            'data'=>$query->orderByDesc('id')->paginate($perPage)
        ]);
    }

    public function show(User $user)
    {
        return response()->json([
            'success'=>true,
            'data'=>$user->load([
                'member:id,user_id,member_code,status',
                'roles:id,name,display_name'
            ])
        ]);
    }

    public function store(Request $request)
    {
        $validated=$request->validate([
            'name'=>'required|string|max:150',
            'email'=>'required|email:rfc|max:255|unique:users,email',
            'mobile'=>'nullable|string|max:20|unique:users,mobile',
            'password'=>'required|string|min:8|confirmed'
        ]);

        $user=User::create([
            'name'=>$validated['name'],
            'email'=>$validated['email'],
            'mobile'=>$validated['mobile']??null,
            'password'=>Hash::make($validated['password']),
            'is_active'=>true,
        ]);

        return response()->json([
            'success'=>true,
            'message'=>'User created successfully.',
            'data'=>$user
        ],201);
    }

    public function update(Request $request,User $user)
    {
        $validated=$request->validate([
            'name'=>'required|string|max:150',
            'email'=>[
                'required',
                'email:rfc',
                'max:255',
                Rule::unique('users','email')->ignore($user->id)
            ],
            'mobile'=>[
                'nullable',
                'string',
                'max:20',
                Rule::unique('users','mobile')->ignore($user->id)
            ],
        ]);

        $user->update([
            'name'=>$validated['name'],
            'email'=>$validated['email'],
            'mobile'=>$validated['mobile']??null,
        ]);

        return response()->json([
            'success'=>true,
            'message'=>'User updated successfully.',
            'data'=>$user->fresh()->load(['member','roles'])
        ]);
    }

    public function toggleStatus(User $user)
    {
        if($user->id===auth()->id()){
            return response()->json([
                'success'=>false,
                'message'=>'You cannot deactivate your own account.'
            ],422);
        }

        $user->update([
            'is_active'=>!$user->is_active
        ]);

        return response()->json([
            'success'=>true,
            'message'=>$user->is_active
                ?'User account activated successfully.'
                :'User account deactivated successfully.',
            'data'=>$user->fresh()
        ]);
    }

    public function resetPassword(Request $request,User $user)
    {
        $validated=$request->validate([
            'password'=>'required|string|min:8|confirmed'
        ]);

        $user->update([
            'password'=>Hash::make($validated['password'])
        ]);

        $user->tokens()->delete();

        return response()->json([
            'success'=>true,
            'message'=>'Password reset successfully.'
        ]);
    }

    public function destroy(User $user)
    {
        if($user->id===auth()->id()){
            return response()->json([
                'success'=>false,
                'message'=>'You cannot delete your own account.'
            ],422);
        }

        if($user->member){
            return response()->json([
                'success'=>false,
                'message'=>'Member-linked users cannot be deleted. Deactivate the account instead.'
            ],422);
        }

        $user->roles()->detach();
        $user->tokens()->delete();
        $user->delete();

        return response()->json([
            'success'=>true,
            'message'=>'User deleted successfully.'
        ]);
    }
}