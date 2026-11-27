# CoVoie - Covoiturage entre étudiants du campus

Application web PHP / MySQL en architecture **MVC**, réalisée pour le BTS SIO option SLAM.
Un étudiant publie ses trajets vers le campus (Paris 15e), les autres demandent une place,
puis chacun note l'autre après le trajet.

| Module | Rôle | Contrôleur |
|---|---|---|
| **A** - Gestion des trajets | Le conducteur publie, modifie, supprime ses trajets et accepte / refuse les demandes | `TrajetController` |
| **B** - Recherche et réservation | Le passager cherche un trajet, demande une place, suit ou annule ses demandes | `ReservationController` |
| **C** - Avis et réputation | Après un trajet terminé, conducteur et passagers se notent de 1 à 5 | `AvisController` |
| Back-office | L'administrateur modère les avis, gère les comptes, consulte les statistiques | `AdminController` |

---

## Installation avec WAMP

1. Copier le dossier `covoie` dans `C:\wamp64\www\`.
2. Démarrer WAMP (icône verte).
3. Ouvrir phpMyAdmin → onglet **Importer** → choisir `sql/bdd_covoie.sql` → **Exécuter**.
   Le script crée lui-même la base `bdd_covoie`.
   *Si une ancienne base `bdd_covoie` existe déjà, la supprimer d'abord (phpMyAdmin → base → Opérations → Supprimer la base).*
4. Ouvrir **http://localhost/covoie/**

Réglages de connexion dans `includes/config.php` (par défaut : `root` sans mot de passe, comme WAMP).
Aucune dépendance à installer : PHP 8 et l'extension PDO MySQL suffisent. La police Roboto est
enregistrée dans `fonts/`, le site fonctionne donc **sans connexion Internet**.

---

## Comptes de démonstration

Mot de passe de **tous** les comptes : `Password123!`

| Compte | Ce qu'il permet de montrer |
|---|---|
| `ilyes.karim@campus.fr` | **Administrateur** : tableau de bord, modération des avis, comptes, trajets |
| `sarah.benjelloun@campus.fr` | **Module A** : 2 demandes pour la dernière place du trajet Issy → Versailles (en accepter une le fait passer « Complet ») ; son trajet d'hier est **clôturé automatiquement**, elle peut noter Emma |
| `emma.girard@campus.fr` | **Module B** : réservations dans les 3 états (En attente, Confirmée, Refusée) |
| `bilel.mansouri@campus.fr` | **Module C** : profil noté 4,75 / 5 |
| `camille.rousset@campus.fr` | Profil noté 3,25 / 5 (pour comparer) |
| `hugo.petit@campus.fr` | Aucun avis : affiche « Pas encore noté » (et non 0) |

---

## Organisation du code

```
covoie/
├── index.php            Contrôleur frontal : TOUTES les pages passent par lui
├── includes/
│   ├── config.php       Nom du site, fuseau horaire, identifiants MySQL, session
│   ├── connexion.php    connexionBdd() et requete() : requêtes préparées PDO
│   ├── fonctions.php    Session, contrôle d'accès, CSRF, messages, affichage
│   └── routes.php       Tableau  page => [Contrôleur, méthode]
├── modeles/             Classes métier : SQL + règles de gestion
├── controleurs/         Font le lien entre l'URL, les modèles et les vues
├── vues/                HTML (un dossier par thème + gabarit commun)
├── css/style.css        Toute la mise en forme (variables de couleur en haut)
├── js/script.js         Menu mobile, confirmation, inverser départ/arrivée
├── images/  fonts/      Logo, illustration d'accueil, police Roboto
├── sql/bdd_covoie.sql  Structure de la base + jeu de démonstration
├── docs/                MCD, MLD, diagramme de classes, conception
└── gantt/               Planning du projet (GanttProject)
```

**Parcours d'une requête** - `index.php?page=trajet&id=12` :

1. `index.php` charge les fichiers puis lit `page=trajet` ;
2. `routes.php` indique `['TrajetController', 'detail']` ;
3. `TrajetController::detail()` appelle le modèle `Trajet::trouver(12)` ;
4. le contrôleur transmet les données à la vue `vues/trajets/detail.php` avec `afficherVue()`.

---

## Règles métier principales

- **Une seule demande** par passager et par trajet (vérifié en PHP **et** par la clé `UNIQUE une_seule_demande`).
- Une demande reste *En attente* sans prendre de place. C'est l'**acceptation** qui appelle
  `Trajet::reserverPlace()` : la place est retirée et, à zéro, le trajet passe *Complet*.
  Les deux mises à jour sont faites dans une **transaction** (`beginTransaction` / `commit` / `rollBack`).
- On ne réserve pas son propre trajet, ni un trajet complet, parti ou terminé.
- Le conducteur ne peut pas descendre le nombre de places sous les places déjà réservées.
- La **clôture est automatique** : au début de chaque page, `Trajet::cloturerTrajetsPasses()` passe
  en *Terminé* les trajets dont l'heure de départ est passée et refuse leurs demandes encore en
  attente. La notation devient alors possible.
- Le conducteur note chacun de ses passagers acceptés, chaque passager note le conducteur,
  **une seule fois** par trajet (clé `UNIQUE un_seul_avis`).
- La note moyenne n'est **pas stockée** : `Avis::noteMoyenne()` la calcule avec `AVG(note)`.
  Sans avis, elle vaut `null` et le site affiche « Pas encore noté ».
- La suppression d'un compte par l'admin rend d'abord les places qu'il occupait, puis la
  suppression en cascade (`ON DELETE CASCADE`) efface ses trajets, réservations et avis.

## Sécurité

| Risque | Protection | Où ? |
|---|---|---|
| Injection SQL | Requêtes préparées (`prepare` + `execute`) | `requete()` dans `connexion.php` |
| Vol de mot de passe | `password_hash()` / `password_verify()` | `Utilisateur` |
| Faille XSS | `htmlspecialchars()` sur tout ce qui est affiché | fonction `e()` |
| Faille CSRF | Jeton secret dans chaque formulaire POST | `champCsrf()`, `verifierFormulaire()` |
| Accès non autorisé | Vérification à chaque action | `proteger()`, `protegerAdmin()`, contrôle du conducteur |
| Vol de session | `session_regenerate_id()` à la connexion, cookie `httponly` | `AuthController`, `config.php` |

## Documentation

- **[Documentation technique (PDF)](docs/documentation.pdf)** : MLD, diagramme de classes, décrément des places, clôture, note moyenne
- [MCD](docs/mcd.png) - [MLD](docs/mld.png) - [Diagramme de classes](docs/diagramme-classes.png)
- [Conception détaillée](docs/conception.md) - [Arborescence](docs/arborescence.txt)
