<!doctype html>
<html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title><?= e(
    $title,
) ?> · TaskFlow</title><link rel="stylesheet" href="/assets/app.css"></head>
<body><a href="#content" class="sr-only focus:not-sr-only">Aller au contenu</a>
<?php if (isset($_SESSION["user"])): ?>
<div class="min-h-screen lg:grid lg:grid-cols-[240px_1fr]">
<aside class="flex flex-col bg-slate-950 px-5 py-7 text-white lg:sticky lg:top-0 lg:h-screen">
<a class="mb-8 flex items-center gap-3 text-2xl font-bold tracking-tight" href="<?= e(
    url("dashboard"),
) ?>"><span class="grid size-10 place-items-center rounded-xl bg-indigo-500 text-xl">T</span> TaskFlow<span class="text-indigo-400">.</span></a>
<p class="mb-3 px-4 text-[10px] font-bold tracking-[.18em] text-slate-500">VOTRE ESPACE DE TRAVAIL</p>
<nav aria-label="Navigation principale" class="flex flex-wrap gap-2 lg:flex-col"><a class="nav-link <?= $route ===
"dashboard"
    ? "bg-slate-800 text-white"
    : "" ?>" href="<?= e(
    url("dashboard"),
) ?>">Vue d’ensemble</a><a class="nav-link <?= str_starts_with(
    $route,
    "project",
)
    ? "bg-slate-800 text-white"
    : "" ?>" href="<?= e(
    url("projects"),
) ?>">Mes projets</a><a class="nav-link <?= str_starts_with($route, "task")
    ? "bg-slate-800 text-white"
    : "" ?>" href="<?= e(url("tasks")) ?>">Mes tâches</a></nav>
<div class="mt-8 rounded-2xl border border-slate-800 bg-slate-900 p-4 lg:mt-auto"><p class="text-sm font-semibold">Une idée. Un projet.</p><p class="mt-2 text-xs leading-5 text-slate-400">Faites avancer ce qui compte, une tâche à la fois.</p></div>
</aside><div class="min-w-0"><header class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-200 bg-white px-6 py-5 lg:px-10"><span class="text-sm text-slate-500">Votre organisation, en toute simplicité</span><div class="flex items-center gap-4"><span class="grid size-9 place-items-center rounded-full bg-indigo-100 font-bold text-indigo-600"><?= e(
    initial($_SESSION["user"]["name"]),
) ?></span><span class="text-sm font-semibold"><?= e(
    $_SESSION["user"]["name"],
) ?></span><form method="post" action="<?= e(
    url("logout"),
) ?>"><?= csrf_field() ?><button class="text-xs text-slate-500 hover:text-indigo-600">Déconnexion</button></form></div></header><main id="content" class="mx-auto max-w-7xl px-5 py-8 lg:px-10">
<?php else: ?><main id="content" class="flex min-h-screen items-center justify-center px-5 py-12"><?php endif; ?>
<div class="w-full"><?php if (
    isset($_SESSION["flash"])
): ?><div role="status" class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800"><?= e(
    $_SESSION["flash"],
) ?></div><?php unset($_SESSION["flash"]);endif; ?>
<?php require $viewFile; ?></div></main><?php if (
    isset($_SESSION["user"])
): ?></div></div><?php endif; ?></body></html>
