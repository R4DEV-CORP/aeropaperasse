# Task 02 — Import des formations EQIOM BETONS

## Contexte

Les collaborateurs EQIOM BETONS ont des formations à importer. La commande `clients:import-eqiom-betons` existe déjà pour les collaborateurs (`app/Console/Commands/ImportEqiomBetonsCoworkers.php`). Il faut l'étendre pour importer également les formations.

**Dépendance : la Task 01 doit être complétée avant celle-ci** (le `expires_at` nullable est nécessaire pour la formation `11.2.2`).

## Décisions prises

### Correspondance formations Excel → système
| Excel | Système | Action |
|---|---|---|
| `11.2.2` | absente | À créer |
| `11.2.5` | absente | À créer |
| `11.2.3.4` | absente | À créer |
| `11.2.3.10` | `11.2.3.10` | Existe déjà |
| `11.2.6.2` | `11.2.6.2 (ditTCA)` | Existe déjà |
| `Permis T` | `Permis T` | Existe déjà |

### Durées de validité
| Formation | Validité | Source de l'expiration |
|---|---|---|
| `11.2.2` | À vie | `expires_at = null` |
| `11.2.5` | 5 ans | Dates explicites dans le fichier Excel (colonnes start + expiry) |
| `11.2.3.10` | 5 ans | Dates explicites dans le fichier Excel (colonnes start + expiry) |
| `11.2.3.4` | 5 ans | `expires_at = started_at + 5 ans` |
| `11.2.6.2` | 3 ans | `expires_at = started_at + 3 ans` |
| `Permis T` | 2 ans | `expires_at = started_at + 2 ans` |

### Dates malformées (à parser automatiquement)
- `"12/02:2026"` → remplacer `:` par `/` → `2026-02-12`
- `"\r\n24/09/24"` → strip + année 2 chiffres → `2024-09-24`
- `"\r\n15/01/26"` → strip + année 2 chiffres → `2026-01-15`

## Collaborateurs avec des formations

| Collaborateur | Formation | Début | Expiration |
|---|---|---|---|
| PELLISCHEK HERVE | 11.2.2 | 2022-10-07 | à vie |
| PELLISCHEK HERVE | 11.2.5 | 2022-10-07 | 2027-10-06 |
| PELLISCHEK HERVE | 11.2.3.10 | 2023-11-13 | 2028-11-11 |
| PELLISCHEK HERVE | 11.2.3.4 | 2025-11-18 | 2030-11-18 |
| PELLISCHEK HERVE | 11.2.6.2 | 2024-05-28 | 2027-05-28 |
| PELLISCHEK HERVE | Permis T | 2024-05-30 | 2026-05-30 |
| CORNIL BAPTISTE | 11.2.2 | 2025-03-03 | à vie |
| CORNIL BAPTISTE | 11.2.5 | 2025-01-15 | 2030-01-15 |
| CORNIL BAPTISTE | 11.2.3.10 | 2025-02-18 | 2030-02-18 |
| CORNIL BAPTISTE | 11.2.3.4 | 2025-11-18 | 2030-11-18 |
| DIABY IBRAHIMA | 11.2.3.10 | 2026-02-26 | 2031-02-26 |
| EVRAY KEVIN | 11.2.3.10 | 2023-11-27 | 2028-11-27 |
| HAIDARA IBRAHIMA | Permis T | 2024-09-26 | 2026-09-26 |
| RAMOS MIRANDA CLAUDINO | Permis T | 2024-09-24 | 2026-09-24 |
| DA SILVA MOREIRA BARRETO JOAO | Permis T | 2024-08-13 | 2026-08-13 |
| ZERROUK MOHAMED | Permis T | 2026-02-12 | 2028-02-12 |
| CHEMMAM KAMEL | Permis T | 2024-09-24 | 2026-09-24 |
| BOUGHANEM ABDELATIF | Permis T | 2024-08-13 | 2026-08-13 |
| SANOGO ADAMA | Permis T | 2024-08-13 | 2026-08-13 |
| FOFANA OUMAR | Permis T | 2024-09-03 | 2026-09-03 |
| YANG THIERRY | Permis T | 2026-02-18 | 2028-02-18 |
| PLUTA KRYSTIAN RYSZARD | Permis T | 2024-09-24 | 2026-09-24 |
| DUCTEIL JEAN PIERRE | Permis T | 2026-01-15 | 2028-01-15 |
| AFONSO LOPES ANTONIO | Permis T | 2025-05-20 | 2027-05-20 |
| SOUVANNAVONG SOURIGNA | Permis T | 2026-02-12 | 2028-02-12 |
| EVRAY DIMITRI | Permis T | 2025-12-09 | 2027-12-09 |

## Plan

### 1. Créer les 3 formations manquantes
Dans la commande, utiliser `firstOrCreate` pour créer `11.2.2`, `11.2.5` et `11.2.3.4` si elles n'existent pas.

### 2. Étendre la commande existante
Ajouter une méthode `importFormations()` dans `ImportEqiomBetonsCoworkers` qui :
1. Résout les `training_id` (par titre) pour les 6 formations
2. Pour chaque collaborateur ayant des formations, retrouve son enregistrement en base via `lastname` + `firstname`
3. Utilise `firstOrCreate` sur `coworker_trainings` (clé unique : `coworker_id` + `training_id`)
4. Affiche un résumé créés / existants, comme pour les collaborateurs
