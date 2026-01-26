# Guide de Migration des Documents - Production

Ce guide décrit la procédure à suivre pour migrer les documents des `activity_requests` vers `activity_request_attachments` en production.

## 📋 Prérequis

- Accès SSH au serveur de production
- Accès à la base de données de production
- Backup complet de la base de données effectué
- Backup des fichiers de stockage effectué
- Fenêtre de maintenance planifiée

## 🔍 Étape 1 : Vérification Pré-Migration

### 1.1 Vérifier la structure de la base de données

```bash
# Se connecter au serveur de production
ssh user@production-server

# Se placer dans le répertoire de l'application
cd /path/to/aeropaperasse

# Vérifier que les tables existent
php artisan tinker
>>> Schema::hasTable('activity_requests')
>>> Schema::hasTable('activity_request_attachments')
>>> exit
```

### 1.2 Vérifier l'état actuel des données

```bash
# Exécuter la commande de vérification
php artisan activity-requests:verify-documents-migration
```

Cette commande affichera :
- Le nombre de documents dans `activity_requests`
- Le nombre d'attachments dans `activity_request_attachments`
- Les différences éventuelles
- Les fichiers manquants

**Notez ces chiffres pour référence.**

### 1.3 Vérifier l'espace disque

```bash
# Vérifier l'espace disque disponible
df -h

# Vérifier la taille de la table activity_requests
php artisan tinker
>>> DB::select("SELECT table_name, ROUND(((data_length + index_length) / 1024 / 1024), 2) AS 'Size (MB)' FROM information_schema.TABLES WHERE table_schema = DATABASE() AND table_name = 'activity_requests'");
>>> exit
```

## 💾 Étape 2 : Sauvegarde

### 2.1 Sauvegarde de la base de données

```bash
# Créer un dump de la base de données
mysqldump -u username -p database_name > backup_activity_requests_$(date +%Y%m%d_%H%M%S).sql

# Ou utiliser Laravel Backup si configuré
php artisan backup:run
```

### 2.2 Sauvegarde des fichiers

```bash
# Sauvegarder le répertoire de stockage
tar -czf backup_storage_$(date +%Y%m%d_%H%M%S).tar.gz storage/app/public/activity_requests/
```

### 2.3 Vérifier les sauvegardes

```bash
# Vérifier que les fichiers de sauvegarde existent et ont une taille raisonnable
ls -lh backup_*.sql backup_*.tar.gz
```

## 🚀 Étape 3 : Mise en Maintenance

### 3.1 Activer le mode maintenance

```bash
php artisan down --message="Migration des documents en cours" --retry=60
```

### 3.2 Vérifier que l'application est bien en maintenance

Visitez l'URL de l'application pour confirmer que la page de maintenance s'affiche.

## 🔄 Étape 4 : Exécution de la Migration

### 4.1 Vérifier les migrations en attente

```bash
php artisan migrate:status
```

### 4.2 Exécuter les migrations

```bash
# Exécuter toutes les migrations en attente
php artisan migrate

# OU exécuter uniquement la migration de données
php artisan migrate --path=database/migrations/2026_01_08_105501_migrate_existing_activity_request_documents_to_attachments.php
```

### 4.3 Surveiller les logs

Pendant l'exécution, surveillez les logs :

```bash
# Dans un autre terminal
tail -f storage/logs/laravel.log
```

La migration affichera :
- Le nombre de documents migrés
- Le nombre d'erreurs
- Les fichiers manquants éventuels

### 4.4 Vérifier le résultat

```bash
# Vérifier que la migration s'est bien passée
php artisan activity-requests:verify-documents-migration
```

**Vérifiez que :**
- Le nombre total de documents correspond
- Aucune erreur critique n'est présente
- Les fichiers manquants sont acceptables (si certains fichiers ont été supprimés manuellement)

## ✅ Étape 5 : Vérification Post-Migration

### 5.1 Vérifier les données dans la base de données

```bash
php artisan tinker
>>> // Compter les documents dans activity_requests
>>> ActivityRequest::whereNotNull('aao_request_document')->count();
>>> ActivityRequest::whereNotNull('kbis_document')->count();
>>> // ... etc

>>> // Compter les attachments
>>> ActivityRequestAttachment::count();
>>> ActivityRequestAttachment::groupBy('type')->selectRaw('type, count(*) as count')->get();

>>> exit
```

### 5.2 Vérifier quelques exemples manuellement

