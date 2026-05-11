<?php

namespace App\Console\Commands;

use App\Models\Badge;
use App\Models\Client;
use App\Models\Coworker;
use App\Models\CoworkerTraining;
use App\Models\Training;
use App\Models\User;
use App\Models\VehiclePass;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class ImportRazelBecCoworkers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clients:import-razel-bec
                            {--dry-run : Affiche ce qui serait créé sans écrire en base}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Importe le client RAZEL-BEC, ses collaborateurs, badges, formations et laissez-passer véhicules (idempotent)';

    private const AIRPORT = 'CDG';

    /**
     * @var array<int, array{lastname: string, firstname: string, email: ?string, phone: ?string, has_leave: bool, departure_date: ?string}>
     */
    private array $coworkers = [
        // === ACTIFS (sheet "Badges TCA" rows 36-138) ===
        ['lastname' => 'Alves Goundim de Sousa', 'firstname' => 'Karine', 'email' => null, 'phone' => null, 'has_leave' => false, 'departure_date' => null],
        ['lastname' => 'Akhlaqi', 'firstname' => 'Nasrullah', 'email' => null, 'phone' => null, 'has_leave' => false, 'departure_date' => null],
        ['lastname' => 'Anton', 'firstname' => 'Emmanuel', 'email' => null, 'phone' => null, 'has_leave' => false, 'departure_date' => null],
        ['lastname' => 'Ardiot', 'firstname' => 'Laurence', 'email' => null, 'phone' => null, 'has_leave' => false, 'departure_date' => null],
        ['lastname' => 'Balon', 'firstname' => 'Philippe', 'email' => null, 'phone' => null, 'has_leave' => false, 'departure_date' => null],
        ['lastname' => 'Baraté', 'firstname' => 'Pascal', 'email' => null, 'phone' => null, 'has_leave' => false, 'departure_date' => null],
        ['lastname' => 'Barriga', 'firstname' => 'Laura', 'email' => null, 'phone' => null, 'has_leave' => false, 'departure_date' => null],
        ['lastname' => 'Bayoud', 'firstname' => 'Rachid', 'email' => null, 'phone' => null, 'has_leave' => false, 'departure_date' => null],
        ['lastname' => 'Bechar', 'firstname' => 'Saïda', 'email' => null, 'phone' => null, 'has_leave' => false, 'departure_date' => null],
        ['lastname' => 'Begni', 'firstname' => 'Michaël', 'email' => null, 'phone' => null, 'has_leave' => false, 'departure_date' => null],
        ['lastname' => 'Ben Kaci', 'firstname' => 'Djamel', 'email' => null, 'phone' => null, 'has_leave' => false, 'departure_date' => null],
        ['lastname' => 'Bernardo', 'firstname' => 'Gabriel', 'email' => null, 'phone' => null, 'has_leave' => false, 'departure_date' => null],
        ['lastname' => 'Boulfroy de Saint Aubin', 'firstname' => 'Antoine', 'email' => null, 'phone' => null, 'has_leave' => false, 'departure_date' => null],
        ['lastname' => 'Bourgault', 'firstname' => 'Frédéric', 'email' => null, 'phone' => null, 'has_leave' => false, 'departure_date' => null],
        ['lastname' => 'Bouzazi', 'firstname' => 'Faicel', 'email' => null, 'phone' => null, 'has_leave' => false, 'departure_date' => null],
        ['lastname' => 'Bouzekraoui', 'firstname' => 'Mohamed', 'email' => null, 'phone' => null, 'has_leave' => false, 'departure_date' => null],
        ['lastname' => 'Cadalen', 'firstname' => 'Jean-Michel', 'email' => null, 'phone' => null, 'has_leave' => false, 'departure_date' => null],
        ['lastname' => 'Cerisier', 'firstname' => 'Alain', 'email' => null, 'phone' => null, 'has_leave' => false, 'departure_date' => null],
        ['lastname' => 'Cestero', 'firstname' => 'Thibaut', 'email' => null, 'phone' => null, 'has_leave' => false, 'departure_date' => null],
        ['lastname' => 'Chapon', 'firstname' => 'Laurent', 'email' => null, 'phone' => null, 'has_leave' => false, 'departure_date' => null],
        ['lastname' => 'Cherifi', 'firstname' => 'Killian', 'email' => null, 'phone' => null, 'has_leave' => false, 'departure_date' => null],
        ['lastname' => 'Coelho Lourenço Rodrigues', 'firstname' => 'Ivo', 'email' => null, 'phone' => null, 'has_leave' => false, 'departure_date' => null],
        ['lastname' => 'Corbetta', 'firstname' => 'François', 'email' => null, 'phone' => null, 'has_leave' => false, 'departure_date' => null],
        ['lastname' => 'Cotillon', 'firstname' => 'Bruno', 'email' => null, 'phone' => null, 'has_leave' => false, 'departure_date' => null],
        ['lastname' => 'Da Silva Oliveira', 'firstname' => 'Filipe', 'email' => null, 'phone' => null, 'has_leave' => false, 'departure_date' => null],
        ['lastname' => 'Dahmani', 'firstname' => 'Mustapha', 'email' => null, 'phone' => null, 'has_leave' => false, 'departure_date' => null],
        ['lastname' => 'De Almeida Clara Atiningi', 'firstname' => 'Indra', 'email' => null, 'phone' => null, 'has_leave' => false, 'departure_date' => null],
        ['lastname' => 'De Almeida Pina', 'firstname' => 'Humberto', 'email' => null, 'phone' => null, 'has_leave' => false, 'departure_date' => null],
        ['lastname' => 'Da Cruz Roca', 'firstname' => 'Mickael', 'email' => null, 'phone' => null, 'has_leave' => false, 'departure_date' => null],
        ['lastname' => 'Daegle', 'firstname' => 'Thierry', 'email' => null, 'phone' => null, 'has_leave' => false, 'departure_date' => null],
        ['lastname' => 'Deroo', 'firstname' => 'Jean-Luc', 'email' => null, 'phone' => null, 'has_leave' => false, 'departure_date' => null],
        ['lastname' => 'Desplan', 'firstname' => 'Dimitry', 'email' => null, 'phone' => null, 'has_leave' => false, 'departure_date' => null],
        ['lastname' => 'Dias', 'firstname' => 'Louis', 'email' => null, 'phone' => null, 'has_leave' => false, 'departure_date' => null],
        ['lastname' => 'Djemai', 'firstname' => 'Mohammed Riyadh', 'email' => null, 'phone' => null, 'has_leave' => false, 'departure_date' => null],
        ['lastname' => 'Douet', 'firstname' => 'Pierre-Emmanuel', 'email' => null, 'phone' => null, 'has_leave' => false, 'departure_date' => null],
        ['lastname' => 'Elasry', 'firstname' => 'Elmehdi', 'email' => null, 'phone' => null, 'has_leave' => false, 'departure_date' => null],
        ['lastname' => 'Fatien', 'firstname' => 'Christophe', 'email' => null, 'phone' => null, 'has_leave' => false, 'departure_date' => null],
        ['lastname' => 'Favin-Lévêque', 'firstname' => 'Thibault', 'email' => 'thibault.favin-leveque@universite-paris-saclay.fr', 'phone' => '+33 6 99 74 97 18', 'has_leave' => false, 'departure_date' => null],
        ['lastname' => 'Ferrec', 'firstname' => 'James', 'email' => null, 'phone' => null, 'has_leave' => false, 'departure_date' => null],
        ['lastname' => 'Fertani', 'firstname' => 'Mathias', 'email' => null, 'phone' => null, 'has_leave' => false, 'departure_date' => null],
        ['lastname' => 'Gaudry', 'firstname' => 'Didier', 'email' => null, 'phone' => null, 'has_leave' => false, 'departure_date' => null],
        ['lastname' => 'Geara', 'firstname' => 'Patrick', 'email' => null, 'phone' => null, 'has_leave' => false, 'departure_date' => null],
        ['lastname' => 'Goundiam', 'firstname' => 'Samba', 'email' => null, 'phone' => null, 'has_leave' => false, 'departure_date' => null],
        ['lastname' => 'Grondin', 'firstname' => 'Guillaume', 'email' => null, 'phone' => null, 'has_leave' => false, 'departure_date' => null],
        ['lastname' => 'Hamonet', 'firstname' => 'Vincent', 'email' => null, 'phone' => null, 'has_leave' => false, 'departure_date' => null],
        ['lastname' => 'Hbarrou', 'firstname' => 'Youssef', 'email' => null, 'phone' => null, 'has_leave' => false, 'departure_date' => null],
        ['lastname' => 'Hdidouane', 'firstname' => 'Ilham', 'email' => null, 'phone' => null, 'has_leave' => false, 'departure_date' => null],
        ['lastname' => 'Hemard', 'firstname' => 'Alexis', 'email' => null, 'phone' => null, 'has_leave' => false, 'departure_date' => null],
        ['lastname' => 'Hijazi', 'firstname' => 'Yassine', 'email' => null, 'phone' => null, 'has_leave' => false, 'departure_date' => null],
        ['lastname' => 'Hoareau', 'firstname' => 'Jean-Patrice', 'email' => null, 'phone' => null, 'has_leave' => false, 'departure_date' => null],
        ['lastname' => 'Hogrel', 'firstname' => 'François', 'email' => null, 'phone' => null, 'has_leave' => false, 'departure_date' => null],
        ['lastname' => 'Home', 'firstname' => 'Frédéric', 'email' => null, 'phone' => null, 'has_leave' => false, 'departure_date' => null],
        ['lastname' => 'Kervarec', 'firstname' => 'Loïc', 'email' => null, 'phone' => null, 'has_leave' => false, 'departure_date' => null],
        ['lastname' => 'Kress', 'firstname' => 'David', 'email' => null, 'phone' => null, 'has_leave' => false, 'departure_date' => null],
        ['lastname' => 'Labres', 'firstname' => 'Kaddour', 'email' => null, 'phone' => null, 'has_leave' => false, 'departure_date' => null],
        ['lastname' => 'Lagoa', 'firstname' => 'Samuel', 'email' => null, 'phone' => null, 'has_leave' => false, 'departure_date' => null],
        ['lastname' => 'Lamarque', 'firstname' => 'Sophie', 'email' => null, 'phone' => null, 'has_leave' => false, 'departure_date' => null],
        ['lastname' => 'Lasfargue', 'firstname' => 'François', 'email' => null, 'phone' => null, 'has_leave' => false, 'departure_date' => null],
        ['lastname' => 'Lemaire', 'firstname' => 'Laurent', 'email' => null, 'phone' => null, 'has_leave' => false, 'departure_date' => null],
        ['lastname' => 'Leroy', 'firstname' => 'David', 'email' => null, 'phone' => null, 'has_leave' => false, 'departure_date' => null],
        ['lastname' => 'Luquet', 'firstname' => 'Eric', 'email' => null, 'phone' => null, 'has_leave' => false, 'departure_date' => null],
        ['lastname' => 'Manojlovic', 'firstname' => 'Gabriel', 'email' => null, 'phone' => null, 'has_leave' => false, 'departure_date' => null],
        ['lastname' => 'Marques Monteiro', 'firstname' => 'Paulo David', 'email' => null, 'phone' => null, 'has_leave' => false, 'departure_date' => null],
        ['lastname' => 'Marques Pereira', 'firstname' => 'Cédric', 'email' => null, 'phone' => null, 'has_leave' => false, 'departure_date' => null],
        ['lastname' => 'Mercier', 'firstname' => 'Eric', 'email' => null, 'phone' => null, 'has_leave' => false, 'departure_date' => null],
        ['lastname' => 'Moncieu', 'firstname' => 'Gaetan', 'email' => null, 'phone' => null, 'has_leave' => false, 'departure_date' => null],
        ['lastname' => 'Monroc', 'firstname' => 'Dylan', 'email' => null, 'phone' => null, 'has_leave' => false, 'departure_date' => null],
        ['lastname' => 'Moreira Da Silva Sousa', 'firstname' => 'José', 'email' => null, 'phone' => null, 'has_leave' => false, 'departure_date' => null],
        ['lastname' => 'Morin', 'firstname' => 'Florent', 'email' => null, 'phone' => null, 'has_leave' => false, 'departure_date' => null],
        ['lastname' => 'Mpode', 'firstname' => 'Pierre-Berlin', 'email' => 'b.mpode@razel-bec.fayat.com', 'phone' => '+33 7 82 84 85 24', 'has_leave' => false, 'departure_date' => null],
        ['lastname' => 'Mugnier', 'firstname' => 'Arthur', 'email' => null, 'phone' => null, 'has_leave' => false, 'departure_date' => null],
        ['lastname' => 'Naceri', 'firstname' => 'Karim', 'email' => null, 'phone' => null, 'has_leave' => false, 'departure_date' => null],
        ['lastname' => 'Parisot', 'firstname' => 'Richard', 'email' => null, 'phone' => null, 'has_leave' => false, 'departure_date' => null],
        ['lastname' => 'Petitpas', 'firstname' => 'Mickael', 'email' => null, 'phone' => null, 'has_leave' => false, 'departure_date' => null],
        ['lastname' => 'Pisano', 'firstname' => 'Pascal', 'email' => null, 'phone' => null, 'has_leave' => false, 'departure_date' => null],
        ['lastname' => 'Planque', 'firstname' => 'Jean-Michel', 'email' => null, 'phone' => null, 'has_leave' => false, 'departure_date' => null],
        ['lastname' => 'Pouemi Pouemi', 'firstname' => 'Ali', 'email' => null, 'phone' => null, 'has_leave' => false, 'departure_date' => null],
        ['lastname' => 'Prudent', 'firstname' => 'Ismael', 'email' => null, 'phone' => null, 'has_leave' => false, 'departure_date' => null],
        ['lastname' => 'Prybis', 'firstname' => 'Valentin', 'email' => null, 'phone' => null, 'has_leave' => false, 'departure_date' => null],
        ['lastname' => 'Ravel', 'firstname' => 'Fabrice', 'email' => null, 'phone' => null, 'has_leave' => false, 'departure_date' => null],
        ['lastname' => 'Ricart', 'firstname' => 'Guillaume', 'email' => null, 'phone' => null, 'has_leave' => false, 'departure_date' => null],
        ['lastname' => 'Rodrigues', 'firstname' => 'Philippe', 'email' => null, 'phone' => null, 'has_leave' => false, 'departure_date' => null],
        ['lastname' => 'Ruel', 'firstname' => 'Baptiste', 'email' => null, 'phone' => null, 'has_leave' => false, 'departure_date' => null],
        ['lastname' => 'Soidiki', 'firstname' => 'Nayoum', 'email' => null, 'phone' => null, 'has_leave' => false, 'departure_date' => null],
        ['lastname' => 'Spineux', 'firstname' => 'Olivier', 'email' => null, 'phone' => null, 'has_leave' => false, 'departure_date' => null],
        ['lastname' => 'Sababady', 'firstname' => 'Jean-Fabrice', 'email' => null, 'phone' => null, 'has_leave' => false, 'departure_date' => null],
        ['lastname' => 'Silla', 'firstname' => 'Lassana', 'email' => null, 'phone' => null, 'has_leave' => false, 'departure_date' => null],
        ['lastname' => 'Simon', 'firstname' => 'David', 'email' => null, 'phone' => null, 'has_leave' => false, 'departure_date' => null],
        ['lastname' => 'Smal', 'firstname' => 'Luc', 'email' => null, 'phone' => null, 'has_leave' => false, 'departure_date' => null],
        ['lastname' => 'Sureau', 'firstname' => 'Valentin', 'email' => null, 'phone' => null, 'has_leave' => false, 'departure_date' => null],
        ['lastname' => 'Swiercz', 'firstname' => 'Aldo Zbigniew', 'email' => null, 'phone' => null, 'has_leave' => false, 'departure_date' => null],
        ['lastname' => 'Sylla', 'firstname' => 'Momodouba Almamy', 'email' => null, 'phone' => null, 'has_leave' => false, 'departure_date' => null],
        ['lastname' => 'Tanasi', 'firstname' => 'Franck', 'email' => null, 'phone' => null, 'has_leave' => false, 'departure_date' => null],
        ['lastname' => 'Tarakdjian', 'firstname' => 'Pierre', 'email' => null, 'phone' => null, 'has_leave' => false, 'departure_date' => null],
        ['lastname' => 'Toucas', 'firstname' => 'Mikael', 'email' => null, 'phone' => null, 'has_leave' => false, 'departure_date' => null],
        ['lastname' => 'Tremolieres', 'firstname' => 'Mathias', 'email' => null, 'phone' => null, 'has_leave' => false, 'departure_date' => null],
        ['lastname' => 'Vaillant', 'firstname' => 'Anaïs', 'email' => 'anais.vaillant13@gmail.com', 'phone' => '+33 6 87 99 96 47', 'has_leave' => false, 'departure_date' => null],
        ['lastname' => 'Vaz Alexandre', 'firstname' => 'Andréa', 'email' => null, 'phone' => null, 'has_leave' => false, 'departure_date' => null],
        ['lastname' => 'Verbeke', 'firstname' => 'Roland', 'email' => null, 'phone' => null, 'has_leave' => false, 'departure_date' => null],
        ['lastname' => 'Villedary', 'firstname' => 'Antoine', 'email' => null, 'phone' => null, 'has_leave' => false, 'departure_date' => null],
        ['lastname' => 'Vouhe', 'firstname' => 'Louis', 'email' => null, 'phone' => null, 'has_leave' => false, 'departure_date' => null],
        ['lastname' => 'Wittmann Le Belin de Chatellenot', 'firstname' => 'Jean', 'email' => null, 'phone' => null, 'has_leave' => false, 'departure_date' => null],
        ['lastname' => 'Zanoncelli', 'firstname' => 'Iris', 'email' => null, 'phone' => null, 'has_leave' => false, 'departure_date' => null],
        ['lastname' => 'Barkate', 'firstname' => 'Eva', 'email' => null, 'phone' => null, 'has_leave' => true, 'departure_date' => '2025-08-27'],
        ['lastname' => 'Bedel', 'firstname' => 'Laurent', 'email' => null, 'phone' => null, 'has_leave' => true, 'departure_date' => '2025-04-24'],
        ['lastname' => 'Berlizot', 'firstname' => 'Calixte', 'email' => null, 'phone' => null, 'has_leave' => true, 'departure_date' => '2026-03-16'],
        ['lastname' => 'Branellec', 'firstname' => 'Adrien', 'email' => null, 'phone' => null, 'has_leave' => true, 'departure_date' => null],
        ['lastname' => 'Chambolle', 'firstname' => 'Jérémy', 'email' => null, 'phone' => null, 'has_leave' => true, 'departure_date' => '2026-04-10'],
        ['lastname' => 'Dabrowski', 'firstname' => 'Rafal', 'email' => null, 'phone' => null, 'has_leave' => true, 'departure_date' => '2026-03-27'],
        ['lastname' => 'Da Silva Castro', 'firstname' => 'Armindo Rogerio', 'email' => null, 'phone' => null, 'has_leave' => true, 'departure_date' => '2024-09-03'],
        ['lastname' => 'Delloue', 'firstname' => 'Antoine', 'email' => null, 'phone' => null, 'has_leave' => true, 'departure_date' => '2024-03-21'],
        ['lastname' => 'Gauthier', 'firstname' => 'Hervé', 'email' => null, 'phone' => null, 'has_leave' => true, 'departure_date' => '2025-01-07'],
        ['lastname' => 'Houari', 'firstname' => 'Abdelkader', 'email' => null, 'phone' => null, 'has_leave' => true, 'departure_date' => '2026-03-27'],
        ['lastname' => 'Izdouzen', 'firstname' => 'Abdelhakim', 'email' => null, 'phone' => null, 'has_leave' => true, 'departure_date' => '2024-09-03'],
        ['lastname' => 'Kebe', 'firstname' => 'Ballia', 'email' => null, 'phone' => null, 'has_leave' => true, 'departure_date' => '2025-01-07'],
        ['lastname' => 'Kondoki', 'firstname' => 'Jérémy', 'email' => null, 'phone' => null, 'has_leave' => true, 'departure_date' => '2024-03-21'],
        ['lastname' => 'Kone', 'firstname' => 'Mohamed', 'email' => null, 'phone' => null, 'has_leave' => true, 'departure_date' => '2026-03-27'],
        ['lastname' => 'Garino', 'firstname' => 'Alessio', 'email' => null, 'phone' => null, 'has_leave' => true, 'departure_date' => '2026-01-09'],
        ['lastname' => 'Gossellin', 'firstname' => 'Paul', 'email' => null, 'phone' => null, 'has_leave' => true, 'departure_date' => '2026-01-26'],
        ['lastname' => 'Guyon', 'firstname' => 'Adam', 'email' => null, 'phone' => null, 'has_leave' => true, 'departure_date' => '2025-10-29'],
        ['lastname' => 'Houlaho', 'firstname' => 'Kossi', 'email' => null, 'phone' => null, 'has_leave' => true, 'departure_date' => '2025-07-31'],
        ['lastname' => 'Macary', 'firstname' => 'Maud', 'email' => null, 'phone' => null, 'has_leave' => true, 'departure_date' => '2024-11-26'],
        ['lastname' => 'Mboup', 'firstname' => 'Aissatou', 'email' => null, 'phone' => null, 'has_leave' => true, 'departure_date' => '2025-12-17'],
        ['lastname' => 'Meziani', 'firstname' => 'Célia', 'email' => null, 'phone' => null, 'has_leave' => true, 'departure_date' => '2024-09-03'],
        ['lastname' => 'Mitrovic', 'firstname' => 'Goran', 'email' => null, 'phone' => null, 'has_leave' => true, 'departure_date' => '2024-07-26'],
        ['lastname' => 'Mrozik', 'firstname' => 'Jacek', 'email' => null, 'phone' => null, 'has_leave' => true, 'departure_date' => '2026-03-27'],
        ['lastname' => 'Ndoumbe Makongue', 'firstname' => 'Alain', 'email' => null, 'phone' => null, 'has_leave' => true, 'departure_date' => '2025-01-16'],
        ['lastname' => 'Oury', 'firstname' => 'Léo', 'email' => null, 'phone' => null, 'has_leave' => true, 'departure_date' => '2026-01-26'],
        ['lastname' => 'Palmieri', 'firstname' => 'Gregory', 'email' => null, 'phone' => null, 'has_leave' => true, 'departure_date' => '2025-08-27'],
        ['lastname' => 'Rahmani', 'firstname' => 'Rachid', 'email' => null, 'phone' => null, 'has_leave' => true, 'departure_date' => '2026-03-27'],
        ['lastname' => 'Rochier', 'firstname' => 'Julien', 'email' => null, 'phone' => null, 'has_leave' => true, 'departure_date' => null],
        ['lastname' => 'Rose', 'firstname' => 'Olivier', 'email' => null, 'phone' => null, 'has_leave' => true, 'departure_date' => '2024-05-28'],
        ['lastname' => 'Rouvellac', 'firstname' => 'Clément', 'email' => null, 'phone' => null, 'has_leave' => true, 'departure_date' => '2025-10-20'],
        ['lastname' => 'Seybou Gati', 'firstname' => 'Khadidja', 'email' => null, 'phone' => null, 'has_leave' => true, 'departure_date' => '2024-07-26'],
        ['lastname' => 'Tbar', 'firstname' => 'Hamza', 'email' => null, 'phone' => null, 'has_leave' => true, 'departure_date' => '2025-09-26'],
        ['lastname' => 'Velayandon', 'firstname' => 'Rémy', 'email' => null, 'phone' => null, 'has_leave' => true, 'departure_date' => null],
        ['lastname' => 'Barcan-Alousque', 'firstname' => 'Dinu-Luigi', 'email' => null, 'phone' => null, 'has_leave' => false, 'departure_date' => null],
        ['lastname' => 'Bouacha', 'firstname' => 'Omar', 'email' => null, 'phone' => null, 'has_leave' => false, 'departure_date' => null],
        ['lastname' => 'Bourgeais', 'firstname' => 'Benoit', 'email' => null, 'phone' => null, 'has_leave' => false, 'departure_date' => null],
        ['lastname' => 'Cammescasse', 'firstname' => 'Christine', 'email' => null, 'phone' => null, 'has_leave' => false, 'departure_date' => null],
        ['lastname' => 'Courbe', 'firstname' => 'Christophe', 'email' => null, 'phone' => null, 'has_leave' => false, 'departure_date' => null],
        ['lastname' => 'Deutscher', 'firstname' => 'Nicolas', 'email' => null, 'phone' => null, 'has_leave' => false, 'departure_date' => null],
        ['lastname' => 'Furtado Caroso Baptista', 'firstname' => 'Carlos', 'email' => null, 'phone' => null, 'has_leave' => false, 'departure_date' => null],
        ['lastname' => 'Gelineau', 'firstname' => 'Donatien', 'email' => null, 'phone' => null, 'has_leave' => false, 'departure_date' => null],
        ['lastname' => 'Guesdon', 'firstname' => 'Agnès', 'email' => null, 'phone' => null, 'has_leave' => false, 'departure_date' => null],
        ['lastname' => 'Hachichi', 'firstname' => 'Mehdi', 'email' => null, 'phone' => null, 'has_leave' => false, 'departure_date' => null],
        ['lastname' => 'Heriot', 'firstname' => 'Christophe', 'email' => null, 'phone' => null, 'has_leave' => false, 'departure_date' => null],
        ['lastname' => 'Jeanjean', 'firstname' => 'Didier', 'email' => null, 'phone' => null, 'has_leave' => false, 'departure_date' => null],
        ['lastname' => 'Kone', 'firstname' => 'Mahamadou', 'email' => null, 'phone' => null, 'has_leave' => false, 'departure_date' => null],
        ['lastname' => 'Lebigot', 'firstname' => 'Aymeric', 'email' => null, 'phone' => null, 'has_leave' => false, 'departure_date' => null],
        ['lastname' => 'Maillot', 'firstname' => 'Marie-Héléne', 'email' => null, 'phone' => null, 'has_leave' => false, 'departure_date' => null],
        ['lastname' => 'Makanda', 'firstname' => 'Taylor', 'email' => null, 'phone' => null, 'has_leave' => false, 'departure_date' => null],
        ['lastname' => 'Mesli', 'firstname' => 'Karim', 'email' => null, 'phone' => null, 'has_leave' => false, 'departure_date' => null],
        ['lastname' => 'Meye', 'firstname' => 'Jean-Pierre', 'email' => null, 'phone' => null, 'has_leave' => false, 'departure_date' => null],
        ['lastname' => 'Nasri', 'firstname' => 'Salma', 'email' => null, 'phone' => null, 'has_leave' => false, 'departure_date' => null],
        ['lastname' => 'N\'Diaye', 'firstname' => 'Koumba', 'email' => null, 'phone' => null, 'has_leave' => false, 'departure_date' => null],
        ['lastname' => 'Nogaro', 'firstname' => 'Yannick', 'email' => null, 'phone' => null, 'has_leave' => false, 'departure_date' => null],
        ['lastname' => 'Ouizgouret', 'firstname' => 'Said', 'email' => null, 'phone' => null, 'has_leave' => false, 'departure_date' => null],
        ['lastname' => 'Ousaid', 'firstname' => 'Ahmed', 'email' => null, 'phone' => null, 'has_leave' => false, 'departure_date' => null],
        ['lastname' => 'Richard', 'firstname' => 'Jérôme', 'email' => null, 'phone' => null, 'has_leave' => false, 'departure_date' => null],
        ['lastname' => 'Roques', 'firstname' => 'Stéphane', 'email' => null, 'phone' => null, 'has_leave' => false, 'departure_date' => null],
        ['lastname' => 'Saade', 'firstname' => 'Maroun', 'email' => null, 'phone' => null, 'has_leave' => false, 'departure_date' => null],
        ['lastname' => 'Tagne Simo', 'firstname' => 'Ronald', 'email' => null, 'phone' => null, 'has_leave' => false, 'departure_date' => null],
        ['lastname' => 'Tourneux', 'firstname' => 'François', 'email' => null, 'phone' => null, 'has_leave' => false, 'departure_date' => null],
        ['lastname' => 'Vassal', 'firstname' => 'Guillaume', 'email' => null, 'phone' => null, 'has_leave' => false, 'departure_date' => null],
        ['lastname' => 'Wehbe', 'firstname' => 'Djamil', 'email' => null, 'phone' => null, 'has_leave' => false, 'departure_date' => null],
        ['lastname' => 'Youssou', 'firstname' => 'Nacima', 'email' => null, 'phone' => null, 'has_leave' => false, 'departure_date' => null],
    ];

    /**
     * @var array<int, array{lastname: string, firstname: string, badge_number: string, expiry_date: ?string, status: string, returned_at: ?string}>
     */
    private array $badges = [
        ['lastname' => 'Alves Goundim de Sousa', 'firstname' => 'Karine', 'badge_number' => '111100688465', 'expiry_date' => '2027-02-12', 'status' => 'active', 'returned_at' => null],
        ['lastname' => 'Akhlaqi', 'firstname' => 'Nasrullah', 'badge_number' => '111200398799', 'expiry_date' => '2028-11-05', 'status' => 'active', 'returned_at' => null],
        ['lastname' => 'Anton', 'firstname' => 'Emmanuel', 'badge_number' => '111200396220', 'expiry_date' => '2027-08-29', 'status' => 'active', 'returned_at' => null],
        ['lastname' => 'Ardiot', 'firstname' => 'Laurence', 'badge_number' => '111200395764', 'expiry_date' => '2027-05-19', 'status' => 'active', 'returned_at' => null],
        ['lastname' => 'Balon', 'firstname' => 'Philippe', 'badge_number' => '111100678567', 'expiry_date' => '2026-12-06', 'status' => 'active', 'returned_at' => null],
        ['lastname' => 'Baraté', 'firstname' => 'Pascal', 'badge_number' => '111200395853', 'expiry_date' => '2027-04-16', 'status' => 'active', 'returned_at' => null],
        ['lastname' => 'Barriga', 'firstname' => 'Laura', 'badge_number' => '111200397174', 'expiry_date' => '2028-03-01', 'status' => 'active', 'returned_at' => null],
        ['lastname' => 'Bayoud', 'firstname' => 'Rachid', 'badge_number' => '111200395711', 'expiry_date' => '2027-03-27', 'status' => 'active', 'returned_at' => null],
        ['lastname' => 'Bechar', 'firstname' => 'Saïda', 'badge_number' => '111200396428', 'expiry_date' => '2027-10-01', 'status' => 'active', 'returned_at' => null],
        ['lastname' => 'Begni', 'firstname' => 'Michaël', 'badge_number' => '111200397203', 'expiry_date' => '2028-03-06', 'status' => 'active', 'returned_at' => null],
        ['lastname' => 'Ben Kaci', 'firstname' => 'Djamel', 'badge_number' => '111200398433', 'expiry_date' => '2028-08-15', 'status' => 'active', 'returned_at' => null],
        ['lastname' => 'Bernardo', 'firstname' => 'Gabriel', 'badge_number' => '111200398884', 'expiry_date' => '2026-06-26', 'status' => 'active', 'returned_at' => null],
        ['lastname' => 'Boulfroy de Saint Aubin', 'firstname' => 'Antoine', 'badge_number' => '111100685339', 'expiry_date' => '2027-01-19', 'status' => 'active', 'returned_at' => null],
        ['lastname' => 'Bourgault', 'firstname' => 'Frédéric', 'badge_number' => '111100679653', 'expiry_date' => '2026-12-08', 'status' => 'active', 'returned_at' => null],
        ['lastname' => 'Bouzazi', 'firstname' => 'Faicel', 'badge_number' => '111200397243', 'expiry_date' => '2028-03-05', 'status' => 'active', 'returned_at' => null],
        ['lastname' => 'Bouzekraoui', 'firstname' => 'Mohamed', 'badge_number' => '111200395487', 'expiry_date' => '2027-03-06', 'status' => 'active', 'returned_at' => null],
        ['lastname' => 'Cadalen', 'firstname' => 'Jean-Michel', 'badge_number' => '111100685440', 'expiry_date' => '2027-01-11', 'status' => 'active', 'returned_at' => null],
        ['lastname' => 'Cerisier', 'firstname' => 'Alain', 'badge_number' => '111200396263', 'expiry_date' => '2027-08-07', 'status' => 'active', 'returned_at' => null],
        ['lastname' => 'Cestero', 'firstname' => 'Thibaut', 'badge_number' => '111200396316', 'expiry_date' => '2027-09-23', 'status' => 'active', 'returned_at' => null],
        ['lastname' => 'Chapon', 'firstname' => 'Laurent', 'badge_number' => '111200397239', 'expiry_date' => '2028-03-13', 'status' => 'active', 'returned_at' => null],
        ['lastname' => 'Cherifi', 'firstname' => 'Killian', 'badge_number' => '111200398761', 'expiry_date' => '2026-08-31', 'status' => 'active', 'returned_at' => null],
        ['lastname' => 'Coelho Lourenço Rodrigues', 'firstname' => 'Ivo', 'badge_number' => '111100690706', 'expiry_date' => '2027-01-13', 'status' => 'active', 'returned_at' => null],
        ['lastname' => 'Corbetta', 'firstname' => 'François', 'badge_number' => '111100682874', 'expiry_date' => '2026-12-07', 'status' => 'active', 'returned_at' => null],
        ['lastname' => 'Cotillon', 'firstname' => 'Bruno', 'badge_number' => '111200396725', 'expiry_date' => '2027-12-17', 'status' => 'active', 'returned_at' => null],
        ['lastname' => 'Da Silva Oliveira', 'firstname' => 'Filipe', 'badge_number' => '111200397560', 'expiry_date' => '2028-05-01', 'status' => 'active', 'returned_at' => null],
        ['lastname' => 'Dahmani', 'firstname' => 'Mustapha', 'badge_number' => '111200396784', 'expiry_date' => '2027-12-19', 'status' => 'active', 'returned_at' => null],
        ['lastname' => 'De Almeida Clara Atiningi', 'firstname' => 'Indra', 'badge_number' => '111200396227', 'expiry_date' => '2027-08-12', 'status' => 'active', 'returned_at' => null],
        ['lastname' => 'De Almeida Pina', 'firstname' => 'Humberto', 'badge_number' => '111200395507', 'expiry_date' => '2027-03-07', 'status' => 'active', 'returned_at' => null],
        ['lastname' => 'Da Cruz Roca', 'firstname' => 'Mickael', 'badge_number' => '11100832800', 'expiry_date' => '2026-12-06', 'status' => 'active', 'returned_at' => null],
        ['lastname' => 'Daegle', 'firstname' => 'Thierry', 'badge_number' => '111100690699', 'expiry_date' => '2027-02-17', 'status' => 'active', 'returned_at' => null],
        ['lastname' => 'Deroo', 'firstname' => 'Jean-Luc', 'badge_number' => '111200395823', 'expiry_date' => '2027-03-27', 'status' => 'active', 'returned_at' => null],
        ['lastname' => 'Desplan', 'firstname' => 'Dimitry', 'badge_number' => '111200398292', 'expiry_date' => '2028-07-27', 'status' => 'active', 'returned_at' => null],
        ['lastname' => 'Dias', 'firstname' => 'Louis', 'badge_number' => '111200397389', 'expiry_date' => '2028-04-09', 'status' => 'active', 'returned_at' => null],
        ['lastname' => 'Djemai', 'firstname' => 'Mohammed Riyadh', 'badge_number' => '111100728273', 'expiry_date' => '2027-03-20', 'status' => 'active', 'returned_at' => null],
        ['lastname' => 'Douet', 'firstname' => 'Pierre-Emmanuel', 'badge_number' => '111100685342', 'expiry_date' => '2027-01-09', 'status' => 'active', 'returned_at' => null],
        ['lastname' => 'Elasry', 'firstname' => 'Elmehdi', 'badge_number' => '11120039339', 'expiry_date' => '2027-03-18', 'status' => 'active', 'returned_at' => null],
        ['lastname' => 'Fatien', 'firstname' => 'Christophe', 'badge_number' => '111100694433', 'expiry_date' => '2027-02-18', 'status' => 'active', 'returned_at' => null],
        ['lastname' => 'Favin-Lévêque', 'firstname' => 'Thibault', 'badge_number' => '111200399273', 'expiry_date' => '2026-06-20', 'status' => 'active', 'returned_at' => null],
        ['lastname' => 'Ferrec', 'firstname' => 'James', 'badge_number' => '111200397204', 'expiry_date' => '2028-02-28', 'status' => 'active', 'returned_at' => null],
        ['lastname' => 'Fertani', 'firstname' => 'Mathias', 'badge_number' => '111100680662', 'expiry_date' => '2026-12-18', 'status' => 'active', 'returned_at' => null],
        ['lastname' => 'Gaudry', 'firstname' => 'Didier', 'badge_number' => '111200398293', 'expiry_date' => '2028-07-06', 'status' => 'active', 'returned_at' => null],
        ['lastname' => 'Geara', 'firstname' => 'Patrick', 'badge_number' => '111200397315', 'expiry_date' => '2028-03-26', 'status' => 'active', 'returned_at' => null],
        ['lastname' => 'Goundiam', 'firstname' => 'Samba', 'badge_number' => '111200398970', 'expiry_date' => '2028-11-05', 'status' => 'active', 'returned_at' => null],
        ['lastname' => 'Grondin', 'firstname' => 'Guillaume', 'badge_number' => '111200398028', 'expiry_date' => '2028-06-26', 'status' => 'active', 'returned_at' => null],
        ['lastname' => 'Hamonet', 'firstname' => 'Vincent', 'badge_number' => '111100680785', 'expiry_date' => '2026-12-29', 'status' => 'active', 'returned_at' => null],
        ['lastname' => 'Hbarrou', 'firstname' => 'Youssef', 'badge_number' => '111200395636', 'expiry_date' => '2027-03-27', 'status' => 'active', 'returned_at' => null],
        ['lastname' => 'Hdidouane', 'firstname' => 'Ilham', 'badge_number' => '111100685343', 'expiry_date' => '2027-01-07', 'status' => 'active', 'returned_at' => null],
        ['lastname' => 'Hemard', 'firstname' => 'Alexis', 'badge_number' => '111200396378', 'expiry_date' => '2027-09-30', 'status' => 'active', 'returned_at' => null],
        ['lastname' => 'Hijazi', 'firstname' => 'Yassine', 'badge_number' => '111200395927', 'expiry_date' => '2027-06-12', 'status' => 'active', 'returned_at' => null],
        ['lastname' => 'Hoareau', 'firstname' => 'Jean-Patrice', 'badge_number' => '111100685043', 'expiry_date' => '2027-01-17', 'status' => 'active', 'returned_at' => null],
        ['lastname' => 'Hogrel', 'firstname' => 'François', 'badge_number' => '111100679996', 'expiry_date' => '2026-12-06', 'status' => 'active', 'returned_at' => null],
        ['lastname' => 'Home', 'firstname' => 'Frédéric', 'badge_number' => '111100685434', 'expiry_date' => '2027-01-11', 'status' => 'active', 'returned_at' => null],
        ['lastname' => 'Kervarec', 'firstname' => 'Loïc', 'badge_number' => '111100685663', 'expiry_date' => '2027-01-24', 'status' => 'active', 'returned_at' => null],
        ['lastname' => 'Kress', 'firstname' => 'David', 'badge_number' => '111100728272', 'expiry_date' => '2027-02-15', 'status' => 'active', 'returned_at' => null],
        ['lastname' => 'Labres', 'firstname' => 'Kaddour', 'badge_number' => '111100685031', 'expiry_date' => '2027-01-11', 'status' => 'active', 'returned_at' => null],
        ['lastname' => 'Lagoa', 'firstname' => 'Samuel', 'badge_number' => '111200396249', 'expiry_date' => '2027-08-26', 'status' => 'active', 'returned_at' => null],
        ['lastname' => 'Lamarque', 'firstname' => 'Sophie', 'badge_number' => '111100681416', 'expiry_date' => '2027-01-08', 'status' => 'active', 'returned_at' => null],
        ['lastname' => 'Lasfargue', 'firstname' => 'François', 'badge_number' => '111200398905', 'expiry_date' => '2028-12-12', 'status' => 'active', 'returned_at' => null],
        ['lastname' => 'Lemaire', 'firstname' => 'Laurent', 'badge_number' => '111200395548', 'expiry_date' => '2027-03-21', 'status' => 'active', 'returned_at' => null],
        ['lastname' => 'Leroy', 'firstname' => 'David', 'badge_number' => '111100681578', 'expiry_date' => '2026-12-14', 'status' => 'active', 'returned_at' => null],
        ['lastname' => 'Luquet', 'firstname' => 'Eric', 'badge_number' => '111100686857', 'expiry_date' => '2027-01-26', 'status' => 'active', 'returned_at' => null],
        ['lastname' => 'Manojlovic', 'firstname' => 'Gabriel', 'badge_number' => '111200397419', 'expiry_date' => '2028-04-26', 'status' => 'active', 'returned_at' => null],
        ['lastname' => 'Marques Monteiro', 'firstname' => 'Paulo David', 'badge_number' => '111200395859', 'expiry_date' => '2027-05-25', 'status' => 'active', 'returned_at' => null],
        ['lastname' => 'Marques Pereira', 'firstname' => 'Cédric', 'badge_number' => '111200397159', 'expiry_date' => '2028-03-02', 'status' => 'active', 'returned_at' => null],
        ['lastname' => 'Mercier', 'firstname' => 'Eric', 'badge_number' => '111100712254', 'expiry_date' => '2027-01-07', 'status' => 'active', 'returned_at' => null],
        ['lastname' => 'Moncieu', 'firstname' => 'Gaetan', 'badge_number' => '111100690912', 'expiry_date' => '2027-02-13', 'status' => 'active', 'returned_at' => null],
        ['lastname' => 'Monroc', 'firstname' => 'Dylan', 'badge_number' => '111200365936', 'expiry_date' => '2027-05-22', 'status' => 'active', 'returned_at' => null],
        ['lastname' => 'Moreira Da Silva Sousa', 'firstname' => 'José', 'badge_number' => '111200396519', 'expiry_date' => '2027-10-08', 'status' => 'active', 'returned_at' => null],
        ['lastname' => 'Morin', 'firstname' => 'Florent', 'badge_number' => '111200397356', 'expiry_date' => '2028-04-08', 'status' => 'active', 'returned_at' => null],
        ['lastname' => 'Mpode', 'firstname' => 'Pierre-Berlin', 'badge_number' => '111200399211', 'expiry_date' => '2029-02-07', 'status' => 'active', 'returned_at' => null],
        ['lastname' => 'Mugnier', 'firstname' => 'Arthur', 'badge_number' => '111200396518', 'expiry_date' => '2027-08-29', 'status' => 'active', 'returned_at' => null],
        ['lastname' => 'Naceri', 'firstname' => 'Karim', 'badge_number' => '111200398549', 'expiry_date' => '2026-09-25', 'status' => 'active', 'returned_at' => null],
        ['lastname' => 'Parisot', 'firstname' => 'Richard', 'badge_number' => '111100685460', 'expiry_date' => '2027-01-10', 'status' => 'active', 'returned_at' => null],
        ['lastname' => 'Petitpas', 'firstname' => 'Mickael', 'badge_number' => '111200398790', 'expiry_date' => '2028-11-09', 'status' => 'active', 'returned_at' => null],
        ['lastname' => 'Pisano', 'firstname' => 'Pascal', 'badge_number' => '111100694410', 'expiry_date' => '2027-02-23', 'status' => 'active', 'returned_at' => null],
        ['lastname' => 'Planque', 'firstname' => 'Jean-Michel', 'badge_number' => '111200395448', 'expiry_date' => '2027-02-15', 'status' => 'active', 'returned_at' => null],
        ['lastname' => 'Pouemi Pouemi', 'firstname' => 'Ali', 'badge_number' => '111100728270', 'expiry_date' => '2027-02-26', 'status' => 'active', 'returned_at' => null],
        ['lastname' => 'Prudent', 'firstname' => 'Ismael', 'badge_number' => '111200398776', 'expiry_date' => '2028-11-05', 'status' => 'active', 'returned_at' => null],
        ['lastname' => 'Prybis', 'firstname' => 'Valentin', 'badge_number' => '111200397671', 'expiry_date' => '2028-05-14', 'status' => 'active', 'returned_at' => null],
        ['lastname' => 'Ravel', 'firstname' => 'Fabrice', 'badge_number' => '111200396524', 'expiry_date' => '2027-10-09', 'status' => 'active', 'returned_at' => null],
        ['lastname' => 'Ricart', 'firstname' => 'Guillaume', 'badge_number' => '111200396271', 'expiry_date' => '2027-09-05', 'status' => 'active', 'returned_at' => null],
        ['lastname' => 'Rodrigues', 'firstname' => 'Philippe', 'badge_number' => '111200397247', 'expiry_date' => '2028-03-05', 'status' => 'active', 'returned_at' => null],
        ['lastname' => 'Ruel', 'firstname' => 'Baptiste', 'badge_number' => '111200396032', 'expiry_date' => '2026-09-25', 'status' => 'active', 'returned_at' => null],
        ['lastname' => 'Soidiki', 'firstname' => 'Nayoum', 'badge_number' => '111200398689', 'expiry_date' => '2027-02-16', 'status' => 'active', 'returned_at' => null],
        ['lastname' => 'Spineux', 'firstname' => 'Olivier', 'badge_number' => '111200397412', 'expiry_date' => '2028-04-09', 'status' => 'active', 'returned_at' => null],
        ['lastname' => 'Sababady', 'firstname' => 'Jean-Fabrice', 'badge_number' => '111100701321', 'expiry_date' => '2027-01-26', 'status' => 'active', 'returned_at' => null],
        ['lastname' => 'Silla', 'firstname' => 'Lassana', 'badge_number' => '111200396163', 'expiry_date' => '2027-08-06', 'status' => 'active', 'returned_at' => null],
        ['lastname' => 'Simon', 'firstname' => 'David', 'badge_number' => '111200397043', 'expiry_date' => '2028-02-12', 'status' => 'active', 'returned_at' => null],
        ['lastname' => 'Smal', 'firstname' => 'Luc', 'badge_number' => '111200398291', 'expiry_date' => '2028-07-15', 'status' => 'active', 'returned_at' => null],
        ['lastname' => 'Sureau', 'firstname' => 'Valentin', 'badge_number' => '111200398890', 'expiry_date' => '2028-11-05', 'status' => 'active', 'returned_at' => null],
        ['lastname' => 'Swiercz', 'firstname' => 'Aldo Zbigniew', 'badge_number' => '111200398778', 'expiry_date' => '2028-11-05', 'status' => 'active', 'returned_at' => null],
        ['lastname' => 'Sylla', 'firstname' => 'Momodouba Almamy', 'badge_number' => '111200395555', 'expiry_date' => '2027-01-25', 'status' => 'active', 'returned_at' => null],
        ['lastname' => 'Tanasi', 'firstname' => 'Franck', 'badge_number' => '111200395826', 'expiry_date' => '2027-05-16', 'status' => 'active', 'returned_at' => null],
        ['lastname' => 'Tarakdjian', 'firstname' => 'Pierre', 'badge_number' => '111200397088', 'expiry_date' => '2028-02-12', 'status' => 'active', 'returned_at' => null],
        ['lastname' => 'Toucas', 'firstname' => 'Mikael', 'badge_number' => '111200396302', 'expiry_date' => '2027-09-13', 'status' => 'active', 'returned_at' => null],
        ['lastname' => 'Tremolieres', 'firstname' => 'Mathias', 'badge_number' => '111200395699', 'expiry_date' => '2027-04-19', 'status' => 'active', 'returned_at' => null],
        ['lastname' => 'Vaillant', 'firstname' => 'Anaïs', 'badge_number' => '111200399079', 'expiry_date' => '2026-08-01', 'status' => 'active', 'returned_at' => null],
        ['lastname' => 'Vaz Alexandre', 'firstname' => 'Andréa', 'badge_number' => '111200395818', 'expiry_date' => '2027-05-03', 'status' => 'active', 'returned_at' => null],
        ['lastname' => 'Verbeke', 'firstname' => 'Roland', 'badge_number' => '111200395422', 'expiry_date' => '2027-02-15', 'status' => 'active', 'returned_at' => null],
        ['lastname' => 'Villedary', 'firstname' => 'Antoine', 'badge_number' => '111200395765', 'expiry_date' => '2027-03-26', 'status' => 'active', 'returned_at' => null],
        ['lastname' => 'Vouhe', 'firstname' => 'Louis', 'badge_number' => '111200397373', 'expiry_date' => '2028-04-13', 'status' => 'active', 'returned_at' => null],
        ['lastname' => 'Wittmann Le Belin de Chatellenot', 'firstname' => 'Jean', 'badge_number' => '111200396392', 'expiry_date' => '2027-09-29', 'status' => 'active', 'returned_at' => null],
        ['lastname' => 'Zanoncelli', 'firstname' => 'Iris', 'badge_number' => '111200397568', 'expiry_date' => '2028-05-19', 'status' => 'active', 'returned_at' => null],
        ['lastname' => 'Barkate', 'firstname' => 'Eva', 'badge_number' => '111200397435', 'expiry_date' => '2028-04-07', 'status' => 'returned', 'returned_at' => '2025-08-22'],
        ['lastname' => 'Bedel', 'firstname' => 'Laurent', 'badge_number' => '111100689514', 'expiry_date' => '2027-02-09', 'status' => 'returned', 'returned_at' => '2025-04-24'],
        ['lastname' => 'Berlizot', 'firstname' => 'Calixte', 'badge_number' => '111200395418', 'expiry_date' => '2026-06-30', 'status' => 'returned', 'returned_at' => '2026-03-16'],
        ['lastname' => 'Branellec', 'firstname' => 'Adrien', 'badge_number' => '111100689394', 'expiry_date' => '2027-02-06', 'status' => 'returned', 'returned_at' => null],
        ['lastname' => 'Chambolle', 'firstname' => 'Jérémy', 'badge_number' => '111200395442', 'expiry_date' => '2027-02-14', 'status' => 'returned', 'returned_at' => '2026-04-10'],
        ['lastname' => 'Dabrowski', 'firstname' => 'Rafal', 'badge_number' => '111200398777', 'expiry_date' => '2028-11-05', 'status' => 'returned', 'returned_at' => '2026-03-27'],
        ['lastname' => 'Da Silva Castro', 'firstname' => 'Armindo Rogerio', 'badge_number' => '111200395778', 'expiry_date' => '2027-04-02', 'status' => 'returned', 'returned_at' => '2024-08-30'],
        ['lastname' => 'Delloue', 'firstname' => 'Antoine', 'badge_number' => '111100676572', 'expiry_date' => '2026-12-06', 'status' => 'returned', 'returned_at' => '2024-03-07'],
        ['lastname' => 'Gauthier', 'firstname' => 'Hervé', 'badge_number' => '111100684487', 'expiry_date' => '2026-12-22', 'status' => 'returned', 'returned_at' => '2024-12-20'],
        ['lastname' => 'Houari', 'firstname' => 'Abdelkader', 'badge_number' => '111200398819', 'expiry_date' => '2028-11-05', 'status' => 'returned', 'returned_at' => '2026-03-27'],
        ['lastname' => 'Izdouzen', 'firstname' => 'Abdelhakim', 'badge_number' => '111200395794', 'expiry_date' => '2027-04-07', 'status' => 'returned', 'returned_at' => '2024-08-30'],
        ['lastname' => 'Kebe', 'firstname' => 'Ballia', 'badge_number' => '111200396180', 'expiry_date' => '2027-08-06', 'status' => 'returned', 'returned_at' => '2025-01-06'],
        ['lastname' => 'Kondoki', 'firstname' => 'Jérémy', 'badge_number' => '111100689713', 'expiry_date' => '2027-02-12', 'status' => 'returned', 'returned_at' => '2024-07-10'],
        ['lastname' => 'Kone', 'firstname' => 'Mohamed', 'badge_number' => '111200398826', 'expiry_date' => '2028-11-05', 'status' => 'returned', 'returned_at' => '2026-03-27'],
        ['lastname' => 'Garino', 'firstname' => 'Alessio', 'badge_number' => '111200397840', 'expiry_date' => '2025-12-31', 'status' => 'returned', 'returned_at' => '2025-12-19'],
        ['lastname' => 'Gossellin', 'firstname' => 'Paul', 'badge_number' => '111200395784', 'expiry_date' => '2027-04-24', 'status' => 'returned', 'returned_at' => '2026-01-23'],
        ['lastname' => 'Guyon', 'firstname' => 'Adam', 'badge_number' => '111200396391', 'expiry_date' => '2027-10-01', 'status' => 'returned', 'returned_at' => '2025-10-24'],
        ['lastname' => 'Houlaho', 'firstname' => 'Kossi', 'badge_number' => '111200396900', 'expiry_date' => '2028-01-21', 'status' => 'returned', 'returned_at' => '2025-07-08'],
        ['lastname' => 'Macary', 'firstname' => 'Maud', 'badge_number' => '111200396179', 'expiry_date' => '2027-07-22', 'status' => 'returned', 'returned_at' => '2024-11-22'],
        ['lastname' => 'Mboup', 'firstname' => 'Aissatou', 'badge_number' => '111100726017', 'expiry_date' => '2027-07-02', 'status' => 'returned', 'returned_at' => '2025-12-12'],
        ['lastname' => 'Meziani', 'firstname' => 'Célia', 'badge_number' => '111200395783', 'expiry_date' => '2024-08-31', 'status' => 'returned', 'returned_at' => '2024-08-30'],
        ['lastname' => 'Mitrovic', 'firstname' => 'Goran', 'badge_number' => '111200395449', 'expiry_date' => '2027-02-26', 'status' => 'returned', 'returned_at' => '2024-07-05'],
        ['lastname' => 'Mrozik', 'firstname' => 'Jacek', 'badge_number' => '111200398801', 'expiry_date' => '2028-11-05', 'status' => 'returned', 'returned_at' => '2026-03-27'],
        ['lastname' => 'Ndoumbe Makongue', 'firstname' => 'Alain', 'badge_number' => '111200396222', 'expiry_date' => '2027-08-06', 'status' => 'returned', 'returned_at' => '2024-12-20'],
        ['lastname' => 'Oury', 'firstname' => 'Léo', 'badge_number' => '111100682210', 'expiry_date' => '2026-01-15', 'status' => 'returned', 'returned_at' => '2026-01-26'],
        ['lastname' => 'Palmieri', 'firstname' => 'Gregory', 'badge_number' => '111100685458', 'expiry_date' => '2027-01-24', 'status' => 'returned', 'returned_at' => '2025-08-26'],
        ['lastname' => 'Rahmani', 'firstname' => 'Rachid', 'badge_number' => '111200398824', 'expiry_date' => '2028-11-05', 'status' => 'returned', 'returned_at' => '2026-03-27'],
        ['lastname' => 'Rose', 'firstname' => 'Olivier', 'badge_number' => '111200395437', 'expiry_date' => '2027-01-09', 'status' => 'returned', 'returned_at' => '2024-05-27'],
        ['lastname' => 'Rouvellac', 'firstname' => 'Clément', 'badge_number' => '111100684227', 'expiry_date' => '2026-12-07', 'status' => 'returned', 'returned_at' => '2025-10-17'],
        ['lastname' => 'Seybou Gati', 'firstname' => 'Khadidja', 'badge_number' => '111100686229', 'expiry_date' => '2027-01-09', 'status' => 'returned', 'returned_at' => '2024-07-25'],
        ['lastname' => 'Tbar', 'firstname' => 'Hamza', 'badge_number' => '111200397111', 'expiry_date' => '2025-09-26', 'status' => 'returned', 'returned_at' => '2025-09-26'],
    ];

    /**
     * @var array<int, array{lastname: string, firstname: string, training: string, started_at: string, expires_at: ?string}>
     */
    private array $coworkerFormations = [
        ['lastname' => 'Alves Goundim de Sousa', 'firstname' => 'Karine', 'training' => '11.2.6.2 (ditTCA)', 'started_at' => '2024-02-13', 'expires_at' => '2027-02-12'],
        ['lastname' => 'Alves Goundim de Sousa', 'firstname' => 'Karine', 'training' => 'Sécurité piétons', 'started_at' => '2024-04-30', 'expires_at' => null],
        ['lastname' => 'Akhlaqi', 'firstname' => 'Nasrullah', 'training' => '11.2.6.2 (ditTCA)', 'started_at' => '2025-11-05', 'expires_at' => '2028-11-05'],
        ['lastname' => 'Akhlaqi', 'firstname' => 'Nasrullah', 'training' => 'Sécurité piétons', 'started_at' => '2026-02-02', 'expires_at' => null],
        ['lastname' => 'Anton', 'firstname' => 'Emmanuel', 'training' => '11.2.6.2 (ditTCA)', 'started_at' => '2024-08-29', 'expires_at' => '2027-08-29'],
        ['lastname' => 'Anton', 'firstname' => 'Emmanuel', 'training' => 'Permis T', 'started_at' => '2024-10-08', 'expires_at' => '2026-10-08'],
        ['lastname' => 'Ardiot', 'firstname' => 'Laurence', 'training' => '11.2.6.2 (ditTCA)', 'started_at' => '2024-05-19', 'expires_at' => '2027-05-19'],
        ['lastname' => 'Ardiot', 'firstname' => 'Laurence', 'training' => 'Permis T', 'started_at' => '2024-07-09', 'expires_at' => '2026-07-09'],
        ['lastname' => 'Balon', 'firstname' => 'Philippe', 'training' => '11.2.6.2 (ditTCA)', 'started_at' => '2023-12-07', 'expires_at' => '2026-12-06'],
        ['lastname' => 'Balon', 'firstname' => 'Philippe', 'training' => 'Permis T', 'started_at' => '2024-01-11', 'expires_at' => '2028-01-12'],
        ['lastname' => 'Baraté', 'firstname' => 'Pascal', 'training' => '11.2.6.2 (ditTCA)', 'started_at' => '2024-04-16', 'expires_at' => '2027-04-16'],
        ['lastname' => 'Barriga', 'firstname' => 'Laura', 'training' => '11.2.6.2 (ditTCA)', 'started_at' => '2025-03-02', 'expires_at' => '2028-03-01'],
        ['lastname' => 'Barriga', 'firstname' => 'Laura', 'training' => 'Permis T', 'started_at' => '2025-07-15', 'expires_at' => '2027-07-15'],
        ['lastname' => 'Barriga', 'firstname' => 'Laura', 'training' => 'Sécurité piétons', 'started_at' => '2025-03-06', 'expires_at' => null],
        ['lastname' => 'Bayoud', 'firstname' => 'Rachid', 'training' => '11.2.6.2 (ditTCA)', 'started_at' => '2024-03-27', 'expires_at' => '2027-03-27'],
        ['lastname' => 'Bayoud', 'firstname' => 'Rachid', 'training' => 'Permis T', 'started_at' => '2024-06-11', 'expires_at' => '2026-06-11'],
        ['lastname' => 'Bechar', 'firstname' => 'Saïda', 'training' => '11.2.6.2 (ditTCA)', 'started_at' => '2024-10-01', 'expires_at' => '2027-10-01'],
        ['lastname' => 'Bechar', 'firstname' => 'Saïda', 'training' => 'Permis T', 'started_at' => '2024-10-22', 'expires_at' => '2026-10-22'],
        ['lastname' => 'Begni', 'firstname' => 'Michaël', 'training' => '11.2.6.2 (ditTCA)', 'started_at' => '2025-03-07', 'expires_at' => '2028-03-06'],
        ['lastname' => 'Begni', 'firstname' => 'Michaël', 'training' => 'Permis T', 'started_at' => '2025-04-10', 'expires_at' => '2027-04-10'],
        ['lastname' => 'Ben Kaci', 'firstname' => 'Djamel', 'training' => '11.2.6.2 (ditTCA)', 'started_at' => '2025-08-16', 'expires_at' => '2028-08-15'],
        ['lastname' => 'Ben Kaci', 'firstname' => 'Djamel', 'training' => 'Permis T', 'started_at' => '2025-09-18', 'expires_at' => '2027-09-18'],
        ['lastname' => 'Bernardo', 'firstname' => 'Gabriel', 'training' => '11.2.6.2 (ditTCA)', 'started_at' => '2025-12-08', 'expires_at' => '2026-06-26'],
        ['lastname' => 'Bernardo', 'firstname' => 'Gabriel', 'training' => 'Sécurité piétons', 'started_at' => '2026-02-04', 'expires_at' => null],
        ['lastname' => 'Boulfroy de Saint Aubin', 'firstname' => 'Antoine', 'training' => '11.2.6.2 (ditTCA)', 'started_at' => '2024-01-20', 'expires_at' => '2027-01-19'],
        ['lastname' => 'Boulfroy de Saint Aubin', 'firstname' => 'Antoine', 'training' => 'Permis T', 'started_at' => '2024-03-28', 'expires_at' => '2026-03-28'],
        ['lastname' => 'Bourgault', 'firstname' => 'Frédéric', 'training' => '11.2.6.2 (ditTCA)', 'started_at' => '2023-12-09', 'expires_at' => '2026-12-08'],
        ['lastname' => 'Bourgault', 'firstname' => 'Frédéric', 'training' => 'Sécurité piétons', 'started_at' => '2024-06-30', 'expires_at' => null],
        ['lastname' => 'Bouzazi', 'firstname' => 'Faicel', 'training' => '11.2.6.2 (ditTCA)', 'started_at' => '2025-03-06', 'expires_at' => '2028-03-05'],
        ['lastname' => 'Bouzazi', 'firstname' => 'Faicel', 'training' => 'Sécurité piétons', 'started_at' => '2025-03-06', 'expires_at' => null],
        ['lastname' => 'Bouzekraoui', 'firstname' => 'Mohamed', 'training' => '11.2.6.2 (ditTCA)', 'started_at' => '2024-03-06', 'expires_at' => '2027-03-06'],
        ['lastname' => 'Bouzekraoui', 'firstname' => 'Mohamed', 'training' => 'Permis T', 'started_at' => '2024-08-27', 'expires_at' => '2026-09-06'],
        ['lastname' => 'Cadalen', 'firstname' => 'Jean-Michel', 'training' => '11.2.6.2 (ditTCA)', 'started_at' => '2024-01-12', 'expires_at' => '2027-01-11'],
        ['lastname' => 'Cadalen', 'firstname' => 'Jean-Michel', 'training' => 'Sécurité piétons', 'started_at' => '2024-02-16', 'expires_at' => null],
        ['lastname' => 'Cerisier', 'firstname' => 'Alain', 'training' => '11.2.6.2 (ditTCA)', 'started_at' => '2024-08-07', 'expires_at' => '2027-08-07'],
        ['lastname' => 'Cerisier', 'firstname' => 'Alain', 'training' => 'Permis T', 'started_at' => '2024-10-24', 'expires_at' => '2026-10-24'],
        ['lastname' => 'Cestero', 'firstname' => 'Thibaut', 'training' => '11.2.6.2 (ditTCA)', 'started_at' => '2024-09-23', 'expires_at' => '2027-09-23'],
        ['lastname' => 'Cestero', 'firstname' => 'Thibaut', 'training' => 'Permis T', 'started_at' => '2024-10-29', 'expires_at' => '2026-10-30'],
        ['lastname' => 'Chapon', 'firstname' => 'Laurent', 'training' => '11.2.6.2 (ditTCA)', 'started_at' => '2025-03-14', 'expires_at' => '2028-03-13'],
        ['lastname' => 'Chapon', 'firstname' => 'Laurent', 'training' => 'Sécurité piétons', 'started_at' => '2025-03-19', 'expires_at' => null],
        ['lastname' => 'Cherifi', 'firstname' => 'Killian', 'training' => '11.2.6.2 (ditTCA)', 'started_at' => '2025-11-05', 'expires_at' => '2026-08-31'],
        ['lastname' => 'Cherifi', 'firstname' => 'Killian', 'training' => 'Sécurité piétons', 'started_at' => '2025-11-20', 'expires_at' => null],
        ['lastname' => 'Coelho Lourenço Rodrigues', 'firstname' => 'Ivo', 'training' => '11.2.6.2 (ditTCA)', 'started_at' => '2024-01-14', 'expires_at' => '2027-01-13'],
        ['lastname' => 'Coelho Lourenço Rodrigues', 'firstname' => 'Ivo', 'training' => 'Sécurité piétons', 'started_at' => '2024-03-06', 'expires_at' => null],
        ['lastname' => 'Corbetta', 'firstname' => 'François', 'training' => '11.2.6.2 (ditTCA)', 'started_at' => '2023-12-08', 'expires_at' => '2026-12-07'],
        ['lastname' => 'Corbetta', 'firstname' => 'François', 'training' => 'Sécurité piétons', 'started_at' => '2024-01-24', 'expires_at' => null],
        ['lastname' => 'Cotillon', 'firstname' => 'Bruno', 'training' => '11.2.6.2 (ditTCA)', 'started_at' => '2024-12-17', 'expires_at' => '2027-12-17'],
        ['lastname' => 'Cotillon', 'firstname' => 'Bruno', 'training' => 'Permis T', 'started_at' => '2025-01-21', 'expires_at' => '2027-07-21'],
        ['lastname' => 'Da Silva Oliveira', 'firstname' => 'Filipe', 'training' => '11.2.6.2 (ditTCA)', 'started_at' => '2025-05-02', 'expires_at' => '2028-05-01'],
        ['lastname' => 'Da Silva Oliveira', 'firstname' => 'Filipe', 'training' => 'Permis T', 'started_at' => '2026-03-05', 'expires_at' => '2028-03-24'],
        ['lastname' => 'Dahmani', 'firstname' => 'Mustapha', 'training' => '11.2.6.2 (ditTCA)', 'started_at' => '2024-12-19', 'expires_at' => '2027-12-19'],
        ['lastname' => 'Dahmani', 'firstname' => 'Mustapha', 'training' => 'Permis T', 'started_at' => '2025-01-28', 'expires_at' => '2027-01-28'],
        ['lastname' => 'De Almeida Clara Atiningi', 'firstname' => 'Indra', 'training' => '11.2.6.2 (ditTCA)', 'started_at' => '2024-08-12', 'expires_at' => '2027-08-12'],
        ['lastname' => 'De Almeida Clara Atiningi', 'firstname' => 'Indra', 'training' => 'Sécurité piétons', 'started_at' => '2024-09-11', 'expires_at' => null],
        ['lastname' => 'De Almeida Pina', 'firstname' => 'Humberto', 'training' => '11.2.6.2 (ditTCA)', 'started_at' => '2024-03-07', 'expires_at' => '2027-03-07'],
        ['lastname' => 'De Almeida Pina', 'firstname' => 'Humberto', 'training' => 'Permis T', 'started_at' => '2026-03-05', 'expires_at' => '2028-04-23'],
        ['lastname' => 'Da Cruz Roca', 'firstname' => 'Mickael', 'training' => '11.2.6.2 (ditTCA)', 'started_at' => '2023-12-07', 'expires_at' => '2026-12-06'],
        ['lastname' => 'Da Cruz Roca', 'firstname' => 'Mickael', 'training' => 'Permis T', 'started_at' => '2024-01-11', 'expires_at' => '2028-01-12'],
        ['lastname' => 'Daegle', 'firstname' => 'Thierry', 'training' => '11.2.6.2 (ditTCA)', 'started_at' => '2024-02-18', 'expires_at' => '2027-02-17'],
        ['lastname' => 'Daegle', 'firstname' => 'Thierry', 'training' => 'Sécurité piétons', 'started_at' => '2024-08-13', 'expires_at' => null],
        ['lastname' => 'Deroo', 'firstname' => 'Jean-Luc', 'training' => '11.2.6.2 (ditTCA)', 'started_at' => '2024-03-27', 'expires_at' => '2027-03-27'],
        ['lastname' => 'Deroo', 'firstname' => 'Jean-Luc', 'training' => 'Permis T', 'started_at' => '2024-10-08', 'expires_at' => '2026-10-08'],
        ['lastname' => 'Desplan', 'firstname' => 'Dimitry', 'training' => '11.2.6.2 (ditTCA)', 'started_at' => '2025-07-28', 'expires_at' => '2028-07-27'],
        ['lastname' => 'Desplan', 'firstname' => 'Dimitry', 'training' => 'Sécurité piétons', 'started_at' => '2025-08-05', 'expires_at' => null],
        ['lastname' => 'Dias', 'firstname' => 'Louis', 'training' => '11.2.6.2 (ditTCA)', 'started_at' => '2025-04-10', 'expires_at' => '2028-04-09'],
        ['lastname' => 'Dias', 'firstname' => 'Louis', 'training' => 'Permis T', 'started_at' => '2025-07-03', 'expires_at' => '2027-07-03'],
        ['lastname' => 'Djemai', 'firstname' => 'Mohammed Riyadh', 'training' => '11.2.6.2 (ditTCA)', 'started_at' => '2024-03-20', 'expires_at' => '2027-03-20'],
        ['lastname' => 'Djemai', 'firstname' => 'Mohammed Riyadh', 'training' => 'Permis T', 'started_at' => '2024-07-05', 'expires_at' => '2026-07-05'],
        ['lastname' => 'Douet', 'firstname' => 'Pierre-Emmanuel', 'training' => '11.2.6.2 (ditTCA)', 'started_at' => '2024-01-10', 'expires_at' => '2027-01-09'],
        ['lastname' => 'Douet', 'firstname' => 'Pierre-Emmanuel', 'training' => 'Permis T', 'started_at' => '2024-06-25', 'expires_at' => '2026-07-05'],
        ['lastname' => 'Elasry', 'firstname' => 'Elmehdi', 'training' => '11.2.6.2 (ditTCA)', 'started_at' => '2024-03-23', 'expires_at' => '2027-03-18'],
        ['lastname' => 'Elasry', 'firstname' => 'Elmehdi', 'training' => 'Permis T', 'started_at' => '2026-03-05', 'expires_at' => '2028-04-25'],
        ['lastname' => 'Fatien', 'firstname' => 'Christophe', 'training' => '11.2.6.2 (ditTCA)', 'started_at' => '2024-02-19', 'expires_at' => '2027-02-18'],
        ['lastname' => 'Favin-Lévêque', 'firstname' => 'Thibault', 'training' => '11.2.6.2 (ditTCA)', 'started_at' => '2026-03-17', 'expires_at' => '2026-06-20'],
        ['lastname' => 'Favin-Lévêque', 'firstname' => 'Thibault', 'training' => 'Sécurité piétons', 'started_at' => '2026-03-07', 'expires_at' => null],
        ['lastname' => 'Ferrec', 'firstname' => 'James', 'training' => '11.2.6.2 (ditTCA)', 'started_at' => '2025-02-28', 'expires_at' => '2028-02-28'],
        ['lastname' => 'Ferrec', 'firstname' => 'James', 'training' => 'Permis T', 'started_at' => '2025-04-08', 'expires_at' => '2027-04-08'],
        ['lastname' => 'Fertani', 'firstname' => 'Mathias', 'training' => '11.2.6.2 (ditTCA)', 'started_at' => '2023-12-19', 'expires_at' => '2026-12-18'],
        ['lastname' => 'Fertani', 'firstname' => 'Mathias', 'training' => 'Permis T', 'started_at' => '2025-12-01', 'expires_at' => '2028-02-15'],
        ['lastname' => 'Gaudry', 'firstname' => 'Didier', 'training' => '11.2.6.2 (ditTCA)', 'started_at' => '2025-07-07', 'expires_at' => '2028-07-06'],
        ['lastname' => 'Gaudry', 'firstname' => 'Didier', 'training' => 'Sécurité piétons', 'started_at' => '2025-08-05', 'expires_at' => null],
        ['lastname' => 'Geara', 'firstname' => 'Patrick', 'training' => '11.2.6.2 (ditTCA)', 'started_at' => '2025-03-27', 'expires_at' => '2028-03-26'],
        ['lastname' => 'Geara', 'firstname' => 'Patrick', 'training' => 'Sécurité piétons', 'started_at' => '2025-04-10', 'expires_at' => null],
        ['lastname' => 'Goundiam', 'firstname' => 'Samba', 'training' => '11.2.6.2 (ditTCA)', 'started_at' => '2025-11-05', 'expires_at' => '2028-11-05'],
        ['lastname' => 'Goundiam', 'firstname' => 'Samba', 'training' => 'Sécurité piétons', 'started_at' => '2026-02-02', 'expires_at' => null],
        ['lastname' => 'Grondin', 'firstname' => 'Guillaume', 'training' => '11.2.6.2 (ditTCA)', 'started_at' => '2025-06-27', 'expires_at' => '2028-06-26'],
        ['lastname' => 'Grondin', 'firstname' => 'Guillaume', 'training' => 'Permis T', 'started_at' => '2025-09-18', 'expires_at' => '2027-09-18'],
        ['lastname' => 'Hamonet', 'firstname' => 'Vincent', 'training' => '11.2.6.2 (ditTCA)', 'started_at' => '2023-12-30', 'expires_at' => '2026-12-29'],
        ['lastname' => 'Hamonet', 'firstname' => 'Vincent', 'training' => 'Sécurité piétons', 'started_at' => '2026-03-19', 'expires_at' => null],
        ['lastname' => 'Hbarrou', 'firstname' => 'Youssef', 'training' => '11.2.6.2 (ditTCA)', 'started_at' => '2024-03-27', 'expires_at' => '2027-03-27'],
        ['lastname' => 'Hbarrou', 'firstname' => 'Youssef', 'training' => 'Sécurité piétons', 'started_at' => '2024-05-03', 'expires_at' => null],
        ['lastname' => 'Hdidouane', 'firstname' => 'Ilham', 'training' => '11.2.6.2 (ditTCA)', 'started_at' => '2024-01-08', 'expires_at' => '2027-01-07'],
        ['lastname' => 'Hdidouane', 'firstname' => 'Ilham', 'training' => 'Sécurité piétons', 'started_at' => '2026-02-05', 'expires_at' => null],
        ['lastname' => 'Hemard', 'firstname' => 'Alexis', 'training' => '11.2.6.2 (ditTCA)', 'started_at' => '2024-09-30', 'expires_at' => '2027-09-30'],
        ['lastname' => 'Hemard', 'firstname' => 'Alexis', 'training' => 'Permis T', 'started_at' => '2024-11-12', 'expires_at' => '2026-11-12'],
        ['lastname' => 'Hijazi', 'firstname' => 'Yassine', 'training' => '11.2.6.2 (ditTCA)', 'started_at' => '2024-06-12', 'expires_at' => '2027-06-12'],
        ['lastname' => 'Hoareau', 'firstname' => 'Jean-Patrice', 'training' => '11.2.6.2 (ditTCA)', 'started_at' => '2024-01-17', 'expires_at' => '2027-01-17'],
        ['lastname' => 'Hoareau', 'firstname' => 'Jean-Patrice', 'training' => 'Permis T', 'started_at' => '2024-06-19', 'expires_at' => '2026-07-05'],
        ['lastname' => 'Hogrel', 'firstname' => 'François', 'training' => '11.2.6.2 (ditTCA)', 'started_at' => '2023-12-07', 'expires_at' => '2026-12-06'],
        ['lastname' => 'Hogrel', 'firstname' => 'François', 'training' => 'Permis T', 'started_at' => '2024-03-05', 'expires_at' => '2028-03-12'],
        ['lastname' => 'Home', 'firstname' => 'Frédéric', 'training' => '11.2.6.2 (ditTCA)', 'started_at' => '2024-01-12', 'expires_at' => '2027-01-11'],
        ['lastname' => 'Home', 'firstname' => 'Frédéric', 'training' => 'Permis T', 'started_at' => '2024-08-27', 'expires_at' => '2026-08-27'],
        ['lastname' => 'Kervarec', 'firstname' => 'Loïc', 'training' => '11.2.6.2 (ditTCA)', 'started_at' => '2024-01-25', 'expires_at' => '2027-01-24'],
        ['lastname' => 'Kervarec', 'firstname' => 'Loïc', 'training' => 'Sécurité piétons', 'started_at' => '2024-02-15', 'expires_at' => null],
        ['lastname' => 'Kress', 'firstname' => 'David', 'training' => '11.2.6.2 (ditTCA)', 'started_at' => '2024-02-16', 'expires_at' => '2027-02-15'],
        ['lastname' => 'Kress', 'firstname' => 'David', 'training' => 'Permis T', 'started_at' => '2026-03-17', 'expires_at' => '2028-05-15'],
        ['lastname' => 'Labres', 'firstname' => 'Kaddour', 'training' => '11.2.6.2 (ditTCA)', 'started_at' => '2024-01-12', 'expires_at' => '2027-01-11'],
        ['lastname' => 'Labres', 'firstname' => 'Kaddour', 'training' => 'Sécurité piétons', 'started_at' => '2026-01-23', 'expires_at' => null],
        ['lastname' => 'Lagoa', 'firstname' => 'Samuel', 'training' => '11.2.6.2 (ditTCA)', 'started_at' => '2024-08-26', 'expires_at' => '2027-08-26'],
        ['lastname' => 'Lagoa', 'firstname' => 'Samuel', 'training' => 'Permis T', 'started_at' => '2024-10-10', 'expires_at' => '2026-10-10'],
        ['lastname' => 'Lamarque', 'firstname' => 'Sophie', 'training' => '11.2.6.2 (ditTCA)', 'started_at' => '2024-01-09', 'expires_at' => '2027-01-08'],
        ['lastname' => 'Lamarque', 'firstname' => 'Sophie', 'training' => 'Sécurité piétons', 'started_at' => '2026-03-02', 'expires_at' => null],
        ['lastname' => 'Lasfargue', 'firstname' => 'François', 'training' => '11.2.6.2 (ditTCA)', 'started_at' => '2025-12-12', 'expires_at' => '2028-12-12'],
        ['lastname' => 'Lasfargue', 'firstname' => 'François', 'training' => 'Permis T', 'started_at' => '2026-02-19', 'expires_at' => '2028-02-19'],
        ['lastname' => 'Lemaire', 'firstname' => 'Laurent', 'training' => '11.2.6.2 (ditTCA)', 'started_at' => '2024-03-21', 'expires_at' => '2027-03-21'],
        ['lastname' => 'Lemaire', 'firstname' => 'Laurent', 'training' => 'Sécurité piétons', 'started_at' => '2024-05-03', 'expires_at' => null],
        ['lastname' => 'Leroy', 'firstname' => 'David', 'training' => '11.2.6.2 (ditTCA)', 'started_at' => '2023-12-15', 'expires_at' => '2026-12-14'],
        ['lastname' => 'Leroy', 'firstname' => 'David', 'training' => 'Sécurité piétons', 'started_at' => '2026-02-06', 'expires_at' => null],
        ['lastname' => 'Luquet', 'firstname' => 'Eric', 'training' => '11.2.6.2 (ditTCA)', 'started_at' => '2024-01-27', 'expires_at' => '2027-01-26'],
        ['lastname' => 'Manojlovic', 'firstname' => 'Gabriel', 'training' => '11.2.6.2 (ditTCA)', 'started_at' => '2025-04-27', 'expires_at' => '2028-04-26'],
        ['lastname' => 'Manojlovic', 'firstname' => 'Gabriel', 'training' => 'Permis T', 'started_at' => '2025-06-24', 'expires_at' => '2027-06-24'],
        ['lastname' => 'Marques Monteiro', 'firstname' => 'Paulo David', 'training' => '11.2.6.2 (ditTCA)', 'started_at' => '2024-05-25', 'expires_at' => '2027-05-25'],
        ['lastname' => 'Marques Monteiro', 'firstname' => 'Paulo David', 'training' => 'Permis T', 'started_at' => '2024-07-05', 'expires_at' => '2026-07-05'],
        ['lastname' => 'Marques Pereira', 'firstname' => 'Cédric', 'training' => '11.2.6.2 (ditTCA)', 'started_at' => '2025-03-03', 'expires_at' => '2028-03-02'],
        ['lastname' => 'Marques Pereira', 'firstname' => 'Cédric', 'training' => 'Sécurité piétons', 'started_at' => '2025-03-06', 'expires_at' => null],
        ['lastname' => 'Mercier', 'firstname' => 'Eric', 'training' => '11.2.6.2 (ditTCA)', 'started_at' => '2024-01-08', 'expires_at' => '2027-01-07'],
        ['lastname' => 'Mercier', 'firstname' => 'Eric', 'training' => 'Sécurité piétons', 'started_at' => '2024-02-25', 'expires_at' => null],
        ['lastname' => 'Moncieu', 'firstname' => 'Gaetan', 'training' => '11.2.6.2 (ditTCA)', 'started_at' => '2024-02-14', 'expires_at' => '2027-02-13'],
        ['lastname' => 'Moncieu', 'firstname' => 'Gaetan', 'training' => 'Permis T', 'started_at' => '2024-10-17', 'expires_at' => '2026-10-17'],
        ['lastname' => 'Monroc', 'firstname' => 'Dylan', 'training' => '11.2.6.2 (ditTCA)', 'started_at' => '2024-05-22', 'expires_at' => '2027-05-22'],
        ['lastname' => 'Monroc', 'firstname' => 'Dylan', 'training' => 'Permis T', 'started_at' => '2024-07-09', 'expires_at' => '2026-07-09'],
        ['lastname' => 'Moreira Da Silva Sousa', 'firstname' => 'José', 'training' => '11.2.6.2 (ditTCA)', 'started_at' => '2024-10-08', 'expires_at' => '2027-10-08'],
        ['lastname' => 'Moreira Da Silva Sousa', 'firstname' => 'José', 'training' => 'Permis T', 'started_at' => '2024-11-12', 'expires_at' => '2026-11-12'],
        ['lastname' => 'Morin', 'firstname' => 'Florent', 'training' => '11.2.6.2 (ditTCA)', 'started_at' => '2025-04-09', 'expires_at' => '2028-04-08'],
        ['lastname' => 'Mpode', 'firstname' => 'Pierre-Berlin', 'training' => '11.2.6.2 (ditTCA)', 'started_at' => '2026-02-19', 'expires_at' => '2029-02-07'],
        ['lastname' => 'Mugnier', 'firstname' => 'Arthur', 'training' => '11.2.6.2 (ditTCA)', 'started_at' => '2024-10-16', 'expires_at' => '2027-08-29'],
        ['lastname' => 'Mugnier', 'firstname' => 'Arthur', 'training' => 'Sécurité piétons', 'started_at' => '2024-11-08', 'expires_at' => null],
        ['lastname' => 'Naceri', 'firstname' => 'Karim', 'training' => '11.2.6.2 (ditTCA)', 'started_at' => '2025-05-15', 'expires_at' => '2026-09-25'],
        ['lastname' => 'Naceri', 'firstname' => 'Karim', 'training' => 'Sécurité piétons', 'started_at' => '2026-02-02', 'expires_at' => null],
        ['lastname' => 'Parisot', 'firstname' => 'Richard', 'training' => '11.2.6.2 (ditTCA)', 'started_at' => '2024-01-11', 'expires_at' => '2027-01-10'],
        ['lastname' => 'Parisot', 'firstname' => 'Richard', 'training' => 'Permis T', 'started_at' => '2025-12-16', 'expires_at' => '2028-02-28'],
        ['lastname' => 'Petitpas', 'firstname' => 'Mickael', 'training' => '11.2.6.2 (ditTCA)', 'started_at' => '2025-09-03', 'expires_at' => '2028-11-09'],
        ['lastname' => 'Petitpas', 'firstname' => 'Mickael', 'training' => 'Permis T', 'started_at' => '2025-12-04', 'expires_at' => '2027-12-04'],
        ['lastname' => 'Pisano', 'firstname' => 'Pascal', 'training' => '11.2.6.2 (ditTCA)', 'started_at' => '2024-02-24', 'expires_at' => '2027-02-23'],
        ['lastname' => 'Pisano', 'firstname' => 'Pascal', 'training' => 'Permis T', 'started_at' => '2024-10-07', 'expires_at' => '2026-10-07'],
        ['lastname' => 'Planque', 'firstname' => 'Jean-Michel', 'training' => '11.2.6.2 (ditTCA)', 'started_at' => '2024-02-16', 'expires_at' => '2027-02-15'],
        ['lastname' => 'Planque', 'firstname' => 'Jean-Michel', 'training' => 'Permis T', 'started_at' => '2024-10-07', 'expires_at' => '2026-10-07'],
        ['lastname' => 'Pouemi Pouemi', 'firstname' => 'Ali', 'training' => '11.2.6.2 (ditTCA)', 'started_at' => '2024-02-27', 'expires_at' => '2027-02-26'],
        ['lastname' => 'Pouemi Pouemi', 'firstname' => 'Ali', 'training' => 'Permis T', 'started_at' => '2024-06-06', 'expires_at' => '2026-06-06'],
        ['lastname' => 'Prudent', 'firstname' => 'Ismael', 'training' => '11.2.6.2 (ditTCA)', 'started_at' => '2025-11-05', 'expires_at' => '2028-11-05'],
        ['lastname' => 'Prudent', 'firstname' => 'Ismael', 'training' => 'Sécurité piétons', 'started_at' => '2026-02-02', 'expires_at' => null],
        ['lastname' => 'Prybis', 'firstname' => 'Valentin', 'training' => '11.2.6.2 (ditTCA)', 'started_at' => '2025-05-15', 'expires_at' => '2028-05-14'],
        ['lastname' => 'Ravel', 'firstname' => 'Fabrice', 'training' => '11.2.6.2 (ditTCA)', 'started_at' => '2024-10-09', 'expires_at' => '2027-10-09'],
        ['lastname' => 'Ravel', 'firstname' => 'Fabrice', 'training' => 'Permis T', 'started_at' => '2024-11-12', 'expires_at' => '2026-11-12'],
        ['lastname' => 'Ricart', 'firstname' => 'Guillaume', 'training' => '11.2.6.2 (ditTCA)', 'started_at' => '2024-09-05', 'expires_at' => '2027-09-05'],
        ['lastname' => 'Ricart', 'firstname' => 'Guillaume', 'training' => 'Sécurité piétons', 'started_at' => '2024-10-01', 'expires_at' => null],
        ['lastname' => 'Rodrigues', 'firstname' => 'Philippe', 'training' => '11.2.6.2 (ditTCA)', 'started_at' => '2025-03-06', 'expires_at' => '2028-03-05'],
        ['lastname' => 'Rodrigues', 'firstname' => 'Philippe', 'training' => 'Sécurité piétons', 'started_at' => '2025-03-06', 'expires_at' => null],
        ['lastname' => 'Ruel', 'firstname' => 'Baptiste', 'training' => '11.2.6.2 (ditTCA)', 'started_at' => '2024-06-24', 'expires_at' => '2026-09-25'],
        ['lastname' => 'Ruel', 'firstname' => 'Baptiste', 'training' => 'Permis T', 'started_at' => '2024-10-08', 'expires_at' => '2026-10-09'],
        ['lastname' => 'Soidiki', 'firstname' => 'Nayoum', 'training' => '11.2.6.2 (ditTCA)', 'started_at' => '2025-10-23', 'expires_at' => '2027-02-16'],
        ['lastname' => 'Soidiki', 'firstname' => 'Nayoum', 'training' => 'Permis T', 'started_at' => '2024-10-29', 'expires_at' => '2026-10-30'],
        ['lastname' => 'Spineux', 'firstname' => 'Olivier', 'training' => '11.2.6.2 (ditTCA)', 'started_at' => '2025-04-10', 'expires_at' => '2028-04-09'],
        ['lastname' => 'Spineux', 'firstname' => 'Olivier', 'training' => 'Permis T', 'started_at' => '2025-06-17', 'expires_at' => '2027-06-17'],
        ['lastname' => 'Sababady', 'firstname' => 'Jean-Fabrice', 'training' => '11.2.6.2 (ditTCA)', 'started_at' => '2024-01-27', 'expires_at' => '2027-01-26'],
        ['lastname' => 'Sababady', 'firstname' => 'Jean-Fabrice', 'training' => 'Permis T', 'started_at' => '2024-07-16', 'expires_at' => '2026-07-16'],
        ['lastname' => 'Silla', 'firstname' => 'Lassana', 'training' => '11.2.6.2 (ditTCA)', 'started_at' => '2024-08-06', 'expires_at' => '2027-08-06'],
        ['lastname' => 'Simon', 'firstname' => 'David', 'training' => '11.2.6.2 (ditTCA)', 'started_at' => '2025-02-12', 'expires_at' => '2028-02-12'],
        ['lastname' => 'Simon', 'firstname' => 'David', 'training' => 'Permis T', 'started_at' => '2025-04-01', 'expires_at' => '2027-04-01'],
        ['lastname' => 'Smal', 'firstname' => 'Luc', 'training' => '11.2.6.2 (ditTCA)', 'started_at' => '2025-07-16', 'expires_at' => '2028-07-15'],
        ['lastname' => 'Smal', 'firstname' => 'Luc', 'training' => 'Sécurité piétons', 'started_at' => '2025-08-05', 'expires_at' => null],
        ['lastname' => 'Sureau', 'firstname' => 'Valentin', 'training' => '11.2.6.2 (ditTCA)', 'started_at' => '2025-11-05', 'expires_at' => '2028-11-05'],
        ['lastname' => 'Sureau', 'firstname' => 'Valentin', 'training' => 'Sécurité piétons', 'started_at' => '2026-02-02', 'expires_at' => null],
        ['lastname' => 'Swiercz', 'firstname' => 'Aldo Zbigniew', 'training' => '11.2.6.2 (ditTCA)', 'started_at' => '2025-11-05', 'expires_at' => '2028-11-05'],
        ['lastname' => 'Sylla', 'firstname' => 'Momodouba Almamy', 'training' => '11.2.6.2 (ditTCA)', 'started_at' => '2024-01-26', 'expires_at' => '2027-01-25'],
        ['lastname' => 'Sylla', 'firstname' => 'Momodouba Almamy', 'training' => 'Permis T', 'started_at' => '2024-10-24', 'expires_at' => '2026-10-24'],
        ['lastname' => 'Tanasi', 'firstname' => 'Franck', 'training' => '11.2.6.2 (ditTCA)', 'started_at' => '2024-05-16', 'expires_at' => '2027-05-16'],
        ['lastname' => 'Tanasi', 'firstname' => 'Franck', 'training' => 'Permis T', 'started_at' => '2026-04-07', 'expires_at' => '2028-04-07'],
        ['lastname' => 'Tarakdjian', 'firstname' => 'Pierre', 'training' => '11.2.6.2 (ditTCA)', 'started_at' => '2025-02-12', 'expires_at' => '2028-02-12'],
        ['lastname' => 'Tarakdjian', 'firstname' => 'Pierre', 'training' => 'Permis T', 'started_at' => '2025-03-25', 'expires_at' => '2027-03-25'],
        ['lastname' => 'Toucas', 'firstname' => 'Mikael', 'training' => '11.2.6.2 (ditTCA)', 'started_at' => '2024-09-13', 'expires_at' => '2027-09-13'],
        ['lastname' => 'Toucas', 'firstname' => 'Mikael', 'training' => 'Sécurité piétons', 'started_at' => '2024-09-11', 'expires_at' => null],
        ['lastname' => 'Tremolieres', 'firstname' => 'Mathias', 'training' => '11.2.6.2 (ditTCA)', 'started_at' => '2024-04-19', 'expires_at' => '2027-04-19'],
        ['lastname' => 'Tremolieres', 'firstname' => 'Mathias', 'training' => 'Sécurité piétons', 'started_at' => '2024-05-03', 'expires_at' => null],
        ['lastname' => 'Vaillant', 'firstname' => 'Anaïs', 'training' => '11.2.6.2 (ditTCA)', 'started_at' => '2026-01-06', 'expires_at' => '2026-08-01'],
        ['lastname' => 'Vaillant', 'firstname' => 'Anaïs', 'training' => 'Sécurité piétons', 'started_at' => '2026-03-23', 'expires_at' => null],
        ['lastname' => 'Vaz Alexandre', 'firstname' => 'Andréa', 'training' => '11.2.6.2 (ditTCA)', 'started_at' => '2024-05-03', 'expires_at' => '2027-05-03'],
        ['lastname' => 'Vaz Alexandre', 'firstname' => 'Andréa', 'training' => 'Sécurité piétons', 'started_at' => '2024-06-12', 'expires_at' => null],
        ['lastname' => 'Verbeke', 'firstname' => 'Roland', 'training' => '11.2.6.2 (ditTCA)', 'started_at' => '2024-02-16', 'expires_at' => '2027-02-15'],
        ['lastname' => 'Verbeke', 'firstname' => 'Roland', 'training' => 'Permis T', 'started_at' => '2024-10-08', 'expires_at' => '2026-10-08'],
        ['lastname' => 'Villedary', 'firstname' => 'Antoine', 'training' => '11.2.6.2 (ditTCA)', 'started_at' => '2024-03-26', 'expires_at' => '2027-03-26'],
        ['lastname' => 'Villedary', 'firstname' => 'Antoine', 'training' => 'Permis T', 'started_at' => '2024-07-02', 'expires_at' => '2026-07-05'],
        ['lastname' => 'Vouhe', 'firstname' => 'Louis', 'training' => '11.2.6.2 (ditTCA)', 'started_at' => '2025-04-14', 'expires_at' => '2028-04-13'],
        ['lastname' => 'Wittmann Le Belin de Chatellenot', 'firstname' => 'Jean', 'training' => '11.2.6.2 (ditTCA)', 'started_at' => '2024-09-29', 'expires_at' => '2027-09-29'],
        ['lastname' => 'Wittmann Le Belin de Chatellenot', 'firstname' => 'Jean', 'training' => 'Sécurité piétons', 'started_at' => '2024-10-15', 'expires_at' => null],
        ['lastname' => 'Zanoncelli', 'firstname' => 'Iris', 'training' => '11.2.6.2 (ditTCA)', 'started_at' => '2025-05-20', 'expires_at' => '2028-05-19'],
        ['lastname' => 'Zanoncelli', 'firstname' => 'Iris', 'training' => 'Permis T', 'started_at' => '2025-07-03', 'expires_at' => '2027-07-03'],
        ['lastname' => 'Barkate', 'firstname' => 'Eva', 'training' => '11.2.6.2 (ditTCA)', 'started_at' => '2025-04-08', 'expires_at' => '2028-04-07'],
        ['lastname' => 'Barkate', 'firstname' => 'Eva', 'training' => 'Sécurité piétons', 'started_at' => '2025-04-07', 'expires_at' => null],
        ['lastname' => 'Bedel', 'firstname' => 'Laurent', 'training' => '11.2.6.2 (ditTCA)', 'started_at' => '2024-02-10', 'expires_at' => '2027-02-09'],
        ['lastname' => 'Bedel', 'firstname' => 'Laurent', 'training' => 'Sécurité piétons', 'started_at' => '2024-06-12', 'expires_at' => null],
        ['lastname' => 'Berlizot', 'firstname' => 'Calixte', 'training' => '11.2.6.2 (ditTCA)', 'started_at' => '2024-01-12', 'expires_at' => '2026-06-30'],
        ['lastname' => 'Berlizot', 'firstname' => 'Calixte', 'training' => 'Sécurité piétons', 'started_at' => '2024-03-20', 'expires_at' => null],
        ['lastname' => 'Branellec', 'firstname' => 'Adrien', 'training' => '11.2.6.2 (ditTCA)', 'started_at' => '2024-02-07', 'expires_at' => '2027-02-06'],
        ['lastname' => 'Chambolle', 'firstname' => 'Jérémy', 'training' => '11.2.6.2 (ditTCA)', 'started_at' => '2024-02-15', 'expires_at' => '2027-02-14'],
        ['lastname' => 'Chambolle', 'firstname' => 'Jérémy', 'training' => 'Permis T', 'started_at' => '2024-06-06', 'expires_at' => '2026-06-06'],
        ['lastname' => 'Dabrowski', 'firstname' => 'Rafal', 'training' => '11.2.6.2 (ditTCA)', 'started_at' => '2025-11-05', 'expires_at' => '2028-11-05'],
        ['lastname' => 'Da Silva Castro', 'firstname' => 'Armindo Rogerio', 'training' => '11.2.6.2 (ditTCA)', 'started_at' => '2024-04-02', 'expires_at' => '2027-04-02'],
        ['lastname' => 'Da Silva Castro', 'firstname' => 'Armindo Rogerio', 'training' => 'Sécurité piétons', 'started_at' => '2024-05-03', 'expires_at' => null],
        ['lastname' => 'Delloue', 'firstname' => 'Antoine', 'training' => '11.2.6.2 (ditTCA)', 'started_at' => '2023-12-07', 'expires_at' => '2026-12-06'],
        ['lastname' => 'Delloue', 'firstname' => 'Antoine', 'training' => 'Permis T', 'started_at' => '2024-01-12', 'expires_at' => '2026-01-12'],
        ['lastname' => 'Gauthier', 'firstname' => 'Hervé', 'training' => '11.2.6.2 (ditTCA)', 'started_at' => '2023-12-23', 'expires_at' => '2026-12-22'],
        ['lastname' => 'Gauthier', 'firstname' => 'Hervé', 'training' => 'Permis T', 'started_at' => '2024-02-15', 'expires_at' => '2026-02-15'],
        ['lastname' => 'Houari', 'firstname' => 'Abdelkader', 'training' => '11.2.6.2 (ditTCA)', 'started_at' => '2025-11-05', 'expires_at' => '2028-11-05'],
        ['lastname' => 'Izdouzen', 'firstname' => 'Abdelhakim', 'training' => '11.2.6.2 (ditTCA)', 'started_at' => '2024-04-07', 'expires_at' => '2027-04-07'],
        ['lastname' => 'Izdouzen', 'firstname' => 'Abdelhakim', 'training' => 'Sécurité piétons', 'started_at' => '2024-05-03', 'expires_at' => null],
        ['lastname' => 'Kebe', 'firstname' => 'Ballia', 'training' => '11.2.6.2 (ditTCA)', 'started_at' => '2024-08-06', 'expires_at' => '2027-08-06'],
        ['lastname' => 'Kebe', 'firstname' => 'Ballia', 'training' => 'Sécurité piétons', 'started_at' => '2024-09-05', 'expires_at' => null],
        ['lastname' => 'Kondoki', 'firstname' => 'Jérémy', 'training' => '11.2.6.2 (ditTCA)', 'started_at' => '2024-02-13', 'expires_at' => '2027-02-12'],
        ['lastname' => 'Kone', 'firstname' => 'Mohamed', 'training' => '11.2.6.2 (ditTCA)', 'started_at' => '2025-11-05', 'expires_at' => '2028-11-05'],
        ['lastname' => 'Garino', 'firstname' => 'Alessio', 'training' => '11.2.6.2 (ditTCA)', 'started_at' => '2025-06-14', 'expires_at' => '2025-12-31'],
        ['lastname' => 'Garino', 'firstname' => 'Alessio', 'training' => 'Sécurité piétons', 'started_at' => '2025-08-05', 'expires_at' => null],
        ['lastname' => 'Gossellin', 'firstname' => 'Paul', 'training' => '11.2.6.2 (ditTCA)', 'started_at' => '2024-04-24', 'expires_at' => '2027-04-24'],
        ['lastname' => 'Guyon', 'firstname' => 'Adam', 'training' => '11.2.6.2 (ditTCA)', 'started_at' => '2024-10-01', 'expires_at' => '2027-10-01'],
        ['lastname' => 'Guyon', 'firstname' => 'Adam', 'training' => 'Sécurité piétons', 'started_at' => '2025-02-06', 'expires_at' => null],
        ['lastname' => 'Houlaho', 'firstname' => 'Kossi', 'training' => '11.2.6.2 (ditTCA)', 'started_at' => '2025-01-21', 'expires_at' => '2028-01-21'],
        ['lastname' => 'Houlaho', 'firstname' => 'Kossi', 'training' => 'Sécurité piétons', 'started_at' => '2025-02-11', 'expires_at' => null],
        ['lastname' => 'Macary', 'firstname' => 'Maud', 'training' => '11.2.6.2 (ditTCA)', 'started_at' => '2024-07-22', 'expires_at' => '2027-07-22'],
        ['lastname' => 'Macary', 'firstname' => 'Maud', 'training' => 'Sécurité piétons', 'started_at' => '2024-09-03', 'expires_at' => null],
        ['lastname' => 'Mboup', 'firstname' => 'Aissatou', 'training' => '11.2.6.2 (ditTCA)', 'started_at' => '2024-07-02', 'expires_at' => '2027-07-02'],
        ['lastname' => 'Mboup', 'firstname' => 'Aissatou', 'training' => 'Permis T', 'started_at' => '2024-09-23', 'expires_at' => '2026-09-23'],
        ['lastname' => 'Meziani', 'firstname' => 'Célia', 'training' => '11.2.6.2 (ditTCA)', 'started_at' => '2024-04-16', 'expires_at' => '2024-08-31'],
        ['lastname' => 'Meziani', 'firstname' => 'Célia', 'training' => 'Sécurité piétons', 'started_at' => '2024-05-03', 'expires_at' => null],
        ['lastname' => 'Mitrovic', 'firstname' => 'Goran', 'training' => '11.2.6.2 (ditTCA)', 'started_at' => '2024-02-27', 'expires_at' => '2027-02-26'],
        ['lastname' => 'Mitrovic', 'firstname' => 'Goran', 'training' => 'Permis T', 'started_at' => '2024-04-25', 'expires_at' => '2026-04-25'],
        ['lastname' => 'Mrozik', 'firstname' => 'Jacek', 'training' => '11.2.6.2 (ditTCA)', 'started_at' => '2025-11-05', 'expires_at' => '2028-11-05'],
        ['lastname' => 'Ndoumbe Makongue', 'firstname' => 'Alain', 'training' => '11.2.6.2 (ditTCA)', 'started_at' => '2024-08-06', 'expires_at' => '2027-08-06'],
        ['lastname' => 'Ndoumbe Makongue', 'firstname' => 'Alain', 'training' => 'Permis T', 'started_at' => '2024-10-09', 'expires_at' => '2026-10-09'],
        ['lastname' => 'Oury', 'firstname' => 'Léo', 'training' => '11.2.6.2 (ditTCA)', 'started_at' => '2024-01-10', 'expires_at' => '2026-01-15'],
        ['lastname' => 'Oury', 'firstname' => 'Léo', 'training' => 'Sécurité piétons', 'started_at' => '2024-01-24', 'expires_at' => null],
        ['lastname' => 'Palmieri', 'firstname' => 'Gregory', 'training' => '11.2.6.2 (ditTCA)', 'started_at' => '2024-01-25', 'expires_at' => '2027-01-24'],
        ['lastname' => 'Palmieri', 'firstname' => 'Gregory', 'training' => 'Permis T', 'started_at' => '2024-03-21', 'expires_at' => '2026-03-21'],
        ['lastname' => 'Rahmani', 'firstname' => 'Rachid', 'training' => '11.2.6.2 (ditTCA)', 'started_at' => '2025-11-05', 'expires_at' => '2028-11-05'],
        ['lastname' => 'Rose', 'firstname' => 'Olivier', 'training' => '11.2.6.2 (ditTCA)', 'started_at' => '2024-01-10', 'expires_at' => '2027-01-09'],
        ['lastname' => 'Rouvellac', 'firstname' => 'Clément', 'training' => '11.2.6.2 (ditTCA)', 'started_at' => '2023-12-29', 'expires_at' => '2026-12-07'],
        ['lastname' => 'Rouvellac', 'firstname' => 'Clément', 'training' => 'Permis T', 'started_at' => '2024-02-15', 'expires_at' => '2026-02-15'],
        ['lastname' => 'Seybou Gati', 'firstname' => 'Khadidja', 'training' => '11.2.6.2 (ditTCA)', 'started_at' => '2024-01-10', 'expires_at' => '2027-01-09'],
        ['lastname' => 'Seybou Gati', 'firstname' => 'Khadidja', 'training' => 'Permis T', 'started_at' => '2024-03-21', 'expires_at' => '2026-03-21'],
        ['lastname' => 'Tbar', 'firstname' => 'Hamza', 'training' => '11.2.6.2 (ditTCA)', 'started_at' => '2025-02-20', 'expires_at' => '2025-09-26'],
        ['lastname' => 'Tbar', 'firstname' => 'Hamza', 'training' => 'Sécurité piétons', 'started_at' => '2025-03-06', 'expires_at' => null],
        ['lastname' => 'Velayandon', 'firstname' => 'Rémy', 'training' => '11.2.6.2 (ditTCA)', 'started_at' => '2025-08-04', 'expires_at' => '2028-08-03'],
        ['lastname' => 'Velayandon', 'firstname' => 'Rémy', 'training' => 'Permis T', 'started_at' => '2025-10-31', 'expires_at' => '2027-10-31'],
    ];

    /**
     * @var array<int, array{plate_number: string, car_brand: string}>
     */
    private array $vehiclePasses = [
        ['plate_number' => 'EZ-403-LR', 'car_brand' => 'Renault ZOE'],
        ['plate_number' => 'FB-020-GR', 'car_brand' => 'Renault C 440 - 26'],
        ['plate_number' => 'FC-735-WT', 'car_brand' => 'Renault ZOE'],
        ['plate_number' => 'FP-572-LK', 'car_brand' => 'Renault ZOE'],
        ['plate_number' => 'GD-638-FR', 'car_brand' => 'Liebherr LM 1090-4'],
        ['plate_number' => 'CW-136-BJ', 'car_brand' => 'Renault Premium'],
        ['plate_number' => 'FM-199-LY', 'car_brand' => 'Renault Master'],
        ['plate_number' => 'GC-446-HW', 'car_brand' => 'Peugeot Expert'],
        ['plate_number' => 'GV-241-RH', 'car_brand' => 'Volkswagen Taigo'],
        ['plate_number' => 'GX-430-PT', 'car_brand' => 'Peugeot Expert'],
        ['plate_number' => 'GY-472-NQ', 'car_brand' => 'Citroën e-Jumpy'],
        ['plate_number' => 'GY-513-NQ', 'car_brand' => 'Citroën e-Jumpy'],
        ['plate_number' => 'GY-543-NQ', 'car_brand' => 'Citroën e-Jumpy'],
        ['plate_number' => 'FR-714-AQ', 'car_brand' => 'Renault ZOE'],
        ['plate_number' => 'FR-846-AQ', 'car_brand' => 'Renault ZOE'],
        ['plate_number' => 'HA-576-PE', 'car_brand' => 'Kia e-Niro'],
        ['plate_number' => 'FE-793-GN', 'car_brand' => 'Mercedes-Benz Sprinter'],
        ['plate_number' => 'FB-872-MC', 'car_brand' => 'Mercedes-Benz Arocs'],
        ['plate_number' => 'HA-903-AD', 'car_brand' => 'Fiat Ducato'],
        ['plate_number' => 'HA-359-AC', 'car_brand' => 'Fiat Ducato'],
        ['plate_number' => 'HA-104-AH', 'car_brand' => 'Fiat Ducato'],
        ['plate_number' => 'GY-748-BX', 'car_brand' => 'Toyota Hilux'],
        ['plate_number' => 'GB-271-QV', 'car_brand' => 'Peugeot Expert'],
        ['plate_number' => 'HA-761-HW', 'car_brand' => 'Volkswagen Taigo'],
        ['plate_number' => 'HB-423-GW', 'car_brand' => 'MG MG4'],
        ['plate_number' => 'FS-509-RB', 'car_brand' => 'Peugeot Expert'],
        ['plate_number' => 'HC-254-NB', 'car_brand' => 'Renault Master'],
        ['plate_number' => 'GM-996-VP', 'car_brand' => 'Iveco Daily 35C14'],
        ['plate_number' => 'HC-622-PQ', 'car_brand' => 'Mercedes-Benz Citan'],
        ['plate_number' => 'HD-427-BF', 'car_brand' => 'Renault Trafic'],
        ['plate_number' => 'HD-995-BF', 'car_brand' => 'Renault Trafic'],
        ['plate_number' => 'FW-996-BX', 'car_brand' => 'Renault Clio'],
        ['plate_number' => 'HE-401-HS', 'car_brand' => 'Mercedes-Benz Citan'],
        ['plate_number' => 'HE-337-AP', 'car_brand' => 'Volkswagen ID Buzz Cargo'],
        ['plate_number' => 'HF-297-BN', 'car_brand' => 'Renault C 380-19'],
        ['plate_number' => 'HE-216-XN', 'car_brand' => 'Renault Master'],
        ['plate_number' => 'HF-306-YC', 'car_brand' => 'Renault Trafic'],
        ['plate_number' => 'HH-021-PD', 'car_brand' => 'Mercedes-Benz Citan'],
        ['plate_number' => 'FT-093-NK', 'car_brand' => 'Peugeot Expert'],
        ['plate_number' => 'HH-146-ME', 'car_brand' => 'Mercedes-Benz Sprinter'],
        ['plate_number' => 'HF-733-ZH', 'car_brand' => 'Mercedes-Benz Sprinter'],
        ['plate_number' => 'HA-694-LN', 'car_brand' => 'Citroën e-Jumpy'],
        ['plate_number' => 'HF-212-YC', 'car_brand' => 'Renault Trafic'],
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

        // --- Client ---
        $this->info('');
        $this->info('=== Client ===');

        $existingClient = Client::where('company_name', 'RAZEL-BEC')->first();

        if ($isDryRun) {
            if ($existingClient) {
                $this->line("  [existant] Client \"RAZEL-BEC\" (id: {$existingClient->id})");
            } else {
                $this->line('  [à créer]  Client "RAZEL-BEC"');
            }
            $client = $existingClient ?? new Client(['id' => 0]);
        } else {
            [$client, $clientCreated] = $this->firstOrCreateClient();

            if ($clientCreated) {
                $this->info("  [créé]     Client \"RAZEL-BEC\" (id: {$client->id})");
            } else {
                $this->line("  [existant] Client \"RAZEL-BEC\" (id: {$client->id})");
            }
        }

        // --- Collaborateurs ---
        $this->importCoworkers($client, $isDryRun);

        // --- Formations ---
        $this->importFormations($client, $isDryRun);

        // --- Badges ---
        $this->importBadges($client, $isDryRun);

        // --- Laissez-passer véhicules ---
        $this->importVehiclePasses($client, $isDryRun);

        return self::SUCCESS;
    }

    private function importCoworkers(Client $client, bool $isDryRun): void
    {
        $this->info('');
        $this->info('=== Collaborateurs ===');

        $created = 0;
        $skipped = 0;

        foreach ($this->coworkers as $data) {
            if ($isDryRun) {
                $exists = $client->id
                    ? Coworker::where('client_id', $client->id)
                        ->where('lastname', $data['lastname'])
                        ->where('firstname', $data['firstname'])
                        ->exists()
                    : false;

                if ($exists) {
                    $this->line("  [existant] {$data['lastname']} {$data['firstname']}");
                    $skipped++;
                } else {
                    $this->line("  [à créer]  {$data['lastname']} {$data['firstname']}");
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
                    'phone' => $data['phone'],
                    'has_leave' => $data['has_leave'],
                    'departure_date' => $data['departure_date'] !== null
                        ? Carbon::parse($data['departure_date'])
                        : null,
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
        $this->info("  Collaborateurs créés     : {$created}");
        $this->line("  Collaborateurs existants : {$skipped}");
    }

    private function importFormations(Client $client, bool $isDryRun): void
    {
        $this->info('');
        $this->info('=== Inscriptions formations ===');

        // Résoudre les training IDs par titre.
        // Razel-Bec utilise : "11.2.6.2 (ditTCA)", "Permis T", "Sécurité piétons".
        $trainingIds = [
            '11.2.6.2 (ditTCA)' => Training::where('title', 'like', '11.2.6.2%')->value('id'),
            'Permis T' => Training::where('title', 'Permis T')->value('id'),
            'Sécurité piétons' => Training::where('title', 'Sécurité piétons')->value('id'),
        ];

        $created = 0;
        $skipped = 0;

        foreach ($this->coworkerFormations as $entry) {
            $trainingKey = $entry['training'];
            $trainingId = $trainingIds[$trainingKey] ?? null;

            if (! $trainingId) {
                $this->warn("  [ignoré]   Formation \"{$trainingKey}\" introuvable en base.");

                continue;
            }

            if ($isDryRun) {
                $created++;
                $this->line("  [à créer]  {$entry['lastname']} {$entry['firstname']} — {$trainingKey}");

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
            $expiresAt = $entry['expires_at'] !== null
                ? Carbon::parse($entry['expires_at'])
                : null;

            $record = CoworkerTraining::firstOrCreate(
                [
                    'coworker_id' => $coworker->id,
                    'training_id' => $trainingId,
                ],
                [
                    'airport' => self::AIRPORT,
                    'started_at' => $startedAt,
                    'expires_at' => $expiresAt,
                ]
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
        $this->info("  Inscriptions créées     : {$created}");
        $this->line("  Inscriptions existantes : {$skipped}");
    }

    private function importBadges(Client $client, bool $isDryRun): void
    {
        $this->info('');
        $this->info('=== Badges ===');

        $created = 0;
        $skipped = 0;

        foreach ($this->badges as $entry) {
            if ($isDryRun) {
                if (! $client->id) {
                    $this->line("  [à créer]  {$entry['lastname']} {$entry['firstname']} — badge {$entry['badge_number']} ({$entry['status']})");
                    $created++;
                } else {
                    $exists = Badge::where('badge_number', $entry['badge_number'])->exists();

                    if ($exists) {
                        $this->line("  [existant] {$entry['lastname']} {$entry['firstname']} — badge {$entry['badge_number']}");
                        $skipped++;
                    } else {
                        $this->line("  [à créer]  {$entry['lastname']} {$entry['firstname']} — badge {$entry['badge_number']} ({$entry['status']})");
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
                    'airport' => self::AIRPORT,
                    'status' => $entry['status'],
                    'expiry_date' => $entry['expiry_date'] !== null
                        ? Carbon::parse($entry['expiry_date'])
                        : null,
                    'returned_at' => $entry['returned_at'] !== null
                        ? Carbon::parse($entry['returned_at'])
                        : null,
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
    }

    private function importVehiclePasses(Client $client, bool $isDryRun): void
    {
        $this->info('');
        $this->info('=== Laissez-passer véhicules ===');

        if (empty($this->vehiclePasses)) {
            $this->line('  Aucun véhicule à importer.');

            return;
        }

        $createdBy = User::where('role', 'sadmin')->orderBy('id')->value('id')
            ?? User::where('role', 'admin')->orderBy('id')->value('id');

        if (! $isDryRun && ! $createdBy) {
            $this->warn('  [ignoré]   Aucun utilisateur sadmin/admin trouvé — laissez-passer non importés.');

            return;
        }

        $created = 0;
        $skipped = 0;

        foreach ($this->vehiclePasses as $entry) {
            if ($isDryRun) {
                if (! $client->id) {
                    $this->line("  [à créer]  {$entry['plate_number']} ({$entry['car_brand']})");
                    $created++;
                } else {
                    $exists = VehiclePass::where('client_id', $client->id)
                        ->where('plate_number', $entry['plate_number'])
                        ->exists();

                    if ($exists) {
                        $this->line("  [existant] {$entry['plate_number']} ({$entry['car_brand']})");
                        $skipped++;
                    } else {
                        $this->line("  [à créer]  {$entry['plate_number']} ({$entry['car_brand']})");
                        $created++;
                    }
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
                    'airport' => self::AIRPORT,
                    'car_brand' => $entry['car_brand'],
                    'status' => 'approved',
                    'approved_at' => now(),
                ]
            );

            if ($pass->wasRecentlyCreated) {
                $this->info("  [créé]     {$entry['plate_number']} ({$entry['car_brand']})");
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

    /**
     * @return array{Client, bool}
     */
    private function firstOrCreateClient(): array
    {
        $created = false;

        $client = Client::where('company_name', 'RAZEL-BEC')->first();

        if (! $client) {
            $client = Client::create([
                'company_name' => 'RAZEL-BEC',
                'trade_name' => 'Razel-Bec',
                'siret_number' => '',
                'address' => '',
                'zip_code' => '',
                'city' => '',
                'kbis_document' => '',
                'safety_document' => '',
                'security_document' => '',
                'notification_email' => '',
                'slug' => Str::uuid(),
                'is_airline_company' => false,
            ]);
            $created = true;
        }

        return [$client, $created];
    }
}
