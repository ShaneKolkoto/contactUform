<?php

declare(strict_types=1);

require __DIR__ . '/../bootstrap.php';

use App\Database;
use App\EntryRepository;
use App\Validator;

$pageTitle = 'Saved entries';
$active = 'entries';

// ------------------------------------------------------------
// Load entries with simple pagination
// ------------------------------------------------------------
$page = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 10;
$offset = ($page - 1) * $perPage;

$entries = [];
$total = 0;
$totalPages = 0;
$dbError = null;

try {
    $pdo = Database::connect($config['database']);
    $repo = new EntryRepository($pdo);

    $total = $repo->count();
    $totalPages = max(1, (int) ceil($total / $perPage));

    // Clamp page to the last valid page if someone requests too far
    if ($page > $totalPages) {
        $page = $totalPages;
        $offset = ($page - 1) * $perPage;
    }

    $entries = $repo->all(limit: $perPage, offset: $offset);
} catch (Throwable $e) {
    error_log('[entries] ' . $e->getMessage());
    $dbError = 'Could not load entries. Please try again in a moment.';
}

// ------------------------------------------------------------
// Turns a timestamp into a friendly relative label.
// "just now", "5 minutes ago", "yesterday", then a date
// beyond a week.
// ------------------------------------------------------------
function format_relative_time(string $timestamp): string
{
    $ts = strtotime($timestamp);
    if ($ts === false) {
        return $timestamp;
    }

    $diff = time() - $ts;

    if ($diff < 60) {
        return 'just now';
    }
    if ($diff < 3600) {
        $mins = (int) floor($diff / 60);
        return $mins === 1 ? '1 minute ago' : "{$mins} minutes ago";
    }
    if ($diff < 86400) {
        $hours = (int) floor($diff / 3600);
        return $hours === 1 ? '1 hour ago' : "{$hours} hours ago";
    }
    if ($diff < 172800) {
        return 'yesterday';
    }
    if ($diff < 604800) {
        $days = (int) floor($diff / 86400);
        return "{$days} days ago";
    }

    return date('j M Y, H:i', $ts);
}

require __DIR__ . '/../templates/header.php';
?>

<div class="container--wide">
    <div class="page-header">
        <h1 class="mb-2">Saved entries</h1>
        <p class="page-header__subtitle">
            <?php if ($total > 0): ?>
                <?= $total ?> submission<?= $total === 1 ? '' : 's' ?> so far
                <?php if ($totalPages > 1): ?>
                    &middot; page <?= $page ?> of <?= $totalPages ?>
                <?php endif; ?>
            <?php else: ?>
                Messages submitted through the contact form appear here.
            <?php endif; ?>
        </p>
    </div>

    <?php if ($dbError !== null): ?>
        <?php render_alert([
            'type' => 'error',
            'message' => $dbError,
        ]); ?>

    <?php elseif ($entries === []): ?>
        <?php render_empty_state([
            'icon' => '📭',
            'title' => 'No entries yet',
            'message' => 'Once someone submits the contact form, their message will show up here.',
            'actionUrl' => '/',
            'actionLabel' => 'Go to the form',
        ]); ?>

    <?php else: ?>
        <div class="card">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th scope="col">Name</th>
                            <th scope="col">Contact</th>
                            <th scope="col">Message</th>
                            <th scope="col">Received</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($entries as $entry): ?>
                            <tr>
                                <td>
                                    <div class="fw-semibold"><?= e($entry['name']) ?></div>
                                </td>
                                <td>
                                    <div>
                                        <a href="mailto:<?= e($entry['email']) ?>">
                                            <?= e($entry['email']) ?>
                                        </a>
                                    </div>
                                    <div class="text-muted small">
                                        <?= e(Validator::formatSaPhone($entry['phone'])) ?>
                                    </div>
                                </td>
                                <td class="message-cell">
                                    <?= e($entry['message']) ?>
                                </td>
                                <td class="date-cell">
                                    <?= e(format_relative_time($entry['created_at'])) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <?php if ($totalPages > 1): ?>
            <nav class="mt-4" aria-label="Pagination">
                <ul class="pagination justify-content-center mb-0">
                    <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                        <a class="page-link"
                           href="?page=<?= $page - 1 ?>"
                           <?= $page <= 1 ? 'tabindex="-1" aria-disabled="true"' : '' ?>>
                            Previous
                        </a>
                    </li>

                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                            <a class="page-link"
                               href="?page=<?= $i ?>"
                               <?= $i === $page ? 'aria-current="page"' : '' ?>>
                                <?= $i ?>
                            </a>
                        </li>
                    <?php endfor; ?>

                    <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                        <a class="page-link"
                           href="?page=<?= $page + 1 ?>"
                           <?= $page >= $totalPages ? 'tabindex="-1" aria-disabled="true"' : '' ?>>
                            Next
                        </a>
                    </li>
                </ul>
            </nav>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/../templates/footer.php'; ?>