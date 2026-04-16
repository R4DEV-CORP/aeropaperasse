<?php

namespace App\Console\Commands;

use App\Models\Badge;
use App\Models\Client;
use App\Models\Coworker;
use App\Models\CoworkerTraining;
use App\Models\Training;
use Carbon\Carbon;
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
    protected $description = 'Importe le client EQIOM BETONS, ses 47 collaborateurs et leurs formations (idempotent)';

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
     * @var array<int, array{lastname: string, firstname: string, badge_number: string, expiry_date: string}>
     */
    private array $badges = [
        // CDG
        ['lastname' => 'HAIDARA',                  'firstname' => 'IBRAHIMA',           'badge_number' => '111200399278', 'expiry_date' => '2027-08-05'],
        ['lastname' => 'SALLA',                    'firstname' => 'ENCA MALIQUE',       'badge_number' => '111200399312', 'expiry_date' => '2027-12-31'],
        ['lastname' => 'RAMOS MIRANDA',            'firstname' => 'CLAUDINO',           'badge_number' => '111200399084', 'expiry_date' => '2027-07-16'],
        ['lastname' => 'DA SILVA MOREIRA BARRETO', 'firstname' => 'JOAO',               'badge_number' => '111200399085', 'expiry_date' => '2027-07-08'],
        ['lastname' => 'ZERROUK',                  'firstname' => 'MOHAMED',            'badge_number' => '111200399094', 'expiry_date' => '2027-12-31'],
        ['lastname' => 'CHEMMAM',                  'firstname' => 'KAMEL',              'badge_number' => '111200399086', 'expiry_date' => '2027-07-03'],
        ['lastname' => 'BOUGHANEM',                'firstname' => 'ABDELATIF',          'badge_number' => '111200399087', 'expiry_date' => '2027-07-02'],
        ['lastname' => 'SANOGO',                   'firstname' => 'ADAMA',              'badge_number' => '111200399076', 'expiry_date' => '2027-07-02'],
        ['lastname' => 'DIABY',                    'firstname' => 'IBRAHIMA',           'badge_number' => '111200399093', 'expiry_date' => '2027-12-31'],
        ['lastname' => 'FOFANA',                   'firstname' => 'OUMAR',              'badge_number' => '111200399077', 'expiry_date' => '2027-07-05'],
        ['lastname' => 'GIACALONE',                'firstname' => 'MAXIME',             'badge_number' => '111200399023', 'expiry_date' => '2027-07-02'],
        ['lastname' => 'SOUEIDAN',                 'firstname' => 'ALI',                'badge_number' => '111200399270', 'expiry_date' => '2027-12-31'],
        ['lastname' => 'PELLISCHEK',               'firstname' => 'HERVE',              'badge_number' => '111200398888', 'expiry_date' => '2027-06-27'],
        ['lastname' => 'VANCENBROCK',              'firstname' => 'KEVIN',              'badge_number' => '111200399043', 'expiry_date' => '2026-12-12'],
        ['lastname' => 'YANG',                     'firstname' => 'THIERRY',            'badge_number' => '111200398782', 'expiry_date' => '2026-11-29'],
        ['lastname' => 'PLUTA',                    'firstname' => 'KRYSTIAN RYSZARD',   'badge_number' => '111200398780', 'expiry_date' => '2027-12-31'],
        ['lastname' => 'DUCTEIL',                  'firstname' => 'JEAN PIERRE',        'badge_number' => '111200398781', 'expiry_date' => '2026-11-29'],
        ['lastname' => 'AFONSO LOPES',             'firstname' => 'ANTONIO',            'badge_number' => '111200398794', 'expiry_date' => '2027-12-31'],
        ['lastname' => 'SOUVANNAVONG',             'firstname' => 'SOURIGNA',           'badge_number' => '111200398773', 'expiry_date' => '2027-02-26'],
        ['lastname' => 'EVRAY',                    'firstname' => 'KEVIN',              'badge_number' => '111200398769', 'expiry_date' => '2027-07-01'],
        ['lastname' => 'EVRAY',                    'firstname' => 'DIMITRI',            'badge_number' => '111200398770', 'expiry_date' => '2027-03-27'],
        ['lastname' => 'VANCENBROCK',              'firstname' => 'BRYAN',              'badge_number' => '111200398234', 'expiry_date' => '2026-12-31'],
        ['lastname' => 'CORNIL',                   'firstname' => 'BAPTISTE',           'badge_number' => '111200397265', 'expiry_date' => '2026-12-31'],
        // Orly
        ['lastname' => 'NIANG',                    'firstname' => 'MAME IBRAHIMA LAYE', 'badge_number' => '101200413402', 'expiry_date' => '2027-12-31'],
        ['lastname' => 'VANG',                     'firstname' => 'SOU',                'badge_number' => '101200412872', 'expiry_date' => '2027-06-30'],
        ['lastname' => 'MOUA',                     'firstname' => 'THONG',              'badge_number' => '101200412875', 'expiry_date' => '2027-06-30'],
        ['lastname' => 'VANG',                     'firstname' => 'DAVID',              'badge_number' => '101200412876', 'expiry_date' => '2027-06-30'],
        ['lastname' => 'LOREDAN',                  'firstname' => 'PIERROT',            'badge_number' => '101200412122', 'expiry_date' => '2027-04-16'],
        ['lastname' => 'VANG',                     'firstname' => 'YEU',                'badge_number' => '101200412108', 'expiry_date' => '2027-04-16'],
        ['lastname' => 'ALLANO',                   'firstname' => 'JEAN PIERRE',        'badge_number' => '101200411994', 'expiry_date' => '2027-02-26'],
        // Le Bourget
        ['lastname' => 'PELLISCHEK',               'firstname' => 'HERVE',              'badge_number' => '121200066028', 'expiry_date' => '2026-12-31'],
        ['lastname' => 'SEMEDO BRITO',             'firstname' => 'AMILTON',            'badge_number' => '121200066025', 'expiry_date' => '2026-12-31'],
        ['lastname' => 'PLUTA',                    'firstname' => 'KRYSTIAN RYSZARD',   'badge_number' => '121200066026', 'expiry_date' => '2026-12-31'],
        ['lastname' => 'SOUVANNAVONG',             'firstname' => 'SOURIGNA',           'badge_number' => '121200066029', 'expiry_date' => '2026-12-31'],
        ['lastname' => 'DUCTEIL',                  'firstname' => 'JEAN PIERRE',        'badge_number' => '121200066027', 'expiry_date' => '2026-11-29'],
    ];

    /**
     * @var array<int, array{lastname: string, firstname: string, training: string, started_at: string, expires_at: string|null}>
     */
    private array $coworkerFormations = [
        // PELLISCHEK HERVE
        ['lastname' => 'PELLISCHEK', 'firstname' => 'HERVE', 'training' => '11.2.2',    'started_at' => '2022-10-07', 'expires_at' => null],
        ['lastname' => 'PELLISCHEK', 'firstname' => 'HERVE', 'training' => '11.2.5',    'started_at' => '2022-10-07', 'expires_at' => '2027-10-06'],
        ['lastname' => 'PELLISCHEK', 'firstname' => 'HERVE', 'training' => '11.2.3.10', 'started_at' => '2023-11-13', 'expires_at' => '2028-11-11'],
        ['lastname' => 'PELLISCHEK', 'firstname' => 'HERVE', 'training' => '11.2.3.4',  'started_at' => '2025-11-18', 'expires_at' => null],
        ['lastname' => 'PELLISCHEK', 'firstname' => 'HERVE', 'training' => '11.2.6.2',  'started_at' => '2024-05-28', 'expires_at' => null],
        ['lastname' => 'PELLISCHEK', 'firstname' => 'HERVE', 'training' => 'Permis T',  'started_at' => '2024-05-30', 'expires_at' => null],
        // CORNIL BAPTISTE
        ['lastname' => 'CORNIL', 'firstname' => 'BAPTISTE', 'training' => '11.2.2',    'started_at' => '2025-03-03', 'expires_at' => null],
        ['lastname' => 'CORNIL', 'firstname' => 'BAPTISTE', 'training' => '11.2.5',    'started_at' => '2025-01-15', 'expires_at' => '2030-01-15'],
        ['lastname' => 'CORNIL', 'firstname' => 'BAPTISTE', 'training' => '11.2.3.10', 'started_at' => '2025-02-18', 'expires_at' => '2030-02-18'],
        ['lastname' => 'CORNIL', 'firstname' => 'BAPTISTE', 'training' => '11.2.3.4',  'started_at' => '2025-11-18', 'expires_at' => null],
        // DIABY IBRAHIMA
        ['lastname' => 'DIABY', 'firstname' => 'IBRAHIMA', 'training' => '11.2.3.10', 'started_at' => '2026-02-26', 'expires_at' => '2031-02-26'],
        // EVRAY KEVIN
        ['lastname' => 'EVRAY', 'firstname' => 'KEVIN', 'training' => '11.2.3.10', 'started_at' => '2023-11-27', 'expires_at' => '2028-11-27'],
        // Permis T
        ['lastname' => 'HAIDARA',                    'firstname' => 'IBRAHIMA',           'training' => 'Permis T', 'started_at' => '2024-09-26', 'expires_at' => null],
        ['lastname' => 'RAMOS MIRANDA',              'firstname' => 'CLAUDINO',           'training' => 'Permis T', 'started_at' => '2024-09-24', 'expires_at' => null],
        ['lastname' => 'DA SILVA MOREIRA BARRETO',   'firstname' => 'JOAO',               'training' => 'Permis T', 'started_at' => '2024-08-13', 'expires_at' => null],
        ['lastname' => 'ZERROUK',                    'firstname' => 'MOHAMED',            'training' => 'Permis T', 'started_at' => '2026-02-12', 'expires_at' => null],
        ['lastname' => 'CHEMMAM',                    'firstname' => 'KAMEL',              'training' => 'Permis T', 'started_at' => '2024-09-24', 'expires_at' => null],
        ['lastname' => 'BOUGHANEM',                  'firstname' => 'ABDELATIF',          'training' => 'Permis T', 'started_at' => '2024-08-13', 'expires_at' => null],
        ['lastname' => 'SANOGO',                     'firstname' => 'ADAMA',              'training' => 'Permis T', 'started_at' => '2024-08-13', 'expires_at' => null],
        ['lastname' => 'FOFANA',                     'firstname' => 'OUMAR',              'training' => 'Permis T', 'started_at' => '2024-09-03', 'expires_at' => null],
        ['lastname' => 'YANG',                       'firstname' => 'THIERRY',            'training' => 'Permis T', 'started_at' => '2026-02-18', 'expires_at' => null],
        ['lastname' => 'PLUTA',                      'firstname' => 'KRYSTIAN RYSZARD',   'training' => 'Permis T', 'started_at' => '2024-09-24', 'expires_at' => null],
        ['lastname' => 'DUCTEIL',                    'firstname' => 'JEAN PIERRE',        'training' => 'Permis T', 'started_at' => '2026-01-15', 'expires_at' => null],
        ['lastname' => 'AFONSO LOPES',               'firstname' => 'ANTONIO',            'training' => 'Permis T', 'started_at' => '2025-05-20', 'expires_at' => null],
        ['lastname' => 'SOUVANNAVONG',               'firstname' => 'SOURIGNA',           'training' => 'Permis T', 'started_at' => '2026-02-12', 'expires_at' => null],
        ['lastname' => 'EVRAY',                      'firstname' => 'DIMITRI',            'training' => 'Permis T', 'started_at' => '2025-12-09', 'expires_at' => null],
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
        $this->info('=== Résumé collaborateurs ===');
        $this->info("  Collaborateurs créés   : {$created}");
        $this->line("  Collaborateurs existants : {$skipped}");

        // --- Formations ---
        $this->importFormations($client, $isDryRun);

        // --- Badges ---
        $this->importBadges($client, $isDryRun);

        return self::SUCCESS;
    }

    private function importFormations(Client $client, bool $isDryRun): void
    {
        $this->info('');
        $this->info('=== Formations ===');

        // Créer les formations manquantes
        $trainingsToCreate = ['11.2.2', '11.2.5', '11.2.3.4'];

        foreach ($trainingsToCreate as $title) {
            if ($isDryRun) {
                $exists = Training::where('title', $title)->exists();
                $this->line($exists
                    ? "  [existant] Formation \"{$title}\""
                    : "  [à créer]  Formation \"{$title}\""
                );
            } else {
                $training = Training::firstOrCreate(['title' => $title]);
                $this->line($training->wasRecentlyCreated
                    ? "  <info>[créé]</info>     Formation \"{$title}\""
                    : "  [existant] Formation \"{$title}\""
                );
            }
        }

        if ($isDryRun) {
            return;
        }

        // Résoudre les training IDs par titre (11.2.6.2 a un titre étendu en base)
        $trainingIds = [
            '11.2.2' => Training::where('title', '11.2.2')->value('id'),
            '11.2.5' => Training::where('title', '11.2.5')->value('id'),
            '11.2.3.10' => Training::where('title', '11.2.3.10')->value('id'),
            '11.2.3.4' => Training::where('title', '11.2.3.4')->value('id'),
            '11.2.6.2' => Training::where('title', 'like', '11.2.6.2%')->value('id'),
            'Permis T' => Training::where('title', 'Permis T')->value('id'),
        ];

        $this->info('');
        $this->info('=== Inscriptions formations ===');

        $created = 0;
        $skipped = 0;

        foreach ($this->coworkerFormations as $entry) {
            $trainingKey = $entry['training'];
            $trainingId = $trainingIds[$trainingKey] ?? null;

            if (! $trainingId) {
                $this->warn("  [ignoré]   Formation \"{$trainingKey}\" introuvable en base.");

                continue;
            }

            $coworker = Coworker::where('client_id', $client->id)
                ->where('lastname', $entry['lastname'])
                ->where('firstname', $entry['firstname'])
                ->first();

            if (! $coworker) {
                $this->warn("  [ignoré]   Collaborateur \"{$entry['lastname']} {$entry['firstname']}\" introuvable.");

                continue;
            }

            $startedAt = Carbon::parse($entry['started_at']);
            $expiresAt = $this->computeExpiresAt($trainingKey, $startedAt, $entry['expires_at'] ?? null);

            $record = CoworkerTraining::firstOrCreate(
                ['coworker_id' => $coworker->id, 'training_id' => $trainingId],
                ['started_at' => $startedAt, 'expires_at' => $expiresAt]
            );

            if ($record->wasRecentlyCreated) {
                $this->info("  [créé]     {$entry['lastname']} {$entry['firstname']} — {$trainingKey}");
                $created++;
            } else {
                $this->line("  [existant] {$entry['lastname']} {$entry['firstname']} — {$trainingKey}");
                $skipped++;
            }
        }

        $this->info('');
        $this->info('=== Résumé formations ===');
        $this->info("  Inscriptions créées   : {$created}");
        $this->line("  Inscriptions existantes : {$skipped}");
        $this->info('');
    }

    private function computeExpiresAt(string $trainingKey, Carbon $startedAt, ?string $explicitExpiry): ?Carbon
    {
        if ($trainingKey === '11.2.2') {
            return null;
        }

        if ($explicitExpiry !== null) {
            return Carbon::parse($explicitExpiry);
        }

        return match ($trainingKey) {
            '11.2.3.4' => $startedAt->copy()->addYears(5),
            '11.2.6.2' => $startedAt->copy()->addYears(3),
            'Permis T' => $startedAt->copy()->addYears(2),
            default => null,
        };
    }

    private function importBadges(Client $client, bool $isDryRun): void
    {
        $this->info('');
        $this->info('=== Badges ===');

        $created = 0;
        $skipped = 0;

        foreach ($this->badges as $entry) {
            if ($isDryRun) {
                // Si le client n'existe pas encore en base, on ne peut pas résoudre les coworkers
                if (! $client->id) {
                    $this->line("  [à créer]  {$entry['lastname']} {$entry['firstname']} — badge {$entry['badge_number']} (expire {$entry['expiry_date']})");
                    $created++;
                } else {
                    $exists = Badge::where('badge_number', $entry['badge_number'])->exists();

                    if ($exists) {
                        $this->line("  [existant] {$entry['lastname']} {$entry['firstname']} — badge {$entry['badge_number']}");
                        $skipped++;
                    } else {
                        $this->line("  [à créer]  {$entry['lastname']} {$entry['firstname']} — badge {$entry['badge_number']} (expire {$entry['expiry_date']})");
                        $created++;
                    }
                }

                continue;
            }

            $coworker = Coworker::where('client_id', $client->id)
                ->where('lastname', $entry['lastname'])
                ->where('firstname', $entry['firstname'])
                ->first();

            if (! $coworker) {
                $this->warn("  [ignoré]   {$entry['lastname']} {$entry['firstname']} — collaborateur introuvable.");

                continue;
            }

            $badge = Badge::firstOrCreate(
                ['badge_number' => $entry['badge_number']],
                [
                    'client_id' => $client->id,
                    'coworker_id' => $coworker->id,
                    'status' => 'active',
                    'expiry_date' => Carbon::parse($entry['expiry_date']),
                ]
            );

            if ($badge->wasRecentlyCreated) {
                $this->info("  [créé]     {$entry['lastname']} {$entry['firstname']} — badge {$entry['badge_number']}");
                $created++;
            } else {
                $this->line("  [existant] {$entry['lastname']} {$entry['firstname']} — badge {$entry['badge_number']}");
                $skipped++;
            }
        }

        $this->info('');
        $this->info('=== Résumé badges ===');
        $this->info("  Badges créés    : {$created}");
        $this->line("  Badges existants : {$skipped}");
        $this->info('');
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
