<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Rename the REM-level role values on the central `users` table to their
     * canonical multi-tenant names (App\Enums\Role). Tenant-level roles
     * (sclient / client / aclient / owner / tenant_admin) keep their values.
     *
     * Runs on the central connection: `users` is central, and REM staff never
     * carry a `tenant_user` pivot row, so no pivot rename is needed.
     * See docs/multi-tenant-migration.md (Roles, Migration of existing data).
     */
    public function up(): void
    {
        $users = DB::connection('central')->table('users');

        $users->where('role', 'sadmin')->update(['role' => 'rem_super_admin']);
        $users->where('role', 'admin')->update(['role' => 'rem_admin']);
    }

    public function down(): void
    {
        $users = DB::connection('central')->table('users');

        $users->where('role', 'rem_super_admin')->update(['role' => 'sadmin']);
        $users->where('role', 'rem_admin')->update(['role' => 'admin']);
    }
};
