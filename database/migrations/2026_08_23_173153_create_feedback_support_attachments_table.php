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
        Schema::create('feedback_support_attachments',function(Blueprint $table){
            $table->id();

            $table->foreignId('feedback_support_id')
                ->constrained('feedback_supports')
                ->restrictOnDelete();

            $table->string('file_path');
            $table->string('original_name');
            $table->string('mime_type',150)->nullable();
            $table->unsignedBigInteger('file_size')->nullable();

            $table->foreignId('uploaded_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->index('feedback_support_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('feedback_support_attachments');
    }
};
