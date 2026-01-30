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
        Schema::create('pd_splicing_points', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('infrastructure_area_id')->constrained('infrastructure_areas')->onDelete('cascade');
            $table->foreignId('joint_box_id')->constrained('pd_joint_boxes')->onDelete('cascade');
            $table->string('code')->unique();
            $table->string('name');
            $table->string('tray_number')->nullable();
            $table->string('splicing_type')->nullable(); // Core-Core, Pigtail-Core, etc.
            $table->string('status')->default('Active'); // Active, Spare, Damaged
            $table->float('loss')->nullable();
            $table->string('photo')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->date('spliced_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pd_splicing_points');
    }
};
