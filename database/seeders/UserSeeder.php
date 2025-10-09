<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $clientAdmin = Client::factory()
            ->create([
                'company_name' => 'R4Web',
                'trade_name' => 'R4Web',
                'siret_number' => '12345678901234',
                'address' => '53 avenue du bois de la pie',
                'zip_code' => '93290',
                'city' => 'Tremblay en Ffrance',
            ]);

        $userAdmin = User::factory()
            ->for($clientAdmin)
            ->create([
                'name' => 'R4Web',
                'email' => 'contact@r4web.fr',
                'role' => 'admin',
            ]);

        // User client
        $client = Client::factory()
            ->create([
                'company_name' => 'Elior SAS',
                'trade_name' => 'Elior',
                'siret_number' => '9875621435',
                'address' => '23 rue Paul Vaillant Couturier',
                'zip_code' => '93290',
                'city' => 'Tremblay en Ffrance',
            ]);

        $userClient = User::factory()
            ->for($client)
            ->create([
                'name' => 'Elior',
                'email' => 'contact@elior.fr',
                'role' => 'client',
            ]);

    }
}
