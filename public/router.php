<?php
declare(strict_types=1);
// Le serveur intégré ne sert que des assets publics connus, jamais de fichier PHP arbitraire.
$path = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH) ?: "/";
if (
    preg_match('~^/assets/[a-zA-Z0-9_.-]+\.(css|js|svg)$~D', $path) &&
    is_file(__DIR__ . $path)
) {
    return false;
}
require __DIR__ . "/index.php";
