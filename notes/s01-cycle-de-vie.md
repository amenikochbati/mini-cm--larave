Le navigateur envoie une requête GET /heure.
La requête entre dans Laravel par public/index.php.
Ce fichier charge vendor/autoload.php puis bootstrap/app.php.
bootstrap/app.php configure l'application et charge routes/web.php.
Le routeur trouve la route /heure dans routes/web.php.
La closure exécute la vue resources/views/heure.blade.php.
Blade génère le HTML et Laravel renvoie la réponse au navigateur.