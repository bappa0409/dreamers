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
        Schema::create('welfare_documents',function(Blueprint $table){
            $table->id();

            $table->foreignId('welfare_request_id')
                ->constrained('welfare_requests')
                ->restrictOnDelete();

            $table->string('document_type',80);
            $table->string('file_path');
            $table->string('original_name');
            $table->string('mime_type',120)->nullable();
            $table->unsignedBigInteger('file_size')->nullable();

            $table->foreignId('uploaded_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->index(
                ['welfare_request_id','document_type'],
                'welfare_documents_request_type_idx'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('welfare_documents');
    }
};
