<?php
declare(strict_types=1);
require __DIR__ . '/bootstrap.php';

require_post_with_csrf();
$_SESSION = [];
session_destroy();
header('Location: login.php');
