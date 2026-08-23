<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('member_exits',function(Blueprint $table){
            $table->id();
            $table->string('exit_no',50)->unique();

            $table->foreignId('member_id')
                ->constrained('members')
                ->restrictOnDelete();

            $table->enum('exit_type',[
                'resignation',
                'termination',
                'death',
                'permanent_removal',
                'other',
            ]);

            $table->date('request_date');
            $table->date('proposed_exit_date')->nullable();

            $table->text('reason');

            $table->enum('status',[
                'submitted',
                'under_review',
                'liabilities_pending',
                'ready_for_approval',
                'approved',
                'settled',
                'closed',
                'rejected',
                'cancelled',
            ])->default('submitted');

            $table->boolean('member_initiated')->default(false);

            $table->decimal('subscription_due',15,2)->default(0);
            $table->decimal('charge_due',15,2)->default(0);
            $table->decimal('loan_due',15,2)->default(0);
            $table->decimal('total_liabilities',15,2)->default(0);
            $table->decimal('share_refund',15,2)->default(0);
            $table->decimal('net_settlement_amount',15,2)->default(0);

            $table->unsignedInteger('blocking_items_count')->default(0);

            $table->text('review_note')->nullable();

            $table->foreignId('initiated_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('reviewed_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('reviewed_at')->nullable();

            $table->foreignId('approved_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('approved_at')->nullable();

            $table->foreignId('rejected_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('rejected_at')->nullable();
            $table->text('rejection_reason')->nullable();

            $table->foreignId('payout_account_id')
                ->nullable()
                ->constrained('accounts')
                ->restrictOnDelete();

            $table->foreignId('settlement_transaction_id')
                ->nullable()
                ->constrained('transactions')
                ->restrictOnDelete();

            $table->foreignId('settled_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('settled_at')->nullable();

            $table->foreignId('closed_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('closed_at')->nullable();

            $table->timestamps();

            $table->index(
                ['member_id','status'],
                'member_exits_member_status_idx'
            );

            $table->index(
                ['status','request_date'],
                'member_exits_status_date_idx'
            );

            $table->index(
                ['exit_type','status'],
                'member_exits_type_status_idx'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('member_exits');
    }
};
