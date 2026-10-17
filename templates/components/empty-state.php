<?php

declare(strict_types=1);

function render_empty_state(array $props): void
{
    $icon = $props['icon'] ?? '';
    $title = $props['title'];
    $message = $props['message'] ?? '';
    $actionUrl = $props['actionUrl'] ?? '';
    $actionLabel = $props['actionLabel'] ?? '';
    ?>
    <div class="card" role="status">
        <div class="empty-state">
            <?php if ($icon !== ''): ?>
                <div class="empty-state__icon" aria-hidden="true"><?= e($icon) ?></div>
            <?php endif; ?>
            <h2 class="h5 mb-2"><?= e($title) ?></h2>
            <?php if ($message !== ''): ?>
                <p class="text-muted mb-4"><?= e($message) ?></p>
            <?php endif; ?>
            <?php if ($actionUrl !== '' && $actionLabel !== ''): ?>
                <a href="<?= e($actionUrl) ?>" class="btn btn-primary">
                    <?= e($actionLabel) ?>
                </a>
            <?php endif; ?>
        </div>
    </div>
    <?php
}