<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add a foreign key on event_audits.event_id -> events.id with cascade delete.
     * Previously there was no FK, causing orphaned audit rows when events were deleted.
     * This migration is idempotent — it drops the FK first if it already exists.
     */
    public function up(): void
    {
        // Drop the FK if it already exists (idempotent)
        try {
            Schema::table('event_audits', function (Blueprint $table) {
                $table->dropForeign(['event_id']);
            });
        } catch (\Exception $e) {
            // FK didn't exist, safe to continue
        }

        Schema::table('event_audits', function (Blueprint $table) {
            $table->foreign('event_id')->references('id')->on('events')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('event_audits', function (Blueprint $table) {
            $table->dropForeign(['event_id']);
        });
    }
};
