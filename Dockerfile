# syntax=docker/dockerfile:1
#
# Production image: Caddy -> php-fpm -> Laravel.
# Works on Railway (which auto-detects this Dockerfile) and with the
# local docker compose setup.

ARG PHP_VERSION=8.2

########################################
# 1. PHP base with the extensions we need
########################################
FROM php:${PHP_VERSION}-fpm AS php-base

RUN apt-get update && apt-get install -y --no-install-recommends \
        libicu-dev \
        libpq-dev \
        libzip-dev \
        unzip \
    && docker-php-ext-install -j"$(nproc)" \
        bcmath \
        intl \
        opcache \
        pcntl \
        pdo_mysql \
        pdo_pgsql \
        zip \
    && rm -rf /var/lib/apt/lists/*

COPY docker/php.ini /usr/local/etc/php/conf.d/99-app.ini

########################################
# 2. Node runtime, glibc so it can be
#    copied into the Debian-based PHP image
########################################
FROM node:22-bookworm-slim AS node

########################################
# 3. Build app + dependencies + assets
########################################
FROM php-base AS build

# Laravel's pagination views live in vendor/ and Tailwind's @source
# directives scan them, so the frontend assets must be built from a tree
# that already contains vendor/ and storage/. That is why this is a single
# stage instead of a separate Node stage.
COPY --from=node /usr/local/bin/node /usr/local/bin/node
COPY --from=node /usr/local/lib/node_modules/npm /usr/local/lib/node_modules/npm
RUN ln -s ../lib/node_modules/npm/bin/npm-cli.js /usr/local/bin/npm

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

ENV COMPOSER_ALLOW_SUPERUSER=1

WORKDIR /var/www

# Install from the lockfile only, before the app source is copied, so this
# layer is cached until composer.lock changes.
COPY composer.json composer.lock ./
RUN composer install \
        --no-dev \
        --no-scripts \
        --no-autoloader \
        --prefer-dist \
        --no-progress \
        --no-interaction

COPY . .

# dump-autoload fires post-autoload-dump (config:clear, clear-compiled,
# package:discover) which needs the application source to be present.
RUN composer dump-autoload --no-dev --optimize --no-interaction

# There is no package-lock.json in this repo, so npm install (not npm ci).
RUN npm install --no-audit --no-fund

RUN npm run build

# The compiled assets in public/build are all that the runtime needs, so drop
# the dev dependencies rather than shipping them into the production image.
RUN rm -rf node_modules

########################################
# 4. Caddy (glibc build, matches php-base)
########################################
FROM caddy:2 AS caddy

########################################
# 5. Runtime
########################################
FROM php-base AS runtime

RUN apt-get update && apt-get install -y --no-install-recommends \
        curl \
    && rm -rf /var/lib/apt/lists/*

COPY --from=caddy /usr/bin/caddy /usr/local/bin/caddy
COPY --from=build /var/www /var/www

COPY docker/Caddyfile /etc/caddy/Caddyfile
COPY docker/start-server.sh /usr/local/bin/start-server

RUN chmod +x /usr/local/bin/start-server /var/www/railway/*.sh \
    && mkdir -p \
        storage/framework/cache/data \
        storage/framework/sessions \
        storage/framework/views \
        storage/logs \
        bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache

EXPOSE 8080

# CMD, not ENTRYPOINT: the worker and scheduler services override the start
# command on Railway, and that must replace this rather than wrap it.
CMD ["start-server"]
