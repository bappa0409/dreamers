<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'transaction_entries',
            function(Blueprint $table){
                $table->id();

                $table->foreignId(
                    'transaction_id'
                )
                    ->constrained('transactions')
                    ->cascadeOnDelete();

                $table->foreignId(
                    'account_id'
                )
                    ->constrained('accounts')
                    ->restrictOnDelete();

                $table->decimal(
                    'debit',
                    15,
                    2
                )->default(0);

                $table->decimal(
                    'credit',
                    15,
                    2
                )->default(0);

                $table->text(
                    'description'
                )->nullable();

                $table->timestamps();

                /*
                |--------------------------------------------------------------------------
                | Optimized indexes
                |--------------------------------------------------------------------------
                */

                // General Ledger:
                // WHERE account_id = ?
                // JOIN transaction
                $table->index(
                    [
                        'account_id',
                        'transaction_id',
                    ],
                    'te_account_transaction_idx'
                );

                // Transaction detail:
                // WHERE transaction_id = ?
                //
                // transaction_id already gets an index
                // from the foreign key on MySQL,
                // so another composite index beginning
                // with transaction_id is unnecessary
                // for the normal workload.
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'transaction_entries'
        );
    }
};