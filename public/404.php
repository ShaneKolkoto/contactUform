<?php

declare(strict_types=1);

require __DIR__ . '/../bootstrap.php';

http_response_code(404);

$pageTitle = 'Page not found';
$active = '';

require __DIR__ . '/../templates/header.php';
?>

<div class="container--narrow mt-4">
    <div class="text-center">
        <div class="card-body p-5">
            <div class="mb-4">
                <span class="display-1 fw-bold text-primary">404</span>
            </div>
            
            <h1 class="h3 mb-3">Page not found</h1>
            <p class="text-muted mb-4">
                The page you are looking for doesn't exist or has been moved. 
                Let's get you back on track.
            </p>
            
            <div class="d-flex justify-content-center gap-2">
                <a href="/" class="btn btn-primary">Go to the form</a>
                <a href="/entries" class="btn btn-secondary">View entries</a>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../templates/footer.php'; ?>