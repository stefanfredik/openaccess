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
        Schema::create('ad_device_connections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();

            // Source device (the device initiated from)
            $table->unsignedBigInteger('source_id');
            $table->string('source_type');

            // Destination device (the target device)
            $table->unsignedBigInteger('destination_id');
            $table->string('destination_type');

            $table->string('connection_type'); // e.g., Uplink, Downlink
            $table->string('port_name')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index(['source_id', 'source_type']);
            $table->index(['destination_id', 'destination_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ad_device_connections');
    }
};
