# Analyse et Stratégie d'Évolution : Gestion des Documents de Demande d'Activité

## 📋 Résumé Exécutif

Cette évolution transforme la gestion des documents des demandes d'activité d'un modèle basé sur des colonnes directes vers un modèle relationnel via une table `activity_request_attachments`. Les changements principaux sont :

1. **Multiplicité des "principals"** : Le document "terms" (renommé "principals") peut maintenant avoir plusieurs occurrences
2. **CTA conditionnel** : Le document CTA est requis uniquement si le client est une compagnie aérienne (`is_airline_company = true`)
3. **Architecture relationnelle** : Tous les documents passent par la table `activity_request_attachments`

---

## 🔍 Analyse de l'Existant

### Structure Actuelle

#### Base de Données
- **Table `activity_requests`** : Contient 6 colonnes pour les documents :
  - `aao_request_document` (string, chemin du fichier)
  - `kbis_document` (string, chemin du fichier)
  - `term_document` (string, chemin du fichier) → **À renommer en "principals"**
  - `safety_referent_document` (string, chemin du fichier)
  - `security_referent_document` (string, chemin du fichier)
  - `cta_document` (string, chemin du fichier)

- **Table `activity_request_attachments`** : **DÉJÀ CRÉÉE** mais non utilisée
  - `id`, `activity_request_id`, `path`, `type`, `name`, `timestamps`
  - Relation `attachments()` déjà définie dans `ActivityRequest`

#### Modèles
- `ActivityRequest` : Modèle principal avec relation `attachments()` déjà présente
- `ActivityRequestAttachment` : Modèle existant mais non utilisé

#### Services
- `ActivityRequestDocumentService` : Gère le stockage et la copie des documents
- `ActivityRequestRenewalService` : Gère le renouvellement

#### Composants Livewire
- `CreateActivityRequestForm` : Formulaire de création/édition
- `ViewActivityRequest` : Affichage et téléchargement des documents

#### Validation
- `ActivityRequestValidator` : Règles de validation pour tous les documents
- `ActivityRequestFormValidator` : Wrapper de validation

#### DTOs et Form Objects
- `CreateActivityRequestData` : DTO pour la création
- `ActivityRequestFormData` : Form object pour le formulaire

---

## 📊 Quantification des Changements

### Fichiers à Modifier (15 fichiers)

#### 1. Base de Données (2 fichiers)
- ✅ `database/migrations/2026_01_08_095144_create_activity_request_attachments_table.php` - **À modifier** (ajouter index sur `type`)
- 📝 **NOUVELLE** : Migration de migration des données existantes
- 📝 **NOUVELLE** : Migration pour supprimer les colonnes de documents de `activity_requests` (après migration)

#### 2. Modèles (2 fichiers)
- `app/Models/ActivityRequest.php` - **Modifications majeures**
  - Supprimer les colonnes de documents du `$fillable`
  - Ajouter des méthodes helper pour récupérer les documents par type
  - Adapter la relation `attachments()`
- `app/Models/ActivityRequestAttachment.php` - **Modifications mineures**
  - Ajouter constantes pour les types de documents
  - Ajouter des scopes pour filtrer par type

#### 3. Services (1 fichier)
- `app/Services/ActivityRequestDocumentService.php` - **Refactorisation complète**
  - Adapter `storeDocuments()` pour créer des enregistrements `ActivityRequestAttachment`
  - Adapter `copyDocumentsFromPreviousRequest()` pour utiliser les attachments
  - Adapter `createDocumentsZip()` pour lire depuis les attachments
  - Adapter `getDocumentPath()` pour lire depuis les attachments

#### 4. Actions (1 fichier)
- `app/Actions/ActivityRequest/SaveActivityRequestAction.php` - **Modifications moyennes**
  - Adapter `handleNewDocuments()` pour utiliser le nouveau service
  - Adapter `handleRenewalDocuments()` pour utiliser le nouveau service

