<?php
declare(strict_types=1);
function task_service_all(PDO $pdo, int $userId, array $filters = []): array
{
    return task_repository_all($pdo, $userId, $filters);
}
function task_service_find(PDO $pdo, int $id, int $userId): array
{
    return task_repository_find($pdo, $id, $userId) ??
        abort_request(404, "Tâche introuvable.");
}
function task_service_save(PDO $pdo, ?int $id, int $userId, array $input): array
{
    if ($id !== null) {
        task_service_find($pdo, $id, $userId);
    }
    $data = [];
    foreach (
        ["title", "description", "status", "priority", "due_date", "project_id"]
        as $field
    ) {
        $data[$field] = validation_text($input, $field);
    }
    $errors = [];
    if ($data["title"] === "" || validation_length($data["title"]) > 150) {
        $errors[] = "Le titre doit contenir entre 1 et 150 caractères.";
    }
    if (validation_length($data["description"]) > 2000) {
        $errors[] = "La description est limitée à 2 000 caractères.";
    }
    if (!in_array($data["status"], ["todo", "doing", "done"], true)) {
        $errors[] = "Le statut est invalide.";
    }
    if (!in_array($data["priority"], ["low", "medium", "high"], true)) {
        $errors[] = "La priorité est invalide.";
    }
    if ($data["due_date"] !== "" && !validation_date($data["due_date"])) {
        $errors[] = "L’échéance doit être une date valide.";
    }
    if (
        !ctype_digit($data["project_id"]) ||
        !in_array(
            (int) $data["project_id"],
            array_map(
                fn(array $p): int => (int) $p["id"],
                project_service_all($pdo, $userId),
            ),
            true,
        )
    ) {
        $errors[] = "Sélectionnez un projet vous appartenant.";
    }
    if (!$errors) {
        task_repository_save($pdo, $id, $userId, $data);
    }
    return $errors;
}
function task_service_delete(PDO $pdo, int $id, int $userId): void
{
    task_service_find($pdo, $id, $userId);
    task_repository_delete($pdo, $id, $userId);
}
