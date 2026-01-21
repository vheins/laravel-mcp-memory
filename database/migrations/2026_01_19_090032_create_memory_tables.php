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
        Schema::create('repositories', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('slug')->unique();
            $table->string('name');
            $table->uuid('organization_id')->index();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('memories', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('organization_id')->index();
            $table->uuid('repository_id')->nullable()->index();
            $table->string('scope_type'); // system, organization, repository, user
            $table->string('memory_type'); // business_rule, decision_log, preference, system_constraint, documentation_ref
            $table->string('status')->default('draft'); // draft, verified, locked, deprecated
            $table->string('created_by_type'); // human, ai
            $table->text('current_content');
            $table->json('metadata')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['repository_id', 'scope_type', 'status'], 'index_memory_scope');
            $table->index(['memory_type', 'status'], 'index_memory_classification');
        });

        Schema::create('memory_versions', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('memory_id')->constrained('memories');
            $table->integer('version_number');
            $table->text('content');
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['memory_id', 'version_number']);
        });

        Schema::create('memory_audit_logs', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('memory_id')->constrained('memories');
            $table->string('actor_id'); // User ID or Agent ID
            $table->string('actor_type'); // human, ai_agent
            $table->string('event'); // created, updated, verified, locked, deprecated, archived
            $table->json('old_value')->nullable();
            $table->json('new_value')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['memory_id', 'created_at'], 'index_audit_lookup');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('memory_audit_logs');
        Schema::dropIfExists('memory_versions');
        Schema::dropIfExists('memories');
        Schema::dropIfExists('repositories');
    }
};
