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
        Schema::create('member_exit_items',function(Blueprint $table){
            $table->id();

            $table->foreignId('member_exit_id')
                ->constrained('member_exits')
                ->restrictOnDelete();

            $table->enum('category',[
                'subscription_due',
                'charge',
                'loan',
                'share',
                'pending_share',
                'committee_position',
                'other',
            ]);

            $table->enum('direction',[
                'liability',
                'entitlement',
                'process',
            ]);

            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();

            $table->string('description',500);
            $table->decimal('amount',15,2)->default(0);

            $table->boolean('is_blocking')->default(false);

            $table->enum('status',[
                'pending',
                'cleared',
                'settled',
                'ignored',
            ])->default('pending');

            $table->timestamps();

            $table->index(
                ['member_exit_id','is_blocking','status'],
                'exit_items_blocker_idx'
            );

            $table->index(
                ['reference_type','reference_id'],
                'exit_items_reference_idx'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('member_exit_items');
    }
};
