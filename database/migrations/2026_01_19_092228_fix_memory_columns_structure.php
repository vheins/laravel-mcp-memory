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
        Schema::table('memories', function (Blueprint $table): void {
            // Drop existing user_id if present (it was a varchar/uuid)
            if (Schema::hasColumn('memories', 'user_id')) {
                // Try dropping index first (generic name or specific)
                // In SQLite, dropping index might be needed
                try {
                    $table->dropIndex(['user_id']);
                } catch (Exception) {
                }

                $table->dropColumn('user_id');
            }

            // Rename columns
            $table->renameColumn('organization_id', 'organization');
            $table->renameColumn('repository_id', 'repository');

            // Add user column (integer foreign key)
            // Use 'user' as column name, pointing to 'id' on 'users' table
            $table->unsignedBigInteger('user')->nullable()->index()->after('repository');
            $table->foreign('user')->references('id')->on('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('memories', function (Blueprint $table): void {
            $table->dropForeign(['user']);
            $table->dropColumn('user');

            $table->renameColumn('organization', 'organization_id');
            $table->renameColumn('repository', 'repository_id');
        });
    }
};
