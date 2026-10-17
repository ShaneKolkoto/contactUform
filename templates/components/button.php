<?php

declare(strict_types=1);

/**
 * Renders a form submit button with an optional loading spinner.
 *
 * $props keys:
 *   label     string   Required. Visible button text.
 *   id        string   Optional id for JS hooks.
 *   variant   string   'primary' (default) or 'secondary'.
 *   size      string   'md' (default) or 'lg'.
 *   block     bool     Full-width when true.
 *   type      string   'submit' (default) or 'button'.
 *   spinner   bool     Include a hidden spinner element for JS to reveal.
 */
function render_button(array $props): void
{
    $label = $props['label'];
    $id = $props['id'] ?? '';
    $variant = $props['variant'] ?? 'primary';
    $size = $props['size'] ?? 'md';
    $block = $props['block'] ?? false;
    $type = $props['type'] ?? 'submit';
    $spinner = $props['spinner'] ?? false;

    $classes = ['btn', "btn-{$variant}"];
    if ($size === 'lg') {
        $classes[] = 'btn-lg';
    }
    if ($block) {
        $classes[] = 'w-100';
    }
    ?>
    <button type="<?= e($type) ?>" class="<?= e(implode(' ', $classes)) ?>" <?= $id !== '' ? 'id="' . e($id) . '"' : '' ?>>
        <?php if ($spinner): ?>
            <span class="btn__spinner d-none" aria-hidden="true"></span>
        <?php endif; ?>
        <span class="btn__label"><?= e($label) ?></span>
    </button>
    <?php
}