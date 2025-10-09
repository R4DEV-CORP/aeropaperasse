<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\ActivityRequest;
use App\Models\ActivityComment;
use App\Models\Client;
use App\Models\ContactClient;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Compte Admin pour tester

        $clientAdmin = Client::factory()
            ->create([
                'company_name' => 'Admin',
                'trade_name' => 'Admin',
                'siret_number' => '12345678901234',
                'address' => '53 avenue du bois de la pie',
                'zip_code' => '93290',
                'city' => 'Tremblay en Ffrance',
            ]);

        $userAdmin = User::factory()
            ->for($clientAdmin)
            ->create([
                'name' => 'Corentin Sarda',
                'email' => 'contact@r4web.fr',
                'role' => 'admin',
            ]);


        // Jeu de test avec 3 clients
        $clients = Client::factory()
            ->count(3)
            ->create();

        foreach ($clients as $client) {

            $user = User::factory()
                ->for($client)
                ->create([
                    'role' => 'sclient',
                ]);

            $contactSafety = ContactClient::factory()
                ->for($client)
                ->create([
                    'role' => 'safety',
                ]);

            $contactSecurity = ContactClient::factory()
                ->for($client)
                ->create([
                    'role' => 'security',
                ]);

            $contactHr = ContactClient::factory()
                ->for($client)
                ->create([
                    'role' => 'hr',
                ]);

            $activityRequests = ActivityRequest::factory()
                ->for($client)
                ->for($user, 'creator')
                ->count(5)
                ->create();
            
            foreach($activityRequests as $activityRequest) {
                ActivityComment::factory()
                    ->for($activityRequest)
                    ->for($user)
                    ->create();

                ActivityComment::factory()
                    ->for($activityRequest)
                    ->for($userAdmin)
                    ->create();

                ActivityComment::factory()
                    ->for($activityRequest)
                    ->for($user)
                    ->create();

                ActivityComment::factory()
                    ->for($activityRequest)
                    ->for($userAdmin)
                    ->create();
            }
        }
    }
}
