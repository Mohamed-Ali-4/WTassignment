<?php
declare(strict_types=1);
require __DIR__ . '/bootstrap.php';

require_admin();

$id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);
$stmt = db()->prepare('SELECT * FROM applications WHERE id = ?');
$stmt->execute([$id ?: 0]);
$row = $stmt->fetch();

if (!$row) {
    http_response_code(404);
    page_start('Not found', '../', 'Admin Panel', admin_nav());
    echo '<section class="card"><h2>Application not found</h2><p><a class="btn btn-primary" href="index.php">&larr; Back to all applications</a></p></section>';
    page_end();
    exit;
}

page_start('Application ' . $row['app_id'], '../', 'Admin Panel', admin_nav());
echo render_result(row_to_application($row), true);
?>
<form method="post" action="delete.php" class="danger-zone" onsubmit="return confirm('Delete this application permanently?');">
  <?= csrf_field() ?>
  <input type="hidden" name="id" value="<?= (int) $row['id'] ?>">
  <button type="submit" class="btn btn-danger">Delete this application</button>
</form>
<?php
page_end();
