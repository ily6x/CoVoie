-- ======================================================================
--  CoVoie - Covoiturage entre étudiants du campus
--  Base de données complète (structure + jeu de démonstration)
--  Projet BTS SIO option SLAM
--
--  Le campus est situé dans le 15e arrondissement de Paris : les trajets
--  vont vers Boulogne-Billancourt, la petite couronne, les aéroports
--  et quelques villes de province.
--
--  UTILISATION (WAMP) :
--    phpMyAdmin -> onglet "Importer" -> choisir ce fichier -> Exécuter
--    (le script crée lui-même la base "covoiturage")
--
--  Les dates sont calculées à partir du jour de l'import : le jeu de
--  données reste cohérent quel que soit le jour de la démonstration.
-- ======================================================================

CREATE DATABASE covoiturage DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE covoiturage;


-- ======================================================================
--  TABLE 1 : utilisateurs
--  Un seul compte par étudiant. Pas de table "conducteur" ni "passager" :
--  on est conducteur sur le trajet qu'on publie et passager sur celui
--  qu'on réserve. La colonne "role" ne sert qu'à repérer l'administrateur.
-- ======================================================================
CREATE TABLE utilisateurs (
    id               INT(11)      NOT NULL AUTO_INCREMENT,
    nom              VARCHAR(100) NOT NULL,
    email            VARCHAR(180) NOT NULL,
    mot_de_passe     VARCHAR(255) NOT NULL,
    role             VARCHAR(10)  NOT NULL DEFAULT 'membre',
    date_inscription DATETIME     NOT NULL,

    PRIMARY KEY (id),
    UNIQUE KEY email_unique (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ======================================================================
--  TABLE 2 : trajets
--  statut : 'Ouvert' -> 'Complet' -> 'Termine'
--  (valeurs techniques sans accent ; le site affiche "Terminé")
--  places_restantes baisse de 1 à chaque réservation ACCEPTÉE.
-- ======================================================================
CREATE TABLE trajets (
    id               INT(11)      NOT NULL AUTO_INCREMENT,
    origine          VARCHAR(120) NOT NULL,
    destination      VARCHAR(120) NOT NULL,
    date_heure       DATETIME     NOT NULL,
    places_totales   INT(2)       NOT NULL,
    places_restantes INT(2)       NOT NULL,
    statut           VARCHAR(10)  NOT NULL DEFAULT 'Ouvert',
    prix             DECIMAL(6,2) NOT NULL DEFAULT 0.00,
    id_conducteur    INT(11)      NOT NULL,

    PRIMARY KEY (id),
    KEY recherche (statut, date_heure),

    CONSTRAINT fk_trajet_conducteur
        FOREIGN KEY (id_conducteur) REFERENCES utilisateurs (id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ======================================================================
--  TABLE 3 : reservations
--  statut : 'En attente' -> 'Confirmee' ou 'Refusee'
-- ======================================================================
CREATE TABLE reservations (
    id               INT(11)     NOT NULL AUTO_INCREMENT,
    statut           VARCHAR(12) NOT NULL DEFAULT 'En attente',
    date_reservation DATETIME    NOT NULL,
    id_trajet        INT(11)     NOT NULL,
    id_passager      INT(11)     NOT NULL,

    PRIMARY KEY (id),

    -- RÈGLE MÉTIER : un passager ne peut pas réserver deux fois le même trajet
    UNIQUE KEY une_seule_demande (id_trajet, id_passager),

    CONSTRAINT fk_reservation_trajet
        FOREIGN KEY (id_trajet) REFERENCES trajets (id) ON DELETE CASCADE,
    CONSTRAINT fk_reservation_passager
        FOREIGN KEY (id_passager) REFERENCES utilisateurs (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ======================================================================
--  TABLE 4 : avis
--  Un avis relie un AUTEUR (qui note), une CIBLE (qui est notée) et un TRAJET.
-- ======================================================================
CREATE TABLE avis (
    id          INT(11)  NOT NULL AUTO_INCREMENT,
    note        INT(1)   NOT NULL,
    commentaire TEXT     NULL,
    date_avis   DATETIME NOT NULL,
    id_trajet   INT(11)  NOT NULL,
    id_auteur   INT(11)  NOT NULL,
    id_cible    INT(11)  NOT NULL,

    PRIMARY KEY (id),

    -- RÈGLE MÉTIER : une seule note par trajet et par personne notée
    UNIQUE KEY un_seul_avis (id_trajet, id_auteur, id_cible),
    KEY recherche_cible (id_cible),

    CONSTRAINT fk_avis_trajet
        FOREIGN KEY (id_trajet) REFERENCES trajets (id) ON DELETE CASCADE,
    CONSTRAINT fk_avis_auteur
        FOREIGN KEY (id_auteur) REFERENCES utilisateurs (id) ON DELETE CASCADE,
    CONSTRAINT fk_avis_cible
        FOREIGN KEY (id_cible) REFERENCES utilisateurs (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ======================================================================
--  GUIDE DE DÉMONSTRATION
--  Mot de passe de TOUS les comptes : Password123!
-- ======================================================================
--
--  COMPTE                          CE QU'IL PERMET DE MONTRER
--  ------------------------------  ------------------------------------------
--  ilyes.karim@campus.fr           ADMINISTRATEUR : modération des avis,
--                                  comptes, trajets, statistiques globales.
--
--  sarah.benjelloun@campus.fr      MODULE A : deux demandes pour la dernière
--                                  place de son trajet Issy -> Versailles :
--                                  en accepter une le fait passer "Complet".
--                                  Son trajet d'hier vers Boulogne Jean Jaurès
--                                  est importé "Ouvert" : la CLÔTURE AUTOMATIQUE
--                                  le passe "Terminé" dès la 1re page affichée,
--                                  refuse la demande d'Hugo et débloque la
--                                  notation de la passagère (Emma).
--
--  bilel.mansouri@campus.fr        MODULE C : profil bien noté (4,75 / 5).
--                                  Réservations envoyées et trajets conduits.
--
--  camille.rousset@campus.fr       Profil moyennement noté (3,25 / 5).
--                                  À montrer à côté de Bilel pour comparer.
--
--  emma.girard@campus.fr           MODULE B : réservations dans tous les
--                                  états (En attente, Confirmée, Refusée).
--
--  louise.perrin@campus.fr         Conductrice ET passagère : montre qu'un
--                                  même compte joue les deux rôles.
--
--  hugo.petit@campus.fr            Aucun avis reçu : la fiche affiche
--  maxime.leroy@campus.fr          "Pas encore noté" et non "0".
--  nathan.faure@campus.fr          C'est le cas où noteMoyenne() = NULL.
--
-- ======================================================================


-- ======================================================================
--  UTILISATEURS
--  Mot de passe de TOUS les comptes : Password123!
--  (les chaînes ci-dessous sont le résultat de password_hash())
-- ======================================================================
INSERT INTO utilisateurs (id, nom, email, mot_de_passe, role, date_inscription) VALUES
( 1, 'Arkam Ghanem',     'arkam.benali@campus.fr', '$2y$12$JDqbkkeqrq5Wh58nIN7Dkedt8ZF0PCV9ilw0QUl/AwIA0T2Aurbv6', 'membre', DATE_ADD(CURDATE(), INTERVAL -529920 MINUTE)),
( 2, 'Bilel Allali',   'bilel.mansouri@campus.fr', '$2y$12$NYRkvCNGPU8x/glQuui8qeTi5eDnBpZm1W5B5iTuQOAM2.Kq1B2US', 'membre', DATE_ADD(CURDATE(), INTERVAL -574560 MINUTE)),
( 3, 'Ilyès Tizaoui',      'ilyes.karim@campus.fr', '$2y$12$OzqANzPnzo.JQILm21g5OeWVTSr15A//ya.GeSLWU0JFVEEFOF232', 'admin', DATE_ADD(CURDATE(), INTERVAL -208800 MINUTE)),
( 4, 'Camille Rousset',  'camille.rousset@campus.fr', '$2y$12$PM7EqucY0bSTXPFAD8AeG.2WEs/8KXa.Zi4uuFr5dWi5DlXmIdG9K', 'membre', DATE_ADD(CURDATE(), INTERVAL -302400 MINUTE)),
( 5, 'Yanis Delorme',    'yanis.delorme@campus.fr', '$2y$12$HbELK5fJIPP9dfjtV8pvuOFYTu.vr0JarJ774WXl.NXNimw303872', 'membre', DATE_ADD(CURDATE(), INTERVAL -69120 MINUTE)),
( 6, 'Sofia Nguyen',     'sofia.nguyen@campus.fr', '$2y$12$h/dULz69RCxXJ0asE5oBreba4Vj4ARxGPwb8ldnN5xgIEoBD3thH6', 'membre', DATE_ADD(CURDATE(), INTERVAL -273600 MINUTE)),
( 7, 'Léa Fontaine',     'lea.fontaine@campus.fr', '$2y$12$8r0TQK.cLSI9NePgcT0ARuTNHSGC/4GPEIZLrplgJaEBEmyv/.qbS', 'membre', DATE_ADD(CURDATE(), INTERVAL -469440 MINUTE)),
( 8, 'Thomas Berger',    'thomas.berger@campus.fr', '$2y$12$GSkYdb.RsfnIy2mcXpQR.O42eZGH2VMb.R33ND1ZGi4n7YBwDpaBa', 'membre', DATE_ADD(CURDATE(), INTERVAL -174240 MINUTE)),
( 9, 'Inès Bouchard',    'ines.bouchard@campus.fr', '$2y$12$5r1eTaWduuRpbXn4PN4QIu4A1FIHk9qf0mZLEAnRw3zCM5Fe1vjF6', 'membre', DATE_ADD(CURDATE(), INTERVAL -102240 MINUTE)),
(10, 'Maxime Leroy',     'maxime.leroy@campus.fr', '$2y$12$mPzvzylK9jwnCZPM4mqQIeMNZKPgIaC5RIhfyUZNfzYN9UVsjcBLS', 'membre', DATE_ADD(CURDATE(), INTERVAL -169920 MINUTE)),
(11, 'Chloé Marchand',   'chloe.marchand@campus.fr', '$2y$12$v5tSkY1MmNvmaqSVyNhaROKCsoZNxbaiR2oMMpvk7I2OY73HY0.PO', 'membre', DATE_ADD(CURDATE(), INTERVAL -241920 MINUTE)),
(12, 'Rayan Cherif',     'rayan.cherif@campus.fr', '$2y$12$YG8RdjX6votggsWzFrX5IutNhLVEE03uP7l8/8Z8MI6StFCOmZNJ6', 'membre', DATE_ADD(CURDATE(), INTERVAL -66240 MINUTE)),
(13, 'Manon Dubois',     'manon.dubois@campus.fr', '$2y$12$m8fVC4KNKBMapB1PgaGM0OXAHrlxIbDbukx4SyCp295.Jyf2JsW7i', 'membre', DATE_ADD(CURDATE(), INTERVAL -421920 MINUTE)),
(14, 'Hugo Petit',       'hugo.petit@campus.fr', '$2y$12$QJAJKkQ352gs/5gK9pIAWOAZ/mqQ7l6MmYPyWvE9fKKJt59a54yXO', 'membre', DATE_ADD(CURDATE(), INTERVAL -537120 MINUTE)),
(15, 'Sarah Benjelloun', 'sarah.benjelloun@campus.fr', '$2y$12$yTrP5mawgg/01Y7q0E8DGe9.nn3Sf8.bFQKwH9CsjJ70Uzpd.I70u', 'membre', DATE_ADD(CURDATE(), INTERVAL -178560 MINUTE)),
(16, 'Lucas Moreau',     'lucas.moreau@campus.fr', '$2y$12$8A6kQyMng1.l2Kvni1fs1.UZHVcdheVGdEVsuqMBn6/buXUtR7jrm', 'membre', DATE_ADD(CURDATE(), INTERVAL -145440 MINUTE)),
(17, 'Emma Girard',      'emma.girard@campus.fr', '$2y$12$Kn22bMNIPjx.MEt9YsWize5xnl19PGmFc93N4wNscunyQiM8maDse', 'membre', DATE_ADD(CURDATE(), INTERVAL -280800 MINUTE)),
(18, 'Nathan Faure',     'nathan.faure@campus.fr', '$2y$12$WDjl4tNPA9hyubOuC3918epdxh6kykmrsw74H4nNSoZa8UE.tm4xC', 'membre', DATE_ADD(CURDATE(), INTERVAL -288000 MINUTE)),
(19, 'Jade Lemoine',     'jade.lemoine@campus.fr', '$2y$12$PUrJTc1p4qbkc3Vx8iW4MeJ7GOOrI0rL36SOGRwfMqmEGI1wsqPBW', 'membre', DATE_ADD(CURDATE(), INTERVAL -243360 MINUTE)),
(20, 'Adam Traoré',      'adam.traore@campus.fr', '$2y$12$AF12FDa056ryoqC7dUSwa.emFu4MvDONnLmKUjHllDu/7gvXjFxWm', 'membre', DATE_ADD(CURDATE(), INTERVAL -276480 MINUTE)),
(21, 'Louise Perrin',    'louise.perrin@campus.fr', '$2y$12$bjSuirQog5UU9jKJwrSpOOk4M4tPE5gb09r2oLwZtMBUZfLeJ3qDe', 'membre', DATE_ADD(CURDATE(), INTERVAL -285120 MINUTE)),
(22, 'Mehdi Amrani',     'mehdi.amrani@campus.fr', '$2y$12$A8b9eRIYe1unbdvvCkWsies20sFIXW4QsK59ddsNzPXd0stunJrKW', 'membre', DATE_ADD(CURDATE(), INTERVAL -167040 MINUTE));


-- ======================================================================
--  TRAJETS
--  places_restantes = places_totales - nombre de réservations 'Confirmee'
-- ======================================================================
INSERT INTO trajets (id, origine, destination, date_heure, places_totales, places_restantes, statut, prix, id_conducteur) VALUES
( 1, 'Porte de Versailles', 'Campus Paris 15e', DATE_ADD(CURDATE(), INTERVAL -97440 MINUTE), 3, 2, 'Termine',   2.00,  1),
( 2, 'Campus Paris 15e', 'Gare de Lyon', DATE_ADD(CURDATE(), INTERVAL -89670 MINUTE), 2, 2, 'Termine',   4.00, 19),
( 3, 'Campus Paris 15e', 'Aéroport Roissy-CDG', DATE_ADD(CURDATE(), INTERVAL -70200 MINUTE), 3, 0, 'Termine',  18.00,  3),
( 4, 'Aéroport Roissy-CDG', 'Issy Val de Seine', DATE_ADD(CURDATE(), INTERVAL -69450 MINUTE), 3, 2, 'Termine',  18.00,  4),
( 5, 'Charles Michels', 'Campus Paris 15e', DATE_ADD(CURDATE(), INTERVAL -67200 MINUTE), 4, 2, 'Termine',   2.00, 19),
( 6, 'Campus Paris 15e', 'Gare Montparnasse', DATE_ADD(CURDATE(), INTERVAL -65145 MINUTE), 3, 0, 'Termine',   3.00, 11),
( 7, 'Amiens', 'Campus Paris 15e', DATE_ADD(CURDATE(), INTERVAL -62280 MINUTE), 2, 0, 'Termine',  24.00,  4),
( 8, 'Campus Paris 15e', 'Beaugrenelle', DATE_ADD(CURDATE(), INTERVAL -55050 MINUTE), 3, 3, 'Termine',   2.00, 18),
( 9, 'Campus Paris 15e', 'Cambronne', DATE_ADD(CURDATE(), INTERVAL -52185 MINUTE), 4, 2, 'Termine',   2.00,  2),
(10, 'Campus Paris 15e', 'Issy Val de Seine', DATE_ADD(CURDATE(), INTERVAL -52170 MINUTE), 3, 1, 'Termine',   2.50, 21),
(11, 'Campus Paris 15e', 'Javel André Citroën', DATE_ADD(CURDATE(), INTERVAL -49230 MINUTE), 3, 1, 'Termine',   2.00,  1),
(12, 'Vanves Malakoff', 'Rungis', DATE_ADD(CURDATE(), INTERVAL -47085 MINUTE), 2, 0, 'Termine',   7.00,  8),
(13, 'Boulogne Marcel Sembat', 'Aéroport d''Orly', DATE_ADD(CURDATE(), INTERVAL -40005 MINUTE), 1, 0, 'Termine',  12.00,  2),
(14, 'Convention', 'Campus Paris 15e', DATE_ADD(CURDATE(), INTERVAL -39810 MINUTE), 2, 1, 'Termine',   2.00,  5),
(15, 'Amiens', 'Campus Paris 15e', DATE_ADD(CURDATE(), INTERVAL -37680 MINUTE), 3, 3, 'Termine',  24.00,  5),
(16, 'Convention', 'Campus Paris 15e', DATE_ADD(CURDATE(), INTERVAL -26865 MINUTE), 3, 1, 'Termine',   2.00,  5),
(17, 'Gare d''Austerlitz', 'Campus Paris 15e', DATE_ADD(CURDATE(), INTERVAL -22605 MINUTE), 2, 1, 'Termine',   4.00,  4),
(18, 'Campus Paris 15e', 'Beaugrenelle', DATE_ADD(CURDATE(), INTERVAL -21870 MINUTE), 3, 3, 'Termine',   2.00,  5),
(19, 'Saint-Quentin-en-Yvelines', 'Campus Paris 15e', DATE_ADD(CURDATE(), INTERVAL -18195 MINUTE), 1, 0, 'Termine',   9.00, 15),
(20, 'Campus Paris 15e', 'Cambronne', DATE_ADD(CURDATE(), INTERVAL -14790 MINUTE), 2, 1, 'Termine',   2.00, 16),
(21, 'Gare Montparnasse', 'Campus Paris 15e', DATE_ADD(CURDATE(), INTERVAL -6735 MINUTE), 1, 0, 'Termine',   3.00, 16),
(22, 'Campus Paris 15e', 'Reims', DATE_ADD(CURDATE(), INTERVAL -1830 MINUTE), 2, 0, 'Termine',  25.00, 22),
(23, 'Gare de Lyon', 'Campus Paris 15e', DATE_ADD(CURDATE(), INTERVAL 3375 MINUTE), 4, 3, 'Ouvert',   4.00, 22),
(24, 'Clamart', 'Campus Paris 15e', DATE_ADD(CURDATE(), INTERVAL 4755 MINUTE), 3, 0, 'Complet',   4.00, 15),
(25, 'Boulogne Jean Jaurès', 'Neuilly-sur-Seine', DATE_ADD(CURDATE(), INTERVAL 4770 MINUTE), 3, 1, 'Ouvert',   5.00, 16),
(26, 'Campus Paris 15e', 'Boulogne Pont de Sèvres', DATE_ADD(CURDATE(), INTERVAL 6855 MINUTE), 3, 1, 'Ouvert',   3.50,  6),
(27, 'Saint-Cloud', 'Créteil Université', DATE_ADD(CURDATE(), INTERVAL 7680 MINUTE), 2, 0, 'Complet',   8.00, 15),
(28, 'Nanterre Université', 'Campus Paris 15e', DATE_ADD(CURDATE(), INTERVAL 9165 MINUTE), 2, 2, 'Ouvert',   6.50, 19),
(29, 'Deauville', 'Campus Paris 15e', DATE_ADD(CURDATE(), INTERVAL 12630 MINUTE), 1, 0, 'Complet',  30.00,  1),
(30, 'Boulogne Jean Jaurès', 'Campus Paris 15e', DATE_ADD(CURDATE(), INTERVAL 13425 MINUTE), 3, 0, 'Complet',   3.00,  2),
(31, 'Deauville', 'Campus Paris 15e', DATE_ADD(CURDATE(), INTERVAL 14100 MINUTE), 1, 1, 'Ouvert',  30.00,  3),
(32, 'Campus Paris 15e', 'Gare d''Austerlitz', DATE_ADD(CURDATE(), INTERVAL 14160 MINUTE), 2, 2, 'Ouvert',   4.00,  1),
(33, 'Campus Paris 15e', 'Vanves Malakoff', DATE_ADD(CURDATE(), INTERVAL 14160 MINUTE), 2, 1, 'Ouvert',   2.50,  2),
(34, 'Campus Paris 15e', 'Boulogne Marcel Sembat', DATE_ADD(CURDATE(), INTERVAL 16920 MINUTE), 2, 1, 'Ouvert',   3.00, 10),
(35, 'Créteil Université', 'Campus Paris 15e', DATE_ADD(CURDATE(), INTERVAL 24915 MINUTE), 4, 0, 'Complet',   8.00, 21),
(36, 'Beaugrenelle', 'Campus Paris 15e', DATE_ADD(CURDATE(), INTERVAL 25020 MINUTE), 3, 3, 'Ouvert',   2.00,  3),
(37, 'Cambronne', 'Campus Paris 15e', DATE_ADD(CURDATE(), INTERVAL 29295 MINUTE), 3, 3, 'Ouvert',   2.00, 21),
(38, 'Issy Val de Seine', 'Versailles Chantiers', DATE_ADD(CURDATE(), INTERVAL 29940 MINUTE), 3, 1, 'Ouvert',   8.00, 15),
(39, 'Meudon-la-Forêt', 'Campus Paris 15e', DATE_ADD(CURDATE(), INTERVAL 30765 MINUTE), 2, 1, 'Ouvert',   4.50, 11),
(40, 'Amiens', 'Campus Paris 15e', DATE_ADD(CURDATE(), INTERVAL 32700 MINUTE), 3, 2, 'Ouvert',  24.00,  3),
(41, 'Campus Paris 15e', 'Saint-Quentin-en-Yvelines', DATE_ADD(CURDATE(), INTERVAL 32775 MINUTE), 4, 0, 'Complet',   9.00,  9),
(42, 'Aéroport Roissy-CDG', 'Meudon-la-Forêt', DATE_ADD(CURDATE(), INTERVAL 35760 MINUTE), 4, 4, 'Ouvert',  18.00, 10),
-- Trajet parti hier, importé "Ouvert" : la clôture automatique le passera "Termine"
(43, 'Campus Paris 15e', 'Boulogne Jean Jaurès', DATE_ADD(CURDATE(), INTERVAL -600 MINUTE), 3, 2, 'Ouvert',   3.00, 15);


-- ======================================================================
--  RESERVATIONS
--  Une réservation 'Refusee' ne consomme aucune place.
-- ======================================================================
INSERT INTO reservations (id, statut, date_reservation, id_trajet, id_passager) VALUES
(  1, 'En attente', DATE_ADD(CURDATE(), INTERVAL -114300 MINUTE),  1,  4),
(  2, 'Confirmee', DATE_ADD(CURDATE(), INTERVAL -103845 MINUTE),  1, 11),
(  3, 'Refusee', DATE_ADD(CURDATE(), INTERVAL -101340 MINUTE),  2, 17),
(  4, 'Refusee', DATE_ADD(CURDATE(), INTERVAL -99795 MINUTE),  1,  5),
(  5, 'Refusee', DATE_ADD(CURDATE(), INTERVAL -86850 MINUTE),  4, 16),
(  6, 'Confirmee', DATE_ADD(CURDATE(), INTERVAL -85815 MINUTE),  4,  3),
(  7, 'Confirmee', DATE_ADD(CURDATE(), INTERVAL -81525 MINUTE),  5,  5),
(  8, 'Confirmee', DATE_ADD(CURDATE(), INTERVAL -81225 MINUTE),  3,  8),
(  9, 'Confirmee', DATE_ADD(CURDATE(), INTERVAL -81090 MINUTE),  6,  8),
( 10, 'Confirmee', DATE_ADD(CURDATE(), INTERVAL -80085 MINUTE),  5,  6),
( 11, 'Confirmee', DATE_ADD(CURDATE(), INTERVAL -79905 MINUTE),  7,  9),
( 12, 'En attente', DATE_ADD(CURDATE(), INTERVAL -79665 MINUTE),  4,  8),
( 13, 'Confirmee', DATE_ADD(CURDATE(), INTERVAL -79425 MINUTE),  3,  4),
( 14, 'Refusee', DATE_ADD(CURDATE(), INTERVAL -79350 MINUTE),  6,  2),
( 15, 'Confirmee', DATE_ADD(CURDATE(), INTERVAL -75120 MINUTE),  3, 15),
( 16, 'Refusee', DATE_ADD(CURDATE(), INTERVAL -74190 MINUTE),  5, 20),
( 17, 'Confirmee', DATE_ADD(CURDATE(), INTERVAL -73530 MINUTE),  7, 21),
( 18, 'Confirmee', DATE_ADD(CURDATE(), INTERVAL -71340 MINUTE),  6,  7),
( 19, 'Refusee', DATE_ADD(CURDATE(), INTERVAL -68370 MINUTE),  9, 12),
( 20, 'Confirmee', DATE_ADD(CURDATE(), INTERVAL -68325 MINUTE), 10,  6),
( 21, 'Confirmee', DATE_ADD(CURDATE(), INTERVAL -67050 MINUTE),  9, 15),
( 22, 'Confirmee', DATE_ADD(CURDATE(), INTERVAL -66315 MINUTE),  6, 16),
( 23, 'Confirmee', DATE_ADD(CURDATE(), INTERVAL -63780 MINUTE), 12, 17),
( 24, 'Confirmee', DATE_ADD(CURDATE(), INTERVAL -63555 MINUTE), 12, 21),
( 25, 'Confirmee', DATE_ADD(CURDATE(), INTERVAL -62700 MINUTE), 11, 19),
( 26, 'Refusee', DATE_ADD(CURDATE(), INTERVAL -60975 MINUTE), 12,  2),
( 27, 'Refusee', DATE_ADD(CURDATE(), INTERVAL -56835 MINUTE), 13,  3),
( 28, 'Refusee', DATE_ADD(CURDATE(), INTERVAL -55290 MINUTE), 15,  1),
( 29, 'Confirmee', DATE_ADD(CURDATE(), INTERVAL -55140 MINUTE), 10, 13),
( 30, 'Refusee', DATE_ADD(CURDATE(), INTERVAL -54930 MINUTE), 14, 13),
( 31, 'Confirmee', DATE_ADD(CURDATE(), INTERVAL -54180 MINUTE),  9,  9),
( 32, 'En attente', DATE_ADD(CURDATE(), INTERVAL -53865 MINUTE),  9, 21),
( 33, 'Confirmee', DATE_ADD(CURDATE(), INTERVAL -52560 MINUTE), 11,  2),
( 34, 'Confirmee', DATE_ADD(CURDATE(), INTERVAL -45525 MINUTE), 14,  2),
( 35, 'Confirmee', DATE_ADD(CURDATE(), INTERVAL -42480 MINUTE), 13,  9),
( 36, 'Confirmee', DATE_ADD(CURDATE(), INTERVAL -38055 MINUTE), 16, 20),
( 37, 'En attente', DATE_ADD(CURDATE(), INTERVAL -36615 MINUTE), 18, 14),
( 38, 'Confirmee', DATE_ADD(CURDATE(), INTERVAL -35385 MINUTE), 17,  3),
( 39, 'En attente', DATE_ADD(CURDATE(), INTERVAL -32550 MINUTE), 19, 18),
( 40, 'Refusee', DATE_ADD(CURDATE(), INTERVAL -31845 MINUTE), 18, 16),
( 41, 'Confirmee', DATE_ADD(CURDATE(), INTERVAL -30990 MINUTE), 20,  2),
( 42, 'Confirmee', DATE_ADD(CURDATE(), INTERVAL -30615 MINUTE), 16,  2),
( 43, 'Refusee', DATE_ADD(CURDATE(), INTERVAL -29040 MINUTE), 17,  9),
( 44, 'Confirmee', DATE_ADD(CURDATE(), INTERVAL -25440 MINUTE), 19, 20),
( 45, 'Refusee', DATE_ADD(CURDATE(), INTERVAL -23745 MINUTE), 21,  5),
( 46, 'En attente', DATE_ADD(CURDATE(), INTERVAL -23190 MINUTE), 20, 14),
( 47, 'Refusee', DATE_ADD(CURDATE(), INTERVAL -20415 MINUTE), 20, 21),
( 48, 'Confirmee', DATE_ADD(CURDATE(), INTERVAL -10545 MINUTE), 22, 14),
( 49, 'En attente', DATE_ADD(CURDATE(), INTERVAL -9300 MINUTE), 27,  7),
( 50, 'En attente', DATE_ADD(CURDATE(), INTERVAL -9240 MINUTE), 22,  4),
( 51, 'Confirmee', DATE_ADD(CURDATE(), INTERVAL -8775 MINUTE), 21, 12),
( 52, 'Confirmee', DATE_ADD(CURDATE(), INTERVAL -8160 MINUTE), 22, 20),
( 53, 'Confirmee', DATE_ADD(CURDATE(), INTERVAL -7425 MINUTE), 25, 11),
( 54, 'Refusee', DATE_ADD(CURDATE(), INTERVAL -7350 MINUTE), 27, 21),
( 55, 'Confirmee', DATE_ADD(CURDATE(), INTERVAL -5280 MINUTE), 29,  3),
( 56, 'Confirmee', DATE_ADD(CURDATE(), INTERVAL -5070 MINUTE), 23,  1),
( 57, 'En attente', DATE_ADD(CURDATE(), INTERVAL -3735 MINUTE), 24,  5),
( 58, 'Confirmee', DATE_ADD(CURDATE(), INTERVAL -3210 MINUTE), 24, 12),
( 59, 'Confirmee', DATE_ADD(CURDATE(), INTERVAL -3150 MINUTE), 24, 16),
( 60, 'Refusee', DATE_ADD(CURDATE(), INTERVAL -1725 MINUTE), 33,  3),
( 61, 'Confirmee', DATE_ADD(CURDATE(), INTERVAL -570 MINUTE), 26, 19),
( 62, 'Refusee', DATE_ADD(CURDATE(), INTERVAL 495 MINUTE), 23,  8),
( 63, 'En attente', DATE_ADD(CURDATE(), INTERVAL 930 MINUTE), 24,  4),
( 64, 'En attente', DATE_ADD(CURDATE(), INTERVAL 930 MINUTE), 26, 22),
( 65, 'Confirmee', DATE_ADD(CURDATE(), INTERVAL 2160 MINUTE), 27,  1),
( 66, 'Confirmee', DATE_ADD(CURDATE(), INTERVAL 2235 MINUTE), 26,  3),
( 67, 'Confirmee', DATE_ADD(CURDATE(), INTERVAL 2595 MINUTE), 25,  6),
( 68, 'Confirmee', DATE_ADD(CURDATE(), INTERVAL 3525 MINUTE), 24, 17),
( 69, 'Confirmee', DATE_ADD(CURDATE(), INTERVAL 4920 MINUTE), 33, 17),
( 70, 'Confirmee', DATE_ADD(CURDATE(), INTERVAL 5535 MINUTE), 27, 12),
( 71, 'Confirmee', DATE_ADD(CURDATE(), INTERVAL 6390 MINUTE), 30, 17),
( 72, 'En attente', DATE_ADD(CURDATE(), INTERVAL 6690 MINUTE), 32,  6),
( 73, 'En attente', DATE_ADD(CURDATE(), INTERVAL 6945 MINUTE), 34, 16),
( 74, 'En attente', DATE_ADD(CURDATE(), INTERVAL 6975 MINUTE), 28, 10),
( 75, 'Refusee', DATE_ADD(CURDATE(), INTERVAL 7710 MINUTE), 36, 12),
( 76, 'Confirmee', DATE_ADD(CURDATE(), INTERVAL 7725 MINUTE), 30, 10),
( 77, 'Confirmee', DATE_ADD(CURDATE(), INTERVAL 9255 MINUTE), 34, 13),
( 78, 'Confirmee', DATE_ADD(CURDATE(), INTERVAL 9720 MINUTE), 30,  1),
( 79, 'En attente', DATE_ADD(CURDATE(), INTERVAL 10830 MINUTE), 29, 18),
( 80, 'En attente', DATE_ADD(CURDATE(), INTERVAL 12510 MINUTE), 35,  7),
( 81, 'En attente', DATE_ADD(CURDATE(), INTERVAL 13665 MINUTE), 39,  2),
( 82, 'Confirmee', DATE_ADD(CURDATE(), INTERVAL 14220 MINUTE), 35,  2),
( 83, 'Refusee', DATE_ADD(CURDATE(), INTERVAL 15300 MINUTE), 34, 11),
( 84, 'Confirmee', DATE_ADD(CURDATE(), INTERVAL 16665 MINUTE), 41,  8),
( 85, 'Confirmee', DATE_ADD(CURDATE(), INTERVAL 16755 MINUTE), 35, 20),
( 86, 'En attente', DATE_ADD(CURDATE(), INTERVAL 17805 MINUTE), 41,  2),
( 87, 'Confirmee', DATE_ADD(CURDATE(), INTERVAL 18405 MINUTE), 41, 22),
( 88, 'Confirmee', DATE_ADD(CURDATE(), INTERVAL 18555 MINUTE), 40,  9),
( 89, 'Refusee', DATE_ADD(CURDATE(), INTERVAL 19365 MINUTE), 35, 17),
( 90, 'Refusee', DATE_ADD(CURDATE(), INTERVAL 20760 MINUTE), 38,  4),
( 91, 'En attente', DATE_ADD(CURDATE(), INTERVAL 21090 MINUTE), 37,  5),
( 92, 'En attente', DATE_ADD(CURDATE(), INTERVAL 21315 MINUTE), 42, 11),
( 93, 'Confirmee', DATE_ADD(CURDATE(), INTERVAL 22470 MINUTE), 35, 16),
( 94, 'Confirmee', DATE_ADD(CURDATE(), INTERVAL 22560 MINUTE), 35,  3),
( 95, 'Confirmee', DATE_ADD(CURDATE(), INTERVAL 22620 MINUTE), 41, 10),
( 96, 'Confirmee', DATE_ADD(CURDATE(), INTERVAL 24075 MINUTE), 41,  6),
( 97, 'Confirmee', DATE_ADD(CURDATE(), INTERVAL 26430 MINUTE), 38, 21),
( 98, 'En attente', DATE_ADD(CURDATE(), INTERVAL 26625 MINUTE), 37, 17),
( 99, 'Confirmee', DATE_ADD(CURDATE(), INTERVAL 28110 MINUTE), 38, 17),
(100, 'Confirmee', DATE_ADD(CURDATE(), INTERVAL 28590 MINUTE), 39, 16),
(101, 'Confirmee', DATE_ADD(CURDATE(), INTERVAL -3000 MINUTE), 43, 17),
(102, 'En attente', DATE_ADD(CURDATE(), INTERVAL -2500 MINUTE), 43, 14),
-- Deux demandes pour la DERNIÈRE place du trajet 38 : en accepter une le fait passer "Complet"
(103, 'En attente', DATE_ADD(CURDATE(), INTERVAL -1440 MINUTE), 38, 14),
(104, 'En attente', DATE_ADD(CURDATE(), INTERVAL -900 MINUTE), 38, 18);


-- ======================================================================
--  AVIS
--  Uniquement sur les trajets au statut 'Termine'.
--  Notation mutuelle : conducteur <-> passagers acceptés.
-- ======================================================================
INSERT INTO avis (id, note, commentaire, date_avis, id_trajet, id_auteur, id_cible) VALUES
(  1, 5, NULL, DATE_ADD(CURDATE(), INTERVAL -95880 MINUTE),  1,  1, 11),
(  2, 3, NULL, DATE_ADD(CURDATE(), INTERVAL -94140 MINUTE),  1, 11,  1),
(  3, 5, 'Super conducteur, parti pile à l''heure et conduite très tranquille.', DATE_ADD(CURDATE(), INTERVAL -67860 MINUTE),  3,  8,  3),
(  4, 3, 'Ça va, sans plus.', DATE_ADD(CURDATE(), INTERVAL -66420 MINUTE),  4,  3,  4),
(  5, 4, NULL, DATE_ADD(CURDATE(), INTERVAL -65580 MINUTE),  4,  4,  3),
(  6, 5, 'A accepté de me déposer devant le terminal, très sympa.', DATE_ADD(CURDATE(), INTERVAL -65520 MINUTE),  5,  5, 19),
(  7, 5, 'Très arrangeant sur le point de rendez-vous, merci !', DATE_ADD(CURDATE(), INTERVAL -65520 MINUTE),  5,  6, 19),
(  8, 5, 'Super conducteur, parti pile à l''heure et conduite très tranquille.', DATE_ADD(CURDATE(), INTERVAL -65340 MINUTE),  3, 15,  3),
(  9, 4, 'Bonne route, voiture confortable.', DATE_ADD(CURDATE(), INTERVAL -65280 MINUTE),  3,  3, 15),
( 10, 5, 'Rien à dire, ponctuel et sympa. Je remonte avec lui quand il veut.', DATE_ADD(CURDATE(), INTERVAL -63840 MINUTE),  5, 19,  6),
( 11, 4, 'Bon covoiturage, je recommande.', DATE_ADD(CURDATE(), INTERVAL -63780 MINUTE),  6, 11,  7),
( 12, 3, NULL, DATE_ADD(CURDATE(), INTERVAL -63480 MINUTE),  6, 11, 16),
( 13, 4, NULL, DATE_ADD(CURDATE(), INTERVAL -60960 MINUTE),  6, 16, 11),
( 14, 5, NULL, DATE_ADD(CURDATE(), INTERVAL -60720 MINUTE),  6, 11,  8),
( 15, 5, 'A accepté de me déposer devant le terminal, très sympa.', DATE_ADD(CURDATE(), INTERVAL -60600 MINUTE),  7,  4,  9),
( 16, 5, 'Passager au top, ponctuel et agréable.', DATE_ADD(CURDATE(), INTERVAL -58020 MINUTE),  7,  4, 21),
( 17, 4, 'Bonne route, voiture confortable.', DATE_ADD(CURDATE(), INTERVAL -56760 MINUTE),  7,  9,  4),
( 18, 3, 'Correct, mais peu de place pour les bagages.', DATE_ADD(CURDATE(), INTERVAL -56700 MINUTE),  7, 21,  4),
( 19, 5, 'Très arrangeant sur le point de rendez-vous, merci !', DATE_ADD(CURDATE(), INTERVAL -50820 MINUTE), 10, 21, 13),
( 20, 4, 'Bon covoiturage, je recommande.', DATE_ADD(CURDATE(), INTERVAL -49740 MINUTE), 10, 13, 21),
( 21, 4, NULL, DATE_ADD(CURDATE(), INTERVAL -48360 MINUTE), 10,  6, 21),
( 22, 3, 'Ça va, sans plus.', DATE_ADD(CURDATE(), INTERVAL -48120 MINUTE), 10, 21,  6),
( 23, 4, 'Bonne route, voiture confortable.', DATE_ADD(CURDATE(), INTERVAL -47940 MINUTE), 11,  2,  1),
( 24, 5, 'A accepté de me déposer devant le terminal, très sympa.', DATE_ADD(CURDATE(), INTERVAL -47820 MINUTE),  9, 15,  2),
( 25, 5, 'Passager au top, ponctuel et agréable.', DATE_ADD(CURDATE(), INTERVAL -45060 MINUTE), 12,  8, 17),
( 26, 5, 'A accepté de me déposer devant le terminal, très sympa.', DATE_ADD(CURDATE(), INTERVAL -43620 MINUTE), 12, 17,  8),
( 27, 4, 'Sympa, mais un peu juste sur l''horaire.', DATE_ADD(CURDATE(), INTERVAL -42360 MINUTE), 12,  8, 21),
( 28, 5, NULL, DATE_ADD(CURDATE(), INTERVAL -42300 MINUTE), 12, 21,  8),
( 29, 5, 'Passager au top, ponctuel et agréable.', DATE_ADD(CURDATE(), INTERVAL -38280 MINUTE), 13,  2,  9),
( 30, 5, 'Très arrangeant sur le point de rendez-vous, merci !', DATE_ADD(CURDATE(), INTERVAL -36780 MINUTE), 14,  5,  2),
( 31, 5, NULL, DATE_ADD(CURDATE(), INTERVAL -35340 MINUTE), 14,  2,  5),
( 32, 5, 'Très arrangeant sur le point de rendez-vous, merci !', DATE_ADD(CURDATE(), INTERVAL -34680 MINUTE), 13,  9,  2),
( 33, 4, NULL, DATE_ADD(CURDATE(), INTERVAL -24720 MINUTE), 16,  5,  2),
( 34, 4, 'Trajet agréable, juste un peu de retard au départ.', DATE_ADD(CURDATE(), INTERVAL -23820 MINUTE), 16,  5, 20),
( 35, 5, NULL, DATE_ADD(CURDATE(), INTERVAL -23520 MINUTE), 16, 20,  5),
( 36, 4, 'Bon covoiturage, je recommande.', DATE_ADD(CURDATE(), INTERVAL -22440 MINUTE), 16,  2,  5),
( 37, 3, 'Arrivée avec une dizaine de minutes de retard au point de rendez-vous.', DATE_ADD(CURDATE(), INTERVAL -19440 MINUTE), 17,  3,  4),
( 38, 4, NULL, DATE_ADD(CURDATE(), INTERVAL -16200 MINUTE), 19, 20, 15),
( 39, 5, 'A accepté de me déposer devant le terminal, très sympa.', DATE_ADD(CURDATE(), INTERVAL -13560 MINUTE), 19, 15, 20),
( 40, 4, NULL, DATE_ADD(CURDATE(), INTERVAL -13260 MINUTE), 20,  2, 16),
( 41, 4, NULL, DATE_ADD(CURDATE(), INTERVAL -3240 MINUTE), 21, 16, 12),
( 42, 4, 'Bon covoiturage, je recommande.', DATE_ADD(CURDATE(), INTERVAL -420 MINUTE), 21, 12, 16),
( 43, 4, 'Tout s''est bien passé.', DATE_ADD(CURDATE(), INTERVAL 660 MINUTE), 22, 14, 22),
( 44, 3, NULL, DATE_ADD(CURDATE(), INTERVAL 2700 MINUTE), 22, 20, 22);


-- ======================================================================
--  AJUSTEMENTS AUTOMATIQUES
--  Une demande précède toujours le départ ; un avis suit toujours le départ.
--  Une demande restée "En attente" sur un trajet terminé est refusée
--  (c'est ce que fait la clôture d'un trajet sur le site).
-- ======================================================================
UPDATE reservations r JOIN trajets t ON t.id = r.id_trajet
   SET r.date_reservation = LEAST(r.date_reservation, NOW(), DATE_SUB(t.date_heure, INTERVAL 1 HOUR));
UPDATE avis a JOIN trajets t ON t.id = a.id_trajet
   SET a.date_avis = LEAST(NOW(), GREATEST(a.date_avis, DATE_ADD(t.date_heure, INTERVAL 1 HOUR)));
UPDATE reservations r JOIN trajets t ON t.id = r.id_trajet
   SET r.statut = 'Refusee' WHERE t.statut = 'Termine' AND r.statut = 'En attente';


-- ======================================================================
--  REQUÊTES DE VÉRIFICATION (facultatives)
--  À lancer après l'import pour contrôler la cohérence des données.
-- ======================================================================

-- 1. Les réputations calculées par le site
SELECT u.nom, ROUND(AVG(a.note), 2) AS note_moyenne, COUNT(a.id) AS nb_avis
  FROM utilisateurs u
  JOIN avis a ON a.id_cible = u.id
 GROUP BY u.id, u.nom
 ORDER BY note_moyenne DESC, nb_avis DESC;

-- 2. Cohérence du compteur de places : doit renvoyer 0 ligne
SELECT t.id, t.places_totales, t.places_restantes
  FROM trajets t
 WHERE (t.places_totales - t.places_restantes) <> (
       SELECT COUNT(*) FROM reservations r
        WHERE r.id_trajet = t.id AND r.statut = 'Confirmee');

-- 3. Cohérence des statuts : doit renvoyer 0 ligne
SELECT id, statut, places_restantes FROM trajets
 WHERE (statut = 'Complet' AND places_restantes <> 0)
    OR (statut = 'Ouvert'  AND places_restantes = 0);

-- 4. Avis sur un trajet non terminé : doit renvoyer 0 ligne
SELECT a.id FROM avis a JOIN trajets t ON t.id = a.id_trajet
 WHERE t.statut <> 'Termine';

-- 5. Répartition des trajets par statut
SELECT statut, COUNT(*) AS nb FROM trajets GROUP BY statut;

-- 6. Les destinations les plus demandées
SELECT destination, COUNT(*) AS nb FROM trajets
 GROUP BY destination ORDER BY nb DESC LIMIT 10;
