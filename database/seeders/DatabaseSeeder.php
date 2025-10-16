<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\ActivityRequest;
use App\Models\ActivityComment;
use App\Models\Client;
use App\Models\ContactClient;
use App\Models\User;
use App\Models\Coworker;
use App\Models\BadgeRequest;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Désactiver les observers pendant le seeding
        \App\Models\BadgeRequest::withoutEvents(function () {
            $this->seedData();
        });
    }

    private function seedData(): void
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
                'email' => 'admin@r4web.fr',
                'role' => 'admin',
            ]);

        $coworkerAdmin = Coworker::factory()
            ->for($clientAdmin)
            ->for($userAdmin)
            ->create([
                'firstname' => 'Corentin',
                'lastname' => 'Sarda',
                'email' => 'admin@r4web.fr',
            ]);

        $userAdmin->coworker_id = $coworkerAdmin->id;
        $userAdmin->save();


        // Jeu de test avec 3 clients
        $clients = Client::factory()
            ->count(3)
            ->create();

        $user = User::factory()
            ->for($clients[0])
            ->create([
                'role' => 'sclient',
                'name' => 'Clément Richard',
                'email' => 'sclient@r4web.fr',
            ]);
        
        $coworker = Coworker::factory()
            ->for($user)
            ->for($clients[0])
            ->create([
                'firstname' => 'Clément',
                'lastname' => 'Richard',
                'email' => 'sclient@r4web.fr',
            ]);

        foreach ($clients as $client) {

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

            $coworkers = Coworker::factory()
                ->for($client)
                ->count(2)
                ->create();

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

                $badgeRequest = BadgeRequest::factory()
                    ->for($activityRequest)
                    ->for($coworkers[0])
                    ->for($user, 'creator')
                    ->create();
            }
        }
    }
}
