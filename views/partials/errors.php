<?php if (
    !empty($errors)
): ?><div role="alert" class="my-5 rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm leading-6 text-rose-700"><?php foreach (
    $errors
    as $error
): ?><p><?= e($error) ?></p><?php endforeach; ?></div><?php endif; ?>
