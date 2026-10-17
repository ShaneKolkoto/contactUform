<?php

declare(strict_types=1);

require __DIR__ . '/../bootstrap.php';

$pageTitle = 'Contact us';
$active = 'form';

// Errors and old input from a failed submit are read here, then cleared
// so a page refresh doesn't keep showing them.
$errors = $_SESSION['errors'] ?? [];
$old = $_SESSION['old'] ?? [];
unset($_SESSION['errors'], $_SESSION['old']);

// Tiny helpers so the template below stays readable
$hasError = fn(string $field): bool => isset($errors[$field]);
$errorFor = fn(string $field): string => $errors[$field] ?? '';
$value = fn(string $field): string => (string) ($old[$field] ?? '');

require __DIR__ . '/../templates/header.php';
?>

<div class="container--narrow">
    <div class="page-header">
        <h1 class="mb-2">Get in touch</h1>
        <p class="page-header__subtitle">Fill in the form below and we'll get back to you shortly.</p>
    </div>

    <?php if ($errors !== []): ?>
        <div class="alert alert-danger" role="alert">
            <strong class="alert__label">Please fix the following:</strong>
            <ul class="mb-0 mt-2 ps-4">
                <?php foreach ($errors as $message): ?>
                    <li><?= e($message) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body p-4 p-md-5">
            <form id="contact-form" action="submit.php" method="post" novalidate>
                <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">

                <div class="mb-3">
                    <label for="name" class="form-label">
                        Full name <span class="text-danger">*</span>
                    </label>
                    <input
                        type="text"
                        id="name"
                        name="name"
                        class="form-control <?= $hasError('name') ? 'is-invalid' : '' ?>"
                        value="<?= e($value('name')) ?>"
                        maxlength="100"
                        autocomplete="name"
                        required
                        <?php if ($hasError('name')): ?>aria-invalid="true" aria-describedby="name-error"<?php endif; ?>
                    >
                    <?php if ($hasError('name')): ?>
                        <div class="invalid-feedback" id="name-error"><?= e($errorFor('name')) ?></div>
                    <?php endif; ?>
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">
                        Email address <span class="text-danger">*</span>
                    </label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        class="form-control <?= $hasError('email') ? 'is-invalid' : '' ?>"
                        value="<?= e($value('email')) ?>"
                        maxlength="255"
                        autocomplete="email"
                        required
                        <?php if ($hasError('email')): ?>aria-invalid="true" aria-describedby="email-error"<?php endif; ?>
                    >
                    <?php if ($hasError('email')): ?>
                        <div class="invalid-feedback" id="email-error"><?= e($errorFor('email')) ?></div>
                    <?php endif; ?>
                </div>

                <div class="mb-3">
                    <label for="phone" class="form-label">
                        South African phone number <span class="text-danger">*</span>
                    </label>
                    <input
                        type="tel"
                        id="phone"
                        name="phone"
                        class="form-control <?= $hasError('phone') ? 'is-invalid' : '' ?>"
                        value="<?= e($value('phone')) ?>"
                        placeholder="e.g. 082 123 4567"
                        autocomplete="tel"
                        required
                        <?php if ($hasError('phone')): ?>aria-invalid="true" aria-describedby="phone-error phone-hint"<?php else: ?>aria-describedby="phone-hint"<?php endif; ?>
                    >
                    <div class="form-text" id="phone-hint">
                        Mobile or landline. Accepts 0XX, +27XX, or 27XX formats.
                    </div>
                    <?php if ($hasError('phone')): ?>
                        <div class="invalid-feedback" id="phone-error"><?= e($errorFor('phone')) ?></div>
                    <?php endif; ?>
                </div>

                <div class="mb-4">
                    <label for="message" class="form-label">
                        Message <span class="text-danger">*</span>
                    </label>
                    <textarea
                        id="message"
                        name="message"
                        class="form-control <?= $hasError('message') ? 'is-invalid' : '' ?>"
                        rows="5"
                        maxlength="5000"
                        required
                        <?php if ($hasError('message')): ?>aria-invalid="true" aria-describedby="message-error"<?php endif; ?>
                    ><?= e($value('message')) ?></textarea>
                    <div class="d-flex justify-content-between align-items-start mt-2">
                        <?php if ($hasError('message')): ?>
                            <div class="invalid-feedback d-block" id="message-error">
                                <?= e($errorFor('message')) ?>
                            </div>
                        <?php else: ?>
                            <span></span>
                        <?php endif; ?>
                        <small class="text-muted" id="char-count">0 / 5000</small>
                    </div>
                </div>

                <div class="d-grid">
                    <button type="submit" class="btn btn-primary btn-lg" id="submit-btn">
                        <span class="btn__spinner d-none" aria-hidden="true"></span>
                        <span class="btn__label">Send message</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../templates/footer.php'; ?>