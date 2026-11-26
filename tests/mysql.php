<?php

// Base temporaire créée par ce test ; la base de l'application reste intacte.
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
];
$serveur = new PDO('mysql:host=127.0.0.1;port=3306;charset=utf8mb4', getenv('COVOIE_DB_USER')?:'root', getenv('COVOIE_DB_PASSWORD')?:'', $options);
$baseTest = 'covoie_test_'.bin2hex(random_bytes(8));
$serveur->exec("CREATE DATABASE `$baseTest` CHARACTER SET utf8mb4");
try {
    $pdoTest = new PDO("mysql:host=127.0.0.1;port=3306;dbname=$baseTest;charset=utf8mb4", getenv('COVOIE_DB_USER')?:'root', getenv('COVOIE_DB_PASSWORD')?:'', $options);
    $sql = file_get_contents(__DIR__.'/../sql/covoiturage.sql');
    preg_match_all('/CREATE TABLE .*?ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;/s', $sql, $tables);
    foreach($tables[0] as $table) {
        $pdoTest->exec($table);
    }
    require __DIR__.'/metier.php';
}
finally {
    $pdoTest = null;
    $serveur->exec("DROP DATABASE `$baseTest`");
}
