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
        Schema::create('pd_cable_cores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cable_id')->constrained('pd_cables')->onDelete('cascade');
            $table->foreignId('tube_id')->nullable()->constrained('pd_cable_tubes')->onDelete('cascade');
            $table->string('color');
            $table->integer('core_number');
            $table->string('status')->default('Available'); // Available, Used, Reserved, Broken
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pd_cable_cores');
    }
};
