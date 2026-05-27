<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Central pivot: links a central user to a tenant with a tenant-scoped role.
     * A user can appear on several tenants with different roles.
     *
     * `client_id` is the company (tenant DB `clients` row) this user maps to
     * within that tenant — a plain int with no FK, since it crosses the
     * central/tenant database boundary (integrity is app-enforced).
     * See docs/multi-tenant-migration.md (Q-CLIENT, Cross-database references).
     */
    public function up(): void
    {
        Schema::create('tenant_user', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('tenant_id');
            $table->string('role');
            $table->unsignedBigInteger('client_id')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->unique(['user_id', 'tenant_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_user');
    }
};
