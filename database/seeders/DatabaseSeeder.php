<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\ActivityComment;
use App\Models\ActivityRequest;
use App\Models\BadgeRequest;
use App\Models\Client;
use App\Models\ContactClient;
use App\Models\Coworker;
use App\Models\Training;
use App\Models\User;
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

        $userSAdmin = User::factory()
            ->for($clientAdmin)
            ->create([
                'name' => 'Corentin Sarda',
                'email' => 'sadmin@r4web.fr',
                'role' => 'sadmin',
            ]);

        $coworkerSAdmin = Coworker::factory()
            ->for($clientAdmin)
            ->for($userSAdmin)
            ->create([
                'firstname' => 'Corentin',
                'lastname' => 'Sarda',
                'email' => 'sadmin@r4web.fr',
            ]);

        $userSAdmin->coworker_id = $coworkerSAdmin->id;
        $userSAdmin->save();

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

        $user2 = User::factory()
            ->for($clients[0])
            ->create([
                'role' => 'client',
                'name' => 'Alexis Putman',
                'email' => 'client@r4web.fr',
            ]);

        $coworker = Coworker::factory()
            ->for($user)
            ->for($clients[0])
            ->create([
                'firstname' => 'Clément',
                'lastname' => 'Richard',
                'email' => 'sclient@r4web.fr',
            ]);

        $coworker2 = Coworker::factory()
            ->for($user2)
            ->for($clients[0])
            ->create([
                'firstname' => 'Alexis',
                'lastname' => 'Putman',
                'email' => 'client@r4web.fr',
            ]);

        // Training
        $training1 = Training::factory()
            ->create(['title' => '11.2.6.2 (ditTCA)']);
        $training2 = Training::factory()
            ->create(['title' => '11.2.3.9']);
        $training3 = Training::factory()
            ->create(['title' => '11.2.3.9 plus TCA']);
        $training4 = Training::factory()
            ->create(['title' => '11.2.3.10']);
        $training5 = Training::factory()
            ->create(['title' => '11.2.3.10 plus TCA']);
        $training6 = Training::factory()
            ->create(['title' => 'Sécurité piétons']);
        $training7 = Training::factory()
            ->create(['title' => 'Permis T']);
        $training8 = Training::factory()
            ->create(['title' => 'Pratique permis T']);
        $training9 = Training::factory()
            ->create(['title' => 'Facteur humain']);
        $training10 = Training::factory()
            ->create(['title' => 'Co activité']);

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

            foreach ($coworkers as $coworker) {
                $coworker->trainings()->attach($training1->id, ['started_at' => now(), 'expires_at' => now()->addYears(5)]);
                $coworker->save();

                $coworker->trainings()->attach($training2->id, ['started_at' => now()->subYears(3)->subMonths(6), 'expires_at' => now()->subMonths(6)]);
                $coworker->save();

                $coworker->trainings()->attach($training3->id, ['started_at' => now()->subYears(3)->addMonths(5), 'expires_at' => now()->addMonths(5)]);
                $coworker->save();
            }

            $activityRequests = ActivityRequest::factory()
                ->for($client)
                ->for($user, 'creator')
                ->count(5)
                ->create();

            foreach ($activityRequests as $activityRequest) {
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
