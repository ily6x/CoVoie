<?php

declare(strict_types = 1);
require __DIR__ . '/includes/config.php';
require __DIR__ . '/includes/database.php';
require __DIR__ . '/includes/functions.php';
spl_autoload_register(function ($className) {
    foreach (['models', 'controllers'] as $directory) {
        $filePath = __DIR__ . "/$directory/$className.php";
        if (is_file($filePath)) {
            require $filePath;
            return;
        }
    }
});
try {
    $page = inputText($_GET, 'page', 'accueil');
    $routes = require __DIR__ . '/includes/routes.php';
    if (!isset($routes[$page])) {
        throw new HttpException('Page introuvable.', 404);
    }
    [$className, $method, $isWrite] = $routes[$page];
    if ($isWrite) {
        requirePost();
    }
    (new $className())->$method();
}
catch (HttpException $e) {
    http_response_code($e->getCode());
    render('error', ['titre' => 'Erreur', 'erreur' => $e->getMessage()]);
}
catch (DomainException $e) {
    http_response_code(422);
    render('error', ['titre' => 'Action impossible', 'erreur' => $e->getMessage()]);
}
catch (Throwable $e) {
    error_log((string) $e);
    http_response_code(503);
    render('error', ['titre' => 'Service indisponible', 'serviceIndisponible' => true, 'erreur' => 'Le service est indisponible. Vérifiez MySQL et l’installation décrite dans le README.']);
}
