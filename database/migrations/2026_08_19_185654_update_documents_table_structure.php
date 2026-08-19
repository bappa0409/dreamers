<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            if (Schema::hasColumn('documents', 'file_path')) {
                $table->dropColumn('file_path');
            }

            if (Schema::hasColumn('documents', 'file_name')) {
                $table->dropColumn('file_name');
            }

            if (Schema::hasColumn('documents', 'file_extension')) {
                $table->dropColumn('file_extension');
            }

            if (Schema::hasColumn('documents', 'file_size')) {
                $table->dropColumn('file_size');
            }
        });
    }

    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->string('file_path');
            $table->string('file_name');
            $table->string('file_extension')->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
        });
    }
};