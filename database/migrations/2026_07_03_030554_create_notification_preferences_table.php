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
        Schema::create('notification_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->boolean('email_updates')->default(true);
            $table->boolean('email_milestones')->default(true);
            $table->boolean('email_comments')->default(true);
            $table->boolean('email_announcements')->default(false);
            $table->boolean('sms_updates')->default(false);
            $table->boolean('sms_donations')->default(false);
            $table->boolean('push_enabled')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_preferences');
    }
};
