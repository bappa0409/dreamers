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
        Schema::create('nominee_documents', function (Blueprint $table) {
            $table->id();

            $table->foreignId('member_nominee_id')
                ->constrained('member_nominees')
                ->cascadeOnDelete();

            $table->string('document_type',80);
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

            $table->index(
                ['member_nominee_id','document_type'],
                'nominee_documents_type_idx'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nominee_documents');
    }
};
