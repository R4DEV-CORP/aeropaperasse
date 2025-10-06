<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Client;
use App\Models\BadgeRequest;
use App\Models\ActivityRequest;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $clients = Client::factory()
                ->count(3)
                ->create();

        foreach($clients as $client) {

            $user = User::factory()
                ->for($client)
                ->create();

            $badgeRequest = BadgeRequest::factory()
                ->for($user)
                ->for($client)
                ->for($user, 'creator')
                ->count(5)
                ->create();

            $activityRequests = ActivityRequest::factory()
                ->for($user)
                ->for($user, 'creator')
                ->count(5)
                ->create();
        }

        // Compte Admin pour tester

        $clientAdmin = Client::factory()
            ->create([
                'name' => 'R4Web',
                'referent_email' => 'contact@r4web.fr',
                'referent_name' => 'Clement Richard',
                'notification_email' => 'contact@r4web.fr',
                'company_name' => 'R4Web',
                'company_address' => '123 Rue de la Paix, Paris, France',
                'company_phone' => '0674859641',
                'company_email' => 'contact@r4web.fr'
            ]);

        $userAdmin = User::factory()
            ->create([
                'name' => 'R4Web',
                'email' => 'contact@r4web.fr',
                'role' => 'admin'
            ]);
    }
}
