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
        Schema::table('pd_cables', function (Blueprint $table) {
            $table->json('waypoint_poles')->nullable()->after('end_node_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pd_cables', function (Blueprint $table) {
            $table->dropColumn('waypoint_poles');
        });
    }
};
