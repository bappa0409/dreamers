<?php

namespace App\Services;

use App\Mail\MemberPasswordSetupMail;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class PasswordSetupService
{
    public function generate(User $user): string
    {
        $plainToken=Str::random(64);

        $user->update([
            'password_setup_token'=>hash('sha256',$plainToken),
            'password_setup_expires_at'=>now()->addHours(24),
        ]);

        return route('password.setup',[
            'token'=>$plainToken,
        ]);
    }

    public function send(User $user): void
    {
        $setupUrl = $this->generate($user);

        Mail::to($user->email)->queue(
            new MemberPasswordSetupMail(
                $user,
                $setupUrl
            )
        );
    }

    public function resend(User $user): void
    {
        $this->send($user);
    }

    public function clear(User $user): void
    {
        $user->update([
            'password_setup_token'=>null,
            'password_setup_expires_at'=>null,
        ]);
    }
}