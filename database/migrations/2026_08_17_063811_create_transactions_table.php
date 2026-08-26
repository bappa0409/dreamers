<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions',function(Blueprint $table){
            $table->id();

            $table->string('transaction_no',50)->unique();

            $table->string(
                'idempotency_key',
                191
            )->nullable()->unique();

            $table->date('transaction_date');

            $table->string('type',50);

            $table->string(
                'source_module',
                50
            )->nullable();

            $table->unsignedBigInteger(
                'source_id'
            )->nullable();

            $table->string(
                'reference_type'
            )->nullable();

            $table->unsignedBigInteger(
                'reference_id'
            )->nullable();

            $table->text(
                'description'
            )->nullable();

            $table->enum('status',[
                'draft',
                'posted',
                'cancelled',
            ])->default('draft');

            $table->foreignId(
                'created_by'
            )
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp(
                'posted_at'
            )->nullable();

            $table->foreignId(
                'posted_by'
            )
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->text(
                'cancel_reason'
            )->nullable();

            $table->foreignId(
                'reversal_transaction_id'
            )
                ->nullable()
                ->constrained('transactions')
                ->nullOnDelete();

            $table->foreignId(
                'reversed_by'
            )
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp(
                'reversed_at'
            )->nullable();

            $table->timestamps();

            /*
            |--------------------------------------------------------------------------
            | Optimized indexes
            |--------------------------------------------------------------------------
            */

            // Core report queries
            $table->index(
                ['status','transaction_date'],
                'transactions_status_date_idx'
            );

            // Journal type filtering
            $table->index(
                ['type','status','transaction_date'],
                'transactions_type_status_date_idx'
            );

            // Business-module source lookup
            $table->index(
                ['source_module','source_id'],
                'transactions_source_idx'
            );

            // Polymorphic/business reference lookup
            $table->index(
                ['reference_type','reference_id'],
                'transactions_reference_idx'
            );

            // Created-by audit/history
            $table->index(
                ['created_by','transaction_date'],
                'transactions_creator_date_idx'
            );

            // Posting audit
            $table->index(
                ['posted_by','posted_at'],
                'transactions_poster_date_idx'
            );

            // Reversal queries
            $table->index(
                ['status','reversed_at'],
                'transactions_status_reversed_idx'
            );

            $table->index(
                'reversal_transaction_id',
                'transactions_reversal_idx'
            );
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'transactions'
        );
    }
};