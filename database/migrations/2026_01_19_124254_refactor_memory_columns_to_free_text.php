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
        Schema::table('memories', function (Blueprint $table) {
            // Change organization and repository to string
            $table->string('organization')->change();
            $table->string('repository')->nullable()->change();

            // Rename user to user_id
            $table->renameColumn('user', 'user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('memories', function (Blueprint $table) {
            $table->renameColumn('user_id', 'user');

            // We can't easily revert to UUID type without knowing the previous state,
            // but since they were already strings/uuids, changing back to UUID if needed:
            // $table->uuid('organization')->change();
            // $table->uuid('repository')->nullable()->change();
        });
    }
};
