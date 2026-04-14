# Task 01 — Formations "à vie" : rendre expires_at nullable

## Contexte

La formation `11.2.2` n'a pas de date d'expiration (validité à vie). Le champ `expires_at` dans `coworker_trainings` est actuellement `NOT NULL`, ce qui ne permet pas de représenter ce cas. La solution retenue est de rendre `expires_at` nullable : `null` = à vie, une date = expire à cette date.

**Ce chantier doit être fait avant la Task 02** (import des formations EQIOM), car l'import de `11.2.2` en dépend.

## Plan

### 1. Migration
Créer une migration pour rendre `expires_at` nullable dans `coworker_trainings`.

### 2. Logique métier
- `app/Models/Coworker.php` — `getActiveTrainings()` ligne ~52
  - Condition actuelle : `$training->expires_at > now()`
  - À corriger : actif si `expires_at` est `null` **ou** `expires_at > now()`
- `app/Models/Client.php` — `getActiveTrainingCount()` ligne ~107
  - Même correction

### 3. Requêtes formations
- `app/Livewire/Training/Show.php`
  - Ligne ~115 : filtre "actif" → ajouter `OR expires_at IS NULL`
  - Lignes ~128-129 : filtre "expire bientôt" → exclure les `expires_at IS NULL`
  - Ligne ~142 : filtre "expiré" → ajouter `AND expires_at IS NOT NULL`
- `app/Livewire/Training/Index.php` ligne ~34 : compteur "expire bientôt" → exclure les `expires_at IS NULL`

### 4. Notifications
- `app/Console/Commands/NotifyTrainingExpiry.php` ligne ~54
  - Ignorer les formations sans date d'expiration lors de l'envoi des alertes

### 5. Interface
- Partout où la date d'expiration est affichée, prévoir d'afficher `À vie` quand `expires_at` est `null`.
