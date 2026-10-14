<?php
declare(strict_types=1);
function project_controller_index(PDO $pdo): void
{
    $userId = $_SESSION["user"]["id"];
    render("projects", [
        "title" => "Mes projets",
        "projects" => project_service_all($pdo, $userId),
    ]);
}
function project_controller_form(PDO $pdo, ?int $id): void
{
    $userId = $_SESSION["user"]["id"];
    $old = $id !== null ? project_service_find($pdo, $id, $userId) : [];
    $errors = [];
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $errors = project_service_save($pdo, $id, $userId, $_POST);
        if (!$errors) {
            flash("Enregistrement effectué.");
            go("projects");
        }
        http_response_code(422);
        $old = array_replace($old, $_POST);
    }
    render("project-form", [
        "title" => $id !== null ? "Modifier : projet" : "Créer : projet",
        "old" => $old,
        "errors" => $errors,
    ]);
}
function project_controller_delete(PDO $pdo, int $id): void
{
    if (($_POST["confirm"] ?? "") !== "yes") {
        abort_request(422, "Confirmez la suppression.");
    }
    project_service_delete($pdo, $id, $_SESSION["user"]["id"]);
    flash("Suppression effectuée.");
    go("projects");
}
