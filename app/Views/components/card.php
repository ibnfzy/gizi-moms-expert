<?php
$variant = $variant ?? 'default';
$borderClass = $variant === 'primary' ? 'border-blue-200' : 'border-gray-200';
$badgeText = $badgeText ?? null;
$actions = $actions ?? [];
?>
<div class="bg-white border <?= esc($borderClass) ?> rounded-xl shadow-sm p-6 space-y-4">
    <?php if (! empty($badgeText)) : ?>
        <span class="inline-flex items-center px-3 py-1 text-xs font-medium text-blue-700 bg-blue-50 rounded-full">
            <?= esc($badgeText) ?>
        </span>
    <?php endif; ?>

    <?php if (! empty($title)) : ?>
        <h3 class="text-lg font-semibold text-gray-900"><?= esc($title) ?></h3>
    <?php endif; ?>

    <?php if (! empty($description)) : ?>
        <p class="text-sm text-gray-600 leading-relaxed"><?= esc($description) ?></p>
    <?php endif; ?>

    <?php if (! empty($actions)) : ?>
        <div class="pt-2 flex flex-wrap gap-3">
            <?php foreach ($actions as $action) : ?>
                <a
                    href="<?= esc($action['href'] ?? '#', 'attr') ?>"
                    class="inline-flex items-center px-4 py-2 text-sm font-medium rounded-md shadow-sm transition
                        <?= ($action['type'] ?? '') === 'primary'
                            ? 'bg-blue-500 text-white hover:bg-blue-600'
                            : 'bg-gray-100 text-gray-700 hover:bg-gray-200' ?>"
                >
                    <?= esc($action['label'] ?? 'Aksi') ?>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
