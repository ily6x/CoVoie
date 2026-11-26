<?php

// HTTP : MySQL réel, cookies de session et formulaires. Aucune donnée existante n'est supprimée.
$base = 'http://localhost/covoie/index.php?page=';
$c = curl_init();
$cookies = tempnam(sys_get_temp_dir(), 'covoie');
curl_setopt_array($c, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_COOKIEJAR => $cookies,
    CURLOPT_COOKIEFILE => $cookies
]);
$n = 0;
function http(string $page, ?array $body = null, int $status = 200):string {
    global $c, $base, $n;
    curl_setopt($c, CURLOPT_URL, $base.$page);
    curl_setopt($c, CURLOPT_POST, $body !== null);
    if($body !== null) {
        curl_setopt($c, CURLOPT_POSTFIELDS, http_build_query($body));
    }
    $html = curl_exec($c);
    $code = curl_getinfo($c, CURLINFO_HTTP_CODE);
    if($code !== $status || preg_match('/(?:Warning:|Fatal error:|Parse error:)/', $html)) {
        throw new RuntimeException("$page : HTTP $code au lieu de $status\n$html");
    }
    $n++;
    return $html;
}
function token(string $html):string {
    if(!preg_match('/name="csrf" value="([^"]+)"/', $html, $m)) {
        throw new RuntimeException('CSRF absent');
    }
    return $m[1];
}
try {
    http('accueil');
    http('trajets');
    http('profil&id=2');
    http('inconnue', null, 404);
    http('mes-trajets', null, 401);
    http('reserver&id=32', null, 405);
    http('connexion-valider', ['csrf' => 'invalide'], 403);
    $t = token(http('connexion'));
    http('connexion-valider', ['csrf' => $t, 'email' => 'arkam.benali@campus.fr', 'mot_de_passe' => 'Password123!'], 303);
    foreach([
        'admin',
        'admin-avis',
        'admin-utilisateurs',
        'admin-trajets',
        'mes-trajets',
        'mes-reservations',
        'mes-avis',
        'mes-statistiques',
        'publier',
        'modifier&id=32',
        'noter&id=1',
        'trajet&id=32'
    ] as $p) {
        http($p);
    }
    $t = token(http('publier'));
    http('publier-valider', [
        'csrf' => $t,
        'origine' => 'Paris',
        'destination' => 'Paris',
        'date_heure' => '2020-01-01T08:00',
        'places' => '-1',
        'prix' => '-20'
    ], 422);
    http('modifier&id=33', null, 403);
    $t = token(http('accueil'));
    http('deconnexion', ['csrf' => $t], 303);
    $t = token(http('connexion'));
    http('connexion-valider', ['csrf' => $t, 'email' => 'emma.girard@campus.fr', 'mot_de_passe' => 'Password123!'], 303);
    http('admin', null, 403);
    http('mes-reservations');
    http('modifier&id=32', null, 403);
    http('noter&id=1', null, 403);
    $t = token(http('trajet&id=32'));
    http('accepter&id=32&reservation=72', ['csrf' => $t], 403);
    echo "$n contrôles HTTP réussis sur MySQL.\n";
}
finally {
    curl_close($c);
    unlink($cookies);
}
