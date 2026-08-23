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
        Schema::create('feedback_support_updates',function(Blueprint $table){
            $table->id();

            $table->foreignId('feedback_support_id')
                ->constrained('feedback_supports')
                ->restrictOnDelete();

            $table->enum('type',[
                'internal_note',
                'support_response',
                'member_follow_up'
            ]);

            $table->text('message');

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            $table->index(
                ['feedback_support_id','type'],
                'feedback_support_updates_type_idx'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('feedback_support_updates');
    }
};
