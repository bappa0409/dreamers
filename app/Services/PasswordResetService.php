<?php

namespace App\Services;

use App\Mail\PasswordResetMail;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class PasswordResetService
{
    /**
     * Reset link validity window (minutes).
     */
    protected int $expiryMinutes = 60;

    /**
     * Generate a reset token for the given user and
     * return the full reset URL.
     */
    public function generate(User $user): string
    {
        $plainToken = Str::random(64);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $user->email],
            [
                'token'      => Hash::make($plainToken),
                'created_at' => now(),
            ]
        );

        return route('password.reset', [
            'token' => $plainToken,
            'email' => $user->email,
        ]);
    }

    /**
     * Generate + email the reset link to the user.
     */
    public function send(User $user): void
    {
        $resetUrl = $this->generate($user);

        Mail::to($user->email)->queue(
            new PasswordResetMail($user, $resetUrl)
        );
    }

    /**
     * Validate a plain token against the stored hash + expiry.
     */
    public function isValid(string $email, string $plainToken): bool
    {
        $record = DB::table('password_reset_tokens')
            ->where('email', $email)
            ->first();

        if (!$record) {
            return false;
        }

        if (now()->diffInMinutes($record->created_at) > $this->expiryMinutes) {
            return false;
        }

        return Hash::check($plainToken, $record->token);
    }

    /**
     * Reset the user's password and invalidate the token.
     */
    public function reset(User $user, string $newPassword): void
    {
        $user->update([
            'password' => Hash::make($newPassword),
        ]);

        // Kill any active sessions / API tokens on password change.
        $user->tokens()->delete();

        $this->clear($user->email);
    }

    /**
     * Remove a used/expired reset token.
     */
    public function clear(string $email): void
    {
        DB::table('password_reset_tokens')
            ->where('email', $email)
            ->delete();
    }
}