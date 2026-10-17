<?php

declare(strict_types=1);

if ($flash === null) {
    return;
}

$type = in_array($flash['type'], ['success', 'danger', 'warning', 'info'], true)
    ? $flash['type']
    : 'info';

$labels = [
    'success' => 'Success',
    'danger'  => 'Error',
    'warning' => 'Warning',
    'info'    => 'Notice',
];
?>
<div class="alert alert-<?= e($type) ?> d-flex align-items-start gap-2" role="alert">
    <span class="alert__label"><?= e($labels[$type]) ?></span>
    <span><?= e($flash['message']) ?></span>
</div>