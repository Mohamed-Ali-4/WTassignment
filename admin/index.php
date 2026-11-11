<?php
declare(strict_types=1);
require __DIR__ . '/bootstrap.php';

require_admin();

$perPage = 15;
$q       = trim((string) ($_GET['q'] ?? ''));
$program = (string) ($_GET['program'] ?? '');
$page    = max(1, (int) ($_GET['page'] ?? 1));

// ----- Filters -----
$where = [];
$params = [];
if ($q !== '') {
    $where[] = "(first_name || ' ' || last_name LIKE :q OR email LIKE :q OR app_id LIKE :q OR phone LIKE :q OR city LIKE :q)";
    $params['q'] = '%' . $q . '%';
}
if (in_array($program, ALLOWED_PROGRAMS, true)) {
    $where[] = 'program = :program';
    $params['program'] = $program;
} else {
    $program = '';
}
$whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$count = db()->prepare("SELECT COUNT(*) FROM applications $whereSql");
$count->execute($params);
$matching = (int) $count->fetchColumn();
$pages = max(1, (int) ceil($matching / $perPage));
$page = min($page, $pages);

$list = db()->prepare("SELECT id, app_id, first_name, last_name, email, phone, program, mode, percentage, created_at, photo IS NOT NULL AS has_photo
    FROM applications $whereSql ORDER BY id DESC LIMIT $perPage OFFSET " . (($page - 1) * $perPage));
$list->execute($params);
$rows = $list->fetchAll();

// ----- Stats -----
$total = (int) db()->query('SELECT COUNT(*) FROM applications')->fetchColumn();
$today = db()->prepare('SELECT COUNT(*) FROM applications WHERE date(created_at) = ?');
$today->execute([date('Y-m-d')]);
$today = (int) $today->fetchColumn();
$avg = db()->query('SELECT AVG(CAST(percentage AS REAL)) FROM applications')->fetchColumn();
$top = db()->query('SELECT program, COUNT(*) c FROM applications GROUP BY program ORDER BY c DESC LIMIT 1')->fetch();

$flash = $_SESSION['flash'] ?? '';
unset($_SESSION['flash']);

function page_url(array $overrides): string
{
    global $q, $program, $page;
    return '?' . http_build_query(array_filter(array_merge(['q' => $q, 'program' => $program, 'page' => $page], $overrides)));
}

page_start('Admin - Applications', '../', 'Admin Panel', admin_nav());
?>
<section class="stats">
  <div class="stat"><small>Total applications</small><strong><?= $total ?></strong></div>
  <div class="stat"><small>Received today</small><strong><?= $today ?></strong></div>
  <div class="stat"><small>Average score</small><strong><?= $avg !== null && $avg !== false ? e(number_format((float) $avg, 1)) . '%' : '&ndash;' ?></strong></div>
  <div class="stat"><small>Most popular program</small><strong class="stat-text"><?= $top ? e($top['program']) : '&ndash;' ?></strong></div>
</section>

<section class="card admin-card">
  <div class="admin-toolbar">
    <h2>Applications</h2>
    <form method="get" class="filters">
      <input type="search" name="q" value="<?= e($q) ?>" placeholder="Search name, email, app no., phone...">
      <select name="program" onchange="this.form.submit()">
        <option value="">All programs</option>
        <?php foreach (ALLOWED_PROGRAMS as $p): ?>
          <option value="<?= e($p) ?>" <?= $p === $program ? 'selected' : '' ?>><?= e($p) ?></option>
        <?php endforeach; ?>
      </select>
      <button type="submit" class="btn btn-primary">Search</button>
      <?php if ($q !== '' || $program !== ''): ?><a href="index.php" class="btn btn-ghost">Clear</a><?php endif; ?>
      <a href="export.php" class="btn btn-ghost">Export CSV</a>
    </form>
  </div>

  <?php if ($flash): ?><p class="flash"><?= e($flash) ?></p><?php endif; ?>

  <?php if (!$rows): ?>
    <div class="empty">
      <p><?= $total ? 'No applications match your search.' : 'No applications yet. Submissions from the form will appear here.' ?></p>
    </div>
  <?php else: ?>
    <div class="table-wrap">
      <table class="admin-table">
        <thead>
          <tr><th>Application</th><th>Applicant</th><th>Program</th><th>Score</th><th>Submitted</th><th class="actions-col">Actions</th></tr>
        </thead>
        <tbody>
        <?php foreach ($rows as $r): ?>
          <tr>
            <td data-label="Application"><a href="view.php?id=<?= (int) $r['id'] ?>" class="app-link"><?= e($r['app_id']) ?></a></td>
            <td data-label="Applicant">
              <strong><?= e($r['first_name'] . ' ' . $r['last_name']) ?></strong>
              <small><?= e($r['email']) ?> &middot; <?= e($r['phone']) ?></small>
            </td>
            <td data-label="Program"><?= e($r['program']) ?><small><?= e($r['mode']) ?></small></td>
            <td data-label="Score"><?= e($r['percentage']) ?>%</td>
            <td data-label="Submitted"><?= e(date('d M Y', strtotime($r['created_at']))) ?><small><?= e(date('h:i A', strtotime($r['created_at']))) ?></small></td>
            <td class="actions-col">
              <a href="view.php?id=<?= (int) $r['id'] ?>" class="btn btn-sm btn-primary">View</a>
              <form method="post" action="delete.php" onsubmit="return confirm('Delete application <?= e($r['app_id']) ?>?');">
                <?= csrf_field() ?>
                <input type="hidden" name="id" value="<?= (int) $r['id'] ?>">
                <button type="submit" class="btn btn-sm btn-danger-ghost">Delete</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <div class="pager">
      <span><?= $matching ?> result<?= $matching === 1 ? '' : 's' ?> &middot; page <?= $page ?> of <?= $pages ?></span>
      <span>
        <?php if ($page > 1): ?><a class="btn btn-sm btn-ghost" href="<?= e(page_url(['page' => $page - 1])) ?>">&larr; Prev</a><?php endif; ?>
        <?php if ($page < $pages): ?><a class="btn btn-sm btn-ghost" href="<?= e(page_url(['page' => $page + 1])) ?>">Next &rarr;</a><?php endif; ?>
      </span>
    </div>
  <?php endif; ?>
</section>
<?php
page_end();
