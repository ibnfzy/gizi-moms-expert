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
    'home' => [
        'attributes' => [
            'viewBox' => '0 0 24 24',
            'fill'    => 'none',
            'stroke'  => 'currentColor',
            'stroke-width' => '1.5',
        ],
        'content' => [
            '<path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955a1.125 1.125 0 0 1 1.592 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75" />',
        ],
    ],
    'users' => [
        'attributes' => [
            'viewBox' => '0 0 24 24',
            'fill'    => 'none',
            'stroke'  => 'currentColor',
            'stroke-width' => '1.5',
        ],
        'content' => [
            '<path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493" />',
            '<path stroke-linecap="round" stroke-linejoin="round" d="M4.5 21v-1.125a6.375 6.375 0 0 1 12.75 0V21" />',
            '<path stroke-linecap="round" stroke-linejoin="round" d="M12 10.125a4.125 4.125 0 1 0-8.25 0 4.125 4.125 0 0 0 8.25 0zm8.25 1.125a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0z" />',
        ],
    ],
    'user-cog' => [
        'attributes' => [
            'viewBox' => '0 0 24 24',
            'fill'    => 'none',
            'stroke'  => 'currentColor',
            'stroke-width' => '1.5',
        ],
        'content' => [
            '<path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6.75a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z" />',
            '<path stroke-linecap="round" stroke-linejoin="round" d="M4.501 20.118a7.5 7.5 0 0 1 14.998 0" />',
            '<path stroke-linecap="round" stroke-linejoin="round" d="M18.75 14.25v1.043l.899.52a.75.75 0 0 1 .274 1.028l-.522.904.522.904a.75.75 0 0 1-.274 1.028l-.899.52V21.75a.75.75 0 0 1-.75.75h-1.5a.75.75 0 0 1-.75-.75v-1.043l-.899-.52a.75.75 0 0 1-.274-1.028l.522-.904-.522-.904a.75.75 0 0 1 .274-1.028l.899-.52V14.25a.75.75 0 0 1 .75-.75h1.5a.75.75 0 0 1 .75.75Z" />',
        ],
    ],
    'document-text' => [
        'attributes' => [
            'viewBox' => '0 0 24 24',
            'fill'    => 'none',
            'stroke'  => 'currentColor',
            'stroke-width' => '1.5',
        ],
        'content' => [
            '<path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v2.25a2.25 2.25 0 0 1-2.25 2.25h-10.5A2.25 2.25 0 0 1 4.5 16.5V7.5a2.25 2.25 0 0 1 2.25-2.25h6l4.5 4.5z" />',
            '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6M9 9h3" />',
        ],
    ],
    'chat-bubble' => [
        'attributes' => [
            'viewBox' => '0 0 24 24',
            'fill'    => 'none',
            'stroke'  => 'currentColor',
            'stroke-width' => '1.5',
        ],
        'content' => [
            '<path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h9m-9 3H12m-9 7.478V5.25A2.25 2.25 0 0 1 5.25 3h13.5A2.25 2.25 0 0 1 21 5.25v6.75A2.25 2.25 0 0 1 18.75 14.25H9.674a1.5 1.5 0 0 0-1.057.437L3 20.25" />',
        ],
    ],
    'calendar' => [
        'attributes' => [
            'viewBox' => '0 0 24 24',
            'fill'    => 'none',
            'stroke'  => 'currentColor',
            'stroke-width' => '1.5',
        ],
        'content' => [
            '<path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5A2.25 2.25 0 0 1 5.25 5.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25A2.25 2.25 0 0 1 18.75 21H5.25A2.25 2.25 0 0 1 3 18.75ZM3 9.75h18" />',
        ],
    ],
    'information-circle' => [
        'attributes' => [
            'viewBox' => '0 0 24 24',
            'fill'    => 'none',
            'stroke'  => 'currentColor',
            'stroke-width' => '1.5',
        ],
        'content' => [
            '<path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25h1.5v5.25h-1.5z" />',
            '<path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9 9 0 1 1 0-18 9 9 0 0 1 0 18Zm0-12.75h.007v.007H12z" />',
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
