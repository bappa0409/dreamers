<?php

namespace App\Services;

use App\Mail\PasswordResetMail;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class PasswordResetService
{
    /**
     * Generate a reset token for the given user and return the full reset URL.
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
     * Password reset is a transactional email. Send it immediately so the
     * reset flow does not silently depend on a queue worker being online.
     */
    public function send(User $user): void
    {
        $resetUrl = $this->generate($user);

        Mail::to($user->email)->send(
            new PasswordResetMail($user, $resetUrl)
        );
    }

    /**
     * Validate a plain token against the stored hash + configured expiry.
     */
    public function isValid(string $email, string $plainToken): bool
    {
        $record = DB::table('password_reset_tokens')
            ->where('email', $email)
            ->first();

        if (!$record) {
            return false;
        }

        $expiryMinutes=max(
            5,
            min(
                (int)setting('password_reset_expiry_minutes',60),
                1440
            )
        );

        $createdAt=Carbon::parse($record->created_at);

        if ($createdAt->copy()->addMinutes($expiryMinutes)->isPast()) {
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
            'must_change_password' => false,
        ]);

        // Kill any active API tokens on password change.
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
