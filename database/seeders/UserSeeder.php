<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Client;

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
                            'role' => 'admin'
        ]);
    }
}
