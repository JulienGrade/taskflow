<?php
declare(strict_types=1);
function auth_controller_login(PDO $pdo): void
{
    if (isset($_SESSION["user"])) {
        go("dashboard");
    }
    $errors =
        $_SERVER["REQUEST_METHOD"] === "POST"
            ? auth_service_login($pdo, $_POST)
            : [];
    if ($_SERVER["REQUEST_METHOD"] === "POST" && !$errors) {
        go("dashboard");
    }
    if ($errors) {
        http_response_code(422);
    }
    render("login", [
        "title" => "Connexion",
        "errors" => $errors,
        "old" => $_POST,
    ]);
}
function auth_controller_logout(PDO $pdo): void
{
    auth_service_logout($pdo);
    go("login");
}
