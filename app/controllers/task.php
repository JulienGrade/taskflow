<?php
declare(strict_types=1);
function task_controller_index(PDO $pdo): void
{
    $userId = $_SESSION["user"]["id"];
    $filters = [
        "q" => is_string($_GET["q"] ?? null) ? substr($_GET["q"], 0, 100) : "",
        "project" =>
            filter_var($_GET["project"] ?? "", FILTER_VALIDATE_INT) ?: "",
        "status" =>
            is_string($_GET["status"] ?? null) &&
            in_array($_GET["status"], ["todo", "doing", "done"], true)
                ? $_GET["status"]
                : "",
    ];
    render("tasks", [
        "title" => "Mes tâches",
        "tasks" => task_service_all($pdo, $userId, $filters),
        "projects" => project_service_all($pdo, $userId),
        "filters" => $filters,
    ]);
}
function task_controller_form(PDO $pdo, ?int $id): void
{
    $userId = $_SESSION["user"]["id"];
    $old = $id !== null ? task_service_find($pdo, $id, $userId) : [];
    $errors = [];
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $errors = task_service_save($pdo, $id, $userId, $_POST);
        if (!$errors) {
            flash("Enregistrement effectué.");
            go("tasks");
        }
        http_response_code(422);
        $old = array_replace($old, $_POST);
    }
    render("task-form", [
        "title" => $id !== null ? "Modifier : tâche" : "Créer : tâche",
        "old" => $old,
        "errors" => $errors,
        "projects" => project_service_all($pdo, $userId),
    ]);
}
function task_controller_delete(PDO $pdo, int $id): void
{
    if (($_POST["confirm"] ?? "") !== "yes") {
        abort_request(422, "Confirmez la suppression.");
    }
    task_service_delete($pdo, $id, $_SESSION["user"]["id"]);
    flash("Suppression effectuée.");
    go("tasks");
}
