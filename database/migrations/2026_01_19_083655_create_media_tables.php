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
        Schema::create('media', function (Blueprint $table): void {
            $table->id();
            $table->string('disk');
            $table->string('directory');
            $table->string('filename')->unique();
            $table->string('extension');
            $table->string('mime_type');
            $table->string('aggregate_type');
            $table->unsignedBigInteger('size');
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('media_attachments', function (Blueprint $table): void {
            $table->foreignId('media_id')->constrained('media')->cascadeOnDelete();
            $table->morphs('entity');
            $table->string('tag')->default('default');
            $table->timestamp('attached_at')->useCurrent();

            $table->unique(['media_id', 'entity_type', 'entity_id', 'tag']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('media_attachments');
        Schema::dropIfExists('media');
    }
};
