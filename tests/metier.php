<?php

ob_start();
require __DIR__.'/../includes/config.php';
require __DIR__.'/../includes/database.php';
require __DIR__.'/../includes/functions.php';
spl_autoload_register(function($c) {
    foreach(['models', 'controllers'] as $d) {
        $f = __DIR__.'/../'.$d.'/'.$c.'.php';
        if(is_file($f)) {
            require $f;
            return;
        }
    }
}
);
$pdo = $pdoTest ??  new PDO('sqlite::memory:', null, null, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
]);
Database::setConnection($pdo);
if ($pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite') {
    $pdo->exec('PRAGMA foreign_keys=ON');
    $pdo->exec("CREATE TABLE utilisateurs(id INTEGER PRIMARY KEY AUTOINCREMENT,nom TEXT,email TEXT UNIQUE,mot_de_passe TEXT,role TEXT,date_inscription TEXT);
CREATE TABLE trajets(id INTEGER PRIMARY KEY AUTOINCREMENT,origine TEXT,destination TEXT,date_heure TEXT,places_totales INTEGER,places_restantes INTEGER,statut TEXT,prix REAL,id_conducteur INTEGER REFERENCES utilisateurs(id) ON DELETE CASCADE);
CREATE TABLE reservations(id INTEGER PRIMARY KEY AUTOINCREMENT,statut TEXT,date_reservation TEXT,id_trajet INTEGER REFERENCES trajets(id) ON DELETE CASCADE,id_passager INTEGER REFERENCES utilisateurs(id) ON DELETE CASCADE,UNIQUE(id_trajet,id_passager));
CREATE TABLE avis(id INTEGER PRIMARY KEY AUTOINCREMENT,note INTEGER,commentaire TEXT,date_avis TEXT,id_trajet INTEGER REFERENCES trajets(id) ON DELETE CASCADE,id_auteur INTEGER REFERENCES utilisateurs(id) ON DELETE CASCADE,id_cible INTEGER REFERENCES utilisateurs(id) ON DELETE CASCADE,UNIQUE(id_trajet,id_auteur,id_cible));");
}
$n = 0;
function verifier(bool $ok, string $message):void {
    global $n;
    if(!$ok) {
        throw new RuntimeException($message);
    }
    $n++;
    echo "OK $message\n";
}
function refuse(callable $f, string $message):void {
    try {
        $f();
    }
    catch(DomainException|HttpException $e) {
        verifier(true, $message);
        return;
    }
    throw new RuntimeException('Action acceptée à tort : '.$message);
}
$a = Utilisateur::register('Conducteur', 'a@example.fr', 'Password123!', 'Password123!');
$b = Utilisateur::register('Passager', 'b@example.fr', 'Password123!', 'Password123!');
$c = Utilisateur::register('Autre passager', 'c@example.fr', 'Password123!', 'Password123!');
verifier(password_verify('Password123!', Utilisateur::find($a)->password), 'mot de passe haché');
refuse(fn() => Utilisateur::register('Duplicata', 'a@example.fr', 'Password123!', 'Password123!'), 'email unique');
verifier(Avis::noteMoyenne($b) === null, 'absence de note retourne NULL');
$date = date('Y-m-d H:i', strtotime('+2 days'));
$t = Trajet::create($a, 'Paris', 'Lyon', $date, 1, 20);
refuse(fn() => Reservation::create($t, $a), 'auto-réservation interdite');
$r = Reservation::create($t, $b);
verifier(Trajet::find($t)->placesRestantes === 1, 'demande en attente ne retire aucune place');
refuse(fn() => Reservation::create($t, $b), 'double demande interdite');
$r2 = Reservation::create($t, $c);
refuse(fn() => Reservation::traiter($t, $r, $c, true), 'autre conducteur interdit');
Reservation::traiter($t, $r, $a, true);
verifier(Trajet::find($t)->placesRestantes === 0 && Trajet::find($t)->statut === 'Complet', 'acceptation retire la dernière place et passe en Complet');
refuse(fn() => Reservation::traiter($t, $r, $a, true), 'double acceptation interdite');
refuse(fn() => Reservation::traiter($t, $r2, $a, true), 'pas de sur-réservation');
verifier(Reservation::getList('r.id=?', [$r2])[0]->statut === 'En attente', 'échec de confirmation conserve la demande');
Reservation::traiter($t, $r2, $a, false);
verifier(Trajet::find($t)->placesRestantes === 0, 'refus ne modifie pas le compteur');
refuse(fn() => Trajet::update($t, $a, [
    'origine' => 'Paris',
    'destination' => 'Lyon',
    'date_heure' => date('Y-m-d\TH:i', strtotime('+2 days')),
    'places' => 0,
    'prix' => 20
]), 'capacité invalide refusée');
Trajet::update($t, $a, [
    'origine' => 'Paris',
    'destination' => 'Lyon',
    'date_heure' => date('Y-m-d\TH:i', strtotime('+2 days')),
    'places' => 2,
    'prix' => 20
]);
verifier(Trajet::find($t)->placesRestantes === 1 && Trajet::find($t)->statut === 'Ouvert', 'modification préserve les places acceptées');
refuse(fn() => Trajet::cloturer($t, $a), 'clôture future interdite');
refuse(fn() => Avis::create($t, $b, $a, 5, 'Bien'), 'avis avant clôture interdit');
Database::query('UPDATE trajets SET date_heure=? WHERE id=?', [date('Y-m-d H:i:s', strtotime('-1 day')), $t]);
Trajet::cloturer($t, $a);
refuse(fn() => Avis::create($t, $c, $a, 5, 'Bien'), 'passager refusé ne peut pas noter');
refuse(fn() => Avis::create($t, $b, $a, 6, 'Bien'), 'note hors limites interdite');
Avis::create($t, $b, $a, 5, 'Bien');
Avis::create($t, $a, $b, 4, 'Ponctuel');
refuse(fn() => Avis::create($t, $b, $a, 2, 'Encore'), 'avis unique par participant');
verifier(Avis::noteMoyenne($a) === 5.0, 'moyenne calculée');
Database::query('DELETE FROM avis WHERE id_cible=?', [$a]);
verifier(Avis::noteMoyenne($a) === null, 'modération recalcule la moyenne');
$t2 = Trajet::create($a, 'Paris', 'Lille', $date, 1, 10);
$r3 = Reservation::create($t2, $b);
Reservation::traiter($t2, $r3, $a, true);
Utilisateur::delete($b, $a);
verifier(Trajet::find($t2)->placesRestantes === 1 && Trajet::find($t2)->statut === 'Ouvert', 'suppression passager libère sa place');
verifier(Utilisateur::find($b) === null && Reservation::getList('r.id_passager=?', [$b]) === [], 'suppression en cascade');
refuse(fn() => Utilisateur::delete($a, $a), 'suppression de son propre compte interdite');
$_SERVER['REQUEST_METHOD'] = 'GET';
refuse(fn() => requirePost(), 'mutation en GET interdite');
$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST['csrf'] = 'incorrect';
refuse(fn() => requirePost(), 'CSRF incorrect interdit');
$_POST['csrf'] = csrf();
requirePost();
verifier(true, 'CSRF valide accepté');
echo "$n contrôles réussis.\n";
