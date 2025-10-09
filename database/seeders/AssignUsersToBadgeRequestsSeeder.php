<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AssignUsersToBadgeRequestsSeeder extends Seeder
{
    public function run()
    {
        // Supposons que vous voulez associer toutes les demandes existantes
        // à l'utilisateur avec l'ID 1 (ou un autre ID que vous choisissez)
        $defaultUserId = 1;

        DB::table('badge_requests')
            ->whereNull('user_id')
            ->update(['user_id' => $defaultUserId]);
    }
}
