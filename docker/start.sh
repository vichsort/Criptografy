#!/bin/sh
set -e

# Os caches são gerados na inicialização, e não no build, porque dependem das
# variáveis de ambiente do Render, que só existem em runtime.
php artisan config:cache
php artisan route:cache

# Os arquivos de cache foram criados como root; o Apache roda como www-data.
chown -R www-data:www-data bootstrap/cache storage

exec apache2-foreground
