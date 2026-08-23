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
        Schema::create('land_valuations',function(Blueprint $table){
            $table->id();

            $table->foreignId('land_id')
                ->constrained('lands')
                ->cascadeOnDelete();

            $table->date('valuation_date');

            $table->decimal('previous_value',15,2)->default(0);
            $table->decimal('current_value',15,2);

            $table->string('valued_by')->nullable();
            $table->text('notes')->nullable();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            $table->index(['land_id','valuation_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('land_valuations');
    }
};
