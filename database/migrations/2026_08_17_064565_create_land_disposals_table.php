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
        Schema::create('land_disposals',function(Blueprint $table){
            $table->id();

            $table->foreignId('land_id')
                ->unique()
                ->constrained('lands')
                ->restrictOnDelete();

            $table->date('sale_date');

            $table->decimal('sale_price',15,2);
            $table->decimal('selling_expense',15,2)->default(0);
            $table->decimal('net_sale_amount',15,2);
            $table->decimal('book_value',15,2);
            $table->decimal('gain_loss',15,2)->default(0);

            $table->foreignId('receive_account_id')
                ->constrained('accounts')
                ->restrictOnDelete();

            $table->string('buyer_name')->nullable();
            $table->string('buyer_phone')->nullable();
            $table->string('reference_no')->nullable();

            $table->text('notes')->nullable();

            $table->foreignId('finance_transaction_id')
                ->nullable()
                ->constrained('transactions')
                ->nullOnDelete();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            $table->index('sale_date');
            $table->index('finance_transaction_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('land_disposals');
    }
};
