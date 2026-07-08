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
        Schema::create('wallets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained()->cascadeOnDelete();
            $table->bigInteger('total_raised_tambala')->default(0);
            $table->bigInteger('available_balance_tambala')->default(0);
            $table->bigInteger('pending_balance_tambala')->default(0);
            $table->bigInteger('processing_withdrawals_tambala')->default(0);
            $table->bigInteger('completed_withdrawals_tambala')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wallets');
    }
};
