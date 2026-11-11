<?php
declare(strict_types=1);
require __DIR__ . '/bootstrap.php';

require_admin();

/** Stop spreadsheet apps from treating a cell as a formula. */
function csv_safe(string $v): string
{
    return preg_match('/^[=+\-@\t\r]/', $v) ? "'" . $v : $v;
}

$cols = ['reg_id', 'created_at', 'first_name', 'last_name', 'email', 'phone', 'dob', 'gender', 'college',
    'roll_no', 'department', 'year', 'city', 'state', 'events', 'participation', 'team_name', 'team_members',
    'tshirt', 'food', 'accommodation', 'fee'];
$events = fest_events();

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="techfest-registrations-' . date('Y-m-d') . '.csv"');

$out = fopen('php://output', 'w');
fwrite($out, "\xEF\xBB\xBF"); // UTF-8 BOM so Excel shows text correctly
fputcsv($out, $cols, ',', '"', '');
foreach (db()->query('SELECT ' . implode(',', $cols) . ' FROM registrations ORDER BY id DESC') as $row) {
    $row['events'] = implode('; ', array_map(fn ($k) => $events[$k]['name'] ?? $k, json_decode($row['events'], true) ?: []));
    $row['team_members'] = implode('; ', json_decode($row['team_members'], true) ?: []);
    fputcsv($out, array_map(fn ($v) => csv_safe((string) $v), $row), ',', '"', '');
}
fclose($out);
