<?php
/**
 * Icon component for reusable SVGs.
 *
 * Parameters:
 * - string $name         Icon identifier (required).
 * - string $class        Tailwind/utility classes appended to the SVG element.
 * - string|null $title   Optional accessible title element content.
 * - array $attributes    Additional attributes to merge into the SVG tag.
 */

$iconName = $name ?? null;

if ($iconName === null) {
    return;
}

$iconLibrary = [
    'chevron-down' => [
        'attributes' => [
            'viewBox' => '0 0 20 20',
            'fill'    => 'currentColor',
        ],
        'content' => [
            '<path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 10.585l3.71-3.354a.75.75 0 1 1 1.04 1.08l-4.24 3.835a.75.75 0 0 1-1.02 0l-4.25-3.835a.75.75 0 0 1 .02-1.06z" clip-rule="evenodd" />',
        ],
    ],
    'menu' => [
        'attributes' => [
            'viewBox' => '0 0 24 24',
            'fill'    => 'none',
            'stroke'  => 'currentColor',
            'stroke-width' => '1.5',
        ],
        'content' => [
            '<path stroke-linecap="round" stroke-linejoin="round" d="M3.75 5.25h16.5M3.75 12h16.5m-16.5 6.75h16.5" />',
        ],
    ],
    'arrow-right' => [
        'attributes' => [
            'viewBox' => '0 0 24 24',
            'fill'    => 'none',
            'stroke'  => 'currentColor',
            'stroke-width' => '1.5',
        ],
        'content' => [
            '<path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12l-7.5 7.5M21 12H3" />',
        ],
    ],
    'facebook' => [
        'attributes' => [
            'viewBox' => '0 0 24 24',
            'fill'    => 'none',
            'stroke'  => 'currentColor',
            'stroke-width' => '1.5',
        ],
        'content' => [
            '<path stroke-linecap="round" stroke-linejoin="round" d="M14.25 9V5.25a2.25 2.25 0 0 1 2.25-2.25h1.5M14.25 9H12m2.25 0H18m0 0v3m0-3h3m-3 3h-3m3 0v9" />',
        ],
    ],
    'instagram' => [
        'attributes' => [
            'viewBox' => '0 0 24 24',
            'fill'    => 'none',
            'stroke'  => 'currentColor',
            'stroke-width' => '1.5',
        ],
        'content' => [
            '<rect x="3" y="3" width="18" height="18" rx="5" ry="5" />',
            '<path d="M16.5 7.5h.008v.008H16.5z" />',
            '<circle cx="12" cy="12" r="3.5" />',
        ],
    ],
    'linkedin' => [
        'attributes' => [
            'viewBox' => '0 0 24 24',
            'fill'    => 'none',
            'stroke'  => 'currentColor',
            'stroke-width' => '1.5',
        ],
        'content' => [
            '<path stroke-linecap="round" stroke-linejoin="round" d="M16 8a6 6 0 0 1 6 6v6h-4v-6a2 2 0 0 0-4 0v6h-4v-6a6 6 0 0 1 6-6z" />',
            '<path stroke-linecap="round" stroke-linejoin="round" d="M2 9h4v12H2z" />',
            '<circle cx="4" cy="4" r="2" />',
        ],
    ],
];

if (! array_key_exists($iconName, $iconLibrary)) {
    return;
}

$definition = $iconLibrary[$iconName];
$attributes = $attributes ?? [];
$class = trim($class ?? '');
$title = $title ?? null;

$svgAttributes = array_merge([
    'xmlns' => 'http://www.w3.org/2000/svg',
    'aria-hidden' => 'true',
    'focusable' => 'false',
], $definition['attributes']);

if ($class !== '') {
    $svgAttributes['class'] = trim(($svgAttributes['class'] ?? '') . ' ' . $class);
}

foreach ($attributes as $key => $value) {
    if ($value === null) {
        unset($svgAttributes[$key]);
        continue;
    }

    $svgAttributes[$key] = $value;
}

$attributeParts = [];
foreach ($svgAttributes as $key => $value) {
    if ($value === false || $value === null) {
        continue;
    }

    if ($value === true) {
        $attributeParts[] = esc($key, 'attr');
        continue;
    }

    $attributeParts[] = esc($key, 'attr') . '="' . esc($value, 'attr') . '"';
}

$attributeString = implode(' ', $attributeParts);
?>
<svg <?= $attributeString ?>>
    <?php if ($title !== null) : ?>
        <title><?= esc($title) ?></title>
    <?php endif; ?>
    <?php foreach ($definition['content'] as $element) : ?>
        <?= $element ?>
    <?php endforeach; ?>
</svg>
