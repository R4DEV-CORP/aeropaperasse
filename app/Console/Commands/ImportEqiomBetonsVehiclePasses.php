<?php

namespace App\Console\Commands;

use App\Enums\Role;
use App\Models\Client;
use App\Models\User;
use App\Models\VehiclePass;
use Illuminate\Console\Command;

class ImportEqiomBetonsVehiclePasses extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clients:import-eqiom-vehicle-passes
                            {--dry-run : Affiche ce qui serait créé sans écrire en base}
                            {--client-id= : Force l\'id du client EQIOM (sinon recherche automatique)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Importe les laissez-passer véhicules EQIOM BETONS pour l\'AAO 23/01561 (Orly, idempotent)';

    /**
     * @var array<int, array{plate_number: string, car_brand: string, airport: string}>
     */
    private array $vehiclePasses = [
        ['plate_number' => 'FK-108-RN', 'car_brand' => 'MAN', 'airport' => 'ORY'],
        ['plate_number' => 'DR-808-GF', 'car_brand' => 'MAN', 'airport' => 'ORY'],
        ['plate_number' => 'FM-318-GJ', 'car_brand' => 'Mercedes-Benz', 'airport' => 'ORY'],
        ['plate_number' => 'FV-824-JB', 'car_brand' => 'Mercedes-Benz', 'airport' => 'ORY'],
        ['plate_number' => 'GL-679-BE', 'car_brand' => 'Mercedes-Benz', 'airport' => 'ORY'],
        ['plate_number' => 'FG-852-HR', 'car_brand' => 'Renault', 'airport' => 'ORY'],
        ['plate_number' => 'EE-915-QF', 'car_brand' => 'Mercedes-Benz', 'airport' => 'ORY'],
        ['plate_number' => 'FJ-516-BR', 'car_brand' => 'Mercedes-Benz', 'airport' => 'ORY'],
        ['plate_number' => 'FC-047-AK', 'car_brand' => 'Mercedes-Benz', 'airport' => 'ORY'],
        ['plate_number' => 'EY-630-EM', 'car_brand' => 'Mercedes-Benz', 'airport' => 'ORY'],
        ['plate_number' => 'GE-939-BS', 'car_brand' => 'Mercedes-Benz', 'airport' => 'ORY'],
        ['plate_number' => 'GQ-922-SR', 'car_brand' => 'Mercedes-Benz', 'airport' => 'ORY'],
    ];

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $isDryRun = (bool) $this->option('dry-run');

        if ($isDryRun) {
            $this->warn('Mode dry-run : aucune écriture en base.');
        }

        // --- Résolution du client EQIOM ---
        $this->info('');
        $this->info('=== Client EQIOM ===');

        $client = $this->resolveClient();

        if (! $client) {
            return self::FAILURE;
        }

        $this->line("  Client cible : \"{$client->company_name}\" (id: {$client->id}, trade: \"{$client->trade_name}\")");

        // --- Laissez-passer véhicules ---
        $this->importVehiclePasses($client, $isDryRun);

        return self::SUCCESS;
    }

    private function resolveClient(): ?Client
    {
        $clientId = $this->option('client-id');

        if ($clientId !== null) {
            $client = Client::find($clientId);
            if (! $client) {
                $this->error("  Client id={$clientId} introuvable.");

                return null;
            }

            return $client;
        }

        // Recherche tolérante : EQIOM, Eqiom, eqiom, etc., sur company_name ou trade_name.
        $matches = Client::where(function ($q) {
            $q->whereRaw('LOWER(company_name) LIKE ?', ['%eqiom%'])
                ->orWhereRaw('LOWER(trade_name) LIKE ?', ['%eqiom%']);
        })->get();

        if ($matches->isEmpty()) {
            $this->error('  Aucun client EQIOM trouvé en base (recherche sur company_name / trade_name).');
            $this->line('  → Exécutez d\'abord `php artisan clients:import-eqiom-betons` ou passez --client-id={id}.');

            return null;
        }

        if ($matches->count() > 1) {
            $this->warn('  Plusieurs clients EQIOM trouvés. Précisez --client-id parmi :');
            foreach ($matches as $c) {
                $this->line("    id={$c->id}  company_name=\"{$c->company_name}\"  trade=\"{$c->trade_name}\"");
            }

            return null;
        }

        return $matches->first();
    }

    private function importVehiclePasses(Client $client, bool $isDryRun): void
    {
        $this->info('');
        $this->info('=== Laissez-passer véhicules (AAO 23/01561 — Orly) ===');

        $createdBy = User::where('role', Role::RemSuperAdmin->value)->orderBy('id')->value('id')
            ?? User::where('role', Role::RemAdmin->value)->orderBy('id')->value('id');

        if (! $isDryRun && ! $createdBy) {
            $this->warn('  [ignoré]   Aucun utilisateur sadmin/admin trouvé — laissez-passer non importés.');

            return;
        }

        $created = 0;
        $skipped = 0;

        foreach ($this->vehiclePasses as $entry) {
            if ($isDryRun) {
                $exists = VehiclePass::where('client_id', $client->id)
                    ->where('plate_number', $entry['plate_number'])
                    ->exists();

                if ($exists) {
                    $this->line("  [existant] {$entry['plate_number']} ({$entry['car_brand']})");
                    $skipped++;
                } else {
                    $this->line("  [à créer]  {$entry['plate_number']} ({$entry['car_brand']}, {$entry['airport']})");
                    $created++;
                }

                continue;
            }

            $pass = VehiclePass::firstOrCreate(
                [
                    'client_id' => $client->id,
                    'plate_number' => $entry['plate_number'],
                ],
                [
                    'created_by' => $createdBy,
                    'airport' => $entry['airport'],
                    'car_brand' => $entry['car_brand'],
                    'status' => 'approved',
                    'approved_at' => now(),
                ]
            );

            if ($pass->wasRecentlyCreated) {
                $this->info("  [créé]     {$entry['plate_number']} ({$entry['car_brand']}, {$entry['airport']})");
                $created++;
            } else {
                $this->line("  [existant] {$entry['plate_number']} ({$entry['car_brand']})");
                $skipped++;
            }
        }

        $this->info('');
        $this->info('=== Résumé laissez-passer véhicules ===');
        $this->info("  LPV créés     : {$created}");
        $this->line("  LPV existants : {$skipped}");
        $this->info('');
    }
}
