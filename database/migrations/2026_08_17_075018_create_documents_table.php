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
        Schema::create('documents', function (Blueprint $table) {
            $table->id();

            // Basic information
            $table->string('title');
            $table->string('document_type')->nullable();

            $table->string('original_name')->nullable();

            // File information
            $table->string('path')->nullable();
            $table->string('disk', 30)->default('local');
            $table->string('mime_type', 150)->nullable();
            $table->string('extension', 20)->nullable()->index();
            $table->unsignedBigInteger('size')->default(0);

            // Classification
            $table->string('category', 100)->nullable()->index();

            // Description
            $table->text('description')->nullable();

            // Access / visibility
            $table->string('visibility', 30)
                ->default('internal')
                ->index();

            // Status
            $table->string('status')
                ->default('active');

            $table->boolean('is_active')
                ->default(true)
                ->index();

            // User who uploaded the document
            $table->foreignId('uploaded_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            // Indexes
            $table->index('document_type');
            $table->index('status');
            $table->index(
                ['is_active','visibility'],
                'documents_active_visibility_idx'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
