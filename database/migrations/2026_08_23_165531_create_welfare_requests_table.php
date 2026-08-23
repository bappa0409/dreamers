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
        Schema::create('welfare_requests',function(Blueprint $table){
            $table->id();

            $table->string('request_no',50)->unique();

            $table->foreignId('welfare_fund_id')
                ->constrained('welfare_funds')
                ->restrictOnDelete();

            $table->foreignId('member_id')
                ->constrained('members')
                ->restrictOnDelete();

            $table->enum('assistance_type',[
                'illness',
                'accident',
                'death',
                'natural_disaster',
                'emergency',
                'financial_hardship',
                'other'
            ]);

            $table->decimal('requested_amount',15,2);
            $table->decimal('approved_amount',15,2)->nullable();

            $table->text('reason');
            $table->text('notes')->nullable();

            $table->date('request_date');

            $table->enum('status',[
                'submitted',
                'under_review',
                'approved',
                'rejected',
                'completed',
                'cancelled',
                'reversed'
            ])->default('submitted');

            $table->foreignId('reviewed_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('reviewed_at')->nullable();
            $table->text('review_note')->nullable();

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

            $table->foreignId('payment_account_id')
                ->nullable()
                ->constrained('accounts')
                ->restrictOnDelete();

            $table->date('disbursement_date')->nullable();

            $table->foreignId('finance_transaction_id')
                ->nullable()
                ->constrained('transactions')
                ->restrictOnDelete();

            $table->foreignId('reversal_transaction_id')
                ->nullable()
                ->constrained('transactions')
                ->restrictOnDelete();

            $table->timestamp('completed_at')->nullable();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            $table->index(
                ['member_id','status'],
                'welfare_requests_member_status_idx'
            );

            $table->index(
                ['welfare_fund_id','status'],
                'welfare_requests_fund_status_idx'
            );

            $table->index(
                ['status','request_date'],
                'welfare_requests_status_date_idx'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('welfare_requests');
    }
};
