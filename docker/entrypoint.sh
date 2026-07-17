#!/bin/sh
set -e

echo "==> Diamond Forum — démarrage du conteneur"

# --- Attendre que la base de données Postgres de Render soit joignable ---
echo "==> Vérification de la connexion à la base de données..."
php bin/console doctrine:query:sql "SELECT 1" --env=prod > /dev/null 2>&1 || {
    echo "==> Base de données pas encore prête, nouvelle tentative dans 3s..."
    sleep 3
}

# --- Clés JWT (lexik/jwt-authentication-bundle) ----------------------------
# Le disque du plan Free de Render N'EST PAS persistant : tout ce qui est
# écrit sur le filesystem disparaît au prochain redémarrage/redéploiement.
# Si on régénérait les clés à chaque démarrage, TOUS les tokens API émis
# deviendraient invalides après chaque redéploiement ou réveil du service.
#
# Solution : générer les clés UNE FOIS en local, les encoder en base64,
# et les stocker dans les variables d'environnement Render JWT_PRIVATE_KEY_B64
# / JWT_PUBLIC_KEY_B64 (voir README-DEPLOY-RENDER.md). On les réécrit sur
# disque à chaque démarrage à partir de ces variables, donc elles restent
# stables dans le temps.
mkdir -p config/jwt
if [ -n "$JWT_PRIVATE_KEY_B64" ] && [ -n "$JWT_PUBLIC_KEY_B64" ]; then
    echo "==> Restauration des clés JWT depuis les variables d'environnement..."
    echo "$JWT_PRIVATE_KEY_B64" | base64 -d > config/jwt/private.pem
    echo "$JWT_PUBLIC_KEY_B64" | base64 -d > config/jwt/public.pem
elif [ ! -f config/jwt/private.pem ]; then
    echo "==> ATTENTION : JWT_PRIVATE_KEY_B64/JWT_PUBLIC_KEY_B64 non définies."
    echo "==> Génération de clés JWT temporaires (seront perdues au prochain redémarrage)."
    php bin/console lexik:jwt:generate-keypair --skip-if-exists --env=prod
fi

# --- Applique les migrations Doctrine en attente ---------------------------
echo "==> Application des migrations Doctrine..."
php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration --env=prod

# --- Réchauffe le cache Symfony ---------------------------------------------
echo "==> Cache warmup..."
php bin/console cache:clear --env=prod --no-debug

echo "==> Prêt. Démarrage de FrankenPHP..."
exec "$@"
