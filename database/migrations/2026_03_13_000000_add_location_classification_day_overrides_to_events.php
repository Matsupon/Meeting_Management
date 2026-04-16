<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->string('time')->nullable()->change();
            $table->string('location')->nullable()->after('title');
            $table->string('classification')->nullable()->after('location');
            $table->json('day_overrides')->nullable()->after('color');
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->string('time')->nullable(false)->change();
            $table->dropColumn(['location', 'classification', 'day_overrides']);
        });
    }
};
