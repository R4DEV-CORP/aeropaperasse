# Analyse des quotas de demandes d'activité

## État actuel du système

### Structure des données

#### 1. **Client** (Entreprise)
- Possède des quotas **globaux** : `badge_limit` et `vehicle_pass_limit`
- Ces quotas sont vérifiés au niveau de l'entreprise entière
- Méthodes de vérification : `canCreateBadge()` et `canCreateVehiclePass()`

#### 2. **ActivityRequest** (Demande d'activité)
- Contient les quotas **spécifiques** : `person_count` et `vehicule_count`
- Ces valeurs indiquent le nombre de personnes et véhicules autorisés pour cette demande d'activité
- **Relation** : `hasMany(BadgeRequest)` (via `activity_request_id` dans `badge_requests`)
- **Relation manquante** : Pas de relation directe avec `VehiclePass`

#### 3. **BadgeRequest** (Demande de badge)
- **Relation** : `belongsTo(ActivityRequest)` via `activity_request_id`
- ✅ Chaque demande de badge est liée à une demande d'activité
- ❌ **Problème** : Aucune validation pour vérifier que le nombre de badges ne dépasse pas `person_count`

#### 4. **VehiclePass** (Laissez-passer véhicule)
- **Relation** : `belongsTo(Client)` via `client_id`
- ❌ **Problème majeur** : Pas de relation avec `ActivityRequest`
- ❌ **Problème** : Aucune validation pour vérifier les quotas par demande d'activité

### Logique actuelle de vérification des quotas

#### Pour les badges (`Client::canCreateBadge()`)
```php
public function canCreateBadge(): bool
{
    return $this->active_badges_count < $this->badge_limit;
}
```
- Vérifie uniquement le quota **global** au niveau du client
- Ne tient pas compte du quota spécifique de chaque `ActivityRequest`

#### Pour les laissez-passer véhicules (`Client::canCreateVehiclePass()`)
```php
public function canCreateVehiclePass(): bool
{
    if ($this->vehicle_pass_limit == 0) {
        return false;
    }
    return $this->active_vehicle_passes_count < $this->vehicle_pass_limit;
}
```
- Vérifie uniquement le quota **global** au niveau du client
- Ne tient pas compte du quota spécifique de chaque `ActivityRequest`
- Pas de relation avec `ActivityRequest`

## Problèmes identifiés

### 1. **BadgeRequest** - Quota non respecté
- ❌ Aucune validation lors de la création d'un `BadgeRequest` pour vérifier que le nombre total de badges pour une `ActivityRequest` ne dépasse pas `person_count`
- ❌ Il est possible de créer 100 demandes de badges pour une demande d'activité qui n'autorise que 5 personnes

### 2. **VehiclePass** - Aucun lien avec ActivityRequest
- ❌ Pas de relation dans la base de données entre `VehiclePass` et `ActivityRequest`
- ❌ Impossible de savoir quel laissez-passer véhicule appartient à quelle demande d'activité
- ❌ Aucune validation possible des quotas par demande d'activité

### 3. **Quotas globaux vs quotas par demande**
- ❌ Actuellement, les quotas globaux (`badge_limit`, `vehicle_pass_limit`) sont utilisés comme limite absolue
- ❌ Les quotas spécifiques (`person_count`, `vehicule_count`) dans `ActivityRequest` ne sont pas utilisés pour la validation

## Solutions techniques proposées

### Solution 1 : Validation des quotas au niveau ActivityRequest (Recommandée)

#### Principe
Les quotas globaux du Client servent de limite globale, mais chaque `ActivityRequest` a ses propres quotas spécifiques qui doivent être respectés.

#### Modifications nécessaires

##### 1.1. Ajouter une relation manquante dans ActivityRequest
```php
// app/Models/ActivityRequest.php
public function badgeRequests(): HasMany
{
    return $this->hasMany(BadgeRequest::class);
}

public function vehiclePasses(): HasMany
{
    return $this->hasMany(VehiclePass::class);
}
```

