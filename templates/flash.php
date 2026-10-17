<?php

declare(strict_types=1);

if ($flash === null) {
    return;
}

render_alert([
    'type' => $flash['type'],
    'message' => $flash['message'],
]);