<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('location_id')->nullable()->constrained()->nullOnDelete();
            $table->string('facilitygrid_asset_id')->nullable();
            $table->string('name');
            $table->string('asset_tag')->nullable();
            $table->string('qr_code')->nullable()->unique();
            $table->string('category')->nullable();
            $table->string('system_type')->nullable();
            $table->string('manufacturer')->nullable();
            $table->string('model_number')->nullable();
            $table->string('serial_number')->nullable();
            $table->enum('condition', ['excellent', 'good', 'fair', 'poor', 'critical', 'unknown'])->default('unknown');
            $table->enum('commissioning_status', ['not_started', 'in_progress', 'completed', 'deferred'])->default('not_started');
            $table->date('install_date')->nullable();
            $table->date('warranty_expiry')->nullable();
            $table->decimal('replacement_cost', 12, 2)->nullable();
            $table->integer('expected_life_years')->nullable();
            $table->integer('runtime_hours')->default(0);
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['tenant_id', 'facilitygrid_asset_id']);
            $table->index(['project_id', 'system_type']);
            $table->index(['tenant_id', 'category']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assets');
    }
};
