@echo off
 REM ============================================================================
 REM Script: apply-security-updates.bat
 REM Description: Applique les mises à jour de sécurité recommandées pour le projet
 REM ============================================================================

@echo on
echo ==========================================
echo Mise à jour de sécurité - Baseball_v1
echo ==========================================
echo.

 REM Vérifier que nous sommes dans le bon répertoire
if not exist "composer.json" (
    echo ❌ Erreur: Exécutez ce script depuis la racine du projet
    pause
    exit /b 1
)

echo ✅ Répertoire du projet vérifié
echo.

 REM Étape 1: Mettre à jour les dépendances Composer
echo 📦 Étape 1/6: Mise à jour des dépendances Composer...
php composer.phar install --no-dev --optimize-autoloader
if errorlevel 1 (
    echo ❌ Échec de la mise à jour des dépendances
    pause
    exit /b 1
)
echo ✅ Dépendances mises à jour
echo.

 REM Étape 2: Vérifier la configuration de sécurité
echo 🔒 Étape 2/6: Vérification de la configuration de sécurité...
if exist "config\packages\security_headers.yaml" (
    echo ⚠️  Fichier security_headers.yaml trouvé (obsolète)
    del config\packages\security_headers.yaml
    echo ✅ Fichier obsolète supprimé
)

if exist "src\EventListener\SecurityHeadersListener.php" (
    echo ✅ SecurityHeadersListener est en place
) else (
    echo ❌ SecurityHeadersListener manquant
)

if exist "src\EventListener\RateLimitListener.php" (
    echo ✅ RateLimitListener est en place
) else (
    echo ❌ RateLimitListener manquant
)
echo.

 REM Étape 3: Vérifier les secrets
echo 🔑 Étape 3/6: Vérification des secrets...
findstr /C:"changeme_in_env_local" .env > nul
if %errorlevel% equ 0 (
    echo ⚠️  Des valeurs par défaut 'changeme_in_env_local' trouvées dans .env
    echo    Copiez .env.local.example en .env.local et configurez vos valeurs
)

if not exist ".env.local" (
    echo ⚠️  .env.local n'existe pas
    echo    Copiez .env.local.example en .env.local :
    echo    copy .env.local.example .env.local
    echo    Puis éditez .env.local avec vos informations
)
echo.

 REM Étape 4: Générer les clés JWT si nécessaire
echo 📝 Étape 4/6: Génération des clés JWT...
if not exist "config\jwt\private.pem" (
    mkdir config\jwt 2> nul
    php bin/console lexik:jwt:generate-keypair --skip-if-exists
    echo ✅ Clés JWT générées
) else (
    echo ✅ Clés JWT existantes
)
echo.

 REM Étape 5: Vérifier la configuration CORS
echo 🌐 Étape 5/6: Vérification de la configuration CORS...
if exist "config\packages\nelmio_cors.yaml" (
    echo ✅ Configuration CORS trouvée
) else (
    echo ❌ Configuration CORS manquante
)
echo.

 REM Étape 6: Générer le secret de l'application
echo 🔐 Étape 6/6: Génération du secret de l'application...
if exist ".env.local" (
    findstr /B /C:"APP_SECRET=" .env.local > nul
    if %errorlevel% equ 0 (
        echo ⚠️  APP_SECRET existe déjà dans .env.local
        echo    Pour en générer un nouveau :
        echo    php bin/console secrets:generate-keys
    ) else (
        for /f "tokens=2 delims==" %%G in ('php bin/console secrets:generate-keys ^| findstr "APP_SECRET="') do (
            echo APP_SECRET=%%G >> .env.local
            echo ✅ APP_SECRET généré et ajouté à .env.local
        )
    )
) else (
    echo ⚠️  .env.local n'existe pas, le secret ne peut pas être ajouté automatiquement
)
echo.

echo ==========================================
echo Mises à jour de sécurité appliquées !
echo ==========================================
echo.
echo ✅ Tâches complétées:
echo    - Dépendances mises à jour
echo    - Configuration de sécurité vérifiée
echo    - Secrets vérifiés
echo    - Clés JWT générées
echo    - Configuration CORS vérifiée
echo.
echo 📋 Prochaines étapes recommandées:
echo    1. Exécutez: composer audit
echo    2. Exécutez: php bin/console lint:yaml
echo    3. Exécutez: php bin/console lint:twig
echo    4. Configurez votre serveur web (Apache/Nginx)
echo    5. Testez votre application
echo.
echo 📚 Documentation:
echo    - Voir SECURITY.md pour plus d'informations
echo    - Voir .env.local.example pour la configuration
echo.
pause
