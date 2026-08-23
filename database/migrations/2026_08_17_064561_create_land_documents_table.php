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
        Schema::create('land_documents', function (Blueprint $table) {
            $table->id();

            $table->foreignId('land_id')
                ->constrained('lands')
                ->cascadeOnDelete();

            $table->string('document_type');

            $table->string('document_number')->nullable();

            $table->string('file_path');

            $table->string('file_name')->nullable();

            $table->date('document_date')->nullable();

            $table->text('description')->nullable();
            $table->foreignId('uploaded_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

                $table->index([
                'land_id',
                'document_type'
            ]);
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('land_documents');
    }
};
