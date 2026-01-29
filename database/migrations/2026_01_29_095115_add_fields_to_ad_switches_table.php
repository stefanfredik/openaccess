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
        Schema::table('ad_switches', function (Blueprint $table) {
            $table->string('username')->nullable()->after('description');
            $table->string('password')->nullable()->after('username');
            $table->integer('purchase_year')->nullable()->after('password');
            $table->string('photo')->nullable()->after('purchase_year');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ad_switches', function (Blueprint $table) {
            $table->dropColumn(['username', 'password', 'purchase_year', 'photo']);
        });
    }
};
