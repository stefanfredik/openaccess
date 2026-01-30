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
        Schema::table('ad_device_connections', function (Blueprint $table) {
            $table->string('source_port')->nullable()->after('destination_type');
            $table->string('destination_port')->nullable()->after('source_port');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ad_device_connections', function (Blueprint $table) {
            $table->dropColumn(['source_port', 'destination_port']);
        });
    }
};
