<?php
/**
 * Database layer (SQLite via PDO, prepared statements only).
 * The database file and table are created automatically on first use.
 * Location can be overridden with the DB_PATH environment variable.
 */

declare(strict_types=1);

function db(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $path = getenv('DB_PATH') ?: dirname(__DIR__) . '/data/techfest.db';
    $dir = dirname($path);
    if (!is_dir($dir)) {
        mkdir($dir, 0775, true);
    }

    $pdo = new PDO('sqlite:' . $path, null, null, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    $pdo->exec('PRAGMA journal_mode = WAL');
    $pdo->exec('CREATE TABLE IF NOT EXISTS registrations (
        id            INTEGER PRIMARY KEY AUTOINCREMENT,
        reg_id        TEXT NOT NULL UNIQUE,
        first_name    TEXT NOT NULL,
        last_name     TEXT NOT NULL,
        email         TEXT NOT NULL,
        phone         TEXT NOT NULL,
        dob           TEXT NOT NULL,
        gender        TEXT NOT NULL,
        college       TEXT NOT NULL,
        roll_no       TEXT NOT NULL,
        department    TEXT NOT NULL,
        year          TEXT NOT NULL,
        city          TEXT NOT NULL,
        state         TEXT NOT NULL,
        events        TEXT NOT NULL,               -- JSON array of event keys
        participation TEXT NOT NULL,
        team_name     TEXT NOT NULL DEFAULT "",
        team_members  TEXT NOT NULL DEFAULT "[]",  -- JSON array of names
        tshirt        TEXT NOT NULL,
        food          TEXT NOT NULL,
        accommodation TEXT NOT NULL,
        fee           INTEGER NOT NULL,
        photo         TEXT,
        created_at    TEXT NOT NULL
    )');

    return $pdo;
}

const DB_COLUMNS = [
    'first_name', 'last_name', 'email', 'phone', 'dob', 'gender', 'college', 'roll_no', 'department',
    'year', 'city', 'state', 'participation', 'team_name', 'tshirt', 'food', 'accommodation', 'fee', 'photo',
];

/** Insert a validated registration; returns it with id, reg_id and created_at filled in. */
function save_registration(array $d): array
{
    $cols = array_merge(DB_COLUMNS, ['events', 'team_members', 'reg_id', 'created_at']);
    $stmt = db()->prepare('INSERT INTO registrations (' . implode(',', $cols) . ') VALUES (:' . implode(',:', $cols) . ')');

    $d['created_at'] = date('Y-m-d H:i:s');
    for ($attempt = 0; ; $attempt++) {
        $d['reg_id'] = registration_id();
        $params = array_intersect_key($d, array_flip(DB_COLUMNS));
        $params['events']       = json_encode($d['events']);
        $params['team_members'] = json_encode($d['team_members']);
        $params['reg_id']       = $d['reg_id'];
        $params['created_at']   = $d['created_at'];
        try {
            $stmt->execute($params);
            $d['id'] = (int) db()->lastInsertId();
            return $d;
        } catch (PDOException $ex) {
            // Extremely unlikely reg_id collision: retry with a new number
            if ($attempt >= 3 || stripos($ex->getMessage(), 'UNIQUE') === false) {
                throw $ex;
            }
        }
    }
}

/** Turn a database row back into the shape render_pass() expects. */
function row_to_registration(array $row): array
{
    $row['events']       = json_decode($row['events'], true) ?: [];
    $row['team_members'] = json_decode($row['team_members'], true) ?: [];
    $row['fee']          = (int) $row['fee'];
    return $row;
}
