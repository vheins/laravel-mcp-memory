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
        Schema::create('memory_relations', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('source_id')->constrained('memories')->onDelete('cascade');
            $table->foreignUuid('target_id')->constrained('memories')->onDelete('cascade');
            $table->string('relation_type')->default('related'); // e.g., related, conflicts, supports
            $table->timestamps();

            $table->unique(['source_id', 'target_id', 'relation_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('memory_relations');
    }
};
