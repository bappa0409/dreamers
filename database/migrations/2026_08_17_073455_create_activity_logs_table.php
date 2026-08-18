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
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('action');

            $table->string('module')->nullable();

            $table->string('description')->nullable();

            $table->nullableMorphs('subject');

            $table->ipAddress('ip_address')->nullable();

            $table->text('user_agent')->nullable();

            $table->json('old_values')->nullable();

            $table->json('new_values')->nullable();

            $table->timestamps();

            $table->index([
                'module',
                'action',
            ]);

            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
