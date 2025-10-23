<?php
$headers = $headers ?? [];
$rows = $rows ?? [];
$title = $title ?? null;
$description = $description ?? null;
$emptyMessage = $emptyMessage ?? 'Data belum tersedia.';

$alignClasses = [
    'left' => 'text-left',
    'center' => 'text-center',
    'right' => 'text-right',
];

$normalizedHeaders = array_map(static function ($header) {
    if (is_array($header)) {
        return [
            'label' => $header['label'] ?? '',
            'align' => $header['align'] ?? 'left',
            'class' => $header['class'] ?? '',
        ];
    }

    return [
        'label' => $header,
        'align' => 'left',
        'class' => '',
    ];
}, $headers);
?>
<div class="rounded-xl border border-gray-200 bg-white shadow-sm shadow-slate-100 dark:border-black/70 dark:bg-slate-950/60 dark:shadow-black/40">
    <?php if ($title || $description) : ?>
        <div class="border-b border-gray-200 px-6 py-4 dark:border-black/70">
            <?php if ($title) : ?>
                <h2 class="text-lg font-semibold text-gray-900 dark:text-slate-100"><?= esc($title) ?></h2>
            <?php endif; ?>
            <?php if ($description) : ?>
                <p class="mt-1 text-sm text-gray-500 dark:text-slate-400"><?= esc($description) ?></p>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <div class="overflow-x-auto">
        <table class="min-w-full border-collapse border border-black dark:border-gray-300 dark:bg-slate-950/40">
            <?php if (! empty($normalizedHeaders)) : ?>
                <thead class="bg-gray-50 dark:bg-slate-950/60">
                    <tr>
                        <?php foreach ($normalizedHeaders as $header) :
                            $label = $header['label'];
                            $align = $header['align'];
                            $extraClass = $header['class'];
                            $thClass = trim('border border-black px-6 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider dark:border-gray-300 dark:text-slate-300 ' . ($alignClasses[$align] ?? $alignClasses['left']) . ' ' . $extraClass);
                            ?>
                            <th scope="col" class="<?= esc($thClass) ?>">
                                <?= esc($label) ?>
                            </th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
            <?php endif; ?>
            <tbody class="bg-white dark:bg-slate-950/30 dark:text-slate-200">
                <?php if (empty($rows)) : ?>
                    <tr>
                        <td colspan="<?= count($normalizedHeaders) ?>" class="border border-black px-6 py-4 text-center text-sm text-gray-500 dark:border-gray-300 dark:text-slate-400">
                            <?= esc($emptyMessage) ?>
                        </td>
                    </tr>
                <?php else : ?>
                    <?php foreach ($rows as $row) :
                        $rowClass = trim($row['class'] ?? 'hover:bg-gray-50 dark:hover:bg-slate-900/60');
                        $cells = $row['cells'] ?? [];
                        ?>
                        <tr class="<?= esc($rowClass) ?>">
                            <?php foreach ($cells as $index => $cell) :
                                if (is_array($cell)) {
                                    $content = $cell['content'] ?? ($cell['text'] ?? '');
                                    $raw = $cell['raw'] ?? false;
                                    $cellClass = $cell['class'] ?? '';
                                    $cellAlign = $cell['align'] ?? ($normalizedHeaders[$index]['align'] ?? 'left');
                                } else {
                                    $content = $cell;
                                    $raw = false;
                                    $cellClass = '';
                                    $cellAlign = $normalizedHeaders[$index]['align'] ?? 'left';
                                }
                                $tdClass = trim('border border-black px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:border-gray-300 dark:text-slate-200 ' . ($alignClasses[$cellAlign] ?? $alignClasses['left']) . ' ' . $cellClass);
                                ?>
                                <td class="<?= esc($tdClass) ?>">
                                    <?php if ($raw) : ?>
                                        <?= $content ?>
                                    <?php else : ?>
                                        <?= esc($content) ?>
                                    <?php endif; ?>
                                </td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
