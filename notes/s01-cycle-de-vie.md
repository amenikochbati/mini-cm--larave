# Session 02 - Git et GitHub

## 1. Index et commit

**Réponse :**

La commande `git add` permet de placer les modifications dans l’index (staging). La commande `git commit` enregistre ces modifications dans l’historique local du dépôt.

## 2. Branche et tag

**Réponse :**

Une branche, comme `main`, avance avec les nouveaux commits. Un tag, comme `lab-01b`, désigne un commit précis et ne se déplace pas automatiquement.

## 3. Le fichier .env

**Réponse :**

Le fichier `.env` contient des informations sensibles, comme des mots de passe et la clé de l’application. Il ne faut pas le publier sur GitHub. Un autre développeur copie `.env.example` vers `.env`, puis exécute `php artisan key:generate`.

## 4. Le dossier vendor/

**Réponse :**

Le dossier `vendor/` contient les bibliothèques PHP installées par Composer. Il n’est pas nécessaire de le publier, car on peut le recréer avec la commande `composer install`, grâce au fichier `composer.lock`.

## 5. Git Credential Manager

**Réponse :**

Git Credential Manager stocke les identifiants d’authentification dans le Gestionnaire d’identifiants Windows, notamment pour GitHub. Sur un ordinateur personnel, je peux les conserver. Sur un ordinateur partagé, je dois les supprimer à la fin de la séance et me déconnecter de GitHub dans le navigateur.