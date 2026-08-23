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
        Schema::create('welfare_request_histories',function(Blueprint $table){
            $table->id();

            $table->foreignId('welfare_request_id')
                ->constrained('welfare_requests')
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
                ['welfare_request_id','created_at'],
                'welfare_history_request_date_idx'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('welfare_request_histories');
    }
};
