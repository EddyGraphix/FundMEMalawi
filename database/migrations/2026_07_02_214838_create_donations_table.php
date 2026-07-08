<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('donations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained()->cascadeOnDelete();
            $table->foreignId('donor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('donor_name')->nullable();
            $table->string('donor_email')->nullable();
            $table->boolean('is_anonymous')->default(false);
            $table->bigInteger('amount_tambala');
            $table->bigInteger('tip_amount_tambala')->default(0);
            $table->string('currency', 4)->default('MWK');
            $table->string('payment_method', 30);
            $table->string('payment_provider', 30)->nullable();
            $table->string('payment_provider_ref', 255)->unique()->nullable();
            $table->string('status', 20)->default('pending');
            $table->text('message')->nullable();
            $table->timestamps();

            $table->index(['campaign_id', 'status']);
            $table->index('payment_provider_ref');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('donations');
    }
};
