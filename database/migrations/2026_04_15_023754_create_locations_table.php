<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('locations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('locations')->nullOnDelete();
            $table->string('external_location_id')->nullable();
            $table->string('name');
            $table->enum('type', ['building', 'floor', 'zone', 'room', 'mechanical_room', 'roof', 'exterior'])->default('room');
            $table->string('code')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();

            $table->unique(['tenant_id', 'external_location_id']);
            $table->index(['project_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('locations');
    }
};
