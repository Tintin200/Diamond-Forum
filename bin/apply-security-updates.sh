#!/bin/bash

# =============================================================================
# Script: apply-security-updates.sh
# Description: Applique les mises à jour de sécurité recommandées pour le projet
# =============================================================================

set -e

echo "=========================================="
echo "Mise à jour de sécurité - Baseball_v1"
echo "=========================================="
echo ""

# Vérifier que nous sommes dans le bon répertoire
if [ ! -f "composer.json" ]; then
    echo "Erreur: Exécutez ce script depuis la racine du projet"
    exit 1
fi

echo "Répertoire du projet vérifié"
echo ""

# Étape 1: Mettre à jour les dépendances Composer
echo "Étape 1/6: Mise à jour des dépendances Composer..."
composer install --no-dev --optimize-autoloader
echo "Dependances mises à jour"
echo ""

# Étape 2: Vérifier la configuration de sécurité
echo "Étape 2/6: Vérification de la configuration de sécurité..."
if [ -f "config/packages/security_headers.yaml" ]; then
    echo "Fichier security_headers.yaml trouvé (obsolète)"
    rm config/packages/security_headers.yaml
    echo "Fichier obsolète supprimé"
fi

if [ -f "src/EventListener/SecurityHeadersListener.php" ]; then
    echo "SecurityHeadersListener est en place"
else
    echo "SecurityHeadersListener manquant"
fi

if [ -f "src/EventListener/RateLimitListener.php" ]; then
    echo "RateLimitListener est en place"
else
    echo "RateLimitListener manquant"
fi
echo ""

# Étape 3: Vérifier les secrets
echo "🔑 Étape 3/6: Vérification des secrets..."
if [ "$(grep -c 'changeme_in_env_local' .env)" -gt 0 ]; then
    echo "Des valeurs par défaut 'changeme_in_env_local' trouvées dans .env"
    echo "   Copiez .env.local.example en .env.local et configurez vos valeurs"
fi

if [ ! -f ".env.local" ]; then
    echo ".env.local n'existe pas"
    echo "   Copiez .env.local.example en .env.local :"
    echo "   cp .env.local.example .env.local"
    echo "   Puis éditez .env.local avec vos informations"
fi
echo ""

# Étape 4: Générer les clés JWT si nécessaire
echo "Étape 4/6: Génération des clés JWT..."
if [ ! -f "config/jwt/private.pem" ] || [ ! -f "config/jwt/public.pem" ]; then
    mkdir -p config/jwt
    php bin/console lexik:jwt:generate-keypair --skip-if-exists
    echo "Clés JWT générées"
else
    echo "Clés JWT existantes"
fi
echo ""

# Étape 5: Vérifier la configuration CORS
echo "Étape 5/6: Vérification de la configuration CORS..."
if [ -f "config/packages/nelmio_cors.yaml" ]; then
    echo "Configuration CORS trouvée"
else
    echo "Configuration CORS manquante"
fi
echo ""

# Étape 6: Générer le secret de l'application
echo "Étape 6/6: Génération du secret de l'application..."
if [ -f ".env.local" ]; then
    if grep -q "^APP_SECRET=" .env.local; then
        echo "APP_SECRET existe déjà dans .env.local"
        echo "   Pour en générer un nouveau :"
        echo "   php bin/console secrets:generate-keys"
    else
        echo "APP_SECRET="$(php bin/console secrets:generate-keys | grep "APP_SECRET=" | cut -d'=' -f2) >> .env.local
        echo "APP_SECRET généré et ajouté à .env.local"
    fi
else
    echo ".env.local n'existe pas, le secret ne peut pas être ajouté automatiquement"
fi
echo ""

echo "=========================================="
echo "Mises à jour de sécurité appliquées !"
echo "=========================================="
echo ""
echo "Tâches complétées:"
echo "   - Dépendances mises à jour"
echo "   - Configuration de sécurité vérifiée"
echo "   - Secrets vérifiés"
echo "   - Clés JWT générées"
echo "   - Configuration CORS vérifiée"
echo ""
echo "Prochaines étapes recommandées:"
echo "   1. Exécutez: composer audit"
echo "   2. Exécutez: php bin/console lint:yaml"
echo "   3. Exécutez: php bin/console lint:twig"
echo "   4. Configurez votre serveur web (Apache/Nginx)"
echo "   5. Testez votre application"
echo ""
echo "Documentation:"
echo "   - Voir SECURITY.md pour plus d'informations"
echo "   - Voir .env.local.example pour la configuration"
echo ""
