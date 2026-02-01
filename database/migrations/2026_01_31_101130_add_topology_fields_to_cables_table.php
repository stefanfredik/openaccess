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
            $table->nullableMorphs('start_node'); // OLT, ODF, JointBox...
            $table->nullableMorphs('end_node');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pd_cables', function (Blueprint $table) {
            //
        });
    }
};
