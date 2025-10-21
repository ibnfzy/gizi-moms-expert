<?php
$variant = $variant ?? 'primary';
$label = $label ?? 'Button';
$tag = isset($href) ? 'a' : 'button';
$type = $type ?? 'button';
$attributes = $attributes ?? [];

$baseClasses = 'inline-flex items-center justify-center px-4 py-2 text-sm font-medium rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 transition';
$variants = [
    'primary' => 'bg-blue-600 text-white hover:bg-blue-700 focus:ring-blue-500',
    'danger' => 'bg-red-600 text-white hover:bg-red-700 focus:ring-red-500',
    'secondary' => 'bg-gray-100 text-gray-700 hover:bg-gray-200 focus:ring-gray-400',
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
