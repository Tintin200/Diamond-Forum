# Rapport de Sécurité - Projet Baseball_v1

## Date du rapport : 22 octobre 2026

## Résumé des vulnérabilités corrigées

Ce rapport documente les vulnérabilités de sécurité identifiées et corrigées dans le projet Baseball_v1 suite aux récentes failles PHP et aux bonnes pratiques de sécurité.

---

## 🚨 Vulnérabilités Critiques Corrigées

### 1. **Clé API en dur** (CWE-798)
- **Fichier** : `src/Service/ApiService.php`
- **Problème** : Une clé API était stockée en dur dans le code source (`ba058b6113f9766ce06bfb633d82aeb6`)
- **Risque** : Exposé dans le dépôt, permettant un accès non autorisé à l'API externe
- **Correction** : 
  - Clé supprimée du code
  - Méthode désactivée avec une exception explicite
  - La clé doit maintenant être injectée via les variables d'environnement
- **Action requise** : Si vous utilisiez ApiService, migrez vers BaseballApiService qui utilise `%env(BASEBALL_API_KEY)%`

### 2. **Secret d'application par défaut** (CWE-798)
- **Fichier** : `.env`
- **Problème** : `APP_SECRET=changeme_in_env_local` était toujours la valeur par défaut
- **Risque** : Un secret faible peut être deviné ou forcé
- **Correction** : Rappelez-vous de générer un nouveau secret avec :
  ```bash
  php bin/console secrets:generate-keys
  ```
  Et placez-le dans `.env.local` (ne pas commiter ce fichier)

---

## 🔧 Améliorations de Sécurité Implémentées

### 1. En-têtes de Sécurité HTTP
**Fichier** : `src/EventListener/SecurityHeadersListener.php`

Les en-têtes suivants sont maintenant ajoutés à toutes les réponses :

| En-tête | Valeur | Protection |
|---------|--------|------------|
| Content-Security-Policy | `default-src 'self'; ...` | XSS, injection de code |
| Strict-Transport-Security | `max-age=31536000; includeSubDomains; preload` | Force HTTPS |
| X-Content-Type-Options | `nosniff` | MIME-sniffing |
| X-Frame-Options | `DENY` | Clickjacking |
| X-XSS-Protection | `1; mode=block` | XSS |
| Referrer-Policy | `strict-origin-when-cross-origin` | Fuite d'informations |
| Permissions-Policy | `geolocation=(), camera=(), ...` | Accès aux fonctionnalités |

### 2. Configuration CORS Renforcée
**Fichier** : `config/packages/nelmio_cors.yaml`

- Méthodes autorisées restreintes à GET, POST, OPTIONS (sauf pour /api)
- En-têtes autorisés limités au minimum nécessaire
- `allow_credentials` désactivé par défaut
- Configuration spécifique pour les endpoints API

### 3. Rate Limiting
**Fichiers** : 
- `config/packages/rate_limiter.yaml` (configuration)
- `src/EventListener/RateLimitListener.php` (implémentation)

Limites configurées :
- **API générale** : 100 requêtes/minute/IP
- **Authentification** : 10 requêtes/minute/IP
- **Pages sensibles** : 20 requêtes/minute/IP

Protège contre :
- Attaques par force brute
- Attaques DDoS
- Abus de l'API

### 4. Longueur minimale du mot de passe
**Fichier** : `src/Form/RegistrationFormType.php`

- **Avant** : 6 caractères minimum
- **Maintenant** : 12 caractères minimum
- **Raison** : Les mots de passe courts sont vulnérables aux attaques par force brute

### 5. Mise à jour des dépendances
**Fichier** : `composer.json`

- Ajout de `symfony/rate-limiter: 8.0.*` pour le rate limiting
- Toutes les dépendances Symfony sont à jour (8.0.*)

**Action requise** : Exécutez :
```bash
composer update
```

---

## Recommandations Supplémentaires

### 1. Variables d'Environnement
Créez un fichier `.env.local` (ne pas commiter) avec :
```ini
# Secret d'application
APP_SECRET=votre_secret_généré_ici

# Clé API Baseball (si utilisée)
BASEBALL_API_KEY=votre_clé_api

# Configuration CORS (adaptez selon votre environnement)
CORS_ALLOW_ORIGIN='^https?://(votre-domaine\.com|localhost|127\.0\.0\.1)(:[0-9]+)?$'
```

