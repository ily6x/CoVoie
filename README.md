# CoVoiturage Campus

Application PHP orientée objet / MySQL en MVC, réalisée à partir du sujet fourni. Les trois modules sont utilisables : conducteur, passager, avis et administration.

## Démarrer avec Wamp

1. Démarrer Apache et MySQL dans Wamp.
2. Pour une **nouvelle installation**, importer `sql/covoiturage.sql` dans phpMyAdmin. Le script crée `covoiturage` et refuse de remplacer une base existante : il ne supprime aucune base.
3. Ouvrir http://localhost/covoie/index.php.

La base a été initialisée et testée sur ce poste. Configuration par défaut : MySQL `127.0.0.1:3306`, base `covoiturage`, utilisateur `root`, mot de passe vide. Pour une autre installation, définir `COVOIE_DSN`, `COVOIE_DB_USER` et `COVOIE_DB_PASSWORD` dans l’environnement du serveur. PHP 8.1 minimum, extensions PDO MySQL et mbstring ; SQLite pour les tests en mémoire, cURL pour les tests HTTP.

### Comptes de démonstration

Mot de passe de tous les comptes : **Password123!**

| Email | Démonstration |
|---|---|
| arkam.benali@campus.fr | Administration et conducteur |
| sarah.benjelloun@campus.fr | Demandes de réservation à traiter |
| emma.girard@campus.fr | Historique des réservations |
| bilel.mansouri@campus.fr | Profil noté 4,75/5 |
| camille.rousset@campus.fr | Profil noté 3,25/5 |
| hugo.petit@campus.fr | Profil sans note |

### Parcours de démonstration

Rechercher un trajet en visiteur. Se connecter avec Sarah, ouvrir Mes trajets et traiter une demande sur un trajet ouvert. Le compteur diminue seulement lors de l’acceptation. Avec Emma, suivre les trois états des demandes. Pour montrer la clôture, publier un trajet futur avec des participants confirmés, puis attendre son départ : le conducteur peut le clôturer depuis sa fiche. Les trajets déjà terminés du jeu de données permettent immédiatement de démontrer la notation. Avec Arkam, modérer les avis et consulter les statistiques.

## Architecture et règles

`index.php` charge le socle, contrôle la méthode HTTP et le jeton CSRF, puis consulte la table de routes. Les contrôleurs valident l’identité et préparent les données des vues. Les classes portent les règles métier et utilisent la connexion PDO commune.

- Une demande reste **En attente** sans consommer de place. L’acceptation verrouille le trajet dans une transaction, décrémente le compteur et confirme la demande. À zéro place, le trajet devient **Complet**.
- La modification de capacité conserve le nombre de places déjà confirmées.
- La clôture est **manuelle après le départ**. Elle refuse les demandes en attente restantes, puis débloque la notation.
- Un avis est autorisé entre le conducteur et chaque passager confirmé, une seule fois par paire et par trajet. Les passagers ne se notent pas entre eux. Ce choix permet au conducteur de noter chacun de ses passagers, comme demandé dans le module C.
- La moyenne est calculée par `AVG(note)` à chaque consultation ; sans avis, elle vaut `NULL` et l’interface affiche « Pas encore noté ».
- La suppression d’un passager restitue les places confirmées avant la suppression en cascade.
- Les pages personnelles sont filtrées sur l’identité de session ; la fiche publique ne révèle pas les demandes des autres participants. La modération et la gestion des comptes exigent le rôle admin.
- Les écritures nécessitent POST et un jeton CSRF. Les mots de passe sont hachés et les requêtes préparées ; les contenus saisis sont échappés en HTML.

Documentation : [MCD](docs/mcd.png), [MLD](docs/mld.png), [diagramme de classes](docs/diagramme-classes.png), [conception détaillée](docs/conception.md). Le planning prévisionnel est dans `gantt/covoiturage.gan`.

## Vérifications

```powershell
php tests/metier.php
php tests/mysql.php
php tests/http.php
```

`metier.php` teste les règles dans une base SQLite en mémoire. `mysql.php` exécute les mêmes contrôles dans une base MySQL temporaire au nom aléatoire, puis supprime uniquement cette base de test. `http.php` teste les pages, les sessions et les autorisations sur le site local avec les comptes de démonstration. Les tests HTTP ne suppriment aucune donnée de démonstration.

Sur ce poste, la configuration CLI de Wamp référence une extension Xdebug dans un ancien chemin `E:`. Pour tester sans cette configuration système :

```powershell
php -n -d extension_dir=C:\wamp64\bin\php\php8.4.0\ext -d extension=php_pdo_sqlite.dll -d extension=php_mbstring.dll tests/metier.php
php -n -d extension_dir=C:\wamp64\bin\php\php8.4.0\ext -d extension=php_pdo_mysql.dll -d extension=php_mbstring.dll tests/mysql.php
php -n -d extension_dir=C:\wamp64\bin\php\php8.4.0\ext -d extension=php_curl.dll tests/http.php
```

## Livrable Git

Aucun dépôt Git n’existait dans le dossier fourni. L’historique de commits par étudiant demandé dans le sujet doit être constitué par les trois étudiants : les contributions ne sont pas inventées. Le code et la documentation sont prêts à être ajoutés à leur dépôt.
