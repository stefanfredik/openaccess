<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('sites', function (Blueprint $table) {
            $table->integer('electrical_capacity')->nullable()->comment('Capacity in VA/Watt');
            $table->string('power_source')->nullable()->comment('PLN, Solar, Genset, Hybrid');
            $table->boolean('has_backup_power')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sites', function (Blueprint $table) {

        });
    }
};
