<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('work_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('asset_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('location_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('issue_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('wo_number')->unique();
            $table->string('title');
            $table->text('description')->nullable();
            // Status, priority, type, and source values must match the code in
            // App\Services\WorkOrder\WorkOrderService and the WorkOrderStatus /
            // Priority enums. Previously this migration listed values like
            // 'sensor_alert' / 'pm_schedule' / 'external_issue' that the
            // application never produced, which caused silent divergence
            // between SQLite (lenient) and MySQL (strict) deployments.
            $table->enum('status', ['pending', 'assigned', 'in_progress', 'on_hold', 'completed', 'verified', 'cancelled'])->default('pending');
            $table->enum('priority', ['emergency', 'critical', 'high', 'medium', 'low'])->default('medium');
            $table->enum('type', ['corrective', 'preventive', 'inspection', 'emergency'])->default('corrective');
            $table->enum('source', ['manual', 'external', 'sensor', 'schedule', 'occupant_request', 'automation'])->default('manual');
            $table->integer('sla_hours')->nullable();
            $table->timestamp('sla_deadline')->nullable();
            $table->boolean('sla_breached')->default(false);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->decimal('estimated_cost', 10, 2)->nullable();
            $table->decimal('actual_cost', 10, 2)->nullable();
            $table->text('resolution_notes')->nullable();
            $table->json('photos')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'status']);
            $table->index(['assigned_to', 'status']);
            $table->index(['project_id', 'type']);
            $table->index('sla_deadline');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_orders');
    }
};
