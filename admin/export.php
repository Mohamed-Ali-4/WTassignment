<?php
declare(strict_types=1);
require __DIR__ . '/bootstrap.php';

require_admin();

/** Stop spreadsheet apps from treating a cell as a formula. */
function csv_safe(string $v): string
{
    return preg_match('/^[=+\-@\t\r]/', $v) ? "'" . $v : $v;
}

$cols = ['app_id', 'created_at', 'first_name', 'last_name', 'dob', 'gender', 'nationality', 'category',
    'program', 'mode', 'qualification', 'percentage', 'passing_year', 'institution', 'interests',
    'email', 'phone', 'address', 'city', 'state', 'pincode', 'statement'];

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="applications-' . date('Y-m-d') . '.csv"');

$out = fopen('php://output', 'w');
fwrite($out, "\xEF\xBB\xBF"); // UTF-8 BOM so Excel shows accents correctly
fputcsv($out, $cols, ',', '"', '');
foreach (db()->query('SELECT ' . implode(',', $cols) . ' FROM applications ORDER BY id DESC') as $row) {
    $row['interests'] = implode('; ', json_decode($row['interests'], true) ?: []);
    fputcsv($out, array_map(fn ($v) => csv_safe((string) $v), $row), ',', '"', '');
}
fclose($out);
