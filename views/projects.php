<div class="mb-8 flex flex-wrap justify-between gap-4"><div><h1 class="text-3xl font-bold">Mes projets</h1><p class="mt-2 text-slate-500">Donnez une direction à vos idées.</p></div><a class="btn self-start" href="<?= e(
    url("project-new"),
) ?>">+ Nouveau projet</a></div><div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-3"><?php foreach (
    $projects
    as $project
):
    require __DIR__ . "/partials/project-card.php";
endforeach; ?></div><?php if (
    !$projects
): ?><div class="panel p-10 text-center"><h2 class="font-bold">Votre prochain projet commence ici.</h2><p class="mt-2 text-slate-500">Cliquez sur « Nouveau projet » pour vous lancer.</p></div><?php endif; ?>
