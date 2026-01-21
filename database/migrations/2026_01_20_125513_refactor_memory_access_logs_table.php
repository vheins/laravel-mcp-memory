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
        Schema::table('memory_access_logs', function (Blueprint $table): void {
            $table->json('filters')->after('query')->nullable();
            $table->integer('result_count')->after('metadata')->nullable();
            $table->dropColumn(['actor_type', 'resource_id']);
        });
    }

    public function down(): void
    {
        Schema::table('memory_access_logs', function (Blueprint $table): void {
            $table->string('actor_type')->after('actor_id')->nullable();
            $table->uuid('resource_id')->after('action')->nullable();
            $table->dropColumn(['filters', 'result_count']);
        });
    }
};
