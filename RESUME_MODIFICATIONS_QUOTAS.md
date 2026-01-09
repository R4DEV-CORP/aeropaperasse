# Résumé des modifications - Migration des quotas vers ActivityRequest

## ✅ Modifications complétées

### 1. Base de données
- ✅ Migration pour ajouter `activity_request_id` dans `vehicle_passes` (nullable)
- ✅ Migration pour supprimer `badge_limit` et `vehicle_pass_limit` de `clients`

### 2. Modèles Eloquent
- ✅ `ActivityRequest` : Ajout des relations `badgeRequests()` et `vehiclePasses()`
- ✅ `ActivityRequest` : Ajout des méthodes de validation :
  - `canCreateBadgeRequest()` : Vérifie si on peut créer un badge
  - `getRemainingBadgeQuota()` : Retourne le quota restant pour les badges
  - `getActiveBadgeRequestsCount()` : Compte les badges actifs
  - `canCreateVehiclePass()` : Vérifie si on peut créer un laissez-passer véhicule
  - `getRemainingVehiclePassQuota()` : Retourne le quota restant pour les véhicules
  - `getActiveVehiclePassesCount()` : Compte les laissez-passer actifs
- ✅ `VehiclePass` : Ajout de la relation `activityRequest()` et `activity_request_id` dans fillable
- ✅ `Client` : Suppression des méthodes et propriétés liées aux quotas globaux :
  - Suppression de `badge_limit` et `vehicle_pass_limit` dans fillable
  - Suppression de `canCreateBadge()` et `canCreateVehiclePass()`
  - Suppression de `getActiveBadgesCountAttribute()` et `getActiveVehiclePassesCountAttribute()`
  - Suppression de `$appends` pour les attributs calculés

### 3. Formulaires Livewire
- ✅ `CreateBadgeRequestForm` : Ajout de la validation du quota ActivityRequest lors de la création (non-brouillon)
- ✅ `CreateVehiclePassForm` : 
  - Ajout de la sélection d'une ActivityRequest
  - Ajout de la validation du quota ActivityRequest
  - Affichage du quota disponible dans la vue
- ✅ `CreateClientForm` : Suppression des champs `badge_limit` et `vehicle_pass_limit`
- ✅ `EditClientForm` : Suppression des champs `badge_limit` et `vehicle_pass_limit`
- ✅ `CreateBadgeForm` : Suppression de la vérification `canCreateBadge()`

### 4. Actions et DTOs
- ✅ `CreateVehiclePassData` : Ajout de `activity_request_id` dans le constructeur et les méthodes
- ✅ `SaveVehiclePassAction` : Utilise déjà le DTO, donc pas de modification nécessaire
- ✅ `CreateClientData` : Suppression de `badge_limit` et `vehicle_pass_limit`
- ✅ `UpdateClientAction` : Suppression des références aux quotas
- ✅ `CreateClientAction` : Pas de modification nécessaire (utilise le DTO)

### 5. Validateurs
- ✅ `ClientValidator` : Suppression des règles de validation pour `badge_limit` et `vehicle_pass_limit`

### 6. Contrôleurs
- ✅ `ClientController` : 
  - Suppression de `getQuotaInfo()`
  - Suppression des validations de quotas dans `store()` et `update()`
  - Suppression des références aux quotas dans `show()` et `updateOverview()`

### 7. Vues
- ✅ `create-badge-request-form.blade.php` : Ajout de l'affichage du quota disponible
- ✅ `create-vehicle-pass-form.blade.php` : Ajout de la sélection ActivityRequest et affichage du quota

## ⏳ Modifications restantes

### Vues à mettre à jour (supprimer les affichages de quotas globaux)
1. ❌ `resources/views/livewire/clients/view-client.blade.php` - Affiche `badge_limit` et `vehicle_pass_limit`
2. ❌ `resources/views/livewire/clients/edit-client-form.blade.php` - Champs de formulaire pour quotas
3. ❌ `resources/views/livewire/clients/create-client-form.blade.php` - Champs de formulaire pour quotas
4. ❌ `resources/views/livewire/clients/index.blade.php` - Affiche les quotas dans la liste
5. ❌ `resources/views/livewire/vehicle-pass/index.blade.php` - Affiche `vehicle_pass_limit`
6. ❌ `resources/views/livewire/badge-requests/index.blade.php` - Affiche `badge_limit`
7. ❌ `resources/views/pdf/client-overview.blade.php` - Affiche les quotas dans le PDF

### Services
1. ❌ `app/Services/ClientOverviewPdfService.php` - Utilise `badge_limit` et `vehicle_pass_limit`

## Notes importantes

### Statuts considérés pour les quotas

#### BadgeRequest (comptés comme utilisés)
- ✅ Comptés : Tous les statuts SAUF `draft`, `rejected_rem`, `rejected_adp`, `terminated`
- Les brouillons et les refusés libèrent le quota

#### VehiclePass (comptés comme utilisés)
- ✅ Comptés : Tous les statuts SAUF `rejected`
- Les refusés libèrent le quota

### VehiclePass existants
- Les `VehiclePass` existants auront `activity_request_id` = NULL
- Ils sont ignorés dans le nouveau système (comme demandé)
- Les nouveaux `VehiclePass` doivent obligatoirement être liés à une `ActivityRequest`

## Prochaines étapes

1. Mettre à jour les vues pour supprimer les affichages de quotas globaux
2. Mettre à jour `ClientOverviewPdfService` pour supprimer les références aux quotas
3. Exécuter les migrations : `php artisan migrate`
4. Tester la création de BadgeRequest avec validation de quota
5. Tester la création de VehiclePass avec validation de quota
6. Vérifier que les quotas sont correctement calculés par ActivityRequest

