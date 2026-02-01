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
        Schema::create('pd_port_terminations', function (Blueprint $table) {
            $table->id();
            $table->morphs('port'); // OLTPort, SwitchPort
            $table->foreignId('core_id')->constrained('pd_cable_cores')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('port_terminations');
    }
};
