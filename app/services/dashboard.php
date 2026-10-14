<?php
declare(strict_types=1);
function dashboard_service_summary(PDO $pdo, int $userId): array
{
    $tasks = task_service_all($pdo, $userId);
    $stats = ["todo" => 0, "doing" => 0, "done" => 0];
    foreach ($tasks as $task) {
        ++$stats[$task["status"]];
    }
    return [
        "projects" => project_service_all($pdo, $userId),
        "tasks" => $tasks,
        "stats" => $stats,
    ];
}
