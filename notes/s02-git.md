# Session 02 - Git et GitHub

## 1. Index et commit

La commande `git add` prepare les modifications. La commande `git commit` enregistre les modifications dans l'historique local du depot.

## 2. Branche et tag

Une branche, comme `main`, avance avec les nouveaux commits. Un tag, comme `lab-01b`, designe un commit precis.

## 3. Le fichier .env

Le fichier `.env` peut contenir des informations sensibles, comme des mots de passe et la cle de l'application. Il ne faut pas le publier sur GitHub. Un autre developpeur copie `.env.example` vers `.env`, puis execute `php artisan key:generate`.

## 4. Le dossier vendor/

Le dossier `vendor/` contient les bibliotheques PHP installees par Composer. On peut le recreer avec `composer install`, grace au fichier `composer.lock`.

## 5. Git Credential Manager

Git Credential Manager gere les identifiants d'authentification dans Windows. Sur un ordinateur partage, il faut supprimer ses identifiants a la fin de la seance et se deconnecter de GitHub dans le navigateur.

## 6. Historique depuis lab-01

Nombre de commits depuis lab-01 : 7 avant ce commit.

Fichiers modifies depuis lab-01 : README.md, notes/s01-cycle-de-vie.md, notes/s02-git.md, resources/views/a-propos.blade.php, routes/web.php et screenshots/s02-a-propos.png.

Ce que `git tag -n` affiche pour lab-01 : `lab-01 lab-01`.
