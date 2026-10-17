<?php

declare(strict_types=1);

require __DIR__ . '/../bootstrap.php';

use App\Database;
use App\EntryRepository;
use App\Validator;

// ------------------------------------------------------------
// Only POST is allowed
// ------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/');
}

// ------------------------------------------------------------
// CSRF check
// ------------------------------------------------------------
if (!csrf_verify($_POST['csrf'] ?? null)) {
    flash('error', 'Your session expired. Please try again.');
    redirect('/');
}

// ------------------------------------------------------------
// Validate
// ------------------------------------------------------------
$validator = new Validator();

if (!$validator->validate($_POST)) {
    $_SESSION['errors'] = $validator->errors();
    $_SESSION['old'] = [
        'name'    => $_POST['name']    ?? '',
        'email'   => $_POST['email']   ?? '',
        'phone'   => $_POST['phone']   ?? '',
        'message' => $_POST['message'] ?? '',
    ];
    redirect('/');
}

$clean = $validator->clean();

// ------------------------------------------------------------
// Save
// ------------------------------------------------------------
try {
    $pdo = Database::connect($config['database']);
    $repo = new EntryRepository($pdo);

    $repo->create([
        'name'    => $clean['name'],
        'email'   => $clean['email'],
        'phone'   => $clean['phone_normalized'],
        'message' => $clean['message'],
        'ip'      => inet_pton($_SERVER['REMOTE_ADDR'] ?? '') ?: null,
    ]);

    // Rotate the CSRF token so the same form can't be replayed
    unset($_SESSION['csrf']);

    flash('success', 'Thank you — your message has been received. We will be in touch soon.');
    redirect('/');

} catch (Throwable $e) {
    error_log('[submit] ' . $e->getMessage());

    flash('error', 'Something went wrong saving your message. Please try again in a moment.');
    $_SESSION['old'] = [
        'name'    => $_POST['name']    ?? '',
        'email'   => $_POST['email']   ?? '',
        'phone'   => $_POST['phone']   ?? '',
        'message' => $_POST['message'] ?? '',
    ];
    redirect('/');
}