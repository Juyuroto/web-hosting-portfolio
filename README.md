# Portfolio - Documentation du Code

## Résumé du Projet

Application web Portfolio développée en PHP avec une architecture MVC (Model-View-Controller), conteneurisée avec Docker. Le site utilise Apache comme serveur web et MySQL pour la base de données. L'application permet d'afficher un portfolio personnel, une page "À propos" et un formulaire de contact.

## Sommaire

- [Portfolio - Documentation du Code](#portfolio---documentation-du-code)
  - [Résumé du Projet](#résumé-du-projet)
  - [Sommaire](#sommaire)
  - [Page](#page)
  - [Structure du Projet](#structure-du-projet)
  - [Description des Dossiers](#description-des-dossiers)
    - [`/docker`](#docker)
    - [`/public`](#public)
    - [`/src`](#src)
      - [`/src/config`](#srcconfig)
      - [`/src/controllers`](#srccontrollers)
      - [`/src/models`](#srcmodels)
      - [`/src/views`](#srcviews)
  - [Fichiers de Configuration](#fichiers-de-configuration)
  - [Architecture MVC](#architecture-mvc)
  - [Installation](#installation)
  - [Commandes Utiles](#commandes-utiles)
  - [Technologies](#technologies)

## Page

- [1. Configuration général](config/Infra.md)
- [2. Configuration routeur OPNsense](config/OPNsense.md)
- [3. Configuration PC Admin](config/PC_Admin.md)
- [4. Configuration Switch](config/Switch.md)

## Structure du Projet

```
mon-projet/
├── docker/
│   ├── apache/
│   │   └── vhost.conf
│   └── php/
│       └── dockerfile
│
├── public/
│   ├── index.php
│   ├── .htaccess
│   ├── cv.pdf
│   └── assets/
│       ├── css/
│       │   ├── me.css
│       │   └── styles.css
│       ├── js/
│       │   ├── me.js
│       │   └── script.js
│       └── photo/
│           └── icons/
│
├── src/
│   ├── config/
│   │   └── database.php
│   ├── controllers/
│   │   ├── HomeController.php
│   │   └── ContactController.php
│   ├── models/
│   │   └── Contact.php
│   └── views/
│       ├── layouts/
│       │   ├── header.php
│       │   ├── main.php
│       │   └── footer.php
│       ├── home.php
│       └── me.php
│
├── .env
├── .gitignore
├── composer.json
├── docker-compose.yml
├── init.sql
└── README.md
```

## Description des Dossiers

### `/docker`
Configuration Docker pour le projet.
- `apache/vhost.conf` : Configuration du VirtualHost Apache
- `php/dockerfile` : Image Docker PHP avec les extensions nécessaires

### `/public`
Point d'entrée public de l'application.
- `index.php` : Contrôleur frontal qui gère le routage
- `.htaccess` : Règles de réécriture Apache
- `cv.pdf` : Fichier CV
- `assets/` : Ressources CSS, JavaScript et images

### `/src`
Code source de l'application.

#### `/src/config`
- `database.php` : Configuration de la connexion à la base de données

#### `/src/controllers`
- `HomeController.php` : Gère la page d'accueil
- `ContactController.php` : Gère le formulaire de contact

#### `/src/models`
- `Contact.php` : Modèle pour gérer les données de contact

#### `/src/views`
- `layouts/` : Composants réutilisables (header, main, footer)
- `home.php` : Page d'accueil
- `me.php` : Page "À propos"

## Fichiers de Configuration

- `.env` : Variables d'environnement (ne pas committer)
- `.gitignore` : Fichiers exclus de Git
- `composer.json` : Dépendances PHP
- `docker-compose.yml` : Configuration des conteneurs Docker
- `init.sql` : Script d'initialisation de la base de données

## Architecture MVC

1. Requête HTTP → `public/index.php`
2. Routage → Appel du contrôleur approprié
3. Contrôleur → Traite la logique et appelle le modèle
4. Modèle → Interaction avec la base de données
5. Vue → Affichage du résultat

## Installation

```bash
# Cloner le projet
git clone [<url>](https://github.com/Juyuroto/web-hosting-portfolio.git)
cd web-hosting-portfolio

# Configurer l'environnement
cp .env.example .env

# Installer les dépendances
docker-compose run --rm php composer install

# Démarrer les conteneurs
docker-compose up -d
```

## Commandes Utiles

```bash
# Démarrer
docker-compose up -d

# Arrêter
docker-compose down

# Logs
docker-compose logs -f

# Accéder au conteneur PHP
docker-compose exec php bash
```

## Technologies

- PHP 8.x
- Apache
- MySQL
- Docker
- Composer