<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('domain')->nullable()->unique();
            $table->json('settings')->nullable();
            $table->string('facilitygrid_api_url')->nullable();
            $table->text('facilitygrid_api_token')->nullable();
            $table->string('facilitygrid_auth_type')->default('bearer');
            $table->timestamp('facilitygrid_token_expires_at')->nullable();
            $table->text('facilitygrid_refresh_token')->nullable();
            $table->json('facilitygrid_scopes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
};
