# CoVoie - Jeu d'essai

Tests réalisés sur le jeu de données de `sql/bdd_covoie.sql` (mot de passe de tous les comptes : `Password123!`).
Pour rejouer les tests : réimporter la base, puis suivre les lignes dans l'ordre.

Chaque test indique **ce qu'on fait** et **ce qu'on doit obtenir**. Les tests marqués *(forcé)* contournent
l'interface (requête envoyée à la main, ex : avec l'onglet Réseau du navigateur) pour vérifier que
le **serveur** refuse aussi, pas seulement le bouton caché.

Trajets utilisés :

| N° | Trajet | Conducteur | État au départ |
|----|--------|------------|----------------|
| 24 | Clamart -> Campus Paris 15e | Sarah Benjelloun | Complet (0 place) |
| 26 | Campus Paris 15e -> Boulogne Pont de Sèvres | Sofia Nguyen | Ouvert, 1 place |
| 38 | Issy Val de Seine -> Versailles Chantiers | Sarah Benjelloun | Ouvert, 1 place, 2 demandes en attente |
| 43 | Campus Paris 15e -> Boulogne Jean Jaurès | Sarah Benjelloun | Parti hier (Emma confirmée, Hugo en attente) |


## 1. Visiteur et sécurité d'accès

| N° | Cas testé | Action | Résultat attendu | Obtenu |
|----|-----------|--------|------------------|--------|
| 1 | Recherche filtrée | Rechercher, départ « Boulogne » | Seuls les trajets ouverts, à venir, avec place, partant de Boulogne | OK |
| 2 | Page inconnue | `index.php?page=nimporte` | Page « Erreur 404 » | OK |
| 3 | Trajet inexistant | `index.php?page=trajet&id=9999` | Page « Erreur 404 » | OK |
| 4 | Page réservée aux membres | Visiteur ouvre « Mes trajets » | Redirection vers la connexion | OK |
| 5 | Action sans formulaire | Membre ouvre `index.php?page=reserver&id=26` dans la barre d'adresse | Erreur 405 : l'action doit venir d'un formulaire | OK |
| 6 | Jeton CSRF faux *(forcé)* | Envoyer le formulaire de réservation avec `csrf=faux` | Erreur 403, rien n'est enregistré | OK |
| 7 | Back-office interdit | Un membre ouvre `index.php?page=admin` | Erreur 403 | OK |


## 2. Comptes (inscription, connexion)

| N° | Cas testé | Action | Résultat attendu | Obtenu |
|----|-----------|--------|------------------|--------|
| 8 | Confirmation différente | Inscription avec deux mots de passe différents | Message sous le champ « Confirmation » | OK |
| 9 | Inscription correcte | Nom « Test Jury », `jury@campus.fr`, `Password123!` | « Compte créé », retour à la connexion | OK |
| 10 | Email déjà pris | Refaire l'inscription avec `jury@campus.fr` | « Cette adresse email est déjà utilisée » | OK |
| 11 | Mot de passe chiffré | Regarder la table `utilisateurs` dans phpMyAdmin | La colonne `mot_de_passe` commence par `$2y$` (password_hash) | OK |
| 12 | Connexion | Se connecter avec `jury@campus.fr` | « Bonjour Test Jury ! » | OK |
| 13 | Mauvais mot de passe | Se connecter avec un faux mot de passe | « Email ou mot de passe incorrect », réponse après 1 seconde | OK |


## 3. Module A : trajets (conducteur)

| N° | Cas testé | Action | Résultat attendu | Obtenu |
|----|-----------|--------|------------------|--------|
| 14 | Départ = arrivée, date passée | Publier « Campus Paris 15e » -> « campus paris 15e », le 01/01/2020 | 2 messages sous les champs : lieux différents, date dans le futur | OK |
| 15 | Publication | Publier un trajet dans 3 jours, 2 places, 2,50 € | « Votre trajet est publié », 2 places libres, statut Ouvert | OK |
| 16 | Modification | Modifier ce trajet : 4 places | 4 places au total, 4 libres | OK |
| 17 | Trajet d'un autre | `index.php?page=modifier&id=26` (trajet de Sofia) | Erreur 403 | OK |
| 18 | Suppression | Supprimer le trajet, confirmer dans la boîte | « Trajet supprimé », il n'est plus en base | OK |
| 19 | Clôture automatique | Ouvrir n'importe quelle page, puis le trajet 43 | Statut « Terminé », la demande de Hugo passe « Refusée » | OK |


