#!/bin/sh
set -e

# Caches are built at startup, not at build time, because they depend on
# Render's environment variables, which only exist at runtime.
php artisan config:cache
php artisan route:cache

# The cache files were created as root; Apache runs as www-data.
chown -R www-data:www-data bootstrap/cache storage

exec apache2-foreground
