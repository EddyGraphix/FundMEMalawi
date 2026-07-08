<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('campaigns', function (Blueprint $table) {
            $table->dropForeign(['organizer_id']);
            $table->renameColumn('organizer_id', 'owner_id');
            $table->foreign('owner_id')->references('id')->on('users');
        });
    }

    public function down(): void
    {
        Schema::table('campaigns', function (Blueprint $table) {
            $table->dropForeign(['owner_id']);
            $table->renameColumn('owner_id', 'organizer_id');
            $table->foreign('organizer_id')->references('id')->on('users');
        });
    }
};
