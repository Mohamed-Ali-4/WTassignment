<?php
/**
 * Database layer (SQLite via PDO). The database file and table are created automatically.
 * Location can be overridden with the DB_PATH environment variable.
 */

declare(strict_types=1);

function db(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $path = getenv('DB_PATH') ?: dirname(__DIR__) . '/data/applications.db';
    $dir = dirname($path);
    if (!is_dir($dir)) {
        mkdir($dir, 0775, true);
    }

    $pdo = new PDO('sqlite:' . $path, null, null, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    $pdo->exec('PRAGMA journal_mode = WAL');
    $pdo->exec('CREATE TABLE IF NOT EXISTS applications (
        id            INTEGER PRIMARY KEY AUTOINCREMENT,
        app_id        TEXT NOT NULL UNIQUE,
        first_name    TEXT NOT NULL,
        last_name     TEXT NOT NULL,
        dob           TEXT NOT NULL,
        gender        TEXT NOT NULL,
        nationality   TEXT NOT NULL,
        category      TEXT NOT NULL,
        program       TEXT NOT NULL,
        mode          TEXT NOT NULL,
        qualification TEXT NOT NULL,
        percentage    TEXT NOT NULL,
        passing_year  TEXT NOT NULL,
        institution   TEXT NOT NULL,
        interests     TEXT NOT NULL DEFAULT "[]",
        statement     TEXT NOT NULL DEFAULT "",
        email         TEXT NOT NULL,
        phone         TEXT NOT NULL,
        address       TEXT NOT NULL,
        city          TEXT NOT NULL,
        state         TEXT NOT NULL,
        pincode       TEXT NOT NULL,
        photo         TEXT,
        created_at    TEXT NOT NULL
    )');

    return $pdo;
}

const DB_COLUMNS = [
    'first_name', 'last_name', 'dob', 'gender', 'nationality', 'category', 'program', 'mode',
    'qualification', 'percentage', 'passing_year', 'institution', 'statement', 'email', 'phone',
    'address', 'city', 'state', 'pincode', 'photo',
];

/** Insert a validated application; returns it with app_id and created_at filled in. */
function save_application(array $d): array
{
    $cols = array_merge(DB_COLUMNS, ['interests', 'app_id', 'created_at']);
    $sql = 'INSERT INTO applications (' . implode(',', $cols) . ') VALUES (:' . implode(',:', $cols) . ')';
    $stmt = db()->prepare($sql);

    $d['created_at'] = date('Y-m-d H:i:s');
    for ($attempt = 0; ; $attempt++) {
        $d['app_id'] = application_id();
        $params = [];
        foreach (DB_COLUMNS as $c) {
            $params[$c] = $d[$c];
        }
        $params['interests']  = json_encode($d['interests']);
        $params['app_id']     = $d['app_id'];
        $params['created_at'] = $d['created_at'];
        try {
            $stmt->execute($params);
            $d['id'] = (int) db()->lastInsertId();
            return $d;
        } catch (PDOException $ex) {
            // Extremely unlikely app_id collision: try a new number a couple of times
            if ($attempt >= 3 || stripos($ex->getMessage(), 'UNIQUE') === false) {
                throw $ex;
            }
        }
    }
}

/** Turn a database row back into the shape render_result() expects. */
function row_to_application(array $row): array
{
    $row['interests'] = json_decode($row['interests'], true) ?: [];
    $dob = new DateTime($row['dob']);
    $row['age'] = $dob->diff(new DateTime($row['created_at']))->y;
    return $row;
}
