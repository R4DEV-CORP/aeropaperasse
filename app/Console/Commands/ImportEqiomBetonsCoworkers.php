<?php

namespace App\Console\Commands;

use App\Models\Client;
use App\Models\Coworker;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class ImportEqiomBetonsCoworkers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clients:import-eqiom-betons
                            {--dry-run : Affiche ce qui serait créé sans écrire en base}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Importe le client EQIOM BETONS et ses 47 collaborateurs (idempotent)';

    /**
     * @var array<int, array{lastname: string, firstname: string, email: string}>
     */
    private array $coworkers = [
        // CDG
        ['lastname' => 'FREIRE BARRETO DE CARVALHO', 'firstname' => 'ADMILSON', 'email' => 'baptiste.cornil@eqiom.com'],
        ['lastname' => 'LY', 'firstname' => 'MAMADOU', 'email' => 'baptiste.cornil@eqiom.com'],
        ['lastname' => 'HAIDARA', 'firstname' => 'IBRAHIMA', 'email' => 'baptiste.cornil@eqiom.com'],
        ['lastname' => 'SALLA', 'firstname' => 'ENCA MALIQUE', 'email' => 'baptiste.cornil@eqiom.com'],
        ['lastname' => 'RAMOS MIRANDA', 'firstname' => 'CLAUDINO', 'email' => 'baptiste.cornil@eqiom.com'],
        ['lastname' => 'DA SILVA MOREIRA BARRETO', 'firstname' => 'JOAO', 'email' => 'baptiste.cornil@eqiom.com'],
        ['lastname' => 'ZERROUK', 'firstname' => 'MOHAMED', 'email' => 'baptiste.cornil@eqiom.com'],
        ['lastname' => 'CHEMMAM', 'firstname' => 'KAMEL', 'email' => 'baptiste.cornil@eqiom.com'],
        ['lastname' => 'BOUGHANEM', 'firstname' => 'ABDELATIF', 'email' => 'baptiste.cornil@eqiom.com'],
        ['lastname' => 'SANOGO', 'firstname' => 'ADAMA', 'email' => 'baptiste.cornil@eqiom.com'],
        ['lastname' => 'DIABY', 'firstname' => 'IBRAHIMA', 'email' => 'baptiste.cornil@eqiom.com'],
        ['lastname' => 'FOFANA', 'firstname' => 'OUMAR', 'email' => 'baptiste.cornil@eqiom.com'],
        ['lastname' => 'GIACALONE', 'firstname' => 'MAXIME', 'email' => 'herve.pellischek@eqiom.com'],
        ['lastname' => 'SOUEIDAN', 'firstname' => 'ALI', 'email' => 'baptiste.cornil@eqiom.com'],
        ['lastname' => 'PELLISCHEK', 'firstname' => 'HERVE', 'email' => 'herve.pellischek@eqiom.com'],
        ['lastname' => 'VANCENBROCK', 'firstname' => 'KEVIN', 'email' => 'baptiste.cornil@eqiom.com'],
        ['lastname' => 'YANG', 'firstname' => 'THIERRY', 'email' => 'baptiste.cornil@eqiom.com'],
        ['lastname' => 'PLUTA', 'firstname' => 'KRYSTIAN RYSZARD', 'email' => 'baptiste.cornil@eqiom.com'],
        ['lastname' => 'DUCTEIL', 'firstname' => 'JEAN PIERRE', 'email' => 'baptiste.cornil@eqiom.com'],
        ['lastname' => 'AFONSO LOPES', 'firstname' => 'ANTONIO', 'email' => 'baptiste.cornil@eqiom.com'],
        ['lastname' => 'SOUVANNAVONG', 'firstname' => 'SOURIGNA', 'email' => 'baptiste.cornil@eqiom.com'],
        ['lastname' => 'EVRAY', 'firstname' => 'KEVIN', 'email' => 'baptiste.cornil@eqiom.com'],
        ['lastname' => 'EVRAY', 'firstname' => 'DIMITRI', 'email' => 'baptiste.cornil@eqiom.com'],
        ['lastname' => 'VANCENBROCK', 'firstname' => 'BRYAN', 'email' => 'baptiste.cornil@eqiom.com'],
        ['lastname' => 'CORNIL', 'firstname' => 'BAPTISTE', 'email' => 'baptiste.cornil@eqiom.com'],
        ['lastname' => 'DOS SANTOS GONCALO', 'firstname' => 'JOAO', 'email' => 'herve.pellischek@eqiom.com'],
        ['lastname' => 'LEBLAY', 'firstname' => 'ALEXANDRE', 'email' => 'herve.pellischek@eqiom.com'],
        ['lastname' => 'FRELON', 'firstname' => 'PAULINE', 'email' => 'herve.pellischek@eqiom.com'],
        ['lastname' => 'BOSCHARINC', 'firstname' => 'FRANCK', 'email' => 'herve.pellischek@eqiom.com'],
        ['lastname' => 'DESCAS', 'firstname' => 'WILLIAM', 'email' => 'herve.pellischek@eqiom.com'],
        ['lastname' => 'SEMEDO BRITO', 'firstname' => 'AMILTON', 'email' => 'herve.pellischek@eqiom.com'],
        ['lastname' => 'KARAMOKO', 'firstname' => 'AMARA', 'email' => 'herve.pellischek@eqiom.com'],
        ['lastname' => 'CAMARA', 'firstname' => 'FOUSSEINI', 'email' => 'herve.pellischek@eqiom.com'],
        ['lastname' => 'DIARRA', 'firstname' => 'MAMEDY', 'email' => 'herve.pellischek@eqiom.com'],
        // Orly
        ['lastname' => 'SANGARE', 'firstname' => 'LACINA', 'email' => 'baptiste.cornil@eqiom.com'],
        ['lastname' => 'DIABY', 'firstname' => 'ISMAILA', 'email' => 'baptiste.cornil@eqiom.com'],
        ['lastname' => 'SILVA MOREIRA', 'firstname' => 'VICTOR ANTONIO', 'email' => 'baptiste.cornil@eqiom.com'],
        ['lastname' => 'BERTHUMEYRIE', 'firstname' => 'PASCAL', 'email' => 'baptiste.cornil@eqiom.com'],
        ['lastname' => 'AIT BESSAI', 'firstname' => 'ATHMANE', 'email' => 'baptiste.cornil@eqiom.com'],
        ['lastname' => 'NIANG', 'firstname' => 'MAME IBRAHIMA LAYE', 'email' => 'baptiste.cornil@eqiom.com'],
        ['lastname' => 'FOUQUET', 'firstname' => 'LUDOVIC', 'email' => 'herve.pellischek@eqiom.com'],
        ['lastname' => 'VANG', 'firstname' => 'SOU', 'email' => 'baptiste.cornil@eqiom.com'],
        ['lastname' => 'MOUA', 'firstname' => 'THONG', 'email' => 'baptiste.cornil@eqiom.com'],
        ['lastname' => 'VANG', 'firstname' => 'DAVID', 'email' => 'baptiste.cornil@eqiom.com'],
        ['lastname' => 'LOREDAN', 'firstname' => 'PIERROT', 'email' => 'jocelyn.cloatre@eqiom.com'],
        ['lastname' => 'VANG', 'firstname' => 'YEU', 'email' => 'jocelyn.cloatre@eqiom.com'],
        ['lastname' => 'ALLANO', 'firstname' => 'JEAN PIERRE', 'email' => 'jocelyn.cloatre@eqiom.com'],
    ];

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $isDryRun = $this->option('dry-run');

        if ($isDryRun) {
            $this->warn('Mode dry-run : aucune écriture en base.');
        }

        // --- Client ---
        $this->info('');
        $this->info('=== Client ===');

        $existingClient = Client::where('company_name', 'EQIOM BETONS')->first();

        if ($isDryRun) {
            if ($existingClient) {
                $this->line("  [existant] Client \"EQIOM BETONS\" (id: {$existingClient->id})");
            } else {
                $this->line('  [à créer]  Client "EQIOM"');
            }
            $client = $existingClient ?? new Client(['id' => 0]);
        } else {
            [$client, $clientCreated] = $this->firstOrCreateClient();

            if ($clientCreated) {
                $this->info("  [créé]     Client \"EQIOM BETONS\" (id: {$client->id})");
            } else {
                $this->line("  [existant] Client \"EQIOM BETONS\" (id: {$client->id})");
            }
        }

        // --- Collaborateurs ---
        $this->info('');
        $this->info('=== Collaborateurs ===');

        $created = 0;
        $skipped = 0;

        foreach ($this->coworkers as $data) {
            if ($isDryRun) {
                $exists = $existingClient
                    ? Coworker::where('client_id', $existingClient->id)
                        ->where('lastname', $data['lastname'])
                        ->where('firstname', $data['firstname'])
                        ->exists()
                    : false;

                if ($exists) {
                    $this->line("  [existant] {$data['lastname']} {$data['firstname']}");
                    $skipped++;
                } else {
                    $this->line("  [à créer]  {$data['lastname']} {$data['firstname']} <{$data['email']}>");
                    $created++;
                }

                continue;
            }

            $coworker = Coworker::firstOrCreate(
                [
                    'client_id' => $client->id,
                    'lastname' => $data['lastname'],
                    'firstname' => $data['firstname'],
                ],
                [
                    'email' => $data['email'],
                    'phone' => '0000000000',
                ]
            );

            if ($coworker->wasRecentlyCreated) {
                $this->info("  [créé]     {$data['lastname']} {$data['firstname']}");
                $created++;
            } else {
                $this->line("  [existant] {$data['lastname']} {$data['firstname']}");
                $skipped++;
            }
        }

        $this->info('');
        $this->info('=== Résumé ===');
        $this->info("  Collaborateurs créés   : {$created}");
        $this->line("  Collaborateurs existants : {$skipped}");
        $this->info('');

        return self::SUCCESS;
    }

    /**
     * @return array{Client, bool}
     */
    private function firstOrCreateClient(): array
    {
        $created = false;

        $client = Client::where('company_name', 'EQIOM')->first();

        if (! $client) {
            $client = Client::create([
                'company_name' => 'EQIOM',
                'trade_name' => 'Eqiom betons',
                'siret_number' => '',
                'address' => '',
                'zip_code' => '',
                'city' => '',
                'kbis_document' => '',
                'safety_document' => '',
                'security_document' => '',
                'notification_email' => 'baptiste.cornil@eqiom.com',
                'slug' => Str::uuid(),
                'is_airline_company' => false,
            ]);
            $created = true;
        }

        return [$client, $created];
    }
}
