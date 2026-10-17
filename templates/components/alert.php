<?php

declare(strict_types=1);

/**
 * Renders a single alert banner.
 *
 * $props keys:
 *   type     'success'|'error'|'warning'|'info'   Required.
 *   message  string                                Required for simple alerts.
 *   title    string                                Optional bold label.
 *   items    string[]                              Optional list of bulleted messages.
 *                                                  When present, message is ignored.
 */
function render_alert(array $props): void
{
    $types = [
        'success' => ['class' => 'success', 'label' => 'Success'],
        'error'   => ['class' => 'danger',  'label' => 'Error'],
        'warning' => ['class' => 'warning', 'label' => 'Warning'],
        'info'    => ['class' => 'info',    'label' => 'Notice'],
    ];

    $type = $types[$props['type'] ?? 'info'] ?? $types['info'];
    $title = $props['title'] ?? $type['label'];
    $message = $props['message'] ?? '';
    $items = $props['items'] ?? [];
    ?>
    <div class="alert alert-<?= e($type['class']) ?>" role="alert">
        <strong class="alert__label"><?= e($title) ?></strong>
        <?php if ($items !== []): ?>
            <ul class="mb-0 mt-2 ps-4">
                <?php foreach ($items as $item): ?>
                    <li><?= e($item) ?></li>
                <?php endforeach; ?>
            </ul>
        <?php elseif ($message !== ''): ?>
            <span><?= e($message) ?></span>
        <?php endif; ?>
    </div>
    <?php
}