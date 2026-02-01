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
        Schema::create('pd_fiber_splices', function (Blueprint $table) {
            $table->id();
            $table->nullableMorphs('enclosure'); // JointBox, ODP, ODF
            $table->foreignId('incoming_core_id')->constrained('pd_cable_cores')->onDelete('cascade');
            $table->foreignId('outgoing_core_id')->constrained('pd_cable_cores')->onDelete('cascade');
            $table->decimal('attenuation', 5, 2)->nullable(); // dB Loss
            $table->string('type')->default('fusion'); // fusion, mechanical
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fiber_splices');
    }
};
