<article class="panel p-6"><div class="mb-5 flex items-start justify-between gap-3"><span style="background-color:<?= e(
    $project["color"],
) ?>" class="grid size-11 place-items-center rounded-xl text-lg font-bold text-white"><?= e(
    initial($project["name"]),
) ?></span><a class="text-xs text-slate-500 hover:text-indigo-600" href="<?= e(
    url("project-edit", ["id" => $project["id"]]),
) ?>">Modifier</a></div><h3 class="font-bold break-words"><a href="<?= e(
    url("tasks", ["project" => $project["id"]]),
) ?>"><?= e(
    $project["name"],
) ?></a></h3><p class="mt-2 min-h-10 break-words text-sm leading-5 text-slate-500"><?= e(
    $project["description"],
) ?></p><?php
$total = (int) $project["total"];
$done = (int) $project["completed"];
$progress = $total ? (int) round(($done / $total) * 100) : 0;
?><div class="mt-5 flex justify-between text-xs text-slate-500"><span><?= e(
    $done,
) ?> / <?= e($total) ?> tâches</span><span><?= e(
     $progress,
 ) ?> %</span></div><div role="progressbar" aria-label="Avancement de <?= e(
     $project["name"],
 ) ?>" aria-valuenow="<?= e(
    $progress,
) ?>" aria-valuemin="0" aria-valuemax="100" class="mt-2 h-1.5 overflow-hidden rounded-full bg-slate-100"><div class="h-full rounded-full" style="width:<?= e(
    $progress,
) ?>%;background-color:<?= e($project["color"]) ?>"></div></div></article>
