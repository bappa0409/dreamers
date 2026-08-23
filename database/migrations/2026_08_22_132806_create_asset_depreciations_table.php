<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asset_depreciations',function(Blueprint $table){
            $table->id();

            $table->foreignId('asset_id')
                ->constrained('assets')
                ->restrictOnDelete();

            $table->date('depreciation_date');

            $table->decimal('amount',15,2);

            $table->decimal('book_value_before',15,2);

            $table->decimal('book_value_after',15,2);

            $table->string('period_key',20);

            $table->text('description')->nullable();

            $table->foreignId('finance_transaction_id')
                ->nullable()
                ->constrained('transactions')
                ->nullOnDelete();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            $table->unique(
                ['asset_id','period_key'],
                'asset_depreciations_period_unique'
            );

            $table->index(
                ['asset_id','depreciation_date'],
                'asset_depreciations_asset_date_idx'
            );

            $table->index(
                'finance_transaction_id',
                'asset_depreciations_finance_tx_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_depreciations');
    }
};