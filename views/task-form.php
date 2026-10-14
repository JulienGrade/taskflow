<a class="text-sm text-slate-500" href="<?= e(
    url("tasks"),
) ?>">← Mes tâches</a><h1 class="mt-4 text-3xl font-bold"><?= e(
    $title,
) ?></h1><?php require __DIR__ . "/partials/errors.php"; ?>
<?php if (
    !$projects
): ?><p class="panel mt-6 p-6">Créez d’abord <a class="text-indigo-600" href="<?= e(
    url("project-new"),
) ?>">un projet</a>.</p><?php else: ?>
<form method="post" class="panel mt-6 max-w-2xl space-y-5 p-6"><?= csrf_field() ?><div><label for="title">Titre de la tâche</label><input id="title" name="title" value="<?= e(
    $old["title"] ?? "",
) ?>" maxlength="150" required></div><div><label for="project_id">Projet</label><select id="project_id" name="project_id" required><?php foreach (
    $projects
    as $p
): ?><option value="<?= e($p["id"]) ?>" <?= (string) ($old["project_id"] ??
    "") === (string) $p["id"]
    ? "selected"
    : "" ?>><?= e(
    $p["name"],
) ?></option><?php endforeach; ?></select></div><div><label for="description">Description</label><textarea id="description" name="description" maxlength="2000" rows="4"><?= e(
    $old["description"] ?? "",
) ?></textarea></div><div class="grid gap-4 sm:grid-cols-3"><div><label for="status">Statut</label><select id="status" name="status"><?php foreach (
    ["todo", "doing", "done"]
    as $s
): ?><option value="<?= e($s) ?>" <?= ($old["status"] ?? "todo") === $s
    ? "selected"
    : "" ?>><?= e(
    status_label($s),
) ?></option><?php endforeach; ?></select></div><div><label for="priority">Priorité</label><select id="priority" name="priority"><?php foreach (
    ["low", "medium", "high"]
    as $p
): ?><option value="<?= e($p) ?>" <?= ($old["priority"] ?? "medium") === $p
    ? "selected"
    : "" ?>><?= e(
    priority_label($p),
) ?></option><?php endforeach; ?></select></div><div><label for="due_date">Échéance</label><input id="due_date" name="due_date" type="date" value="<?= e(
    $old["due_date"] ?? "",
) ?>"></div></div><div class="flex gap-3"><button class="btn">Enregistrer la tâche</button><a class="btn-secondary" href="<?= e(
    url("tasks"),
) ?>">Annuler</a></div></form><?php endif; ?>
<?php if (
    !empty($old["id"])
): ?><form class="mt-6 max-w-2xl rounded-xl border border-rose-200 bg-rose-50 p-5" method="post" action="<?= e(
    url("task-delete", ["id" => $old["id"]]),
) ?>"><?= csrf_field() ?><label class="flex items-center gap-2"><input class="max-w-5" type="checkbox" name="confirm" value="yes" required>Je confirme la suppression définitive de cette tâche.</label><button class="mt-3 text-sm font-semibold text-rose-700">Supprimer la tâche</button></form><?php endif; ?>
