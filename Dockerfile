# syntax=docker/dockerfile:1

# --- Dependências (sem pacotes de dev) ---------------------------------------
FROM composer:2 AS vendor

WORKDIR /app

COPY composer.json composer.lock ./

RUN composer install \
    --no-dev \
    --no-interaction \
    --no-progress \
    --no-scripts \
    --prefer-dist \
    --optimize-autoloader

# --- Runtime -----------------------------------------------------------------
# A imagem oficial já inclui as extensões que o Laravel e a API usam
# (openssl, mbstring, ctype, tokenizer, ...), então nada é compilado aqui.
FROM php:8.3-apache

# Padrões de produção. Qualquer um pode ser sobrescrito pelas variáveis de
# ambiente do Render. PORT é injetada pelo Render em runtime.
ENV APP_ENV=production \
    APP_DEBUG=false \
    LOG_CHANNEL=stderr \
    CACHE_STORE=file \
    SESSION_DRIVER=array \
    PORT=8080

RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" \
    && echo 'expose_php = Off' > "$PHP_INI_DIR/conf.d/zz-app.ini" \
    && a2dissite 000-default \
    && sed -i 's/^Listen 80$/Listen ${PORT}/' /etc/apache2/ports.conf

COPY docker/apache-server.conf /etc/apache2/conf-available/zz-app.conf
COPY docker/apache-vhost.conf /etc/apache2/sites-available/app.conf
RUN a2enconf zz-app && a2ensite app

WORKDIR /var/www/html

COPY --from=vendor /app/vendor ./vendor
COPY . .

# Descoberta de pacotes que o --no-scripts pulou; storage e cache graváveis
# pelo usuário do Apache.
RUN php artisan package:discover --ansi \
    && chown -R www-data:www-data storage bootstrap/cache

COPY docker/start.sh /usr/local/bin/start
RUN chmod +x /usr/local/bin/start

EXPOSE 8080

CMD ["start"]