##### 1.2. Ajouter la relation ActivityRequest dans VehiclePass
- **Migration** : Ajouter `activity_request_id` dans la table `vehicle_passes`
- **Modèle** : Ajouter la relation `belongsTo(ActivityRequest)`

```php
// Migration
$table->unsignedBigInteger('activity_request_id');
$table->foreign('activity_request_id')->references('id')->on('activity_requests');

// app/Models/VehiclePass.php
public function activityRequest(): BelongsTo
{
    return $this->belongsTo(ActivityRequest::class);
}
```

##### 1.3. Ajouter des méthodes de validation dans ActivityRequest
```php
// app/Models/ActivityRequest.php
public function canCreateBadgeRequest(): bool
{
    $existingBadgeRequestsCount = $this->badgeRequests()
        ->where('status', '!=', 'draft')
        ->where('status', '!=', 'rejected_rem')
        ->where('status', '!=', 'rejected_adp')
        ->where('status', '!=', 'terminated')
        ->count();
    
    return $existingBadgeRequestsCount < $this->person_count;
}

public function getRemainingBadgeQuota(): int
{
    $existingCount = $this->badgeRequests()
        ->where('status', '!=', 'draft')
        ->where('status', '!=', 'rejected_rem')
        ->where('status', '!=', 'rejected_adp')
        ->where('status', '!=', 'terminated')
        ->count();
    
    return max(0, $this->person_count - $existingCount);
}

public function canCreateVehiclePass(): bool
{
    $existingVehiclePassesCount = $this->vehiclePasses()
        ->where('status', '!=', 'rejected')
        ->count();
    
    return $existingVehiclePassesCount < $this->vehicule_count;
}

public function getRemainingVehiclePassQuota(): int
{
    $existingCount = $this->vehiclePasses()
        ->where('status', '!=', 'rejected')
        ->count();
    
    return max(0, $this->vehicule_count - $existingCount);
}
```

##### 1.4. Modifier la validation lors de la création de BadgeRequest
```php
// app/Actions/BadgeRequest/SaveBadgeRequestAction.php ou
// app/Livewire/BadgeRequests/CreateBadgeRequestForm.php
protected function processBadgeRequest(bool $isDraft): void
{
    // ... validations existantes ...
    
    // NOUVEAU : Vérifier le quota de la demande d'activité
    $activityRequest = ActivityRequest::findOrFail($this->selected_activity_request_id);
    
    if (!$isDraft && !$activityRequest->canCreateBadgeRequest()) {
        $remaining = $activityRequest->getRemainingBadgeQuota();
        $this->errorMessage = "Le quota de badges pour cette demande d'activité est atteint. Il reste {$remaining} place(s) disponible(s).";
        return;
    }
    
    // ... reste du code ...
}
```

##### 1.5. Modifier la création de VehiclePass pour lier à ActivityRequest
```php
// app/Livewire/VehiclePass/CreateVehiclePassForm.php
// Ajouter la sélection d'une ActivityRequest dans le formulaire

// Validation
if (!$activityRequest->canCreateVehiclePass()) {
    $remaining = $activityRequest->getRemainingVehiclePassQuota();
    $this->errorMessage = "Le quota de laissez-passer véhicules pour cette demande d'activité est atteint. Il reste {$remaining} place(s) disponible(s).";
    return;
}
```

##### 1.6. Mettre à jour la validation dans les Form Validators
- Ajouter une règle de validation personnalisée pour vérifier les quotas
- Utiliser une validation Laravel custom rule

#### Avantages
- ✅ Respecte le principe : chaque demande d'activité a ses propres quotas
- ✅ Permet de suivre précisément quels badges/véhicules appartiennent à quelle activité
- ✅ Validation claire et compréhensible
- ✅ Compatible avec le système existant (les quotas globaux restent comme limite supérieure)

