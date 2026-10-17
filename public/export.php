<?php

declare(strict_types=1);

require __DIR__ . '/../bootstrap.php';

use App\Database;
use App\EntryRepository;
use App\Validator;

$search = trim((string) ($_GET['q'] ?? ''));
$range  = (string) ($_GET['range'] ?? 'all');

$rangeOptions = [
    'all'   => 0,
    'today' => 1,
    'week'  => 7,
    'month' => 30,
];
$days = $rangeOptions[$range] ?? 0;

$sortOptions = ['newest', 'oldest', 'name_asc', 'name_desc'];
$sort = (string) ($_GET['sort'] ?? 'newest');
if (!in_array($sort, $sortOptions, true)) {
    $sort = 'newest';
}

try {
    $pdo = Database::connect($config['database']);
    $repo = new EntryRepository($pdo);
    $entries = $repo->exportAll(
        $search !== '' ? $search : null,
        $days,
        $sort
    );
} catch (Throwable $e) {
    error_log('[export] ' . $e->getMessage());
    http_response_code(500);
    header('Content-Type: text/plain; charset=utf-8');
    exit('Could not generate export. Please try again.');
}

// Filename
$filenameParts = ['entries'];
if ($search !== '') {
    $safeSearch = preg_replace('/[^a-z0-9]+/i', '-', $search);
    $safeSearch = trim($safeSearch, '-');
    if ($safeSearch !== '') {
        $filenameParts[] = $safeSearch;
    }
}
if ($days > 0) {
    $filenameParts[] = 'last-' . $days . '-days';
}
if ($sort !== 'newest') {
    $filenameParts[] = $sort;
}
$filenameParts[] = date('Y-m-d');
$filename = implode('_', $filenameParts) . '.csv';

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: no-store');

$output = fopen('php://output', 'w');

// UTF-8 BOM for Excel compatibility
fwrite($output, "\xEF\xBB\xBF");

fputcsv($output, ['ID', 'Name', 'Email', 'Phone', 'Message', 'Received']);

foreach ($entries as $entry) {
    fputcsv($output, [
        $entry['id'],
        $entry['name'],
        $entry['email'],
        Validator::formatSaPhone($entry['phone']),
        $entry['message'],
        $entry['created_at'],
    ]);
}

fclose($output);
exit;