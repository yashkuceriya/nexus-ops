<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Extends the existing `checklist_templates` / `checklist_completions`
 * schema to support "Pre-Functional Checklists" (PFCs) — the asset-level
 * readiness deliverables that must be complete *before* an FPT can run.
 *
 * The original checklist tables were modelled for facility-operations
 * workflows (one checklist per work order). PFCs live in a different
 * lifecycle entirely: they attach to an asset inside a project, they have
 * an associated Cx Level (L1 / L2 in ASHRAE G0 parlance), and a failed
 * item auto-opens a deficiency issue — identical to the FPT pattern.
 *
 * This migration is additive: existing facility-ops checklists keep
 * working with `type = 'facility_ops'` and the original `work_order_id`
 * relation; PFCs use `type = 'pfc'` and pivot on `project_id` + `asset_id`.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('checklist_templates', function (Blueprint $table): void {
            $table->string('type', 32)->default('facility_ops')->after('description');
            $table->string('cx_level', 8)->nullable()->after('category');
            $table->json('system_types')->nullable()->after('cx_level');
            $table->index(['tenant_id', 'type']);
        });

        Schema::table('checklist_completions', function (Blueprint $table): void {
            $table->foreignId('project_id')
                ->nullable()
                ->after('tenant_id')
                ->constrained()
                ->nullOnDelete();
            $table->foreignId('asset_id')
                ->nullable()
                ->after('project_id')
                ->constrained()
                ->nullOnDelete();
            $table->string('type', 32)->default('facility_ops')->after('checklist_template_id');
            $table->text('notes')->nullable()->after('responses');
            $table->unsignedInteger('pass_count')->default(0)->after('notes');
            $table->unsignedInteger('fail_count')->default(0)->after('pass_count');
            $table->unsignedInteger('na_count')->default(0)->after('fail_count');
            $table->index(['tenant_id', 'type', 'status']);
            $table->index(['project_id', 'type']);
            $table->index(['asset_id', 'type']);
        });

        // `work_order_id` was previously NOT NULL; PFCs aren't tied to a
        // work order, so relax that constraint. We do a column change via
        // a nullable redefinition; drop the FK first to avoid cross-DB
        // issues, then re-add it.
        Schema::table('checklist_completions', function (Blueprint $table): void {
            try {
                $table->dropForeign(['work_order_id']);
            } catch (Throwable) {
                // Some SQLite test DBs don't track the FK name the same way.
            }
            $table->unsignedBigInteger('work_order_id')->nullable()->change();
            $table->foreign('work_order_id')
                ->references('id')
                ->on('work_orders')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('checklist_completions', function (Blueprint $table): void {
            try {
                $table->dropForeign(['project_id']);
                $table->dropForeign(['asset_id']);
            } catch (Throwable) {
            }
            $table->dropColumn([
                'project_id', 'asset_id', 'type', 'notes',
                'pass_count', 'fail_count', 'na_count',
            ]);
        });

        Schema::table('checklist_templates', function (Blueprint $table): void {
            $table->dropIndex(['tenant_id', 'type']);
            $table->dropColumn(['type', 'cx_level', 'system_types']);
        });
    }
};
