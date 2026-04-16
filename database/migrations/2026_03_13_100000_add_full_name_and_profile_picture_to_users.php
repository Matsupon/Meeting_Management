<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations. Replaces username/name display with full_name.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'full_name')) {
                $table->string('full_name')->nullable()->after('id');
            }
            if (!Schema::hasColumn('users', 'profile_picture')) {
                $table->string('profile_picture')->nullable()->after('password');
            }
        });

        // Populate full_name from name or username (whichever exists)
        $hasUsername = Schema::hasColumn('users', 'username');
        $hasName = Schema::hasColumn('users', 'name');
        if ($hasUsername) {
            DB::table('users')->whereNull('full_name')->update(['full_name' => DB::raw('COALESCE(username, "")')]);
        } elseif ($hasName) {
            DB::table('users')->whereNull('full_name')->update(['full_name' => DB::raw('COALESCE(name, "")')]);
        }
        DB::table('users')->where('full_name', '')->orWhereNull('full_name')->update(['full_name' => 'User']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'full_name')) {
                $table->dropColumn('full_name');
            }
            // Optionally drop profile_picture; leave it to avoid data loss
        });
    }
};
