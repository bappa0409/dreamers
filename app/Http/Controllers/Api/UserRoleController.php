<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class UserRoleController extends Controller
{
    public function index(Request $request)
    {
        $search=trim((string)$request->input('search',''));

        $users=User::query()
            ->select('id','name','email','mobile','is_active')
            ->with([
                'member:id,user_id,member_code,status,profile_photo',
                'roles:id,name,display_name,is_system'
            ])
            ->when($search,function($query)use($search){
                $query->where(function($q)use($search){
                    $q->where('name','like',"%{$search}%")
                        ->orWhere('email','like',"%{$search}%")
                        ->orWhere('mobile','like',"%{$search}%")
                        ->orWhereHas('member',function($memberQuery)use($search){
                            $memberQuery->where('member_code','like',"%{$search}%");
                        });
                });
            })
            ->orderBy('name')
            ->paginate(20);

        return response()->json([
            'success'=>true,
            'data'=>$users
        ]);
    }

    public function roles()
    {
        $roles=Cache::remember(
            'rbac:assignable_roles',
            now()->addMinutes(30),
            fn()=>Role::query()
                ->select('id','name','display_name','description','is_system')
                ->orderByDesc('is_system')
                ->orderBy('display_name')
                ->get()
        );

        return response()->json([
            'success'=>true,
            'data'=>$roles
        ]);
    }

    public function show(User $user)
    {
        $user->load([
            'member:id,user_id,member_code,status,profile_photo',
            'roles:id,name,display_name,is_system'
        ]);

        return response()->json([
            'success'=>true,
            'data'=>$user
        ]);
    }

    public function sync(Request $request,User $user)
    {
        $validated=$request->validate([
            'role_ids'=>'required|array|min:1',
            'role_ids.*'=>'required|integer|distinct|exists:roles,id'
        ]);

        if($user->id===auth()->id()){
            $systemAnalystRole=Role::where('name','system_analyst')->first();

            if(
                $systemAnalystRole &&
                $user->hasRole('system_analyst') &&
                !in_array($systemAnalystRole->id,$validated['role_ids'])
            ){
                throw ValidationException::withMessages([
                    'role_ids'=>[
                        'You cannot remove your own System Analyst role.'
                    ]
                ]);
            }
        }

        $user->loadMissing('member');

        $memberRole=Role::where('name','member')->first();

        if(
            !$user->member&&
            $memberRole&&
            in_array((int)$memberRole->id,array_map('intval',$validated['role_ids']),true)
        ){
            throw ValidationException::withMessages([
                'role_ids'=>[
                    'The Member role can only be assigned to a user with a member profile.'
                ]
            ]);
        }

        if($user->member && $memberRole){
            $validated['role_ids'][]=$memberRole->id;
        }

        $roleIds=collect($validated['role_ids'])
            ->map(fn($id)=>(int)$id)
            ->unique()
            ->values()
            ->all();

        DB::transaction(function()use($user,$roleIds,$memberRole){
            $user->roles()->sync($roleIds);

            /*
             * Keep the legacy users.role_id column consistent while the
             * application finishes migrating to the user_roles pivot table.
             * Prefer the existing legacy role when it is still assigned;
             * otherwise prefer a non-member role for staff/admin accounts.
             */
            $currentRoleId=(int)($user->role_id??0);

            $legacyRoleId=in_array($currentRoleId,$roleIds,true)
                ?$currentRoleId
                :(
                    collect($roleIds)->first(
                        fn($id)=>!$memberRole||$id!==$memberRole->id
                    )
                    ??$roleIds[0]
                );

            $user->update([
                'role_id'=>$legacyRoleId,
            ]);
        });

        $this->forgetUserRoleCache($user);

        return response()->json([
            'success'=>true,
            'message'=>'User roles updated successfully.',
            'data'=>$user->fresh()->load(
                'roles:id,name,display_name,is_system'
            )
        ]);
    }

    protected function forgetUserRoleCache(User $user): void
    {
        Cache::forget("rbac:user:{$user->id}:roles");
        Cache::forget("rbac:user:{$user->id}:permissions");
    }
}