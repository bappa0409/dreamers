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
        Schema::table('documents',function(Blueprint $table){
            if(!Schema::hasColumn('documents','original_name')){
                $table->string('original_name')->nullable()->after('title');
            }

            if(!Schema::hasColumn('documents','path')){
                $table->string('path')->nullable();
            }

            if(!Schema::hasColumn('documents','disk')){
                $table->string('disk',30)->default('local');
            }

            if(!Schema::hasColumn('documents','mime_type')){
                $table->string('mime_type',150)->nullable();
            }

            if(!Schema::hasColumn('documents','extension')){
                $table->string('extension',20)->nullable()->index();
            }

            if(!Schema::hasColumn('documents','size')){
                $table->unsignedBigInteger('size')->default(0);
            }

            if(!Schema::hasColumn('documents','category')){
                $table->string('category',100)->nullable()->index();
            }

            if(!Schema::hasColumn('documents','description')){
                $table->text('description')->nullable();
            }

            if(!Schema::hasColumn('documents','visibility')){
                $table->string('visibility',30)->default('internal')->index();
            }

            if(!Schema::hasColumn('documents','is_active')){
                $table->boolean('is_active')->default(true)->index();
            }

            if(!Schema::hasColumn('documents','uploaded_by')){
                $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
