#!/usr/bin/env bash
#
# Forge deploy script for preprod (dev.aeropaperasse.fr) — multi-tenant.
# Paste this into the Forge site's "Deploy Script" editor (Forge stores it
# itself; this file is the source-of-truth copy). Adjust the cd path if the
# site directory differs.
#
set -e

cd /home/forge/dev.aeropaperasse.fr

git pull origin $FORGE_SITE_BRANCH

$FORGE_COMPOSER install --no-dev --no-interaction --prefer-dist --optimize-autoloader

# Frontend assets (Vite/Tailwind build → public/build).
npm ci
npm run build

$FORGE_PHP artisan down --retry=15 || true

# Central schema (registry, users, trainings, sessions).
$FORGE_PHP artisan migrate --force

# Every tenant database (no-op until the first tenant exists).
$FORGE_PHP artisan tenants:migrate --force

$FORGE_PHP artisan storage:link || true

$FORGE_PHP artisan config:cache
$FORGE_PHP artisan route:cache
$FORGE_PHP artisan view:cache

$FORGE_PHP artisan up

( flock -w 10 9 || exit 1; echo 'Restarting FPM...'; sudo -S service $FORGE_PHP_FPM reload ) 9>/tmp/fpmlock
