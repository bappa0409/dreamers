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
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('accounts')->nullOnDelete();
            $table->string('name')->index();
            $table->string('code')->unique();
            $table->enum('type', ['cash','bank','asset','liability','income','expense','equity'])->index();
            $table->string('sub_type', 50)->nullable()->index();
            $table->decimal('opening_balance', 15, 2)->default(0);
            $table->boolean('is_system')->default(false);
            $table->boolean('is_active')->default(true);
            $table->enum('approval_status', ['pending', 'approved', 'rejected', 'cancelled'])
                ->default('approved');
            $table->text('description')->nullable();
            $table->timestamps();
            
            $table->index(['type', 'is_active']);
            $table->index(['parent_id', 'is_active']);
            $table->index(['is_system', 'is_active']);
            $table->index(['approval_status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accounts');
    }
};
