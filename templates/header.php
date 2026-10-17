<?php

declare(strict_types=1);

require_once __DIR__ . '/components/form-field.php';
require_once __DIR__ . '/components/alert.php';
require_once __DIR__ . '/components/button.php';
require_once __DIR__ . '/components/empty-state.php';

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

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">    <link href="assets/css/style.css" rel="stylesheet">

    <?php foreach (($pageStyles ?? []) as $href): ?>
        <link href="<?= e($href) ?>" rel="stylesheet">
    <?php endforeach; ?>

    <link rel="icon" type="image/x-icon" href="/assets/favicon.ico">
    <link rel="icon" type="image/png" sizes="32x32" href="/assets/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/assets/favicon-16x16.png">
    <link rel="apple-touch-icon" sizes="180x180" href="/assets/apple-touch-icon.png">
    <link rel="manifest" href="/assets/site.webmanifest">
    <meta name="theme-color" content="#1f6feb">
</head>
<body>
<header class="site-header">
    <div class="container">
        <div class="d-flex align-items-center justify-content-between" style="height: 60px;">
            <a class="brand" href="/">
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