<?php
declare(strict_types=1);
function task_repository_all(PDO $pdo, int $userId, array $filters = []): array
{
    $sql =
        "SELECT t.*, p.name AS project_name FROM tasks t JOIN projects p ON p.id=t.project_id WHERE p.user_id=?";
    $params = [$userId];
    if (!empty($filters["project"])) {
        $sql .= " AND t.project_id=?";
        $params[] = $filters["project"];
    }
    if (!empty($filters["status"])) {
        $sql .= " AND t.status=?";
        $params[] = $filters["status"];
    }
    if (!empty($filters["q"])) {
        $sql .= " AND t.title ILIKE ?";
        $params[] = "%" . $filters["q"] . "%";
    }
    $sql .=
        " ORDER BY CASE t.status WHEN 'doing' THEN 0 WHEN 'todo' THEN 1 ELSE 2 END, t.due_date ASC NULLS LAST, t.id DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}
function task_repository_find(PDO $pdo, int $id, int $userId): ?array
{
    $stmt = $pdo->prepare(
        "SELECT t.* FROM tasks t JOIN projects p ON p.id=t.project_id WHERE t.id=? AND p.user_id=?",
    );
    $stmt->execute([$id, $userId]);
    return $stmt->fetch() ?: null;
}
function task_repository_save(
    PDO $pdo,
    ?int $id,
    int $userId,
    array $data,
): void {
    $params = [
        $data["title"],
        $data["description"],
        $data["status"],
        $data["priority"],
        $data["due_date"] ?: null,
        $data["project_id"],
    ];
    if ($id !== null) {
        $stmt = $pdo->prepare(
            "UPDATE tasks SET title=?,description=?,status=?,priority=?,due_date=?,project_id=? WHERE id=? AND project_id IN (SELECT id FROM projects WHERE user_id=?)",
        );
        $stmt->execute([...$params, $id, $userId]);
    } else {
        $stmt = $pdo->prepare(
            "INSERT INTO tasks(title,description,status,priority,due_date,project_id) SELECT ?,?,?,?,?,? WHERE EXISTS (SELECT 1 FROM projects WHERE id=? AND user_id=?)",
        );
        $stmt->execute([...$params, $data["project_id"], $userId]);
    }
}
function task_repository_delete(PDO $pdo, int $id, int $userId): void
{
    $stmt = $pdo->prepare(
        "DELETE FROM tasks WHERE id=? AND project_id IN (SELECT id FROM projects WHERE user_id=?)",
    );
    $stmt->execute([$id, $userId]);
}
