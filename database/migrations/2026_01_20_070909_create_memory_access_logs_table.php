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
        Schema::create('memory_access_logs', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('actor_id')->nullable();
            $table->string('actor_type')->nullable(); // 'user', 'system', 'ai'
            $table->string('action'); // 'search', 'read', 'write', 'update', 'delete'
            $table->uuid('resource_id')->nullable(); // memory_id
            $table->json('metadata')->nullable(); // query, tokens, etc.
            $table->timestamp('created_at')->useCurrent();

            $table->index(['created_at']);
            $table->index(['action', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('memory_access_logs');
    }
};
