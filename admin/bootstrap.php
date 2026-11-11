<?php
/** Loaded by every admin page. */

declare(strict_types=1);

require dirname(__DIR__) . '/includes/config.php';
require dirname(__DIR__) . '/includes/functions.php';
require dirname(__DIR__) . '/includes/db.php';
require dirname(__DIR__) . '/includes/layout.php';
require dirname(__DIR__) . '/includes/auth.php';

header('X-Frame-Options: DENY');
header('Cache-Control: no-store');