```bash
php artisan tinker
>>> // Prendre un activity_request avec des documents
>>> $ar = ActivityRequest::whereNotNull('aao_request_document')->first();
>>> $ar->id;
>>> $ar->aao_request_document;

>>> // Vérifier que l'attachment correspondant existe
>>> ActivityRequestAttachment::where('activity_request_id', $ar->id)->where('type', 'aao_request')->first();

>>> exit
```

### 5.3 Tester l'application

```bash
# Désactiver le mode maintenance temporairement pour tester
php artisan up

# Tester quelques fonctionnalités :
# - Afficher une activity_request existante
# - Vérifier que les documents s'affichent correctement
# - Tester le téléchargement d'un document
```

## 🔧 Étape 6 : Nettoyage (Optionnel - À FAIRE APRÈS VALIDATION)

⚠️ **ATTENTION : Cette étape est irréversible. Ne l'exécutez QUE après avoir validé que tout fonctionne correctement en production pendant plusieurs jours.**

### 6.1 Créer une migration pour supprimer les colonnes

```bash
php artisan make:migration remove_document_columns_from_activity_requests_table --no-interaction
```

### 6.2 Éditer la migration

```php
public function up(): void
{
    Schema::table('activity_requests', function (Blueprint $table) {
        $table->dropColumn([
            'aao_request_document',
            'kbis_document',
            'term_document',
            'safety_referent_document',
            'security_referent_document',
            'cta_document',
        ]);
    });
}

public function down(): void
{
    Schema::table('activity_requests', function (Blueprint $table) {
        $table->string('aao_request_document')->nullable();
        $table->string('kbis_document')->nullable();
        $table->string('term_document')->nullable();
        $table->string('safety_referent_document')->nullable();
        $table->string('security_referent_document')->nullable();
        $table->string('cta_document')->nullable();
    });
}
```

### 6.3 Exécuter la migration de nettoyage

```bash
# ATTENTION : Cette opération est irréversible
php artisan migrate
```

## 📊 Étape 7 : Monitoring Post-Déploiement

### 7.1 Surveiller les logs pendant 24-48h

```bash
# Surveiller les erreurs liées aux documents
tail -f storage/logs/laravel.log | grep -i "document\|attachment\|activity_request"
```

### 7.2 Vérifier les métriques

- Nombre de requêtes réussies
- Erreurs 500 éventuelles
- Temps de réponse

### 7.3 Vérifier périodiquement

```bash
# Exécuter la vérification quotidiennement pendant une semaine
php artisan activity-requests:verify-documents-migration
```

## 🔄 Rollback (En cas de problème)

### Si la migration échoue partiellement

```bash
# La migration est idempotente, vous pouvez la réexécuter
php artisan migrate:rollback --step=1
php artisan migrate
```

### Si vous devez revenir en arrière complètement

```bash
# 1. Restaurer la base de données
mysql -u username -p database_name < backup_activity_requests_YYYYMMDD_HHMMSS.sql

# 2. Restaurer les fichiers
tar -xzf backup_storage_YYYYMMDD_HHMMSS.tar.gz

# 3. Annuler les migrations
php artisan migrate:rollback
```

## 📝 Checklist de Déploiement

- [ ] Backup de la base de données effectué
- [ ] Backup des fichiers effectué
- [ ] Vérification pré-migration exécutée
- [ ] Mode maintenance activé
- [ ] Migration exécutée avec succès
- [ ] Vérification post-migration effectuée
- [ ] Tests fonctionnels réalisés
- [ ] Mode maintenance désactivé
- [ ] Monitoring activé
- [ ] Documentation mise à jour

## ⚠️ Points d'Attention

1. **Fichiers manquants** : Si certains fichiers sont manquants dans le stockage, la migration créera quand même les enregistrements dans `activity_request_attachments` pour préserver la référence. Ces fichiers devront être restaurés manuellement si nécessaire.

2. **Performance** : La migration utilise des chunks pour éviter les problèmes de mémoire. Pour de très grandes bases de données, la migration peut prendre plusieurs minutes.

3. **Doublons** : La migration est idempotente et vérifie les doublons avant de créer des attachments. Vous pouvez la réexécuter sans risque.

4. **Transactions** : Chaque attachment est créé dans une transaction pour garantir l'intégrité des données.

## 📞 Support

En cas de problème :
1. Consultez les logs : `storage/logs/laravel.log`
2. Exécutez la commande de vérification : `php artisan activity-requests:verify-documents-migration`
3. Vérifiez les sauvegardes avant toute action de rollback
