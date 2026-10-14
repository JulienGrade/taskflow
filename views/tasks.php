<div class="mb-8 flex flex-wrap justify-between gap-4"><div><h1 class="text-3xl font-bold">Mes tâches</h1><p class="mt-2 text-slate-500">Une vue claire sur vos prochaines actions.</p></div><a class="btn self-start" href="<?= e(
    url("task-new"),
) ?>">+ Nouvelle tâche</a></div>
<form method="get" class="panel mb-6 grid gap-3 p-4 md:grid-cols-[2fr_1fr_1fr_auto]"><input type="hidden" name="route" value="tasks"><div><label for="q">Rechercher</label><input id="q" name="q" placeholder="Rechercher une tâche…" maxlength="100" value="<?= e(
    $filters["q"],
) ?>"></div><div><label for="project">Projet</label><select id="project" name="project"><option value="">Tous les projets</option><?php foreach (
    $projects
    as $p
): ?><option value="<?= e($p["id"]) ?>" <?= (string) $filters["project"] ===
(string) $p["id"]
    ? "selected"
    : "" ?>><?= e(
    $p["name"],
) ?></option><?php endforeach; ?></select></div><div><label for="status">Statut</label><select id="status" name="status"><option value="">Tous les statuts</option><?php foreach (
    ["todo", "doing", "done"]
    as $s
): ?><option value="<?= e($s) ?>" <?= $filters["status"] === $s
    ? "selected"
    : "" ?>><?= e(
    status_label($s),
) ?></option><?php endforeach; ?></select></div><button class="btn self-end">Filtrer</button></form>
<p class="mb-3 text-sm text-slate-500"><?= count(
    $tasks,
) ?> tâche(s) trouvée(s) · <a class="text-indigo-600" href="<?= e(
     url("tasks"),
 ) ?>">Réinitialiser les filtres</a></p>
<div class="panel overflow-x-auto"><table class="w-full min-w-[700px] text-left text-sm"><thead class="border-b border-slate-200 bg-slate-50 text-xs text-slate-500"><tr><th class="px-5 py-4">Tâche / Projet</th><th class="px-5 py-4">Statut</th><th class="px-5 py-4">Priorité</th><th class="px-5 py-4">Échéance</th><th class="px-5 py-4">Action</th></tr></thead><tbody class="divide-y divide-slate-100"><?php foreach (
    $tasks
    as $task
): ?><tr class="hover:bg-slate-50"><td class="px-5 py-4"><a class="font-semibold hover:text-indigo-600" href="<?= e(
    url("task-edit", ["id" => $task["id"]]),
) ?>"><?= e($task["title"]) ?></a><p class="mt-1 text-xs text-slate-400"><?= e(
    $task["project_name"],
) ?></p></td><td class="px-5 py-4"><span class="badge <?= e(
    status_class($task["status"]),
) ?>"><?= e(
    status_label($task["status"]),
) ?></span></td><td class="px-5 py-4"><span class="badge <?= e(
    priority_class($task["priority"]),
) ?>"><?= e(
    priority_label($task["priority"]),
) ?></span></td><td class="px-5 py-4 <?= $task["due_date"] &&
$task["due_date"] < date("Y-m-d") &&
$task["status"] !== "done"
    ? "font-semibold text-rose-600"
    : "text-slate-500" ?>"><?= e(
    date_label($task["due_date"]),
) ?></td><td class="px-5 py-4"><a class="text-indigo-600" href="<?= e(
    url("task-edit", ["id" => $task["id"]]),
) ?>">Modifier</a></td></tr><?php endforeach; ?></tbody></table><?php if (
    !$tasks
): ?><p class="p-10 text-center text-slate-500">Aucune tâche ne correspond à votre recherche.</p><?php endif; ?></div>
