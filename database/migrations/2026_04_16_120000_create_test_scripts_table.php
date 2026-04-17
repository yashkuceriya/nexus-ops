<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Commissioning Functional Performance Test (FPT) scripts.
 *
 * A test_script is a template — a named, versioned, ordered sequence of
 * measurements and observations performed on an asset to prove that it
 * meets design intent.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('test_scripts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->string('name');
            $table->string('slug', 120);
            $table->text('description')->nullable();

            $table->string('system_type', 60);
            $table->string('asset_category', 60)->nullable();

            // Commissioning test level taxonomy (L1 factory → L5 IST).
            // Nullable for tenant-authored ad-hoc scripts that don't map to a level.
            $table->enum('cx_level', ['L1', 'L2', 'L3', 'L4', 'L5'])->nullable();

            $table->unsignedInteger('version')->default(1);
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');

            $table->boolean('is_system')->default(false);

            // Self-reference for "clone of this system script". Lets tenants
            // fork a system template for customisation without losing the
            // provenance link back to the original.
            $table->foreignId('cloned_from_id')->nullable()
                ->constrained('test_scripts')->nullOnDelete();

            $table->unsignedInteger('estimated_duration_minutes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'system_type']);
            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'cx_level']);
            $table->index(['is_system', 'system_type']);
            $table->unique(['tenant_id', 'slug', 'version']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('test_scripts');
    }
};