#### 5. Composants Livewire (2 fichiers)
- `app/Livewire/ActivityRequests/CreateActivityRequestForm.php` - **Modifications majeures**
  - Changer `$principals` de `UploadedFile` à `array` (pour gérer plusieurs fichiers)
  - Adapter la logique de chargement des documents existants
  - Adapter la logique de validation conditionnelle pour CTA
  - Adapter `getExistingDocumentsArray()` pour utiliser les attachments
- `app/Livewire/ActivityRequests/ViewActivityRequest.php` - **Modifications moyennes**
  - Adapter `downloadDocument()` pour utiliser les attachments
  - Adapter `downloadAllDocuments()` pour utiliser les attachments

#### 6. Form Objects et DTOs (3 fichiers)
- `app/Forms/ActivityRequestFormData.php` - **Modifications majeures**
  - Changer `term_document` en `principals` (array d'UploadedFile)
  - Adapter `getDocuments()` pour gérer les multiples principals
  - Adapter `hasDocuments()` pour gérer les multiples principals
  - Adapter `fillFromActivityRequest()` pour charger depuis les attachments
  - Adapter `getExistingDocumentsFlags()` pour utiliser les attachments
- `app/DataTransferObjects/CreateActivityRequestData.php` - **Modifications moyennes**
  - Changer `term_document` en `principals` (array)
  - Adapter `getDocuments()` pour gérer les multiples principals
- `app/Forms/ActivityRequestFormValidator.php` - **Aucun changement** (délègue à ActivityRequestValidator)

#### 7. Validators (1 fichier)
- `app/Validators/ActivityRequestValidator.php` - **Modifications majeures**
  - Changer `term_document` en `principals` (array, min:1)
  - Rendre `cta_document` conditionnel selon `is_airline_company`
  - Adapter les messages d'erreur

#### 8. Vues Blade (2 fichiers)
- `resources/views/livewire/activity-requests/create-activity-request-form.blade.php` - **Modifications majeures**
  - Changer le champ "Donneurs d'ordre" pour accepter plusieurs fichiers
  - Rendre le champ CTA conditionnel selon `$client->is_airline_company`
  - Adapter l'affichage des documents existants
- `resources/views/livewire/activity-requests/view-activity-request.blade.php` - **Modifications majeures**
  - Afficher tous les documents principals (boucle)
  - Remplacer les accès directs aux colonnes par des appels aux attachments
  - Adapter la logique de téléchargement

#### 9. Tests (Optionnel mais recommandé)
- Tests unitaires pour les nouveaux helpers du modèle
- Tests de feature pour la validation conditionnelle CTA
- Tests de feature pour les multiples principals

---

## 🎯 Stratégie d'Implémentation

### Phase 1 : Préparation de la Structure (1-2h)

#### 1.1 Améliorer la Migration `activity_request_attachments`
- Ajouter un index sur `type` pour les performances
- Ajouter un index composite sur `(activity_request_id, type)` si nécessaire
- Vérifier que le champ `type` est suffisamment long (enum ou string)

#### 1.2 Améliorer le Modèle `ActivityRequestAttachment`
```php
// Constantes pour les types
const TYPE_AAO_REQUEST = 'aao_request';
const TYPE_KBIS = 'kbis';
const TYPE_PRINCIPALS = 'principals';
const TYPE_SAFETY_REFERENT = 'safety_referent';
const TYPE_SECURITY_REFERENT = 'security_referent';
const TYPE_CTA = 'cta';

// Scopes
public function scopeOfType($query, string $type) { ... }
```

#### 1.3 Ajouter des Helpers au Modèle `ActivityRequest`
```php
// Méthodes helper pour récupérer les documents
public function getAaoRequestDocument(): ?ActivityRequestAttachment { ... }
public function getKbisDocument(): ?ActivityRequestAttachment { ... }
public function getPrincipalsDocuments(): Collection { ... } // Retourne plusieurs
public function getCtaDocument(): ?ActivityRequestAttachment { ... }
// etc.
```

### Phase 2 : Migration des Données Existantes (2-3h)

#### 2.1 Créer une Migration de Données
- Créer une migration qui lit les colonnes existantes et crée les enregistrements `ActivityRequestAttachment`
- Gérer les cas où les fichiers n'existent plus
- Logger les erreurs sans bloquer la migration

```php
// Pseudo-code
foreach (ActivityRequest::all() as $request) {
    if ($request->aao_request_document) {
        ActivityRequestAttachment::create([
            'activity_request_id' => $request->id,
            'type' => 'aao_request',
            'path' => $request->aao_request_document,
            'name' => 'Demande AAO',
        ]);
    }
    // ... pour chaque type de document
}
```

#### 2.2 Tester la Migration
- Vérifier que tous les documents existants sont migrés
- Vérifier l'intégrité des chemins de fichiers

### Phase 3 : Refactorisation du Service (3-4h)

#### 3.1 Adapter `ActivityRequestDocumentService`
- Refactoriser `storeDocuments()` pour créer des `ActivityRequestAttachment`
- Adapter `copyDocumentsFromPreviousRequest()` pour copier les attachments
- Adapter `createDocumentsZip()` pour lire depuis les attachments
- Adapter `getDocumentPath()` pour retourner le chemin depuis un attachment

#### 3.2 Tester le Service
- Tester le stockage de nouveaux documents
- Tester la copie pour renouvellement
- Tester la création du ZIP

### Phase 4 : Adaptation des Composants Backend (4-5h)

#### 4.1 Adapter `ActivityRequestFormData`
- Changer `term_document` en `principals` (array)
- Adapter toutes les méthodes qui utilisent `term_document`
- Adapter `fillFromActivityRequest()` pour charger depuis les attachments

#### 4.2 Adapter `CreateActivityRequestData`
- Changer `term_document` en `principals` (array)
- Adapter `getDocuments()` pour retourner un format compatible avec le service

#### 4.3 Adapter `ActivityRequestValidator`
- Changer les règles pour `principals` (array, min:1)
- Ajouter la validation conditionnelle pour CTA
- Adapter les messages d'erreur

#### 4.4 Adapter `SaveActivityRequestAction`
- S'assurer que l'action utilise le service refactorisé

### Phase 5 : Adaptation du Composant Livewire (4-5h)

#### 5.1 Adapter `CreateActivityRequestForm`
- Changer `$principals` pour accepter un array
- Adapter `loadDraft()` pour charger depuis les attachments
- Adapter `getExistingDocumentsArray()` pour utiliser les attachments
- Ajouter la logique conditionnelle pour CTA basée sur `$client->is_airline_company`

#### 5.2 Adapter la Vue `create-activity-request-form.blade.php`
- Changer le champ principals pour `multiple`
- Ajouter la condition `@if($client->is_airline_company)` pour CTA
- Adapter l'affichage des documents existants

### Phase 6 : Adaptation de l'Affichage (2-3h)

#### 6.1 Adapter `ViewActivityRequest`
- Adapter `downloadDocument()` pour utiliser les attachments
- Gérer le cas des multiples principals (télécharger tous ou un spécifique)

#### 6.2 Adapter la Vue `view-activity-request.blade.php`
- Remplacer les accès directs aux colonnes par des appels aux helpers
- Afficher tous les documents principals dans une boucle
- Adapter la logique de téléchargement

### Phase 7 : Nettoyage et Tests (2-3h)

#### 7.1 Créer une Migration de Suppression
- Créer une migration pour supprimer les colonnes de documents de `activity_requests`
- **ATTENTION** : Ne l'exécuter qu'après validation complète

#### 7.2 Tests
- Tester la création d'une nouvelle demande avec multiples principals
- Tester la création avec/sans CTA selon le type de client
- Tester le renouvellement
- Tester l'édition d'un brouillon
- Tester l'affichage et le téléchargement

#### 7.3 Nettoyage du Code
- Supprimer les références obsolètes aux colonnes de documents
- Vérifier qu'aucun code n'utilise plus les anciennes colonnes

---

## ⚠️ Points d'Attention

### 1. Validation Conditionnelle CTA
- La validation doit vérifier `$client->is_airline_company` au moment de la validation
- S'assurer que le client est chargé avant la validation
- Gérer le cas où le client n'est pas encore sélectionné (pour les admins)

### 2. Multiplicité des Principals
- Le formulaire doit permettre l'upload de plusieurs fichiers
- La validation doit s'assurer qu'au moins un principal est fourni
- L'affichage doit montrer tous les principals
- Le téléchargement doit gérer le cas de plusieurs fichiers

### 3. Migration des Données
- Tester la migration sur un environnement de staging
- Prévoir un rollback en cas de problème
- Vérifier l'intégrité des chemins de fichiers après migration

### 4. Compatibilité Ascendante
- Pendant la transition, maintenir la compatibilité avec les anciennes colonnes
- Ne supprimer les colonnes qu'après validation complète

### 5. Performance
- Les requêtes pour récupérer les documents doivent être optimisées (eager loading)
- L'index sur `type` est important pour les performances

---

## 📈 Estimation Globale

| Phase | Durée Estimée | Complexité |
|-------|---------------|------------|
| Phase 1 : Préparation | 1-2h | Faible |
| Phase 2 : Migration Données | 2-3h | Moyenne |
| Phase 3 : Refactorisation Service | 3-4h | Élevée |
| Phase 4 : Adaptation Backend | 4-5h | Élevée |
| Phase 5 : Adaptation Livewire | 4-5h | Élevée |
| Phase 6 : Adaptation Affichage | 2-3h | Moyenne |
| Phase 7 : Nettoyage et Tests | 2-3h | Moyenne |
| **TOTAL** | **18-25h** | **Élevée** |

---

## ✅ Checklist de Validation

- [ ] Migration des données testée et validée
- [ ] Création d'une nouvelle demande avec multiples principals fonctionne
- [ ] Création avec/sans CTA selon le type de client fonctionne
- [ ] Renouvellement d'une demande fonctionne
- [ ] Édition d'un brouillon fonctionne
- [ ] Affichage des documents fonctionne (y compris multiples principals)
- [ ] Téléchargement individuel des documents fonctionne
- [ ] Téléchargement ZIP fonctionne
- [ ] Validation conditionnelle CTA fonctionne
- [ ] Tous les tests passent
- [ ] Code nettoyé (aucune référence aux anciennes colonnes)
- [ ] Migration de suppression des colonnes prête (mais non exécutée)

---

## 🚀 Ordre d'Exécution Recommandé

1. **Phase 1** : Préparer la structure (modèles, migration)
2. **Phase 2** : Migrer les données existantes
3. **Phase 3** : Refactoriser le service (cœur de la logique)
4. **Phase 4** : Adapter les composants backend (DTOs, validators)
5. **Phase 5** : Adapter le composant Livewire et la vue
6. **Phase 6** : Adapter l'affichage
7. **Phase 7** : Tests et nettoyage

**Important** : Tester chaque phase avant de passer à la suivante.

---

## 📝 Notes Supplémentaires

### Types de Documents
Les types de documents à gérer sont :
- `aao_request` : Demande AAO
- `kbis` : Extrait KBIS
- `principals` : Donneurs d'ordre (peut être multiple)
- `safety_referent` : Référent sûreté
- `security_referent` : Référent sécurité
- `cta` : CTA (conditionnel selon `is_airline_company`)

### Gestion des Fichiers
- Les fichiers continuent d'être stockés dans le même système de fichiers
- Seule la référence en base de données change (de colonnes à table relationnelle)
- Les chemins de fichiers restent identiques après migration

### Compatibilité
- Pendant la transition, maintenir la compatibilité avec les deux systèmes
- Une fois validé, supprimer les anciennes colonnes
