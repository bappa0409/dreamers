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
        Schema::create('notices', function (Blueprint $table) {
            $table->id();

            $table->string('title');

            $table->text('content');

            $table->enum('type', [
                'notice',
                'announcement',
                'event',
                'urgent',
            ])->default('notice');

            $table->enum('priority', [
                'low',
                'normal',
                'high',
                'urgent',
            ])->default('normal');

            $table->boolean('is_published')->default(false);

            $table->dateTime('publish_at')->nullable();

            $table->dateTime('expires_at')->nullable();

            $table->string('attachment')->nullable();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            $table->index(
                ['is_published','publish_at'],
                'notices_published_publish_at_idx'
            );
            $table->index(
                ['is_published','expires_at'],
                'notices_published_expires_at_idx'
            );
            $table->index(
                ['type','priority'],
                'notices_type_priority_idx'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notices');
    }
};
