<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('feature_flags', function (Blueprint $table) {
            $table->string('key')->unique()->nullable();
            $table->string('name')->nullable();
            $table->text('description')->nullable();
            $table->boolean('enabled')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('feature_flags', function (Blueprint $table) {
            $table->dropColumn(['key', 'name', 'description', 'enabled']);
        });
    }
};
