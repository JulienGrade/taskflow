<?php
declare(strict_types=1);
function project_repository_all(PDO $pdo, int $userId): array
{
    $stmt = $pdo->prepare(
        "SELECT p.*, COUNT(t.id) AS total, COUNT(t.id) FILTER (WHERE t.status='done') AS completed FROM projects p LEFT JOIN tasks t ON t.project_id=p.id WHERE p.user_id=? GROUP BY p.id ORDER BY p.id DESC",
    );
    $stmt->execute([$userId]);
    return $stmt->fetchAll();
}
function project_repository_find(PDO $pdo, int $id, int $userId): ?array
{
    $stmt = $pdo->prepare("SELECT * FROM projects WHERE id=? AND user_id=?");
    $stmt->execute([$id, $userId]);
    return $stmt->fetch() ?: null;
}
function project_repository_save(
    PDO $pdo,
    ?int $id,
    int $userId,
    array $data,
): void {
    if ($id !== null) {
        $stmt = $pdo->prepare(
            "UPDATE projects SET name=?,description=?,color=? WHERE id=? AND user_id=?",
        );
        $stmt->execute([
            $data["name"],
            $data["description"],
            $data["color"],
            $id,
            $userId,
        ]);
    } else {
        $stmt = $pdo->prepare(
            "INSERT INTO projects(name,description,color,user_id) VALUES (?,?,?,?)",
        );
        $stmt->execute([
            $data["name"],
            $data["description"],
            $data["color"],
            $userId,
        ]);
    }
}
function project_repository_delete(PDO $pdo, int $id, int $userId): void
{
    $stmt = $pdo->prepare("DELETE FROM projects WHERE id=? AND user_id=?");
    $stmt->execute([$id, $userId]);
}
