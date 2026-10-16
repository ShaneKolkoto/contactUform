<?php

declare(strict_types=1);

$pageTitle = $pageTitle ?? 'Contact';
$active = $active ?? '';
$flash = flash_get();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle) ?> — <?= e($config['app']['name']) ?></title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
<header class="site-header">
    <div class="container">
        <div class="d-flex align-items-center justify-content-between" style="height: 60px;">
            <a class="brand" href="index.php">
                <span class="brand__mark" aria-hidden="true"></span>
                <span><?= e($config['app']['name']) ?></span>
            </a>

            <nav class="nav" aria-label="Primary">
                <a href="/"
                   class="nav-link <?= $active === 'form' ? 'active fw-semibold' : '' ?>"
                   <?= $active === 'form' ? 'aria-current="page"' : '' ?>>
                    Contact
                </a>
                <a href="entries"
                   class="nav-link <?= $active === 'entries' ? 'active fw-semibold' : '' ?>"
                   <?= $active === 'entries' ? 'aria-current="page"' : '' ?>>
                    Entries
                </a>
            </nav>
        </div>
    </div>
</header>

<main class="py-5">
    <div class="container">
        <?php require __DIR__ . '/flash.php'; ?>