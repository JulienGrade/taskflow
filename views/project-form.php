<a class="text-sm text-slate-500" href="<?= e(
    url("projects"),
) ?>">← Mes projets</a><h1 class="mt-4 text-3xl font-bold"><?= e(
    $title,
) ?></h1><?php require __DIR__ .
    "/partials/errors.php"; ?><form class="panel mt-6 max-w-2xl space-y-5 p-6" method="post"><?= csrf_field() ?><div><label for="name">Nom du projet</label><input id="name" name="name" value="<?= e(
    $old["name"] ?? "",
) ?>" maxlength="100" required></div><div><label for="description">Description</label><textarea id="description" name="description" rows="4" maxlength="2000"><?= e(
    $old["description"] ?? "",
) ?></textarea></div><div><label for="color">Couleur du projet</label><input id="color" name="color" type="color" value="<?= e(
    $old["color"] ?? "#6366f1",
) ?>" class="h-10 w-16 rounded border border-slate-200"></div><div class="flex gap-3"><button class="btn">Enregistrer le projet</button><a class="btn-secondary" href="<?= e(
    url("projects"),
) ?>">Annuler</a></div></form>
<?php if (
    !empty($old["id"])
): ?><form class="mt-6 max-w-2xl rounded-xl border border-rose-200 bg-rose-50 p-5" method="post" action="<?= e(
    url("project-delete", ["id" => $old["id"]]),
) ?>"><?= csrf_field() ?><p class="mb-3 text-sm text-rose-800">La suppression du projet efface toutes ses tâches.</p><label class="flex items-center gap-2"><input type="checkbox" name="confirm" value="yes" required class="max-w-5">Je confirme la suppression définitive.</label><button class="mt-3 text-sm font-semibold text-rose-700">Supprimer le projet</button></form><?php endif; ?>
