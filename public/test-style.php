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
                <button class="btn btn-primary">Primary</button>
                <button class="btn btn-secondary">Secondary</button>
                <button class="btn btn-primary" disabled>Disabled</button>
            </div>

            <h2 class="h5 mb-3">Form fields</h2>
            <div class="mb-3">
                <label class="form-label" for="demo">Label <span class="text-danger">*</span></label>
                <input class="form-control" id="demo" placeholder="Placeholder text">
                <div class="form-text">Help text under the field.</div>
            </div>

            <div class="mb-4">
                <label class="form-label" for="demo2">Invalid field</label>
                <input class="form-control is-invalid" id="demo2" value="bad value">
                <div class="invalid-feedback">This is what an error looks like.</div>
            </div>

            <h2 class="h5 mb-3">Alerts</h2>
            <div class="alert alert-success d-flex align-items-start gap-2" role="alert">
                <span class="alert__label">Success</span>
                <span>Your message has been received.</span>
            </div>
            <div class="alert alert-danger d-flex align-items-start gap-2" role="alert">
                <span class="alert__label">Error</span>
                <span>Something went wrong. Please try again.</span>
            </div>
            <div class="alert alert-warning d-flex align-items-start gap-2" role="alert">
                <span class="alert__label">Warning</span>
                <span>Your session expired.</span>
            </div>

            <h2 class="h5 mb-3 mt-4">Table</h2>
            <div class="table-responsive">
                <table class="table align-middle">
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

    <div class="card mt-4">
        <div class="empty-state">
            <div class="empty-state__icon" aria-hidden="true">📭</div>
            <h2 class="h5 mb-2">No entries yet</h2>
            <p class="text-muted mb-4">Once someone submits the form, their message appears here.</p>
            <a href="/" class="btn btn-primary">Go to the form</a>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../templates/footer.php'; ?>