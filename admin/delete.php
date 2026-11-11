<?php
declare(strict_types=1);
require __DIR__ . '/bootstrap.php';

require_admin();
require_post_with_csrf();

$id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
if ($id) {
    db()->prepare('DELETE FROM registrations WHERE id = ?')->execute([$id]);
    $_SESSION['flash'] = 'Registration deleted.';
}
header('Location: index.php');
