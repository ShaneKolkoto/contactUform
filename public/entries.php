<?php

declare(strict_types=1);

require __DIR__ . '/../bootstrap.php';

use App\Database;
use App\EntryRepository;
use App\Validator;

$pageTitle = 'Saved entries';
$active = 'entries';
$pageStyles = ['assets/css/entries.css'];

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
// Helpers
// ------------------------------------------------------------

/**
 * Turns a timestamp into a friendly relative label.
 */
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

/**
 * Renders an avatar with the sender's initials.
 */
function entry_initials(string $name): string
{
    $parts = preg_split('/\s+/', trim($name));
    if ($parts === false || $parts === []) {
        return '?';
    }

    $first = mb_substr($parts[0], 0, 1);
    $last = count($parts) > 1 ? mb_substr(end($parts), 0, 1) : '';
    return mb_strtoupper($first . $last);
}

require __DIR__ . '/../templates/header.php';
?>

<div class="container--wide mt-4">

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

        <div class="entries-header">
            <div>
                <h1 class="entries-header__title">Inbox</h1>
                <p class="entries-header__subtitle">
                    <span class="entries-header__dot" aria-hidden="true"></span>
                    <?= $total ?> submission<?= $total === 1 ? '' : 's' ?>
                    <?php if ($totalPages > 1): ?>
                        &middot; page <?= $page ?> of <?= $totalPages ?>
                    <?php endif; ?>
                </p>
            </div>
        </div>

        <div class="entries-list">
            <?php foreach ($entries as $entry): ?>
                <article class="entry-card">
                    <div class="entry-card__avatar" aria-hidden="true">
                        <?= e(entry_initials($entry['name'])) ?>
                    </div>

                    <div class="entry-card__body">
                        <div class="entry-card__top">
                            <h2 class="entry-card__name"><?= e($entry['name']) ?></h2>
                            <span class="entry-card__time">
                                <?= e(format_relative_time($entry['created_at'])) ?>
                            </span>
                        </div>

                        <div class="entry-card__contact">
                            <a href="mailto:<?= e($entry['email']) ?>" class="entry-card__email">
                                <?= e($entry['email']) ?>
                            </a>
                            <span class="entry-card__phone">
                                <?= e(Validator::formatSaPhone($entry['phone'])) ?>
                            </span>
                        </div>

                        <p class="entry-card__message"><?= e($entry['message']) ?></p>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>

        <?php if ($totalPages > 1): ?>
            <nav class="entries-pagination" aria-label="Pagination">
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