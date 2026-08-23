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
        Schema::create('meeting_expenses',function(Blueprint $table){
            $table->id();
            $table->foreignId('meeting_id')->constrained('meetings')->cascadeOnDelete();
            $table->string('expense_no',40)->unique();
            $table->string('category',100);
            $table->foreignId('expense_account_id')->constrained('accounts')->restrictOnDelete();
            $table->foreignId('payment_account_id')->constrained('accounts')->restrictOnDelete();
            $table->decimal('amount',15,2);
            $table->date('expense_date');
            $table->string('payee')->nullable();
            $table->string('reference_no',150)->nullable();
            $table->text('description')->nullable();
            $table->enum('status',['posted','cancelled'])->default('posted');
            $table->foreignId('finance_transaction_id')->nullable()->constrained('transactions')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['meeting_id','status']);
            $table->index(['meeting_id','expense_date']);
            $table->index(['category','expense_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meeting_expenses');
    }
};
