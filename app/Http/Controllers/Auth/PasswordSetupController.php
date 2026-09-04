<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class PasswordSetupController extends Controller
{
    public function show(Request $request)
    {
        $token=(string)$request->query('token','');

        if(!$token){
            abort(404);
        }

        $user=$this->resolveUser($token);

        return view('auth.setup-password',[
            'token'=>$token,
            'user'=>$user,
        ]);
    }

    public function setup(Request $request)
    {
        $validated=$request->validate([
            'token'=>'required|string|max:255',
            'password'=>'required|string|min:8|confirmed',
        ]);

        $user=$this->resolveUser(
            $validated['token']
        );

        if(!$user->is_active){
            throw ValidationException::withMessages([
                'token'=>[
                    'Your membership account is not active yet.'
                ],
            ]);
        }

        $user->update([
            'password'=>Hash::make($validated['password']),
            'password_setup_token'=>null,
            'password_setup_expires_at'=>null,
            'must_change_password'=>false,
        ]);

        $user->tokens()->delete();

        Auth::login($user);

        $request->session()->regenerate();

        $user->loadMissing(['member','roles']);

        $isActiveMember=$user->member?->status==='active';
        $isOnlyMember=$user->roles->isNotEmpty()
            &&$user->roles->every(fn($role)=>$role->name==='member');

        $route=$isActiveMember&&$isOnlyMember
            ?'member.dashboard'
            :'dashboard';

        return redirect()
            ->route($route)
            ->with(
                'success',
                'Password created successfully. Welcome to Dreamers Association.'
            );
    }

    protected function resolveUser(string $plainToken): User
    {
        $hashedToken=hash(
            'sha256',
            $plainToken
        );

        $user=User::query()
            ->where(
                'password_setup_token',
                $hashedToken
            )
            ->first();

        if(!$user){
            throw ValidationException::withMessages([
                'token'=>[
                    'This password setup link is invalid.'
                ],
            ]);
        }

        if(
            !$user->password_setup_expires_at ||
            $user->password_setup_expires_at->isPast()
        ){
            throw ValidationException::withMessages([
                'token'=>[
                    'This password setup link has expired.'
                ],
            ]);
        }

        return $user;
    }
}