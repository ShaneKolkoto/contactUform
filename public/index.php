<?php

declare(strict_types=1);

require __DIR__ . '/../bootstrap.php';
$pageTitle = 'Contact';
$active = 'form';
$pageStyles = ['assets/css/contact-form.css'];

$errors = $_SESSION['errors'] ?? [];
$old = $_SESSION['old'] ?? [];
unset($_SESSION['errors'], $_SESSION['old']);

$errorFor = fn(string $field): string => $errors[$field] ?? '';
$value = fn(string $field): string => (string) ($old[$field] ?? '');

require __DIR__ . '/../templates/header.php';
?>

<div class="container--narrow mt-4">

    <div class="contact-split">
        <!-- LEFT SIDE: Social & Info -->
        <div class="contact-split__left">
            <div class="social-links">
                <!-- Portfolio -->
                <a href="https://shane-kolkoto-portfolio.vercel.app/" target="_blank" rel="noopener"
                   class="social-link" aria-label="Visit my Portfolio" title="Portfolio">
                    <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 17.93c-3.95-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93zm6.9-2.54c-.26-.81-1-1.39-1.9-1.39h-1v-3c0-.55-.45-1-1-1H8v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.39z"/>
                    </svg>
                </a>
                <!-- LinkedIn -->
                <a href="https://linkedin.com/in/shane-morne-kolkoto" target="_blank" rel="noopener"
                   class="social-link" aria-label="Visit my LinkedIn profile" title="LinkedIn">
                    <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false">
                        <path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z"/>
                    </svg>
                </a>
                <!-- GitHub -->
                <a href="https://github.com/ShaneKolkoto/contactUform" target="_blank" rel="noopener"
                   class="social-link" aria-label="View this project on GitHub" title="GitHub">
                    <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false">
                        <path d="M12 .297c-6.63 0-12 5.373-12 12 0 5.303 3.438 9.8 8.205 11.385.6.113.82-.258.82-.577 0-.285-.01-1.04-.015-2.04-3.338.724-4.042-1.61-4.042-1.61C4.422 18.07 3.633 17.7 3.633 17.7c-1.087-.744.084-.729.084-.729 1.205.084 1.838 1.236 1.838 1.236 1.07 1.835 2.809 1.305 3.495.998.108-.776.417-1.305.76-1.605-2.665-.3-5.466-1.332-5.466-5.93 0-1.31.465-2.38 1.235-3.22-.135-.303-.54-1.523.105-3.176 0 0 1.005-.322 3.3 1.23.96-.267 1.98-.399 3-.405 1.02.006 2.04.138 3 .405 2.28-1.552 3.285-1.23 3.285-1.23.645 1.653.24 2.873.12 3.176.765.84 1.23 1.91 1.23 3.22 0 4.61-2.805 5.625-5.475 5.92.42.36.81 1.096.81 2.22 0 1.606-.015 2.896-.015 3.286 0 .315.21.69.825.57C20.565 22.092 24 17.592 24 12.297c0-6.627-5.373-12-12-12"/>
                    </svg>
                </a>
            </div>

            <div class="contact-info">
                <h1 class="contact-title">Contact <span>Me...</span></h1>
                <p class="contact-description">
                    Hi there! I built this little contact form as a showcase of clean,
                    framework-free PHP. It's a simple project, but it's built with care
                    proper validation, secure database handling, and a focus on user
                    experience. Whether you have a question, a project in mind, or just
                    want to say hello, drop me a line below and I'll get back to you soon.
                </p>
            </div>
        </div>

        <!-- RIGHT SIDE: Form -->
        <div class="contact-split__right">
            <?php if ($errors !== []): ?>
                <?php render_alert([
                    'type' => 'error',
                    'title' => 'Please fix the following:',
                    'items' => array_values($errors),
                ]); ?>
            <?php endif; ?>

            <form id="contact-form" action="submit.php" method="post" novalidate>
                <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">

                <?php render_form_field([
                    'name' => 'name',
                    'label' => 'Name',
                    'type' => 'text',
                    'value' => $value('name'),
                    'error' => $errorFor('name'),
                    'required' => true,
                    'maxlength' => 100,
                    'placeholder' => 'Name',
                    'hide_label' => true,
                ]); ?>

                <?php render_form_field([
                    'name' => 'email',
                    'label' => 'Email',
                    'type' => 'email',
                    'value' => $value('email'),
                    'error' => $errorFor('email'),
                    'required' => true,
                    'maxlength' => 255,
                    'placeholder' => 'Email',
                    'hide_label' => true,
                ]); ?>

                <?php render_form_field([
                    'name' => 'phone',
                    'label' => 'Phone Number',
                    'type' => 'tel',
                    'value' => $value('phone'),
                    'error' => $errorFor('phone'),
                    'required' => true,
                    'placeholder' => 'Phone number',
                    'hint' => 'e.g. 082 123 4567',
                    'hide_label' => true,
                ]); ?>

                <?php render_form_field([
                    'name' => 'message',
                    'label' => 'Message',
                    'textarea' => true,
                    'rows' => 4,
                    'value' => $value('message'),
                    'error' => $errorFor('message'),
                    'required' => true,
                    'maxlength' => 5000,
                    'placeholder' => 'Message...',
                    'hide_label' => true,
                ]); ?>

                <div class="form-footer">
                    <small class="char-count" id="char-count" aria-live="polite">0 / 5000</small>
                    <?php render_button([
                        'label' => 'Submit',
                        'id' => 'submit-btn',
                        'variant' => 'primary',
                        'spinner' => true,
                    ]); ?>
                </div>
            </form>
        </div>
    </div>

</div>

<?php require __DIR__ . '/../templates/footer.php'; ?>