<?php
/**
 * ------------------------------------------------------------------
 *  CoVoie - connexion.php
 * ------------------------------------------------------------------
 *  Connexion à la base MySQL avec PDO.
 *
 *  Deux fonctions seulement :
 *   - connexionBdd() : renvoie l'objet PDO (créé une seule fois)
 *   - requete()      : prépare et exécute une requête SQL
 *
 *  Toutes les requêtes du site passent par requete() : elles sont donc
 *  TOUTES préparées, ce qui protège contre les injections SQL.
 * ------------------------------------------------------------------
 */

/**
 * Renvoie la connexion PDO à la base de données.
 * La variable "static" garde la connexion en mémoire : on ne se
 * connecte qu'une seule fois par page, même si on appelle la fonction 20 fois.
 *
 * @return PDO
 */
function connexionBdd(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        $dsn = 'mysql:host=' . BDD_HOTE . ';port=' . BDD_PORT . ';dbname=' . BDD_NOM . ';charset=utf8mb4';

        try {
            $pdo = new PDO($dsn, BDD_UTILISATEUR, BDD_MOT_DE_PASSE, [
                // En cas d'erreur SQL, PDO lance une exception (au lieu de rester silencieux)
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                // Les résultats arrivent sous forme de tableaux associatifs : $ligne['nom']
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                // Vraies requêtes préparées côté MySQL
                PDO::ATTR_EMULATE_PREPARES => false
            ]);
        } catch (PDOException $erreur) {
            // Message clair au lieu d'une erreur PHP brute (WAMP arrêté ou base non importée)
            afficherErreur(500, 'Base de données inaccessible : lancez WAMP et importez sql/bdd_covoie.sql.');
        }
    }

    return $pdo;
}

/**
 * Prépare puis exécute une requête SQL.
 * Les valeurs sont passées à part, dans $parametres, à la place des "?".
 *
 * Exemple : requete('SELECT * FROM trajets WHERE id = ?', [12]);
 *
 * @param string $sql        La requête avec des "?" à la place des valeurs
 * @param array  $parametres Les valeurs, dans l'ordre des "?"
 * @return PDOStatement      Le résultat, à lire avec fetch() ou fetchAll()
 */
function requete(string $sql, array $parametres = []): PDOStatement
{
    $resultat = connexionBdd()->prepare($sql);
    $resultat->execute($parametres);

    return $resultat;
}
