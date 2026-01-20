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
        Schema::table('memory_access_logs', function (Blueprint $table) {
            $table->text('query')->after('resource_id')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('memory_access_logs', function (Blueprint $table) {
            $table->dropColumn('query');
        });
    }
};
