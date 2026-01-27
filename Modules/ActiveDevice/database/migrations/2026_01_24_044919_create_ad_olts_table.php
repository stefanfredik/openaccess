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
        Schema::create('olt', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('infrastructure_area_id')->constrained('infrastructure_areas')->cascadeOnDelete();
            $table->foreignId('pop_id')->nullable()->constrained('pops')->nullOnDelete();
            $table->string('code')->index();
            $table->string('name');
            $table->string('brand')->nullable();
            $table->string('model')->nullable();
            $table->string('serial_number')->nullable();
            $table->string('mac_address')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('username')->nullable();
            $table->string('password')->nullable();
            $table->year('purchase_year')->nullable();
            $table->string('latitude')->nullable();
            $table->string('longitude')->nullable();
            $table->string('pon_type')->nullable();
            $table->boolean('is_active')->default(true);
            $table->date('installed_at')->nullable();
            $table->text('description')->nullable();
            $table->string('device_image')->nullable();
            $table->string('service_status')->nullable();
            $table->enum('status', ['Active', 'Inactive', 'Planned'])->default('Active');
            $table->timestamps();
            $table->unique(['company_id', 'code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('olt');
    }
};
