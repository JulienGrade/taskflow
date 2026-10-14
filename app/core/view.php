<?php
declare(strict_types=1);
function render(string $template, array $data = []): void
{
    extract($data, EXTR_SKIP);
    $route = is_string($_GET["route"] ?? null) ? $_GET["route"] : "dashboard";
    $viewFile = dirname(__DIR__, 2) . "/views/" . $template . ".php";
    require dirname(__DIR__, 2) . "/views/layout.php";
}
function abort_request(int $status, string $message): never
{
    http_response_code($status);
    render("error", [
        "title" => "Erreur",
        "code" => $status,
        "message" => $message,
    ]);
    exit();
}
