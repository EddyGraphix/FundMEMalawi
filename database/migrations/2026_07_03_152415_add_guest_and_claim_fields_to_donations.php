<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('donations', function (Blueprint $table) {
            $table->string('donor_phone')->nullable()->after('donor_email');
            $table->foreignId('claimed_by_id')->nullable()->constrained('users')->after('donor_id');
            $table->timestamp('claimed_at')->nullable()->after('claimed_by_id');
        });
    }

    public function down(): void
    {
        Schema::table('donations', function (Blueprint $table) {
            $table->dropForeign(['claimed_by_id']);
            $table->dropColumn(['donor_phone', 'claimed_by_id', 'claimed_at']);
        });
    }
};
