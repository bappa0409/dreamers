<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class RoleController extends Controller
{
    public function index(Request $request)
    {
        $roles=Role::query()
            ->withCount('users')
            ->with(['permissions:id,name,display_name,module'])
            ->orderByDesc('is_system')
            ->orderBy('display_name')
            ->get();

        return response()->json([
            'success'=>true,
            'data'=>$roles,
        ]);
    }

    public function permissions()
    {
        $permissions=Cache::remember('rbac:permissions:list',now()->addMinutes(30),function(){
            return Permission::query()
                ->select('id','name','display_name','module')
                ->orderBy('module')
                ->orderBy('display_name')
                ->get()
                ->groupBy(fn($permission)=>$permission->module?:'General');
        });

        return response()->json([
            'success'=>true,
            'data'=>$permissions,
        ]);
    }

    public function store(Request $request)
    {
        $validated=$request->validate([
            'name'=>[
                'required',
                'string',
                'max:100',
                'regex:/^[a-z0-9_]+$/',
                'unique:roles,name'
            ],
            'display_name'=>'required|string|max:150',
            'description'=>'nullable|string|max:1000',
            'permission_ids'=>'nullable|array',
            'permission_ids.*'=>'integer|exists:permissions,id',
        ]);

        $role=DB::transaction(function()use($validated){
            $role=Role::create([
                'name'=>$validated['name'],
                'display_name'=>$validated['display_name'],
                'description'=>$validated['description']??null,
                'is_system'=>false,
            ]);

            if(!empty($validated['permission_ids'])){
                $role->permissions()->sync($validated['permission_ids']);
            }

            $this->forgetRoleCaches($role);

            return $role;
        });

        return response()->json([
            'success'=>true,
            'message'=>'Role created successfully.',
            'data'=>$role->load('permissions:id,name,display_name,module'),
        ],201);
    }

    public function show(Role $role)
    {
        return response()->json([
            'success'=>true,
            'data'=>$role->load([
                'permissions:id,name,display_name,module',
                'users:id,name,email'
            ]),
        ]);
    }

    public function update(Request $request,Role $role)
    {
        $validated=$request->validate([
            'name'=>[
                'required',
                'string',
                'max:100',
                'regex:/^[a-z0-9_]+$/',
                Rule::unique('roles','name')->ignore($role->id)
            ],
            'display_name'=>'required|string|max:150',
            'description'=>'nullable|string|max:1000',
            'permission_ids'=>'nullable|array',
            'permission_ids.*'=>'integer|exists:permissions,id',
        ]);

        if($role->name==='system_analyst'){
            throw ValidationException::withMessages([
                'role'=>['System Analyst role cannot be modified.'],
            ]);
        }

        DB::transaction(function()use($role,$validated){
            $role->update([
                'name'=>$validated['name'],
                'display_name'=>$validated['display_name'],
                'description'=>$validated['description']??null,
            ]);

            $role->permissions()->sync($validated['permission_ids']??[]);

            $this->forgetRoleCaches($role);
        });

        return response()->json([
            'success'=>true,
            'message'=>'Role updated successfully.',
            'data'=>$role->fresh()->load('permissions:id,name,display_name,module'),
        ]);
    }

    public function destroy(Role $role)
    {
        if($role->is_system){
            return response()->json([
                'success'=>false,
                'message'=>'System roles cannot be deleted.',
            ],422);
        }

        if($role->users()->exists()){
            return response()->json([
                'success'=>false,
                'message'=>'This role is assigned to users and cannot be deleted.',
            ],422);
        }

        DB::transaction(function()use($role){
            $role->permissions()->detach();
            $role->delete();

            Cache::forget('rbac:permissions:list');
        });

        return response()->json([
            'success'=>true,
            'message'=>'Role deleted successfully.',
        ]);
    }

    public function syncPermissions(Request $request,Role $role)
    {
        $validated=$request->validate([
            'permission_ids'=>'required|array',
            'permission_ids.*'=>'integer|exists:permissions,id',
        ]);

        if($role->name==='system_analyst'){
            return response()->json([
                'success'=>false,
                'message'=>'System Analyst has full access automatically.',
            ],422);
        }

        DB::transaction(function()use($role,$validated){
            $role->permissions()->sync($validated['permission_ids']);
            $this->forgetRoleCaches($role);
        });

        return response()->json([
            'success'=>true,
            'message'=>'Permissions updated successfully.',
            'data'=>$role->fresh()->load('permissions:id,name,display_name,module'),
        ]);
    }

    protected function forgetRoleCaches(Role $role): void
    {
        Cache::forget('rbac:permissions:list');

        $role->users()
            ->select('users.id')
            ->chunkById(200,function($users){
                foreach($users as $user){
                    Cache::forget("rbac:user:{$user->id}:roles");
                    Cache::forget("rbac:user:{$user->id}:permissions");
                }
            });
    }
}