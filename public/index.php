<?php
declare(strict_types=1);

ini_set("session.use_strict_mode", "1");
// Les noms différents permettent d’ouvrir les deux démos sur localhost simultanément.
session_name("taskflow_proc");
session_set_cookie_params([
    "httponly" => true,
    "samesite" => "Lax",
    "secure" => !empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off",
]);
session_start();
$_SESSION["csrf"] ??= bin2hex(random_bytes(32));
header(
    "Content-Security-Policy: default-src 'self'; style-src 'self' 'unsafe-inline'; form-action 'self'; frame-ancestors 'none'; base-uri 'self'",
);
header("X-Content-Type-Options: nosniff");
header("Referrer-Policy: same-origin");
header("Cache-Control: no-store");
require dirname(__DIR__) . "/views/helpers.php";
require dirname(__DIR__) . "/app/core/database.php";
require dirname(__DIR__) . "/app/core/view.php";
require dirname(__DIR__) . "/app/repositories/user.php";
require dirname(__DIR__) . "/app/repositories/project.php";
require dirname(__DIR__) . "/app/repositories/task.php";
require dirname(__DIR__) . "/app/services/validation.php";
require dirname(__DIR__) . "/app/services/auth.php";
require dirname(__DIR__) . "/app/services/project.php";
require dirname(__DIR__) . "/app/services/task.php";
require dirname(__DIR__) . "/app/services/dashboard.php";
require dirname(__DIR__) . "/app/controllers/auth.php";
require dirname(__DIR__) . "/app/controllers/project.php";
require dirname(__DIR__) . "/app/controllers/task.php";
require dirname(__DIR__) . "/app/controllers/dashboard.php";
try {
    $route = is_string($_GET["route"] ?? null) ? $_GET["route"] : "dashboard";
    $routes = [
        "login" => ["GET", "POST"],
        "logout" => ["POST"],
        "dashboard" => ["GET"],
        "projects" => ["GET"],
        "project-new" => ["GET", "POST"],
        "project-edit" => ["GET", "POST"],
        "project-delete" => ["POST"],
        "tasks" => ["GET"],
        "task-new" => ["GET", "POST"],
        "task-edit" => ["GET", "POST"],
        "task-delete" => ["POST"],
    ];
    if (!isset($routes[$route])) {
        abort_request(404, "Page introuvable.");
    }
    if (!in_array($_SERVER["REQUEST_METHOD"], $routes[$route], true)) {
        header("Allow: " . implode(", ", $routes[$route]));
        abort_request(405, "Méthode non autorisée.");
    }
    if ($route !== "login" && !isset($_SESSION["user"])) {
        go("login");
    }
    if (
        $_SERVER["REQUEST_METHOD"] === "POST" &&
        (!is_string($_POST["_token"] ?? null) ||
            !hash_equals($_SESSION["csrf"], $_POST["_token"]))
    ) {
        abort_request(419, "Le formulaire a expiré. Rechargez la page.");
    }
    foreach ($_POST as $value) {
        if (!is_string($value)) {
            abort_request(422, "Les champs doivent être des valeurs simples.");
        }
    }
    $id = null;
    if (str_ends_with($route, "-edit") || str_ends_with($route, "-delete")) {
        $id = filter_var($_GET["id"] ?? null, FILTER_VALIDATE_INT, [
            "options" => ["min_range" => 1],
        ]);
        if (!$id) {
            abort_request(404, "Identifiant invalide.");
        }
    }

    $pdo = database_connect();
    match ($route) {
        "login" => auth_controller_login($pdo),
        "logout" => auth_controller_logout($pdo),
        "dashboard" => dashboard_controller_index($pdo),
        "projects" => project_controller_index($pdo),
        "project-new" => project_controller_form($pdo, null),
        "project-edit" => project_controller_form($pdo, $id),
        "project-delete" => project_controller_delete($pdo, $id),
        "tasks" => task_controller_index($pdo),
        "task-new" => task_controller_form($pdo, null),
        "task-edit" => task_controller_form($pdo, $id),
        "task-delete" => task_controller_delete($pdo, $id),
    };
} catch (Throwable $e) {
    error_log((string) $e);
    abort_request(
        500,
        "Une erreur est survenue. Consultez les journaux du serveur.",
    );
}
