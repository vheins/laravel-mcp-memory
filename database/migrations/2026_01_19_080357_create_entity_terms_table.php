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
        Schema::create('entity_terms', function (Blueprint $table) {
            $table->foreignId('term_id')->constrained()->cascadeOnDelete();
            $table->morphs('entity');
            $table->timestamps();

            $table->unique(['term_id', 'entity_type', 'entity_id']);
            // Index is already created by morphs()
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('entity_terms');
    }
};