#### Inconvénients
- ⚠️ Nécessite une migration pour ajouter `activity_request_id` dans `vehicle_passes`
- ⚠️ Nécessite de mettre à jour les `VehiclePass` existants (peut nécessiter une migration de données)
- ⚠️ Nécessite de modifier le formulaire de création de `VehiclePass` pour sélectionner une `ActivityRequest`

---

### Solution 2 : Validation hybride (Quotas globaux + Quotas par demande)

#### Principe
Les quotas globaux du Client servent de limite absolue, et chaque `ActivityRequest` a des quotas qui sont des "sous-limites" à respecter en plus.

#### Modifications
- Même que Solution 1, mais ajouter une double validation :
  1. Vérifier le quota global du Client (`canCreateBadge()`)
  2. Vérifier le quota spécifique de l'ActivityRequest (`canCreateBadgeRequest()`)

#### Avantages
- ✅ Double niveau de sécurité
- ✅ Permet d'avoir des quotas globaux plus élevés que la somme des quotas des demandes d'activité

#### Inconvénients
- ⚠️ Plus complexe à comprendre pour les utilisateurs
- ⚠️ Peut créer de la confusion si les quotas globaux sont atteints alors qu'une demande d'activité a encore de la place

---

### Solution 3 : Utiliser uniquement les quotas par ActivityRequest (Simplifiée)

#### Principe
Supprimer complètement les quotas globaux du Client et utiliser uniquement les quotas par `ActivityRequest`.

#### Modifications
- Supprimer `badge_limit` et `vehicle_pass_limit` du modèle `Client`
- Supprimer les méthodes `canCreateBadge()` et `canCreateVehiclePass()` du Client
- Utiliser uniquement les méthodes de validation au niveau `ActivityRequest`

#### Avantages
- ✅ Plus simple conceptuellement
- ✅ Plus flexible : chaque demande d'activité est indépendante

#### Inconvénients
- ⚠️ Perte de la capacité à limiter globalement le nombre de badges/véhicules par entreprise
- ⚠️ Nécessite une migration pour supprimer les colonnes (avec risque de perte de données)

---

## Recommandation

**Je recommande la Solution 1** car elle :
- Respecte le principe métier : les quotas sont définis par demande d'activité
- Conserve la flexibilité des quotas globaux (optionnels)
- Est compatible avec l'existant
- Permet un suivi précis de l'utilisation des quotas

## Questions à valider avant implémentation

1. **VehiclePass existants** : Comment gérer les `VehiclePass` existants qui n'ont pas de `activity_request_id` ?
   - Option A : Les marquer comme "non assignés" et permettre leur assignation rétroactive
   - Option B : Créer une migration de données pour les associer à une `ActivityRequest` par défaut
   - Option C : Les considérer comme valides mais ne plus en créer de nouveaux sans `ActivityRequest`

2. **Quotas globaux** : Les quotas globaux (`badge_limit`, `vehicle_pass_limit`) doivent-ils :
   - Option A : Rester comme limite absolue (Solution 2)
   - Option B : Devenir optionnels/informatifs seulement (Solution 1)
   - Option C : Être supprimés complètement (Solution 3)

3. **Statuts à considérer** : Quels statuts de `BadgeRequest` et `VehiclePass` doivent être comptés dans le quota ?
   - Pour BadgeRequest : `draft`, `rejected_*`, `terminated` doivent-ils libérer le quota ?
   - Pour VehiclePass : `rejected` doit-il libérer le quota ?

4. **Formulaire VehiclePass** : Doit-on permettre de sélectionner une `ActivityRequest` lors de la création d'un `VehiclePass` ?
   - Oui : Nécessite de modifier le formulaire
   - Non : Auto-assigner à la dernière `ActivityRequest` approuvée du client ?

## Prochaines étapes

Une fois les solutions validées, je proposerai :
1. Les migrations de base de données nécessaires
2. Les modifications des modèles Eloquent
3. Les modifications des actions/controllers
4. Les modifications des formulaires Livewire
5. Les règles de validation personnalisées
6. Les tests unitaires et fonctionnels

