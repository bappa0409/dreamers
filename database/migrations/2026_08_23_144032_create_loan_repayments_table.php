<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loan_repayments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loan_id')->constrained('loans')->restrictOnDelete();
            $table->foreignId('receive_account_id')->constrained('accounts')->restrictOnDelete();
            $table->decimal('principal_amount',15,2)->default(0);
            $table->decimal('interest_amount',15,2)->default(0);
            $table->decimal('penalty_amount',15,2)->default(0);
            $table->decimal('total_amount',15,2);
            $table->date('repayment_date');
            $table->text('notes')->nullable();
            $table->foreignId('finance_transaction_id')->nullable()->constrained('transactions')->restrictOnDelete();
            $table->foreignId('received_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['loan_id','repayment_date'],'loan_repayments_loan_date_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loan_repayments');
    }
};