# Résumé des modifications - Factories et Seeders

## ✅ Modifications complétées

### 1. **ClientFactory**
- ✅ Suppression de `badge_limit` et `vehicle_pass_limit` du `definition()`
- ✅ La factory génère maintenant uniquement les champs valides du modèle `Client`

### 2. **VehiclePassFactory** (nouvelle factory créée)
- ✅ Factory créée avec tous les champs requis
- ✅ `activity_request_id` est nullable par défaut (non défini dans `definition()`)
- ✅ Génération de données réalistes :
  - `airport` : ORY, CDG, ou LBG
  - `plate_number` : Format français (ex: AB-123-CD)
  - `car_brand` : Marques de véhicules réalistes
  - `status` : pending, rejected, ou approved
  - Gestion automatique des timestamps selon le statut
- ✅ États personnalisés créés :
  - `forActivityRequest(ActivityRequest $activityRequest)` : Lie le VehiclePass à une ActivityRequest et définit automatiquement le `client_id`
  - `withoutActivityRequest()` : Explicite pour les anciens VehiclePass sans ActivityRequest

### 3. **ActivityRequestFactory**
- ✅ Aucune modification nécessaire
- ✅ Génère déjà correctement `person_count` et `vehicule_count`

### 4. **BadgeRequestFactory**
- ✅ Aucune modification nécessaire
- ✅ Utilise déjà `activity_request_id` via la relation `->for($activityRequest)`

### 5. **DatabaseSeeder**
- ✅ Aucune modification nécessaire
- ✅ N'utilise pas de références aux quotas globaux
- ✅ Ne crée pas de VehiclePass (peut être ajouté ultérieurement si besoin)

## Exemples d'utilisation

### Créer un Client
```php
$client = Client::factory()->create();
```

### Créer un ActivityRequest
```php
$activityRequest = ActivityRequest::factory()
    ->for($client)
    ->for($user, 'creator')
    ->create([
        'person_count' => 5,
        'vehicule_count' => 2,
    ]);
```

### Créer un VehiclePass lié à une ActivityRequest
```php
$vehiclePass = VehiclePass::factory()
    ->for($user, 'createdBy')
    ->for($client)
    ->forActivityRequest($activityRequest)
    ->create([
        'status' => 'pending',
    ]);
```

### Créer un VehiclePass sans ActivityRequest (ancien format)
```php
$vehiclePass = VehiclePass::factory()
    ->for($user, 'createdBy')
    ->for($client)
    ->withoutActivityRequest()
    ->create();
```

### Créer un BadgeRequest
```php
$badgeRequest = BadgeRequest::factory()
    ->for($activityRequest)
    ->for($coworker)
    ->for($user, 'creator')
    ->create();
```

## Notes importantes

1. **VehiclePass avec ActivityRequest** : 
   - Lors de l'utilisation de `forActivityRequest()`, le `client_id` est automatiquement défini depuis l'ActivityRequest
   - Cela assure la cohérence des données

2. **VehiclePass sans ActivityRequest** :
   - Peut être créé pour les anciens enregistrements
   - Le `activity_request_id` sera `null`

3. **Quotas** :
   - Les quotas sont maintenant définis uniquement dans `ActivityRequest` via `person_count` et `vehicule_count`
   - Plus besoin de gérer des quotas globaux au niveau du Client

## Tests recommandés

1. Tester la création d'un Client sans quotas
2. Tester la création d'un VehiclePass avec ActivityRequest
3. Tester la création d'un VehiclePass sans ActivityRequest
4. Vérifier que les relations sont correctement définies

