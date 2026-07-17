# syntax=docker/dockerfile:1

# =====================================================================
# Diamond Forum — image de production pour Render
# Basée sur FrankenPHP (serveur web + PHP en un seul binaire),
# l'approche officiellement recommandée par Symfony pour Docker.
# =====================================================================

FROM dunglas/frankenphp:php8.4 AS app

# --- Extensions PHP nécessaires au projet -----------------------------
# pdo_pgsql/pgsql : connexion PostgreSQL (Render Postgres)
# intl            : symfony/intl, symfony/validator
# opcache         : performances en production
# zip             : composer / certaines dépendances
RUN install-php-extensions \
    pdo_pgsql \
    pgsql \
    intl \
    opcache \
    zip \
    gd

# --- Configuration PHP pour la prod ------------------------------------
ENV APP_ENV=prod
ENV APP_DEBUG=0
# Le port réel est fourni par Render au runtime (variable $PORT) ;
# c'est le Caddyfile qui la lit directement, voir docker/Caddyfile.

WORKDIR /app

# --- Composer -----------------------------------------------------------
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# --- Dépendances PHP (mise en cache Docker : copiées avant le reste) ---
COPY composer.json composer.lock symfony.lock ./
RUN composer install --no-dev --no-scripts --no-progress --no-interaction --optimize-autoloader

# --- Reste du code de l'application -------------------------------------
COPY . .

# Termine l'installation des paquets Symfony Flex (scripts post-install)
RUN composer dump-autoload --optimize --no-dev \
    && composer symfony:dump-env prod || true

# --- Assets (AssetMapper, pas de build Node nécessaire) ------------------
RUN php bin/console asset-map:compile --env=prod --no-debug || true

# Droits d'écriture pour var/ (cache, logs, clés JWT générées au démarrage)
RUN mkdir -p var config/jwt \
    && chown -R www-data:www-data var config/jwt

COPY docker/Caddyfile /etc/caddy/Caddyfile
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 80

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["frankenphp", "run", "--config", "/etc/caddy/Caddyfile"]
