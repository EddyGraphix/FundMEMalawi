<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('campaigns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organizer_id')->constrained('users');
            $table->foreignId('beneficiary_id')->nullable()->constrained('users');
            $table->foreignId('category_id')->constrained('categories');
            $table->string('title');
            $table->string('slug')->unique();
            $table->longText('story');
            $table->bigInteger('goal_amount_tambala');
            $table->bigInteger('raised_amount_tambala')->default(0);
            $table->string('currency', 4)->default('MWK');
            $table->string('cover_media')->nullable();
            $table->string('status', 20)->default('draft');
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index('status');
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campaigns');
    }
};
