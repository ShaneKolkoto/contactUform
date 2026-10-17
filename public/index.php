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

// Small helpers so the template below stays readable
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
        <?php render_alert([
            'type' => 'error',
            'title' => 'Please fix the following:',
            'items' => array_values($errors),
        ]); ?>
    <?php endif; ?>

    <div class="card">
        <div class="card-body p-4 p-md-5">
            <form id="contact-form" action="submit.php" method="post" novalidate>
                <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">

                <?php render_form_field([
                    'name' => 'name',
                    'label' => 'Full name',
                    'type' => 'text',
                    'value' => $value('name'),
                    'error' => $errorFor('name'),
                    'required' => true,
                    'maxlength' => 100,
                    'autocomplete' => 'name',
                ]); ?>

                <?php render_form_field([
                    'name' => 'email',
                    'label' => 'Email address',
                    'type' => 'email',
                    'value' => $value('email'),
                    'error' => $errorFor('email'),
                    'required' => true,
                    'maxlength' => 255,
                    'autocomplete' => 'email',
                ]); ?>

                <?php render_form_field([
                    'name' => 'phone',
                    'label' => 'South African phone number',
                    'type' => 'tel',
                    'value' => $value('phone'),
                    'error' => $errorFor('phone'),
                    'hint' => 'Mobile or landline. Accepts 0XX, +27XX, or 27XX formats.',
                    'required' => true,
                    'placeholder' => 'e.g. 082 123 4567',
                    'autocomplete' => 'tel',
                ]); ?>

                <?php render_form_field([
                    'name' => 'message',
                    'label' => 'Message',
                    'textarea' => true,
                    'rows' => 5,
                    'value' => $value('message'),
                    'error' => $errorFor('message'),
                    'required' => true,
                    'maxlength' => 5000,
                ]); ?>

                <div class="d-flex justify-content-end mb-4">
                    <small class="text-muted" id="char-count">0 / 5000</small>
                </div>

                <?php render_button([
                    'label' => 'Send message',
                    'id' => 'submit-btn',
                    'variant' => 'primary',
                    'size' => 'lg',
                    'block' => true,
                    'spinner' => true,
                ]); ?>
            </form>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../templates/footer.php'; ?>