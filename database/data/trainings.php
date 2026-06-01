<?php

/**
 * Catalogue partagé des formations (central). Liste réelle pré-multi-tenant.
 * `requires_airport` = true uniquement pour « Permis T » (cf. migration
 * 2026_04_29_092341_add_requires_airport_to_trainings_table.php).
 */
return [
    ['title' => '11.2.6.2 (ditTCA)',         'requires_airport' => false],
    ['title' => '11.2.3.9',                  'requires_airport' => false],
    ['title' => '11.2.3.9 plus TCA',         'requires_airport' => false],
    ['title' => '11.2.3.10',                 'requires_airport' => false],
    ['title' => '11.2.3.10 plus TCA',        'requires_airport' => false],
    ['title' => 'Sécurité piétons',          'requires_airport' => false],
    ['title' => 'Permis T',                  'requires_airport' => true],
    ['title' => 'Pratique permis T',         'requires_airport' => false],
    ['title' => 'Facteur humain',            'requires_airport' => false],
    ['title' => 'Co activité',               'requires_airport' => false],
];