Générez un secret avec :
```bash
php bin/console secrets:generate-keys
```

### 2. Configuration de la Base de Données
Assurez-vous que votre configuration de base de données dans `.env.local` est sécurisée :
- Utilisez un mot de passe fort
- Ne pas utiliser SQLite en production
- Exemple pour MySQL :
```ini
DATABASE_URL="mysql://utilisateur:mot_de_passe_fort@localhost:3306/baseball?serverVersion=8.0"
```

### 3. Configuration JWT
Le bundle LexikJWTAuthentication est utilisé. Assurez-vous que :
- La clé privée est stockée de manière sécurisée
- La clé publique est accessible
- Les tokens expirent après un délai raisonnable

### 4. Protection XSS pour les Articles
Actuellement, le contenu des articles est échappé par Twig, ce qui est sûr mais peut empêcher le formatage riche.

**Option 1** (Sécurisé) : Continuer à échapper (recommandé pour du texte simple)
- Le HTML saisie par les utilisateurs sera affiché comme du texte
- Sécurité maximale

**Option 2** (Si HTML nécessaire) : Implémenter un sanitizer
```bash
composer require sensiolabs/security-checker
```
Puis utiliser un service comme HTML Purifier pour nettoyer le HTML avant de l'afficher.

### 5. Journalisation (Logging)
Vérifiez que la journalisation est configurée pour :
- Capturer les tentatives de connexion échouées
- Capturer les erreurs d'authentification
- Ne pas logger les informations sensibles (mots de passe, tokens)

### 6. Sauvegardes
- Sauvegardez régulièrement votre base de données
- Testez la restauration des sauvegardes

### 7. HTTPS en Production
- Utilisez toujours HTTPS en production
- Le HSTS est déjà configuré pour forcer HTTPS après la première visite
- Utilisez un certificat SSL valide (Let's Encrypt recommandé)

---

## 🔍 Vérification de la Sécurité

### Pour vérifier que tout est configuré correctement :

1. **Tester les en-têtes de sécurité** :
   ```bash
   curl -I http://votre-site.com
   ```
   Vous devriez voir tous les en-têtes de sécurité listés ci-dessus.

2. **Tester le rate limiting** :
   - Faites 11 requêtes consécutives à /login depuis la même IP
   - La 11ème requête devrait retourner HTTP 429 (Too Many Requests)

3. **Vérifier la validation du mot de passe** :
   - Essayez de créer un compte avec un mot de passe de 11 caractères
   - Le formulaire devrait rejeter avec un message d'erreur

4. **Tester CORS** :
   - Faites une requête depuis un domaine non autorisé
   - La requête devrait être rejetée (sauf si le domaine est dans CORS_ALLOW_ORIGIN)

---

## Ressources de Sécurité

- [OWASP Top 10](https://owasp.org/www-project-top-ten/)
- [Symfony Security Best Practices](https://symfony.com/doc/current/best_practices/security.html)
- [CWE/SANS Top 25](https://cwe.mitre.org/top25/)

---

## Prochaines Étapes Recommandées

1. **Mettre à jour toutes les dépendances** :
   ```bash
   composer update
   composer audit
   ```

2. **Configurer un scanner de sécurité** :
   - [Symfony Security Checker](https://security.symfony.com/)
   - [Local PHP Security Checker](https://github.com/fabpot/local-php-security-checker)

3. **Configurer des sauvegardes automatiques**

4. **Mettre en place un monitoring** :
   - Surveiller les tentatives de connexion échouées
   - Surveiller les erreurs 429 (rate limiting)

5. **Effectuer un audit de sécurité complet** :
   - Vérifier tous les contrôleurs pour les injections SQL (Doctrine ORM protège, mais vérifiez les requêtes natives)
   - Vérifier les formulaires pour la validation des entrées
   - Vérifier les templates pour l'échappement XSS

---

## Support

Si vous avez des questions sur la sécurité de ce projet, veuillez consulter :
- La documentation Symfony sur la sécurité
- Les bonnes pratiques OWASP
- Les recommandations PHP-The Right Way

**Ne jamais** :
- Stocker des secrets dans le code source
- Utiliser des mots de passe faibles
- Désactiver le rate limiting en production
- Ignorer les mises à jour de sécurité

---

*Ce rapport a été généré automatiquement le 22 octobre 2026.*
