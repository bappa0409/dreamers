<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loans', function (Blueprint $table) {
            $table->id();
            $table->string('loan_no',50)->unique();
            $table->foreignId('member_id')->constrained('members')->restrictOnDelete();
            $table->decimal('requested_amount',15,2);
            $table->decimal('approved_amount',15,2)->nullable();
            $table->decimal('interest_rate',7,4)->nullable();
            $table->decimal('interest_amount',15,2)->nullable();
            $table->decimal('total_payable',15,2)->nullable();
            $table->unsignedSmallInteger('duration_months')->nullable();
            $table->date('request_date');
            $table->date('maturity_date')->nullable();
            $table->enum('status',[
                'pending',
                'approved',
                'rejected',
                'active',
                'overdue',
                'repaid',
                'cancelled',
                'defaulted',
                'written_off'
            ])->default('pending');
            $table->text('purpose')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('rejected_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('rejected_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->foreignId('disbursement_account_id')->nullable()->constrained('accounts')->restrictOnDelete();
            $table->date('disbursement_date')->nullable();
            $table->foreignId('finance_transaction_id')->nullable()->constrained('transactions')->restrictOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['member_id','status'],'loans_member_status_idx');
            $table->index(['status','maturity_date'],'loans_status_maturity_idx');
            $table->index(['request_date','status'],'loans_request_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loans');
    }
};