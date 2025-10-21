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
<div class="bg-white border border-gray-200 rounded-xl shadow-sm">
    <?php if ($title || $description) : ?>
        <div class="px-6 py-4 border-b border-gray-200">
            <?php if ($title) : ?>
                <h2 class="text-lg font-semibold text-gray-900"><?= esc($title) ?></h2>
            <?php endif; ?>
            <?php if ($description) : ?>
                <p class="text-sm text-gray-500 mt-1"><?= esc($description) ?></p>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <?php if (! empty($normalizedHeaders)) : ?>
                <thead class="bg-gray-50">
                    <tr>
                        <?php foreach ($normalizedHeaders as $header) :
                            $label = $header['label'];
                            $align = $header['align'];
                            $extraClass = $header['class'];
                            $thClass = trim('px-6 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider ' . ($alignClasses[$align] ?? $alignClasses['left']) . ' ' . $extraClass);
                            ?>
                            <th scope="col" class="<?= esc($thClass) ?>">
                                <?= esc($label) ?>
                            </th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
            <?php endif; ?>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if (empty($rows)) : ?>
                    <tr>
                        <td colspan="<?= count($normalizedHeaders) ?>" class="px-6 py-4 text-sm text-gray-500 text-center">
                            <?= esc($emptyMessage) ?>
                        </td>
                    </tr>
                <?php else : ?>
                    <?php foreach ($rows as $row) :
                        $rowClass = trim($row['class'] ?? 'hover:bg-gray-50');
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
                                $tdClass = trim('px-6 py-4 whitespace-nowrap text-sm ' . ($alignClasses[$cellAlign] ?? $alignClasses['left']) . ' ' . $cellClass);
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
