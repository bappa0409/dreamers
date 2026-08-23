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
        Schema::create('feedback_support_status_histories',function(Blueprint $table){
            $table->id();

            $table->foreignId('feedback_support_id')
                ->constrained('feedback_supports')
                ->restrictOnDelete();

            $table->string('from_status',40)->nullable();
            $table->string('to_status',40);
            $table->text('note')->nullable();

            $table->foreignId('changed_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            $table->index(
                ['feedback_support_id','created_at'],
                'feedback_support_history_idx'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('feedback_support_status_histories');
    }
};
