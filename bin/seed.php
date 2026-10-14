<?php
declare(strict_types=1);
// Script CLI volontairement identique dans les deux projets.
if (PHP_SAPI !== "cli") {
    exit(1);
}
$pdo = new PDO(
    sprintf(
        "pgsql:host=%s;port=%s;dbname=%s",
        getenv("DB_HOST") ?: "db",
        getenv("DB_PORT") ?: "5432",
        getenv("DB_NAME") ?: "taskflow",
    ),
    getenv("DB_USER") ?: "taskflow",
    getenv("DB_PASSWORD") ?: "taskflow_local",
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION],
);
$pdo->beginTransaction();
try {
    $pdo->exec("SELECT pg_advisory_xact_lock(19082026)");
    if (in_array("--reset", $argv, true)) {
        $pdo->exec("TRUNCATE tasks, projects, users RESTART IDENTITY CASCADE");
    }
    if ((int) $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn() > 0) {
        $pdo->commit();
        echo "Base déjà remplie : aucune modification.\n";
        exit();
    }
    $users = [
        ["Julien Grade", "julien@taskflow.test"],
        ["Camille Martin", "camille@taskflow.test"],
    ];
    $projects = [
        [
            "Refonte du site web",
            "Une nouvelle expérience pour nos clients.",
            "#6366f1",
        ],
        [
            "Application mobile",
            "Concevoir et livrer notre application de suivi.",
            "#0d9488",
        ],
        [
            "Formation PHP",
            "Un parcours pratique du procédural à la POO.",
            "#db2777",
        ],
        [
            "Infrastructure cloud",
            "Fiabiliser les environnements et les sauvegardes.",
            "#d97706",
        ],
        [
            "Lancement produit",
            "Préparer la communication et la mise en service.",
            "#2563eb",
        ],
    ];
    $titles = [
        "Recueillir les besoins",
        "Définir les critères de réussite",
        "Préparer les maquettes",
        "Valider la structure des données",
        "Réaliser la première version",
        "Ajouter les validations",
        "Tester les parcours principaux",
        "Préparer la démonstration",
    ];
    foreach ($users as [$name, $email]) {
        $stmt = $pdo->prepare(
            "INSERT INTO users(name,email,password) VALUES (?,?,?) RETURNING id",
        );
        $stmt->execute([
            $name,
            $email,
            password_hash("Demo1234!", PASSWORD_DEFAULT),
        ]);
        $userId = $stmt->fetchColumn();
        foreach ($projects as $p => [$projectName, $description, $color]) {
            $stmt = $pdo->prepare(
                "INSERT INTO projects(user_id,name,description,color) VALUES (?,?,?,?) RETURNING id",
            );
            $stmt->execute([$userId, $projectName, $description, $color]);
            $projectId = $stmt->fetchColumn();
            foreach ($titles as $i => $title) {
                $status = [
                    "done",
                    "done",
                    "doing",
                    "doing",
                    "todo",
                    "todo",
                    "todo",
                    "todo",
                ][$i];
                $priority = ["high", "medium", "low"][($i + $p) % 3];
                $date = (new DateTimeImmutable("today"))
                    ->modify(sprintf("%+d days", $i * 3 + $p - 5))
                    ->format("Y-m-d");
                $stmt = $pdo->prepare(
                    "INSERT INTO tasks(project_id,title,description,status,priority,due_date) VALUES (?,?,?,?,?,?)",
                );
                $stmt->execute([
                    $projectId,
                    $title,
                    "Livrable attendu : un résultat vérifiable et documenté pour « " .
                    $projectName .
                    " ».",
                    $status,
                    $priority,
                    $date,
                ]);
            }
        }
    }
    $pdo->commit();
    echo "Démonstration créée : 2 comptes, 10 projets, 80 tâches.\n";
} catch (Throwable $e) {
    $pdo->rollBack();
    throw $e;
}
