<?php
declare(strict_types=1);
function dashboard_controller_index(PDO $pdo): void
{
    render(
        "dashboard",
        ["title" => "Vue d’ensemble"] +
            dashboard_service_summary($pdo, $_SESSION["user"]["id"]),
    );
}
