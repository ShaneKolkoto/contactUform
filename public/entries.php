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
// Read filters from the query string
// ------------------------------------------------------------
$search = trim((string) ($_GET['q'] ?? ''));
$range  = (string) ($_GET['range'] ?? 'all');

$rangeOptions = [
    'all'   => 0,
    'today' => 1,
    'week'  => 7,
    'month' => 30,
];
$days = $rangeOptions[$range] ?? 0;
$range = array_key_exists($range, $rangeOptions) ? $range : 'all';

$page = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 10;
$offset = ($page - 1) * $perPage;

$entries = [];
$total = 0;
$totalPages = 1;
$dbError = null;

try {
    $pdo = Database::connect($config['database']);
    $repo = new EntryRepository($pdo);

    $total = $repo->searchCount($search !== '' ? $search : null, $days);
    $totalPages = max(1, (int) ceil($total / $perPage));

    if ($page > $totalPages) {
        $page = $totalPages;
        $offset = ($page - 1) * $perPage;
    }

    $entries = $repo->search(
        $search !== '' ? $search : null,
        $days,
        $perPage,
        $offset
    );
} catch (Throwable $e) {
    error_log('[entries] ' . $e->getMessage());
    $dbError = 'Could not load entries. Please try again in a moment.';
}

$hasActiveFilter = $search !== '' || $days > 0;

// ------------------------------------------------------------
// Helpers
// ------------------------------------------------------------

function format_relative_time(string $timestamp): string
{
    $ts = strtotime($timestamp);
    if ($ts === false) return $timestamp;

    $diff = time() - $ts;

    if ($diff < 60) return 'just now';
    if ($diff < 3600) {
        $mins = (int) floor($diff / 60);
        return $mins === 1 ? '1 minute ago' : "{$mins} minutes ago";
    }
    if ($diff < 86400) {
        $hours = (int) floor($diff / 3600);
        return $hours === 1 ? '1 hour ago' : "{$hours} hours ago";
    }
    if ($diff < 172800) return 'yesterday';
    if ($diff < 604800) {
        $d = (int) floor($diff / 86400);
        return "{$d} days ago";
    }

    return date('j M Y, H:i', $ts);
}

function entry_initials(string $name): string
{
    $parts = preg_split('/\s+/', trim($name));
    if ($parts === false || $parts === []) return '?';

    $first = mb_substr($parts[0], 0, 1);
    $last = count($parts) > 1 ? mb_substr(end($parts), 0, 1) : '';
    return mb_strtoupper($first . $last);
}

function build_query(array $overrides = []): string
{
    $params = array_merge([
        'q' => $_GET['q'] ?? '',
        'range' => $_GET['range'] ?? 'all',
        'page' => $_GET['page'] ?? 1,
    ], $overrides);

    if ($params['q'] === '') unset($params['q']);
    if (($params['range'] ?? 'all') === 'all') unset($params['range']);
    if (($params['page'] ?? 1) == 1) unset($params['page']);

    $qs = http_build_query($params);
    return $qs === '' ? '?' : '?' . $qs;
}

require __DIR__ . '/../templates/header.php';
?>

<div class="container--wide mt-4">

    <?php if ($dbError !== null): ?>
        <?php render_alert([
            'type' => 'error',
            'title' => 'Entries unavailable',
            'message' => $dbError,
        ]); ?>

        <div class="card">
            <div class="card-body p-4 p-md-5">
                <p class="text-muted mb-3">
                    The database couldn't be reached. This is usually temporary.
                </p>
                <a href="/entries" class="btn btn-secondary">Try again</a>
            </div>
        </div>

    <?php else: ?>

        <div class="entries-header">
            <div>
                <h1 class="entries-header__title">Inbox</h1>
                <p class="entries-header__subtitle">
                    <span class="entries-header__dot" aria-hidden="true"></span>
                    <?php if ($hasActiveFilter): ?>
                        <?= $total ?> match<?= $total === 1 ? '' : 'es' ?>
                        <?php if ($totalPages > 1): ?>
                            &middot; page <?= $page ?> of <?= $totalPages ?>
                        <?php endif; ?>
                    <?php else: ?>
                        <?= $total ?> submission<?= $total === 1 ? '' : 's' ?>
                        <?php if ($totalPages > 1): ?>
                            &middot; page <?= $page ?> of <?= $totalPages ?>
                        <?php endif; ?>
                    <?php endif; ?>
                </p>
            </div>
        </div>

        <form method="get" action="/entries" class="entries-filters" role="search">
            <div class="entries-filters__search">
                <label for="q" class="visually-hidden">Search entries</label>
                <input
                    type="search"
                    id="q"
                    name="q"
                    class="form-control"
                    placeholder="Search by name, email, or message..."
                    value="<?= e($search) ?>"
                >
            </div>

            <div class="entries-filters__range">
                <label for="range" class="visually-hidden">Filter by date</label>
                <select id="range" name="range" class="form-select">
                    <option value="all"   <?= $range === 'all'   ? 'selected' : '' ?>>All time</option>
                    <option value="today" <?= $range === 'today' ? 'selected' : '' ?>>Today</option>
                    <option value="week"  <?= $range === 'week'  ? 'selected' : '' ?>>This week</option>
                    <option value="month" <?= $range === 'month' ? 'selected' : '' ?>>This month</option>
                </select>
            </div>

            <button type="submit" class="btn btn-primary">Filter</button>

            <?php if ($hasActiveFilter): ?>
                <a href="/entries" class="btn btn-secondary">Clear</a>
            <?php endif; ?>
        </form>

        <?php if ($hasActiveFilter && $entries === []): ?>
            <?php render_empty_state([
                'icon' => '🔍',
                'title' => 'No matching entries',
                'message' => 'Try a different search term or widen the date range.',
                'actionUrl' => 'entries.php',
                'actionLabel' => 'Clear filters',
            ]); ?>

        <?php elseif ($entries === []): ?>
            <?php render_empty_state([
                'icon' => '📭',
                'title' => 'No entries yet',
                'message' => 'When someone submits the contact form, their message will show up here.',
                'actionUrl' => '/',
                'actionLabel' => 'Go to the form',
            ]); ?>

        <?php else: ?>

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
                               href="<?= e(build_query(['page' => $page - 1])) ?>"
                               <?= $page <= 1 ? 'tabindex="-1" aria-disabled="true"' : '' ?>>
                                <span aria-hidden="true">&laquo;</span>
                                <span class="visually-hidden">Previous page</span>
                            </a>
                        </li>

                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                                <a class="page-link"
                                   href="<?= e(build_query(['page' => $i])) ?>"
                                   <?= $i === $page ? 'aria-current="page"' : '' ?>>
                                    <?= $i ?>
                                </a>
                            </li>
                        <?php endfor; ?>

                        <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                            <a class="page-link"
                               href="<?= e(build_query(['page' => $page + 1])) ?>"
                               <?= $page >= $totalPages ? 'tabindex="-1" aria-disabled="true"' : '' ?>>
                                <span aria-hidden="true">&raquo;</span>
                                <span class="visually-hidden">Next page</span>
                            </a>
                        </li>
                    </ul>
                </nav>

                <p class="text-center text-muted small mt-3">
                    Showing entries <?= $offset + 1 ?>&ndash;<?= min($offset + $perPage, $total) ?>
                    of <?= $total ?>
                </p>
            <?php endif; ?>

        <?php endif; ?>

    <?php endif; ?>

</div>

<?php require __DIR__ . '/../templates/footer.php'; ?>