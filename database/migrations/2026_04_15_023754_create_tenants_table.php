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
            $table->string('external_api_url')->nullable();
            $table->text('external_api_token')->nullable();
            $table->string('external_auth_type')->default('bearer');
            $table->timestamp('external_token_expires_at')->nullable();
            $table->text('external_refresh_token')->nullable();
            $table->json('external_scopes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
};
