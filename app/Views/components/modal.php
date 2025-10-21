<?php
$modalId = $id ?? uniqid('modal-');
$title = $title ?? 'Detail';
$content = $content ?? '';
$contentView = $contentView ?? null;
$contentData = $contentData ?? [];
$trigger = $trigger ?? ['label' => 'Buka Modal'];
$actions = $actions ?? [];
$closeLabel = $closeLabel ?? 'Tutup';
?>
<div x-data="{ open: false }" x-cloak class="relative">
    <?= view('components/button', array_merge($trigger, [
        'variant' => $trigger['variant'] ?? 'primary',
        'attributes' => array_merge($trigger['attributes'] ?? [], ['@click' => 'open = true'])
    ])) ?>

    <div
        x-show="open"
        x-transition.opacity
        class="fixed inset-0 z-40 flex items-center justify-center px-4"
        @keydown.escape.window="open = false"
    >
        <div class="absolute inset-0 bg-gray-900 bg-opacity-50" @click="open = false"></div>

        <div
            x-show="open"
            x-transition.scale
            class="relative z-50 w-full max-w-xl bg-white rounded-xl shadow-2xl overflow-hidden"
            role="dialog"
            aria-modal="true"
            aria-labelledby="<?= esc($modalId) ?>-title"
        >
            <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                <h3 id="<?= esc($modalId) ?>-title" class="text-lg font-semibold text-gray-900">
                    <?= esc($title) ?>
                </h3>
                <button
                    type="button"
                    class="text-gray-400 hover:text-gray-600"
                    @click="open = false"
                    aria-label="Tutup"
                >
                    &times;
                </button>
            </div>

            <div class="px-6 py-5 space-y-3">
                <?php if ($contentView) : ?>
                    <?= view($contentView, $contentData) ?>
                <?php elseif (is_array($content)) : ?>
                    <?php foreach ($content as $paragraph) : ?>
                        <p class="text-sm text-gray-600">
                            <?= esc($paragraph) ?>
                        </p>
                    <?php endforeach; ?>
                <?php else : ?>
                    <p class="text-sm text-gray-600">
                        <?= esc($content) ?>
                    </p>
                <?php endif; ?>
            </div>

            <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-end gap-3">
                <?= view('components/button', [
                    'label' => $closeLabel,
                    'variant' => 'secondary',
                    'attributes' => ['@click' => 'open = false']
                ]) ?>
                <?php foreach ($actions as $action) :
                    $actionAttributes = $action['attributes'] ?? [];
                    if (($action['closesModal'] ?? false) && ! isset($actionAttributes['@click'])) {
                        $actionAttributes['@click'] = 'open = false';
                    }
                    echo view('components/button', array_merge($action, [
                        'variant' => $action['variant'] ?? 'primary',
                        'attributes' => $actionAttributes,
                    ]));
                endforeach; ?>
            </div>
        </div>
    </div>
</div>
