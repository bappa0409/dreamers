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
        Schema::create('lands', function (Blueprint $table) {
            $table->id();

            $table->string('land_code')->unique();

            $table->string('title');

            $table->text('description')->nullable();

            $table->string('district')->nullable();
            $table->string('upazila')->nullable();
            $table->string('mouza')->nullable();

            $table->string('khatian_no')->nullable();
            $table->string('dag_no')->nullable();

            $table->decimal('land_area', 12, 4)->nullable();

            $table->string('area_unit')->default('decimal');

            $table->decimal('purchase_price', 15, 2)->default(0);

            $table->date('purchase_date')->nullable();

            $table->string('seller_name')->nullable();
            $table->string('seller_phone')->nullable();

            $table->enum('status', [
                'planned',
                'negotiating',
                'purchased',
                'sold',
                'cancelled',
            ])->default('planned');

            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lands');
    }
};
