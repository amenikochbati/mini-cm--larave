# Mini CMS Laravel

## Présentation

Mini CMS est un projet web développé avec Laravel dans le cadre d'un atelier de développement web. Il permet de découvrir les routes Laravel, les vues Blade et les bases de la gestion de versions avec Git et GitHub.

## Fonctionnalités

- Page d'accueil.
- Pages de démonstration : `/bonjour`, `/bonjour-court`, `/bienvenue`, `/heure` et `/version`.
- Page À propos : `/a-propos`.

## Prérequis

Installer les outils suivants :

- PHP
- Composer
- Node.js et npm
- Git

## Installation sous Windows (PowerShell)

Ouvrir PowerShell dans le dossier où vous souhaitez installer le projet, puis exécuter :

```powershell
git clone https://github.com/amenikochbati/mini-cm--larave.git
cd mini-cm--larave
Copy-Item .env.example .env
composer install
npm install
php artisan key:generate
```

Configurer les variables nécessaires dans `.env` si le projet utilise une base de données.

Démarrer le serveur Laravel :

```powershell
php artisan serve
```

Si les ressources front-end sont nécessaires, ouvrir un deuxième terminal dans le dossier du projet et exécuter :

```powershell
npm run dev
```

## Installation sous Linux ou macOS (Bash)

```bash
git clone https://github.com/amenikochbati/mini-cm--larave.git
cd mini-cm--larave
cp .env.example .env
composer install
npm install
php artisan key:generate
```

Configurer les variables nécessaires dans `.env` si le projet utilise une base de données.

Démarrer le serveur Laravel :

```bash
php artisan serve
```

Si les ressources front-end sont nécessaires, ouvrir un deuxième terminal et exécuter :

```bash
npm run dev
```

## Accès aux pages

Après le démarrage du serveur, ouvrir :

- Accueil : http://127.0.0.1:8000/
- Bienvenue : http://127.0.0.1:8000/bienvenue
- À propos : http://127.0.0.1:8000/a-propos

## Captures d'écran

Les captures d'écran disponibles sont :

- Accueil : `screenshots/s01-accueil.png`
- Bienvenue : `screenshots/s01-bienvenue.png`
- Liste des routes : `screenshots/s01-route-list.png`

## Git et GitHub

La branche principale est `main`.

Tags du TP :
- `lab-01` : état initial.
- `lab-01b` : état final du livrable, à créer après les dernières modifications et vérifications.

## Fichiers ignorés

Le fichier `.env`, les dossiers `vendor/` et `node_modules/`, ainsi que les bases SQLite locales, sont exclus du suivi Git lorsqu'ils correspondent aux règles de `.gitignore`.

Ces éléments ne doivent pas être publiés, car ils peuvent contenir des informations sensibles, des dépendances installées ou des données locales.

## Auteur

Ameni Kochbati