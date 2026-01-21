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
        if (config('database.default') !== 'sqlite') {
            Schema::table('memories', function (Blueprint $table): void {
                $table->fullText(['title', 'current_content']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (config('database.default') !== 'sqlite') {
            Schema::table('memories', function (Blueprint $table): void {
                $table->dropFullText(['title', 'current_content']);
            });
        }
    }
};
