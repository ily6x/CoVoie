<?php

declare(strict_types = 1);
const NOM_APPLICATION = 'CoVoiturage Campus';
date_default_timezone_set('Europe/Paris');
ini_set('session.use_strict_mode', '1');
session_set_cookie_params([
    'httponly' => true,
    'samesite' => 'Lax',
    'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'
]);
if (PHP_SAPI !== 'cli' && session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
if (PHP_SAPI === 'cli') {
    $_SESSION ??= [];
}
