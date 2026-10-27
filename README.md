# Baseball v1 - Documentation Complete

## Table des matieres

1. [Introduction](#introduction)
2. [Pre-requis](#pre-requis)
3. [Installation](#installation)
4. [Configuration](#configuration)
5. [Architecture du projet](#architecture-du-projet)
6. [Entites Doctrine](#entites-doctrine)
7. [Controlleurs](#controlleurs)
8. [Services](#services)
9. [API Baseball](#api-baseball)
10. [API REST](#api-rest)
11. [Authentification](#authentification)
12. [Docker](#docker)
13. [Tests](#tests)
14. [Utilisation](#utilisation)
15. [Captures d'ecran](#captures-decrans)
16. [Contribuer](#contribuer)
17. [Licence](#licence)

---

## Introduction

Baseball v1 est une application web developpee en PHP avec le framework Symfony 8.0, destinee a fournir une plateforme de suivi et de gestion de contenu lie au baseball. L'application permet aux utilisateurs de consulter les matchs en direct, les classements des ligues, les statistiques des equipes, et de publier des articles sur le baseball.

**Fonctionnalites principales**
- Affichage des matchs MLB en temps reel
- Consultation des classements des ligues (American League et National League)
- Systeme de gestion d'articles avec likes et vues
- Authentification JWT pour l'API REST
- Systeme de chat en temps reel
- Gestion des utilisateurs et de leurs interactions

**Technologies utilisees**
- Backend: Symfony 8.0, PHP 8.4+
- Base de donnees: MariaDB/MySQL 11.1
- Frontend: Twig, Stimulus, Turbo
- API: API Platform, Lexik JWT Authentication
- Cache: Symfony Cache
- Docker: Containerisation complete

---

## Pre-requis

Pour executez ce projet localement, vous devez avoir les elements suivants installs sur votre machine:

### Logiciels requis

| Logiciel | Version | Lien |
|----------|---------|------|
| PHP | 8.4 ou superieur | [php.net](https://www.php.net) |
| Composer | 2.x | [getcomposer.org](https://getcomposer.org) |
| Node.js | 18.x ou superieur | [nodejs.org](https://nodejs.org) |
| Docker | 20.x ou superieur | [docker.com](https://www.docker.com) |
| Docker Compose | 2.x | Inclus avec Docker Desktop |
| Git | 2.x | [git-scm.com](https://git-scm.com) |

### Extensions PHP requises

```bash
- ext-ctype
- ext-iconv
- PDO
- PDO_MYSQL
- mbstring
- intl
- json
- xml
- curl
- gd
```

---

## Installation

### Methode 1: Installation avec Docker (recommandee)

#### Etape 1: Cloner le depot

```bash
cd /chemin/vers/votre/dossier
git clone https://github.com/votre-utilisateur/baseball_v1.git
cd baseball_v1
```

#### Etape 2: Configurer les variables d'environnement

Copiez le fichier .env exemple et modifiez-le selon vos besoins:

```bash
cp .env .env.local
```

Editez le fichier .env.local avec vos configurations:

```ini
# Configuration de la base de donnees
DATABASE_URL=mysql://app:!ChangeMe!@database:3306/app?serverVersion=11.1&charset=utf8mb4

# Cle secrete de l'application (generez-en une nouvelle)
APP_SECRET=votre_cle_secrète_ici

# Configuration SMTP pour les emails (optionnel)
MAILER_DSN=smtp://localhost:1025

# Cle API Baseball (API-Sports)
BASEBALL_API_URL=https://api-baseball.p.rapidapi.com
BASEBALL_API_KEY=votre_cle_api_ici
```

#### Etape 3: Demarrer les conteneurs Docker

```bash
docker-compose up -d
```

Ce commande va:
- Creer et demarrer les conteneurs pour l'application, la base de donnees, le serveur mail et phpMyAdmin
- Installer les dependances Composer automatiquement (via le Dockerfile)

#### Etape 4: Attendre que la base de donnees soit prete

```bash
docker-compose exec app bash -c "while ! mysqladmin ping -h database --silent; do sleep 1; done"
```

#### Etape 5: Executer les migrations de la base de donnees

```bash
docker-compose exec app php bin/console doctrine:migrations:migrate
```

#### Etape 6: Charger les fixtures (optionnel)

Si vous avez des fixtures configurees:

```bash
docker-compose exec app php bin/console doctrine:fixtures:load
```

#### Etape 7: Acceder a l'application

- **Application**: [http://localhost:8000](http://localhost:8000)
- **phpMyAdmin**: [http://localhost:8080](http://localhost:8080)
- **MailPit (pour les emails)**: [http://localhost:8025](http://localhost:8025)

### Methode 2: Installation locale (sans Docker)

#### Etape 1: Cloner le depot

```bash
cd /chemin/vers/votre/dossier
git clone https://github.com/votre-utilisateur/baseball_v1.git
cd baseball_v1
```

#### Etape 2: Installer les dependances Composer

```bash
composer install
```

#### Etape 3: Configurer la base de donnees

Creez une base de donnees MySQL/MariaDB et configurez le fichier .env:

```ini
DATABASE_URL=mysql://utilisateur:motdepasse@localhost:3306/baseball?serverVersion=11.1&charset=utf8mb4
```

#### Etape 4: Executer les migrations

```bash
php bin/console doctrine:migrations:migrate
```

#### Etape 5: Installer les dependances Node.js (pour les assets)

```bash
npm install
npm run dev
```

#### Etape 6: Demarrer le serveur de developpement

```bash
symfony server:start
```

L'application sera accessible a [http://localhost:8000](http://localhost:8000)

---

## Configuration

### Configuration de l'API Baseball (API-Sports)

Pour obtenir des donnees reelles sur les matchs et classements, vous devez obtenir une cle API de [API-Sports](https://rapidapi.com/api-sports/api/api-baseball):

1. Inscrivez-vous sur RapidAPI
2. Souscrivez a l'API Baseball (gratuit avec limite de requetes)
3. Obtenez votre cle API
4. Ajoutez-la a votre fichier .env.local:

```ini
BASEBALL_API_URL=https://api-baseball.p.rapidapi.com
BASEBALL_API_KEY=votre_cle_api_rapidapi
```

**Note**: Si vous ne configurez pas la cle API, l'application utilisera des donnees de demonstration (mock) pour les matchs et classements.

### Configuration de la base de donnees

Le projet utilise Doctrine ORM avec MariaDB/MySQL. La configuration se trouve dans:

- `config/packages/doctrine.yaml` - Configuration de Doctrine
- `docker-compose.yml` - Configuration du conteneur de base de donnees

Pour modifier la configuration de la base de donnees:

```yaml
# config/packages/doctrine.yaml
doctrine:
    dbal:
        url: '%env(DATABASE_URL)%'
        server_version: '11.1'
```

### Configuration de l'API Platform

La configuration de l'API Platform se trouve dans `config/packages/api_platform.yaml`:

```yaml
api_platform:
    title: Hello API Platform
    version: 1.0.0
    defaults:
        stateless: true
        cache_headers:
            vary: ['Content-Type', 'Authorization', 'Origin']
```

Vous pouvez personnaliser le titre et la version de votre API.

### Configuration de la securite

La configuration de la securite est dans `config/packages/security.yaml`. Elle gere:

- L'authentification JWT pour l'API
- L'authentification classique pour le frontend
- Les roles et permissions d'acces

Pour ajouter un nouvel utilisateur administrateur, modifiez le role dans la base de donnees:

```sql
UPDATE app_user SET roles = JSON_ARRAY('ROLE_ADMIN') WHERE email = 'admin@example.com';
```

---

## Architecture du projet

```
baseball_v1/
├── assets/                    # Assets frontend (JS, CSS, images)
│   ├── controllers.json       # Configuration des controlleurs Stimulus
│   └── vendor/               # Dependances frontend
├── bin/                      # Scripts executables
│   └── console               # Console Symfony
├── config/                   # Configuration Symfony
│   ├── bundles.php           # Bundles actives
│   ├── packages/             # Configuration des bundles
│   │   ├── api_platform.yaml
│   │   ├── doctrine.yaml
│   │   ├── framework.yaml
│   │   ├── security.yaml
│   │   └── ...
│   ├── routes/               # Routes
│   │   ├── api_platform.yaml
│   │   └── framework.yaml
│   └── services.yaml         # Configuration des services
├── docker/                   # Configuration Docker
│   ├── Caddyfile             # Configuration Caddy
│   └── entrypoint.sh         # Script d'entree
├── migrations/               # Migrations de la base de donnees
│   └── VersionYYYYMMDD....php
├── public/                   # Point d'entree web
│   └── index.php
├── src/                      # Code source principal
│   ├── Controller/           # Controlleurs
│   │   ├── Api/              # Controlleurs API
│   │   │   └── BaseballApiController.php
│   │   ├── ArticleController.php
│   │   ├── ChatController.php
│   │   ├── HomeController.php
│   │   ├── NewsController.php
│   │   ├── AboutController.php
│   │   ├── RegistrationController.php
│   │   └── SecurityController.php
│   ├── Entity/               # Entites Doctrine
│   │   ├── Article.php
│   │   ├── ArticleLike.php
│   │   ├── ArticleVue.php
│   │   ├── Contact.php
│   │   ├── Equipement.php
│   │   └── User.php
│   ├── Form/                 # Formulaires
│   ├── OpenApi/              # Schema OpenAPI
│   ├── Repository/           # Repositories Doctrine
│   ├── Security/             # Securite
│   │   └── AppCustomAuthenticator.php
│   ├── Service/              # Services
│   │   ├── BaseballApiService.php
│   │   └── ...
│   └── Kernel.php            # Noyau de l'application
├── templates/                # Templates Twig
│   ├── about/                # Page A propos
│   ├── article/              # Articles
│   ├── base.html.twig       # Template de base
│   ├── chat/                 # Chat
│   ├── home/                 # Page d'accueil
│   ├── news/                 # Actualites
│   ├── registration/         # Inscription
│   └── security/             # Authentification
├── tests/                    # Tests unitaires et fonctionnels
├── translations/            # Fichiers de traduction
├── var/                      # Fichiers variables (cache, logs)
├── vendor/                   # Dependances Composer
├── .env                      # Variables d'environnement
├── .env.local                # Variables d'environnement locales
├── compose.yaml              # Configuration Docker Compose
├── composer.json             # Dependances PHP
├── Dockerfile                # Dockerfile pour l'application
└── README.md                 # Documentation
```

---

## Entites Doctrine

Le projet utilise Doctrine ORM pour la gestion de la base de donnees. Voici les entites principales:

### 1. User (App\Entity\User)

**Description**: Entite representant un utilisateur du systeme.

**Champs**

| Propriete | Type | Description |
|-----------|------|-------------|
| id | integer | Identifiant unique (auto-increment) |
| email | string (180) | Adresse email de l'utilisateur (unique) |
| roles | array | Roles de l'utilisateur (ex: ROLE_USER, ROLE_ADMIN) |
| password | string | Mot de passe hashe |

**Relations**
- OneToMany: articles (Article) - Articles publies par l'utilisateur
- OneToMany: articleLikes (ArticleLike) - Likes poses par l'utilisateur
- OneToMany: articleVues (ArticleVue) - Vues effectues par l'utilisateur

**Methodes principales**
- `getUserIdentifier()`: Retourne l'email comme identifiant
- `getRoles()`: Retourne les roles avec ROLE_USER par defaut
- `getPassword()`: Retourne le mot de passe hashe
- `addArticle()`, `removeArticle()`: Gestion des articles
- `addArticleLike()`, `removeArticleLike()`: Gestion des likes
- `addArticleVue()`, `removeArticleVue()`: Gestion des vues

### 2. Article (App\Entity\Article)

**Description**: Entite representant un article publie sur le site.

**Champs**

| Propriete | Type | Description |
|-----------|------|-------------|
| id | integer | Identifiant unique |
| sujet | string (180) | Titre/sujet de l'article |
| contenu | text | Contenu de l'article |
| auteur | string (150) | Nom de l'auteur (redondant avec author) |
| image | string (255) | URL de l'image de couverture (nullable) |
| tags | string (255) | Tags/categories separes par des virgules (nullable) |

**Relations**
- ManyToOne: author (User) - Utilisateur qui a publie l'article
- OneToMany: articleLikes (ArticleLike) - Likes recueillis par l'article
- OneToMany: articleVues (ArticleVue) - Vues de l'article

**Methodes principales**
- `getId()`, `getSujet()`, `getContenu()`, etc.: Getters pour tous les champs
- `setSujet()`, `setContenu()`, etc.: Setters pour tous les champs
- `addArticleLike()`, `removeArticleLike()`: Gestion des likes
- `addArticleVue()`, `removeArticleVue()`: Gestion des vues

### 3. ArticleLike (App\Entity\ArticleLike)

**Description**: Entite representant un like sur un article.

**Champs**
- id: Identifiant unique
- article: Relation ManyToOne vers Article
- user: Relation ManyToOne vers User

**Relations**
- ManyToOne: article (Article)
- ManyToOne: user (User)

### 4. ArticleVue (App\Entity\ArticleVue)

**Description**: Entite representant une vue d'un article.

**Champs**
- id: Identifiant unique
- article: Relation ManyToOne vers Article
- user: Relation ManyToOne vers User

**Relations**
- ManyToOne: article (Article)
- ManyToOne: user (User)

### 5. Contact (App\Entity\Contact)

**Description**: Entite pour gerer les messages de contact.

### 6. Equipement (App\Entity\Equipement)

**Description**: Entite representant un equipement de baseball.

---

## Controlleurs

### Controlleurs Web (Frontend)

#### HomeController

**Route**: `/` (app_home)

**Description**: Controlleur principal qui affiche la page d'accueil avec:
- Les matchs MLB en direct ou a venir
- Les classements de l'American League et National League
- Les statistiques globales (nombre d'utilisateurs, articles, likes)
- Les 3 derniers articles publies

**Methodes**
- `index()`: Affiche la page d'accueil

**Dependances**
- BaseballApiService: Pour recuperer les donnees de l'API Baseball
- UserRepository: Pour compter les utilisateurs
- ArticleRepository: Pour recuperer les articles
- ArticleLikeRepository: Pour compter les likes

#### ArticleController

**Routes**
- `/articles` (app_article_index): Liste des articles
- `/articles/new` (app_article_new): Formulaire de creation d'article
- `/articles/{id}` (app_article_show): Affichage d'un article
- `/articles/{id}/edit` (app_article_edit): Edition d'un article
- `/articles/{id}/delete` (app_article_delete): Suppression d'un article
- `/articles/{id}/like` (app_article_like): Ajouter un like a un article
- `/articles/{id}/vue` (app_article_vue): Enregistrer une vue d'article

**Description**: Gere la creation, lecture, mise a jour et suppression des articles, ainsi que le systeme de likes et de vues.

#### ChatController

**Route**: `/chat` (app_chat)

**Description**: Gere le systeme de chat en temps reel.

#### NewsController

**Route**: `/news` (app_news)

**Description**: Affiche les actualites liees au baseball.

#### AboutController

**Route**: `/about` (app_about)

**Description**: Affiche la page "A propos" avec des informations sur l'application.

#### RegistrationController

**Routes**
- `/register` (app_register): Formulaire d'inscription

**Description**: Gere l'inscription des nouveaux utilisateurs.

#### SecurityController

**Routes**
- `/login` (app_login): Formulaire de connexion
- `/logout` (app_logout): Deconnexion

**Description**: Gere l'authentification des utilisateurs.

### Controlleurs API

#### BaseballApiController

**Route de base**: `/api/external`

**Endpoints**

| Methode | Endpoint | Description | Parametres |
|---------|----------|-------------|------------|
| GET | `/games` | Recupere les matchs de baseball | date, season, live |
| GET | `/leagues` | Recupere les ligues de baseball | id, name, country, season |
| GET | `/standings` | Recupere les classements | season (requis) |

**Description**: Fournit des endpoints REST pour acceder aux donnees de l'API Baseball (API-Sports). Tous les endpoints forcent league=1 (MLB).

**Exemples de requetes**

```bash
# Recuperer les matchs du jour
GET /api/external/games?date=2026-10-11

# Recuperer les matchs en direct
GET /api/external/games?live=true

# Recuperer les ligues
GET /api/external/leagues

# Recuperer les classements pour une saison
GET /api/external/standings?season=2024
```

---

## Services

### BaseballApiService

**Description**: Service centralise pour interagir avec l'API Baseball (API-Sports).

**Fonctionnalites**
- Requetes cachees vers l'API externe
- Gestion des erreurs
- Conversion des donnees
- Requetes especifiques pour chaque type de donnee

**Methodes**

| Methode | Description | Cache TTL |
|---------|-------------|-----------|
| `request(endpoint, query, ttl)` | Requete generique | Configurable |
| `getLeagues(query)` | Recupere les ligues | 24 heures |
| `getSeasons()` | Recupere les saisons | 24 heures |
| `getTeams(query)` | Recupere les equipes | 12 heures |
| `getGames(query)` | Recupere les matchs | 30s (live) / 10 min (autre) |
| `getStandings(query)` | Recupere les classements | 30 minutes |
| `getMostRecentStandings(leagueId)` | Recupere les classements les plus recents | 30 minutes |
| `getTeamStatistics(query)` | Recupere les statistiques d'une equipe | 1 heure |

**Injecte dans**
- BaseballApiController
- HomeController

**Configuration requise**

Dans votre fichier .env:

```ini
BASEBALL_API_URL=https://api-baseball.p.rapidapi.com
BASEBALL_API_KEY=votre_cle_api
```

---

## API Baseball

### Presentation de l'API-Sports

L'application utilise l'API Baseball de [API-Sports](https://rapidapi.com/api-sports/api/api-baseball) pour recuperer des donnees en temps reel sur:

- Les ligues de baseball
- Les saisons
- Les equipes
- Les matchs (en direct, passes, a venir)
- Les classements
- Les statistiques

### Configuration

Pour activer l'API:

1. Obtenez une cle API sur RapidAPI
2. Ajoutez-la a votre fichier .env.local
3. Redemarrez l'application

**Sans cle API**, l'application utilisera des donnees de demonstration (mock) pour:
- Les matchs en direct
- Les classements des ligues

### Donnees de demonstration

Si l'API n'est pas configuree ou retourne une erreur, l'application affiche:

**Matchs de demonstration**
- New York Yankees vs Boston Red Sox (4-3, 6eme manche)
- Los Angeles Dodgers vs San Francisco Giants (22:10)
- Chicago Cubs vs St. Louis Cardinals (2-5, termine)

**Classements de demonstration (2024)**

American League:
1. New York Yankees (94-68, .580)
2. Cleveland Guardians (92-69, .571)
3. Baltimore Orioles (91-71, .562)
4. Houston Astros (88-73, .547)
5. Kansas City Royals (86-76, .531)
6. Detroit Tigers (86-76, .531)

National League:
1. Los Angeles Dodgers (98-64, .605)
2. Philadelphia Phillies (95-67, .586)
3. Milwaukee Brewers (93-69, .574)
4. San Diego Padres (93-69, .574)
5. Atlanta Braves (89-73, .549)
6. New York Mets (89-73, .549)

---

## API REST

### Endpoints disponibles

#### Endpoints API Platform

L'application utilise API Platform pour exposer automatiquement les entites comme des endpoints REST.

**Entites exposees**
- User
- Article
- ArticleLike
- ArticleVue
- Contact
- Equipement

**Format des endpoints**

```
GET    /api/articles              - Liste tous les articles
GET    /api/articles/{id}        - Recupere un article
POST   /api/articles             - Cree un article
PUT    /api/articles/{id}        - Met a jour un article
DELETE /api/articles/{id}        - Supprime un article
```

#### Endpoints personnalises

**BaseballApiController** (`/api/external`)
- GET `/games` - Matchs de baseball
- GET `/leagues` - Ligues de baseball
- GET `/standings` - Classements

### Authentification API

L'API utilise JWT (JSON Web Token) pour l'authentification.

**Obtenir un token JWT**

```bash
POST /api/login_check
Content-Type: application/json

{
  "email": "utilisateur@example.com",
  "password": "votre_mot_de_passe"
}
```

**Reponse**

```json
{
  "token": "votre_token_jwt_ici"
}
```

**Utiliser le token**

Ajoutez le header Authorization a vos requetes:

```bash
Authorization: Bearer votre_token_jwt_ici
```

### Configuration API Platform

Fichier: `config/packages/api_platform.yaml`

```yaml
api_platform:
    title: Baseball API
    version: 1.0.0
    defaults:
        stateless: true
        cache_headers:
            vary: ['Content-Type', 'Authorization', 'Origin']
```

Pour personnaliser l'API, modifiez ce fichier.

---

## Authentification

### Systeme d'authentification

L'application utilise deux systemes d'authentification:

1. **Authentification classique** (pour le frontend)
   - Formulaire de connexion/inscription
   - Session PHP
   - Geree par SecurityController et AppCustomAuthenticator

2. **Authentification JWT** (pour l'API)
   - Token JWT
   - Stateless
   - Geree par LexikJWTAuthenticationBundle

### Configuration de la securite

Fichier: `config/packages/security.yaml`

**Pointscles**

- **Firewall `dev`**: Desactive la securite pour les outils de developpement
- **Firewall `api`**: Authentification JWT pour l'API
  - `json_login`: Connexion via JSON
  - `jwt`: Validation des tokens JWT
- **Firewall `main`**: Authentification classique pour le frontend
  - `custom_authenticator`: Utilise AppCustomAuthenticator
  - `remember_me`: Connexion automatique

**Regles d'acces**

```yaml
access_control:
    - { path: ^/api/login_check, roles: PUBLIC_ACCESS }
    - { path: ^/api$, roles: PUBLIC_ACCESS }
    - { path: ^/api/docs, roles: PUBLIC_ACCESS }
    - { path: ^/api/contexts, roles: PUBLIC_ACCESS }
    - { path: ^/api, roles: IS_AUTHENTICATED_FULLY }
```

### AppCustomAuthenticator

**Fichier**: `src/Security/AppCustomAuthenticator.php`

Ce service personnalise gere l'authentification via formulaire pour le frontend.

### Lexik JWT Authentication

Pour generer un token JWT:

```bash
POST /api/login_check
```

Le token doit etre passe dans le header `Authorization`:

```
Authorization: Bearer <token>
```

---

## Docker

### Configuration Docker

Le projet utilise Docker pour simplifier le deploiement et le developpement.

**Fichiers Docker**
- `Dockerfile`: Definit l'image de l'application
- `docker-compose.yml`: Definit les services
- `docker/Caddyfile`: Configuration du serveur web
- `docker/entrypoint.sh`: Script d'entree

### Services Docker

| Service | Port | Description |
|---------|------|-------------|
| app | 8000:80 | Application Symfony avec Caddy |
| database | 3306 | MariaDB 11.1 |
| mailer | 1025, 8025 | MailPit pour les emails |
| phpmyadmin | 8080:80 | Interface web pour la base de donnees |

### Commandes Docker utiles

**Demarrer tous les services**

```bash
docker-compose up -d
```

**Arreter tous les services**

```bash
docker-compose down
```

**Voir les logs**

```bash
docker-compose logs -f app
```

**Executer une commande dans le conteneur app**

```bash
docker-compose exec app php bin/console doctrine:migrations:migrate
```

**Acceder au conteneur de la base de donnees**

```bash
docker-compose exec database mysql -u app -p!ChangeMe! app
```

### Volumes Docker

| Volume | Description |
|--------|-------------|
| database_data | Donnees de la base de donnees |
| vendor | Dependances Composer |
| var | Fichiers variables (cache, logs) |

---

## Tests

### Configuration des tests

Le projet utilise PHPUnit 13.0 pour les tests unitaires et fonctionnels.

**Fichier de configuration**: `phpunit.xml.dist`

### Executer les tests

**Tous les tests**

```bash
php bin/phpunit
```

**Tests unitaires**

```bash
php bin/phpunit --filter Unit
```

**Tests fonctionnels**

```bash
php bin/phpunit --filter Functional
```

**Test d'une classe specifique**

```bash
php bin/phpunit tests/Entity/ArticleTest.php
```

### Structure des tests

```
tests/
├── Entity/              # Tests unitaires des entites
│   └── ArticleTest.php
├── Controller/         # Tests fonctionnels des controlleurs
└── ...
```

### Exemple de test unitaire

```php
<?php

namespace App\Tests\Entity;

use App\Entity\Article;
use PHPUnit\Framework\TestCase;

class ArticleTest extends TestCase
{
    public function testGetId(): void
    {
        $article = new Article();
        $this->assertNull($article->getId());
    }

    public function testSetSujet(): void
    {
        $article = new Article();
        $article->setSujet('Nouvel article');
        $this->assertEquals('Nouvel article', $article->getSujet());
    }
}
```

---

## Utilisation

### Pour les utilisateurs finaux

#### Creation d'un compte

1. Allez sur la page d'inscription: `/register`
2. Remplissez le formulaire avec votre email et mot de passe
3. Validez le formulaire
4. Vous etes redirige vers la page de connexion

#### Connexion

1. Allez sur la page de connexion: `/login`
2. Entrez votre email et mot de passe
3. Cliquez sur "Se connecter"

#### Consulter les matchs

La page d'accueil (`/`) affiche:
- Les matchs en direct et a venir
- Les classements des ligues
- Les statistiques du site
- Les derniers articles

#### Publier un article

1. Connectez-vous
2. Allez sur la page de creation d'article: `/articles/new`
3. Remplissez le formulaire:
   - Sujet/Titre
   - Contenu
   - Auteur (votre nom)
   - Image (URL optionnelle)
   - Tags (optionnel, separes par des virgules)
4. Validez le formulaire

#### Liker un article

1. Affichez l'article que vous voulez liker
2. Cliquez sur le bouton "Like"
3. Le like est enregistre dans la base de donnees

#### Chat en temps reel

1. Allez sur la page de chat: `/chat`
2. Envoyez des messages en temps reel

### Pour les developpeurs

#### Ajouter une nouvelle entite

1. Creez la classe dans `src/Entity/`
2. Executez:

```bash
php bin/console make:entity App\Entity\NouvelleEntite
```

3. Creez la migration:

```bash
php bin/console make:migration
```

4. Executez la migration:

```bash
php bin/console doctrine:migrations:migrate
```

#### Creer un nouveau controlleur

```bash
php bin/console make:controller NomDuController
```

#### Creer un nouveau service

```bash
php bin/console make:service NomDuService
```

#### Mettre a jour les dependances

```bash
composer update
```

#### Vider le cache

```bash
php bin/console cache:clear
```

---

## Captures d'ecrans

### Comment ajouter des captures d'ecrans

Pour ajouter des captures d'ecran a la documentation:

1. Creez un dossier `docs/screenshots/` a la racine du projet:

```bash
mkdir -p docs/screenshots
```

2. Ajoutez vos images (PNG, JPG, WebP recommandes) dans ce dossier

3. Referencez-les dans ce README.md comme suit:

```markdown
![Description de l'image](docs/screenshots/nom-de-l-image.png)
```

### Exemples de captures d'ecrans a inclure

Voici une liste de captures d'ecrans que vous pourriez ajouter:

1. **Page d'accueil**
   - Nom: `homepage.png`
   - Description: Page d'accueil avec matchs et classements
   - Reference: `![Page d'accueil](docs/screenshots/homepage.png)`

2. **Page de connexion**
   - Nom: `login.png`
   - Description: Formulaire de connexion
   - Reference: `![Page de connexion](docs/screenshots/login.png)`

3. **Page d'inscription**
   - Nom: `register.png`
   - Description: Formulaire d'inscription
   - Reference: `![Page d'inscription](docs/screenshots/register.png)`

4. **Liste des articles**
   - Nom: `articles.png`
   - Description: Liste des articles publies
   - Reference: `![Liste des articles](docs/screenshots/articles.png)`

5. **Article unique**
   - Nom: `article-detail.png`
   - Description: Affichage d'un article avec ses details
   - Reference: `![Article detail](docs/screenshots/article-detail.png)`

6. **Page de chat**
   - Nom: `chat.png`
   - Description: Interface de chat en temps reel
   - Reference: `![Page de chat](docs/screenshots/chat.png)`

7. **Page A propos**
   - Nom: `about.png`
   - Description: Page d'information sur l'application
   - Reference: `![Page A propos](docs/screenshots/about.png)`

8. **phpMyAdmin**
   - Nom: `phpmyadmin.png`
   - Description: Interface de gestion de la base de donnees
   - Reference: `![phpMyAdmin](docs/screenshots/phpmyadmin.png)`

9. **MailPit**
   - Nom: `mailpit.png`
   - Description: Interface de visualisation des emails
   - Reference: `![MailPit](docs/screenshots/mailpit.png)`

### Structure recommandee

```
docs/
└── screenshots/
    ├── homepage.png
    ├── login.png
    ├── register.png
    ├── articles.png
    ├── article-detail.png
    ├── chat.png
    ├── about.png
    ├── phpmyadmin.png
    └── mailpit.png
```

### Redimensionnement des images

Pour optimiser les captures d'ecrans:

- **Format**: PNG pour les captures avec transparence, JPG pour les photos
- **Resolution**: 1920x1080 ou la taille de votre ecran
- **Compression**: Utilisez des outils comme TinyPNG pour reduire la taille
- **Noms de fichiers**: Utilisez des noms descriptifs en minuscules avec des tirets

---

## Contribuer

### Guide de contribution

Nous sommes ravis que vous souhaitiez contribuer a ce projet! Voici comment proceder:

#### 1. Forker le projet

1. Allez sur la page du projet sur GitHub
2. Cliquez sur le bouton "Fork"
3. Clonez votre fork localement

```bash
git clone https://github.com/votre-utilisateur/baseball_v1.git
cd baseball_v1
```

#### 2. Configurer le projet

Suivez les instructions d'installation ci-dessus pour configurer le projet localement.

#### 3. Creer une branche de fonctionnalite

```bash
git checkout -b feature/nom-de-la-fonctionnalite
git checkout -b fix/nom-du-correctif
git checkout -b docs/maj-documentation
```

#### 4. Faire vos modifications

- Suivez les conventions de codage du projet
- Ajoutez des tests pour les nouvelles fonctionnalites
- Mettez a jour la documentation si necessaire

#### 5. Valider vos modifications

```bash
git add .
git commit -m "Description claire et concise de la modification"
```

**Conventions de commit**

- Utilisez des messages de commit clairs et descriptifs
- Preferez les messages en anglais
- Utilisez le present de l'imperatif: "Add feature" au lieu de "Added feature"
- Limitez a 72 caracteres par ligne

#### 6. Pousser vers votre fork

```bash
git push origin feature/nom-de-la-fonctionnalite
```

#### 7. Creer une Pull Request

1. Allez sur la page de votre fork sur GitHub
2. Cliquez sur "Compare & pull request"
3. Remplissez le formulaire avec une description detaillee de vos modifications
4. Attendez la revue du code

### Conventions de codage

**PHP**
- Suivez les [PSR-12](https://www.php-fig.org/psr/psr-12/) standards
- Utilisez 4 espaces pour l'indentation
- Pas de tabulations
- Les accents sont autorises dans les commentaires et strings

**Twig**
- Indentation de 2 ou 4 espaces
- Utilisez la syntaxe `{{ }}` et `{% %}` correctement
- Nommez les variables de maniere descriptive

**JavaScript**
- Suivez le standard ES6+
- Utilisez des noms de variables en camelCase
- Commentez votre code

### Tests

- Tous les tests doivent passer avant de soumettre une PR
- Ajoutez des tests pour les nouvelles fonctionnalites
- Maintenez une couverture de test elevee

**Executer les tests**

```bash
php bin/phpunit
```

### Revue de code

- Attendez-vous a des commentaires et suggestions
- Soyez pret a faire des modifications
- Respectez les decisions de l'equipe de developpement

---

## Licence

Ce projet est sous licence **proprietary** (droits reserves).

**Ce que cela signifie**
- Vous ne pouvez pas utiliser, copier, modifier ou distribuer ce logiciel sans autorisation ecrite
- L'utilisation commerciale est interdite sans licence appropriee
- Tous les droits sont reserves

**Pour obtenir une licence**

Contactez l'equipe de developpement pour discuter des options de licence:
- Licence pour utilisation personnelle
- Licence pour utilisation commerciale
- Licence pour contribution au projet

### Avertissement legal

Ce logiciel est fourni "tel quel", sans garantie d'aucune sorte, exprimee ou implicite. En aucun cas les auteurs ou detenteurs des droits ne pourront etre tenus responsables de tout dommage direct, indirect, incident, special, exemplaire ou consequentiel resultant de l'utilisation ou de l'impossibilite d'utiliser ce logiciel.

---

## Support

Pour obtenir de l'aide ou signaler un bug:

### Signaler un bug

1. Vérifiez que le bug n'est pas déjà signalé
2. Ouvrez une issue sur GitHub avec:
   - Une description claire du bug
   - Les étapes pour reproduire
   - La version de PHP et Symfony utilisee
   - Les logs d'erreur si applicable

### Demander une fonctionnalite

1. Vérifiez que la fonctionnalite n'est pas déjà en developpement
2. Ouvrez une issue avec:
   - Une description de la fonctionnalite
   - Le cas d'utilisation
   - Les avantages pour le projet

### Contacter l'equipe

Pour toute question ou commentaire:

- Email: contact@example.com (a remplacer par l'email reel)
- GitHub: [lien-vers-le-depot](https://github.com/votre-utilisateur/baseball_v1)

---

## Journal des modifications

### v1.0.0 (2026-10-11)

**Nouvelles fonctionnalites**
- Page d'accueil avec matchs en direct et classements
- Systeme de gestion d'articles
- Authentification utilisateur
- API REST avec JWT
- Integration de l'API Baseball (API-Sports)
- Systeme de chat en temps reel
- Documentation complete

**Ameliorations**
- Interface utilisateur moderne
- Systeme de cache pour les requetes API
- Gestion des erreurs amelioree
- Performances optimisees

**Corrections de bugs**
- Correction des problemes d'authentification
- Correction des erreurs d'affichage
- Correction des requetes API

---

## Remerciements

- L'equipe de developpement pour leur travail acharne
- Tous les contributeurs qui ont aide a ameliorer ce projet
- La communaute Symfony pour ses excellents outils
- API-Sports pour leurs donnees de baseball de qualite

---

## Annexes

### A. Configuration avancee

#### Configuration CORS

Fichier: `config/packages/nelmio_cors.yaml`

```yaml
nelmio_cors:
    defaults:
        allow_credentials: false
        allow_origin: ['*']
        allow_headers: ['*']
        allow_methods: ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS']
        max_age: 3600
```

#### Configuration du cache

Fichier: `config/packages/cache.yaml`

```yaml
framework:
    cache:
        app: cache.adapter.redis
        default_redis_provider: 'redis://localhost'
```

### B. Deploiement

#### Deploiement avec Docker

1. Configurez votre serveur avec Docker et Docker Compose
2. Clonez le projet
3. Configurez les variables d'environnement
4. Executez:

```bash
docker-compose up -d
```

#### Deploiement sans Docker

1. Configurez un serveur web (Apache, Nginx, Caddy)
2. Configurez PHP 8.4+
3. Installez les dependances Composer
4. Configurez la base de donnees
5. Executez les migrations

### C. Monitoring

#### Logs

- **Logs de l'application**: `var/log/dev.log`
- **Logs Docker**: `docker-compose logs`

#### Metriques

L'application expose des metriques via:
- Symfony Profiler (en developpement)
- API Platform metrics

---

## Index des fichiers importants

| Fichier | Description |
|---------|-------------|
| `README.md` | Documentation complete (ce fichier) |
| `composer.json` | Dependances PHP |
| `Dockerfile` | Configuration de l'image Docker |
| `docker-compose.yml` | Configuration des services Docker |
| `config/packages/security.yaml` | Configuration de la securite |
| `config/packages/api_platform.yaml` | Configuration de l'API Platform |
| `config/packages/doctrine.yaml` | Configuration de Doctrine |
| `src/Entity/` | Entites Doctrine |
| `src/Controller/` | Controlleurs |
| `src/Service/BaseballApiService.php` | Service de l'API Baseball |
| `src/Security/AppCustomAuthenticator.php` | Authentificateur personnalise |
| `templates/` | Templates Twig |
| `migrations/` | Migrations de la base de donnees |

---

Fin de la documentation
