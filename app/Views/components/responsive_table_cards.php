<?php
/**
 * @var array<int, array<string, mixed>> $items
 */

$items = $items ?? [];
?>
<div class="space-y-4">
    <?php foreach ($items as $item):
        $title      = $item['title'] ?? null;
        $subtitle   = $item['subtitle'] ?? null;
        $badges     = $item['badges'] ?? [];
        $fields     = $item['fields'] ?? [];
        $actions    = $item['actions'] ?? [];
        $attributes = $item['attributes'] ?? [];

        $attributeString = '';

        if (is_string($attributes) && trim($attributes) !== '') {
            $attributeString = ' ' . trim($attributes);
        } elseif (is_array($attributes)) {
            foreach ($attributes as $attr => $value) {
                if (is_int($attr)) {
                    $attributeString .= ' ' . esc($value, 'attr');
                    continue;
                }

                $attributeString .= sprintf(' %s="%s"', esc($attr, 'attr'), esc((string) $value, 'attr'));
            }
        }
    ?>
        <div<?= $attributeString ?> class="rounded-2xl border border-slate-200/80 bg-white/80 p-5 shadow-sm shadow-slate-100/60 ring-1 ring-slate-200/70 dark:border-black/70 dark:bg-slate-950/70 dark:shadow-black/30 dark:ring-black/60">
            <?php if (! empty($title)): ?>
                <div class="text-base font-semibold text-slate-900 dark:text-slate-100"><?= esc($title) ?></div>
            <?php endif; ?>

            <?php if (! empty($subtitle)): ?>
                <div class="mt-1 text-sm text-slate-500 dark:text-slate-400"><?= esc($subtitle) ?></div>
            <?php endif; ?>

            <?php if (! empty($badges)): ?>
                <div class="mt-3 flex flex-wrap gap-2">
                    <?php foreach ($badges as $badge):
                        $badgeLabel = $badge['label'] ?? '';
                        $badgeClass = $badge['class'] ?? 'bg-slate-100 text-slate-700';
                        $badgeHtml  = $badge['isHtml'] ?? false;
                    ?>
                        <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold <?= esc($badgeClass) ?>">
                            <?php if ($badgeHtml): ?>
                                <?= $badgeLabel ?>
                            <?php else: ?>
                                <?= esc($badgeLabel) ?>
                            <?php endif; ?>
                        </span>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if (! empty($fields)): ?>
                <dl class="mt-4 space-y-3">
                    <?php foreach ($fields as $index => $field):
                        $label      = $field['label'] ?? '';
                        $value      = $field['value'] ?? '';
                        $isHtml     = $field['isHtml'] ?? false;
                        $hasDivider = $index > 0;
                    ?>
                        <div class="<?= $hasDivider ? 'border-t border-slate-100 pt-3 dark:border-slate-800' : '' ?>">
                            <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400"><?= esc($label) ?></dt>
                            <dd class="mt-1 text-sm text-slate-700 dark:text-slate-200">
                                <?php if ($isHtml): ?>
                                    <?= $value ?>
                                <?php else: ?>
                                    <?= esc($value) ?>
                                <?php endif; ?>
                            </dd>
                        </div>
                    <?php endforeach; ?>
                </dl>
            <?php endif; ?>

            <?php if (! empty($actions)): ?>
                <div class="mt-4 flex flex-wrap gap-3">
                    <?php foreach ($actions as $action) {
                        $actionHtml = $action['content'] ?? $action;
                        $isHtml     = $action['isHtml'] ?? ! is_string($actionHtml);

                        if (is_string($actionHtml)) {
                            echo $isHtml ? $actionHtml : esc($actionHtml);
                            continue;
                        }

                        if (is_callable($actionHtml)) {
                            echo $actionHtml();
                        }
                    } ?>
                </div>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
</div>
