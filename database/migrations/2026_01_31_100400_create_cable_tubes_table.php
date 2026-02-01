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
        Schema::create('pd_cable_tubes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cable_id')->constrained('pd_cables')->onDelete('cascade');
            $table->string('color');
            $table->integer('tube_number');
            $table->integer('core_count')->default(12);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cable_tubes');
    }
};
