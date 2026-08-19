<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lands',function(Blueprint $table){
            $table->decimal('current_value',15,2)->nullable()->after('purchase_price');
            $table->decimal('sale_price',15,2)->nullable()->after('current_value');
            $table->decimal('selling_expense',15,2)->default(0)->after('sale_price');
            $table->date('sale_date')->nullable()->after('purchase_date');
            $table->string('buyer_name')->nullable()->after('seller_phone');
            $table->string('buyer_phone',30)->nullable()->after('buyer_name');
        });
    }

    public function down(): void
    {
        Schema::table('lands',function(Blueprint $table){
            $table->dropColumn([
                'current_value',
                'sale_price',
                'selling_expense',
                'sale_date',
                'buyer_name',
                'buyer_phone'
            ]);
        });
    }
};