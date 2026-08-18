<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class ClearExpiredPasswordSetupTokens extends Command
{
    protected $signature='auth:clear-expired-setup-tokens';

    protected $description='Clear expired member password setup tokens';

    public function handle(): int
    {
        $count=User::query()
            ->whereNotNull('password_setup_token')
            ->whereNotNull('password_setup_expires_at')
            ->where('password_setup_expires_at','<',now())
            ->update([
                'password_setup_token'=>null,
                'password_setup_expires_at'=>null,
            ]);

        $this->info("Cleared {$count} expired password setup tokens.");

        return self::SUCCESS;
    }
}