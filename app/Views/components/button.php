<?php
$variant = $variant ?? 'primary';
$label = $label ?? 'Button';
$tag = isset($href) ? 'a' : 'button';
$type = $type ?? 'button';
$attributes = $attributes ?? [];

$baseClasses = 'inline-flex items-center justify-center rounded-lg border px-4 py-2 text-sm font-medium shadow-sm transition focus:outline-none focus:ring-2 focus:ring-offset-2';
$variants = [
    'primary' => 'border-blue-600 bg-blue-600 text-white shadow-blue-100 hover:bg-blue-700 hover:border-blue-700 focus:ring-blue-500',
    'danger' => 'border-red-600 bg-red-600 text-white shadow-red-100 hover:bg-red-700 hover:border-red-700 focus:ring-red-500',
    'secondary' => 'border-gray-300 bg-white text-gray-700 hover:bg-gray-50 hover:border-gray-400 focus:ring-gray-400',
];

$variantClass = $variants[$variant] ?? $variants['primary'];
$classAttribute = trim(($attributes['class'] ?? '') . ' ' . $baseClasses . ' ' . $variantClass);
$attributes['class'] = $classAttribute;

if (! empty($isFullWidth)) {
    $attributes['class'] .= ' w-full';
}

if ($tag === 'button') {
    $attributes['type'] = $type;
} else {
    $attributes['href'] = $href;
}

$iconHtml = null;
if (isset($icon) && $icon !== '') {
    $iconHtml = '<span class="mr-2 flex items-center">' . esc($icon, 'raw') . '</span>';
}

$attributeString = '';
foreach ($attributes as $attr => $value) {
    if ($value === null || $value === '') {
        continue;
    }
    $attributeString .= ' ' . $attr . '="' . esc($value, 'attr') . '"';
}
?>
<<?= $tag . $attributeString ?>>
    <?php if ($iconHtml) : ?>
        <?= $iconHtml ?>
    <?php endif; ?>
    <?= esc($label) ?>
</<?= $tag ?>>
