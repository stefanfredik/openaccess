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
        Schema::create('ad_device_interfaces', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->morphs('interfacable');
            $table->string('name'); // e.g., GE01, SFP01
            $table->string('type')->nullable(); // e.g., Gigabit, Fiber
            $table->string('speed')->nullable(); // e.g., 1000Mbps
            $table->string('mac_address')->nullable();
            $table->string('status')->default('down'); // up, down, idle, error
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ad_device_interfaces');
    }
};
