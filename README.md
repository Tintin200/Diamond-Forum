# 💎 Diamond Forum

Un forum communautaire moderne développé avec **Symfony 8**, **Doctrine ORM**, **Twig** et **Docker**.

---

## 🛠️ Stack technique

| Technologie | Version |
|---|---|
| PHP | ≥ 8.4 |
| Symfony | 8.0.x |
| Doctrine ORM | ^3.6 |
| MariaDB | 11.1 |
| Twig | ^3.0 |
| PHPUnit | ^13.0 |
| Docker / Docker Compose | - |

---

## 📁 Structure du projet

```
Diamond-Forum/
├── assets/           # Fichiers front (JS, CSS)
├── bin/              # Binaires Symfony
├── config/           # Configuration de l'application
├── migrations/       # Migrations Doctrine
├── public/           # Point d'entrée web
├── src/              # Code source PHP (Controllers, Entities, etc.)
├── templates/        # Templates Twig
├── tests/            # Tests PHPUnit
├── translations/     # Fichiers de traduction
├── .env              # Variables d'environnement (base)
├── docker-compose.yml
└── Dockerfile
```

---

## 🚀 Installation

### Prérequis

- [Docker](https://www.docker.com/) & [Docker Compose](https://docs.docker.com/compose/)
- [Composer](https://getcomposer.org/)
- PHP ≥ 8.4

### Avec Docker (recommandé)

```bash
# 1. Cloner le repository
git clone https://github.com/Tintin200/Diamond-Forum.git
cd Diamond-Forum

# 2. Copier le fichier d'environnement
cp .env .env.local
# Modifier .env.local selon votre configuration

# 3. Lancer les conteneurs
docker compose up -d

# 4. Installer les dépendances PHP
docker compose exec app composer install

# 5. Exécuter les migrations
docker compose exec app php bin/console doctrine:migrations:migrate

# 6. Installer les assets
docker compose exec app php bin/console importmap:install
```

L'application est disponible sur **http://localhost:8000**.

### Sans Docker

```bash
# 1. Installer les dépendances
composer install

# 2. Configurer la base de données dans .env.local
DATABASE_URL="mysql://user:password@127.0.0.1:3306/diamond?serverVersion=10.11.2-MariaDB&charset=utf8mb4"

# 3. Créer la base et exécuter les migrations
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate

# 4. Lancer le serveur de développement
php bin/console server:start
```

---

## 🐳 Services Docker

| Service | URL | Description |
|---|---|---|
| Application | http://localhost:8000 | Application Symfony |
| phpMyAdmin | http://localhost:8080 | Interface base de données |
| Mailpit | http://localhost:8025 | Capture des emails (dev) |

---

## ⚙️ Variables d'environnement

Les variables clés à configurer dans `.env.local` :

```env
APP_ENV=dev
APP_SECRET=votre_secret_ici

DATABASE_URL="mysql://user:password@127.0.0.1:3306/diamond?serverVersion=10.11.2-MariaDB&charset=utf8mb4"

MAILER_DSN=smtp://localhost:1025

MESSENGER_TRANSPORT_DSN=doctrine://default?auto_setup=0
```

> ⚠️ Ne jamais commiter de secrets en production dans les fichiers `.env`. Utiliser [`symfony secrets`](https://symfony.com/doc/current/configuration/secrets.html).

---

## 🧪 Tests

```bash
# Lancer tous les tests
php bin/phpunit

# Avec Docker
docker compose exec app php bin/phpunit
```

Les tests utilisent PHPUnit 13 avec le Browser Kit Symfony pour les tests fonctionnels.

---

## 📦 Commandes utiles

```bash
# Vider le cache
php bin/console cache:clear

# Créer une migration après modification d'une entité
php bin/console doctrine:migrations:diff

# Lancer les migrations
php bin/console doctrine:migrations:migrate

# Lister toutes les routes
php bin/console debug:router
```

---

## 🌍 Internationalisation

Le projet inclut un support de traduction via `symfony/translation`. Les fichiers de traduction se trouvent dans le dossier `translations/`.

---

## 📬 Emails

En développement, les emails sont capturés par **[Mailpit](https://github.com/axllent/mailpit)** et consultables sur `http://localhost:8025`. Aucun email n'est envoyé réellement.

---

## 🤝 Contribution

1. Forker le projet
2. Créer une branche (`git checkout -b feature/ma-feature`)
3. Commiter vos changements (`git commit -m 'feat: ajout de ma feature'`)
4. Pousser la branche (`git push origin feature/ma-feature`)
5. Ouvrir une Pull Request

---

## 📄 Licence

Ce projet est sous licence propriétaire.
