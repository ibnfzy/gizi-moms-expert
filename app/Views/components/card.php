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
            <?php foreach ($actions as $action) :
                $variantMap = [
                    'primary' => 'primary',
                    'danger' => 'danger',
                ];
                $variant = $variantMap[$action['type'] ?? ''] ?? 'secondary';
                echo view('components/button', [
                    'label' => $action['label'] ?? 'Aksi',
                    'variant' => $variant,
                    'href' => $action['href'] ?? null,
                    'attributes' => $action['attributes'] ?? [],
                ]);
            endforeach; ?>
        </div>
    <?php endif; ?>
</div>
