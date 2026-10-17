<?php

declare(strict_types=1);

require __DIR__ . '/../bootstrap.php';

$pageTitle = 'Style Preview';
$active = '';

// Uncomment to test the flash message
// flash('success', 'This is a success flash. Refresh — it should disappear.');

require __DIR__ . '/../templates/header.php';
?>

<div class="container--narrow">
    <div class="page-header">
        <h1 class="mb-2">Style preview</h1>
        <p class="page-header__subtitle">Every component we use, rendered together.</p>
    </div>

    <div class="card">
        <div class="card-body p-4 p-md-5">

            <h2 class="h5 mb-3">Buttons</h2>
            <div class="d-flex flex-wrap gap-2 mb-4">
                <?php render_button(['label' => 'Primary']); ?>
                <?php render_button(['label' => 'Secondary', 'variant' => 'secondary']); ?>
                <?php render_button(['label' => 'Large', 'size' => 'lg']); ?>
                <?php render_button(['label' => 'With spinner', 'spinner' => true]); ?>
                <?php render_button(['label' => 'Disabled', 'type' => 'button']); ?>
            </div>

            <h2 class="h5 mb-3">Form fields</h2>
            <?php render_form_field([
                'name' => 'demo_text',
                'label' => 'Text field',
                'type' => 'text',
                'placeholder' => 'Placeholder text',
                'hint' => 'Help text under the field.',
                'required' => true,
            ]); ?>

            <?php render_form_field([
                'name' => 'demo_email',
                'label' => 'Email field',
                'type' => 'email',
                'value' => 'thabo@example.co.za',
                'autocomplete' => 'email',
            ]); ?>

            <?php render_form_field([
                'name' => 'demo_error',
                'label' => 'Invalid field',
                'type' => 'text',
                'value' => 'bad value',
                'error' => 'This is what an error looks like.',
            ]); ?>

            <?php render_form_field([
                'name' => 'demo_message',
                'label' => 'Message field',
                'textarea' => true,
                'rows' => 3,
                'value' => 'A message that was preserved after a failed submit.',
            ]); ?>

            <h2 class="h5 mb-3 mt-4">Alerts</h2>
            <?php render_alert([
                'type' => 'success',
                'message' => 'Your message has been received.',
            ]); ?>

            <?php render_alert([
                'type' => 'error',
                'message' => 'Something went wrong. Please try again.',
            ]); ?>

            <?php render_alert([
                'type' => 'warning',
                'message' => 'Your session expired.',
            ]); ?>

            <?php render_alert([
                'type' => 'info',
                'message' => 'This is an informational notice.',
            ]); ?>

            <?php render_alert([
                'type' => 'error',
                'title' => 'Please fix the following:',
                'items' => [
                    'Name is required.',
                    'Please enter a valid email address.',
                    'Please enter a valid South African phone number (e.g. 082 123 4567).',
                    'Message must be at least 10 characters.',
                ],
            ]); ?>

            <h2 class="h5 mb-3 mt-4">Table</h2>
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Message</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Thabo Mokoena</td>
                            <td>thabo@example.co.za</td>
                            <td class="message-cell">I would like a quote for website development. We need something modern with a booking system.</td>
                            <td class="date-cell">2026-10-16 14:22</td>
                        </tr>
                        <tr>
                            <td>Sarah van Wyk</td>
                            <td>sarah@example.com</td>
                            <td class="message-cell">Please call me back regarding the marketing package you advertised.</td>
                            <td class="date-cell">2026-10-15 09:10</td>
                        </tr>
                    </tbody>
                </table>
            </div>

        </div>
    </div>

    <h2 class="h5 mb-3 mt-4">Empty states</h2>
    <?php render_empty_state([
        'icon' => '📭',
        'title' => 'No entries yet',
        'message' => 'Once someone submits the form, their message appears here.',
        'actionUrl' => '/',
        'actionLabel' => 'Go to the form',
    ]); ?>

    <?php render_empty_state([
        'icon' => '🔍',
        'title' => 'No results',
        'message' => 'Nothing matched your search. Try a different term.',
    ]); ?>
</div>

<?php require __DIR__ . '/../templates/footer.php'; ?>