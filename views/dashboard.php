<div class="mb-8 flex flex-wrap items-end justify-between gap-4"><div><p class="mb-2 text-xs font-bold tracking-widest text-indigo-600">VUE D’ENSEMBLE</p><h1 class="text-3xl font-bold tracking-tight">Bonjour <?= e(
    explode(" ", $_SESSION["user"]["name"])[0],
) ?>.</h1><p class="mt-2 text-slate-500">Une nouvelle journée pour faire avancer vos idées.</p></div><a class="btn" href="<?= e(
    url("task-new"),
) ?>">+ Nouvelle tâche</a></div>
<div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4"><?php foreach (
    [
        ["Projets actifs", count($projects), "text-indigo-600"],
        ["Tâches à faire", $stats["todo"], "text-slate-800"],
        ["En cours", $stats["doing"], "text-amber-600"],
        ["Terminées", $stats["done"], "text-emerald-600"],
    ]
    as [$label, $value, $color]
): ?><div class="panel p-6"><p class="text-sm text-slate-500"><?= e(
    $label,
) ?></p><p class="mt-3 text-4xl font-bold <?= e($color) ?>"><?= e(
    $value,
) ?></p></div><?php endforeach; ?></div>
<div class="mt-8 grid gap-7 xl:grid-cols-[1.4fr_1fr]"><section><div class="mb-4 flex justify-between"><h2 class="text-lg font-bold">Vos projets</h2><a class="text-sm font-semibold text-indigo-600" href="<?= e(
    url("projects"),
) ?>">Tout voir →</a></div><div class="grid gap-4 sm:grid-cols-2"><?php
foreach (array_slice($projects, 0, 4) as $project):
    require __DIR__ . "/partials/project-card.php";
endforeach;
if (
    !$projects
): ?><p class="panel p-6 text-slate-500">Créez votre premier projet pour commencer.</p><?php endif;
?></div></section><section><h2 class="mb-4 text-lg font-bold">À suivre en priorité</h2><div class="panel divide-y divide-slate-100"><?php
foreach (
    array_slice(
        array_values(array_filter($tasks, fn($t) => $t["status"] !== "done")),
        0,
        6,
    )
    as $task
): ?><a class="block p-4 hover:bg-slate-50" href="<?= e(
    url("task-edit", ["id" => $task["id"]]),
) ?>"><p class="text-sm font-semibold"><?= e(
    $task["title"],
) ?></p><div class="mt-2 flex justify-between gap-2 text-xs text-slate-400"><span><?= e(
    $task["project_name"],
) ?></span><span><?= e(
    date_label($task["due_date"]),
) ?></span></div></a><?php endforeach;
if (
    $stats["todo"] + $stats["doing"] ===
    0
): ?><p class="p-6 text-sm text-slate-500">Tout est à jour. Beau travail !</p><?php endif;
?></div></section></div>