## 4. Module B : réservations

| N° | Cas testé | Action | Résultat attendu | Obtenu |
|----|-----------|--------|------------------|--------|
| 20 | Demander une place | Test Jury, trajet 26, « Demander une place » | « Demande envoyée », statut « En attente » | OK |
| 21 | Deux fois le même trajet *(forcé)* | Renvoyer la demande sur le trajet 26 | Refusé : toujours UNE seule demande en base | OK |
| 22 | Annuler sur la boîte de dialogue | Mes réservations, « Annuler », puis « Annuler » dans la boîte | Rien n'est envoyé, la demande reste | OK |
| 23 | Annuler sa demande | Même chose, puis « Confirmer » | « Demande annulée » | OK |
| 24 | Trajet complet | Ouvrir le trajet 24 | « Réservation impossible », pas de bouton | OK |
| 25 | Trajet complet *(forcé)* | Envoyer quand même la demande sur le 24 | Aucune réservation créée | OK |
| 26 | Son propre trajet | Sarah ouvre un de ses trajets | Pas de bouton « Demander une place », seulement « Gérer mon trajet » | OK |
| 27 | Accepter (décrément) | Sarah accepte une demande sur le trajet 38 | « Demande acceptée », 0 place, statut « Complet » | OK |
| 28 | Accepter sans place *(forcé)* | Accepter la 2e demande du trajet 38 | Refusé, la demande reste « En attente », places jamais sous 0 | OK |
| 29 | Refuser | Sarah refuse la 2e demande du trajet 38 | Statut « Refusée » | OK |


## 5. Module C : avis et note moyenne

| N° | Cas testé | Action | Résultat attendu | Obtenu |
|----|-----------|--------|------------------|--------|
| 30 | Noter un passager | Sarah, trajet 43, note 4 à Emma avec le commentaire `Passagère ponctuelle <b>test</b>` | « Avis enregistré », Emma affichée « Déjà noté » | OK |
| 31 | Deux avis pour la même personne *(forcé)* | Renvoyer une note pour Emma sur le trajet 43 | Refusé : un seul avis en base | OK |
| 32 | Noter un passager refusé *(forcé)* | Sarah note Hugo (demande refusée) sur le trajet 43 | Refusé : aucun avis créé | OK |
| 33 | Code HTML dans un commentaire | Ouvrir le profil d'Emma | Le texte `<b>test</b>` s'affiche tel quel, sans mettre en gras (pas de faille XSS) | OK |
| 34 | Le passager note le conducteur | Emma, Mes avis, « À noter », noter Sarah | « Avis enregistré » | OK |
| 35 | Non-participant | Hugo ouvre `index.php?page=noter&id=43` | Erreur 403 | OK |
| 36 | Note moyenne | Profil de Bilel Allali | « ★ 4,75 » (moyenne de ses avis reçus) | OK |
| 37 | Membre sans avis | Profil de Hugo Petit | « Pas encore noté » (Avis::noteMoyenne renvoie null) | OK |


## 6. Administration

| N° | Cas testé | Action | Résultat attendu | Obtenu |
|----|-----------|--------|------------------|--------|
| 38 | Pages admin | Ilyès ouvre les 4 onglets du back-office | Les 4 pages s'affichent | OK |
| 39 | Modération d'un avis | Supprimer un avis reçu par Camille | L'avis disparaît, la moyenne de Camille change | OK |
| 40 | Supprimer un passager confirmé | Supprimer le compte de Lucas Moreau (passager du trajet 24) | Le trajet 24 récupère 1 place et redevient « Ouvert » | OK |
| 41 | Suppression en cascade | Même action | Les trajets, réservations et avis de Lucas sont supprimés | OK |
| 42 | L'admin ne se supprime pas *(forcé)* | Ilyès envoie la suppression de son propre compte | Refusé, le compte existe toujours | OK |
| 43 | Déconnexion | Bouton « Déconnexion » | « Vous êtes déconnecté » | OK |


## 7. Affichage

| N° | Cas testé | Action | Résultat attendu | Obtenu |
|----|-----------|--------|------------------|--------|
| 44 | Téléphone | Ouvrir le site en mode mobile (F12, iPhone) | Pas de défilement horizontal, barre d'onglets en bas une fois connecté | OK |
| 45 | Tablette et petit écran | Largeurs 768 px et 1024 px | Pas de débordement, le menu tient sur une ligne | OK |
