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
            $table->date('transaction_date')->index();

            /*
            |--------------------------------------------------------------------------
            | Transaction Type
            |--------------------------------------------------------------------------
            | Examples:
            | income, expense, subscription_due, subscription_payment,
            | charge, asset_purchase, investment, transfer,
            | adjustment, manual_journal
            */
            $table->string('type',50)->index();

            /*
            |--------------------------------------------------------------------------
            | Source
            |--------------------------------------------------------------------------
            | Identifies which business module generated the journal.
            */
            $table->string('source_module',50)->nullable();
            $table->unsignedBigInteger('source_id')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Polymorphic Reference
            |--------------------------------------------------------------------------
            */
            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Description
            |--------------------------------------------------------------------------
            */
            $table->text('description')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Status
            |--------------------------------------------------------------------------
            */
            $table->enum('status',[
                'draft',
                'posted',
                'cancelled',
            ])->default('draft')->index();

            /*
            |--------------------------------------------------------------------------
            | Created / Posted
            |--------------------------------------------------------------------------
            */
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('posted_at')->nullable();

            $table->foreignId('posted_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Cancellation
            |--------------------------------------------------------------------------
            */
            $table->text('cancel_reason')->nullable();

            $table->timestamps();

            /*
            |--------------------------------------------------------------------------
            | Indexes
            |--------------------------------------------------------------------------
            */
            $table->index([
                'source_module',
                'source_id',
            ]);

            $table->index([
                'reference_type',
                'reference_id',
            ]);

            $table->index([
                'transaction_date',
                'status',
            ]);

            $table->index([
                'type',
                'status',
            ]);

            $table->index([
                'created_by',
                'transaction_date',
            ]);

            $table->index([
                'posted_by',
                'posted_at',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};