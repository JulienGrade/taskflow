<?php
declare(strict_types=1);
function project_service_all(PDO $pdo, int $userId): array
{
    return project_repository_all($pdo, $userId);
}
function project_service_find(PDO $pdo, int $id, int $userId): array
{
    return project_repository_find($pdo, $id, $userId) ??
        abort_request(404, "Projet introuvable.");
}
function project_service_save(
    PDO $pdo,
    ?int $id,
    int $userId,
    array $input,
): array {
    if ($id !== null) {
        project_service_find($pdo, $id, $userId);
    }
    $data = [
        "name" => validation_text($input, "name"),
        "description" => validation_text($input, "description"),
        "color" => validation_text($input, "color"),
    ];
    $errors = [];
    if ($data["name"] === "" || validation_length($data["name"]) > 100) {
        $errors[] = "Le nom doit contenir entre 1 et 100 caractères.";
    }
    if (validation_length($data["description"]) > 2000) {
        $errors[] = "La description est limitée à 2 000 caractères.";
    }
    if (!preg_match('/^#[0-9a-fA-F]{6}$/D', $data["color"])) {
        $errors[] =
            "La couleur doit être au format hexadécimal, par exemple #6366f1.";
    }
    if (!$errors) {
        project_repository_save($pdo, $id, $userId, $data);
    }
    return $errors;
}
function project_service_delete(PDO $pdo, int $id, int $userId): void
{
    project_service_find($pdo, $id, $userId);
    project_repository_delete($pdo, $id, $userId);
}
