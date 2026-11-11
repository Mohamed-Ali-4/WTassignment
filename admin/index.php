<?php
declare(strict_types=1);
require __DIR__ . '/bootstrap.php';

require_admin();

$perPage = 15;
$events  = fest_events();
$q       = trim((string) ($_GET['q'] ?? ''));
$event   = (string) ($_GET['event'] ?? '');
$page    = max(1, (int) ($_GET['page'] ?? 1));

// ----- Filters -----
$where = [];
$params = [];
if ($q !== '') {
    $where[] = "(first_name || ' ' || last_name LIKE :q OR email LIKE :q OR reg_id LIKE :q OR phone LIKE :q
                 OR college LIKE :q OR team_name LIKE :q OR roll_no LIKE :q)";
    $params['q'] = '%' . $q . '%';
}
if (isset($events[$event])) {
    // events is stored as a JSON array like ["hackathon","tech_quiz"]; keys are whitelisted above
    $where[] = 'events LIKE :event';
    $params['event'] = '%"' . $event . '"%';
} else {
    $event = '';
}
$whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$count = db()->prepare("SELECT COUNT(*) FROM registrations $whereSql");
$count->execute($params);
$matching = (int) $count->fetchColumn();
$pages = max(1, (int) ceil($matching / $perPage));
$page = min($page, $pages);

$list = db()->prepare("SELECT id, reg_id, first_name, last_name, email, phone, college, year, events, participation,
        team_name, fee, created_at
    FROM registrations $whereSql ORDER BY id DESC LIMIT $perPage OFFSET " . (($page - 1) * $perPage));
$list->execute($params);
$rows = $list->fetchAll();

// ----- Stats -----
$total = (int) db()->query('SELECT COUNT(*) FROM registrations')->fetchColumn();
$todayStmt = db()->prepare('SELECT COUNT(*) FROM registrations WHERE date(created_at) = ?');
$todayStmt->execute([date('Y-m-d')]);
$today = (int) $todayStmt->fetchColumn();
$feeTotal = (int) db()->query('SELECT COALESCE(SUM(fee), 0) FROM registrations')->fetchColumn();

$eventCounts = array_fill_keys(array_keys($events), 0);
foreach (db()->query('SELECT events FROM registrations') as $r) {
    foreach (json_decode($r['events'], true) ?: [] as $k) {
        if (isset($eventCounts[$k])) {
            $eventCounts[$k]++;
        }
    }
}
arsort($eventCounts);
$topKey = $total ? array_key_first($eventCounts) : null;

$flash = $_SESSION['flash'] ?? '';
unset($_SESSION['flash']);

function page_url(array $overrides): string
{
    global $q, $event, $page;
    return '?' . http_build_query(array_filter(array_merge(['q' => $q, 'event' => $event, 'page' => $page], $overrides)));
}

page_start('Admin - Registrations', '../', 'Admin Panel', admin_nav());
?>
<section class="stats">
  <div class="stat"><small>Total registrations</small><strong><?= $total ?></strong></div>
  <div class="stat"><small>Registered today</small><strong><?= $today ?></strong></div>
  <div class="stat"><small>Fees expected</small><strong><?= rupees($feeTotal) ?></strong></div>
  <div class="stat"><small>Most popular event</small><strong class="stat-text"><?= $topKey ? e($events[$topKey]['name']) . ' <span class="muted">(' . $eventCounts[$topKey] . ')</span>' : '&ndash;' ?></strong></div>
</section>

<?php if ($total): ?>
<section class="card admin-card event-bars">
  <h3>Registrations per event</h3>
  <?php $max = max(1, max($eventCounts)); foreach ($eventCounts as $k => $n): ?>
    <a class="bar-row" href="?event=<?= e($k) ?>">
      <span class="bar-label"><?= e($events[$k]['name']) ?></span>
      <span class="bar-track"><span class="bar-fill" style="width: <?= round($n / $max * 100) ?>%"></span></span>
      <span class="bar-num"><?= $n ?></span>
    </a>
  <?php endforeach; ?>
</section>
<?php endif; ?>

<section class="card admin-card">
  <div class="admin-toolbar">
    <h2>Registrations</h2>
    <form method="get" class="filters">
      <input type="search" name="q" value="<?= e($q) ?>" placeholder="Search name, email, reg no., college, team...">
      <select name="event" onchange="this.form.submit()" aria-label="Filter by event">
        <option value="">All events</option>
        <?php foreach ($events as $k => $ev): ?>
          <option value="<?= e($k) ?>" <?= $k === $event ? 'selected' : '' ?>><?= e($ev['name']) ?></option>
        <?php endforeach; ?>
      </select>
      <button type="submit" class="btn btn-primary">Search</button>
      <?php if ($q !== '' || $event !== ''): ?><a href="index.php" class="btn btn-ghost">Clear</a><?php endif; ?>
      <a href="export.php" class="btn btn-ghost">Export CSV</a>
    </form>
  </div>

  <?php if ($flash): ?><p class="flash"><?= e($flash) ?></p><?php endif; ?>

  <?php if (!$rows): ?>
    <div class="empty">
      <p><?= $total ? 'No registrations match your search.' : 'No registrations yet. Submissions from the form will appear here.' ?></p>
    </div>
  <?php else: ?>
    <div class="table-wrap">
      <table class="admin-table">
        <thead>
          <tr><th>Reg. No.</th><th>Participant</th><th>College</th><th>Events</th><th>Fee</th><th>Registered</th><th class="actions-col">Actions</th></tr>
        </thead>
        <tbody>
        <?php foreach ($rows as $r): ?>
          <tr>
            <td data-label="Reg. No."><a href="view.php?id=<?= (int) $r['id'] ?>" class="app-link"><?= e($r['reg_id']) ?></a></td>
            <td data-label="Participant">
              <strong><?= e($r['first_name'] . ' ' . $r['last_name']) ?></strong>
              <small><?= e($r['email']) ?> &middot; <?= e($r['phone']) ?></small>
              <?php if ($r['participation'] === 'Team'): ?><small>Team: <?= e($r['team_name']) ?></small><?php endif; ?>
            </td>
            <td data-label="College"><?= e($r['college']) ?><small><?= e($r['year']) ?></small></td>
            <td data-label="Events">
              <div class="tags">
                <?php foreach (json_decode($r['events'], true) ?: [] as $k): ?>
                  <span class="tag"><?= e($events[$k]['name'] ?? $k) ?></span>
                <?php endforeach; ?>
              </div>
            </td>
            <td data-label="Fee"><?= rupees((int) $r['fee']) ?></td>
            <td data-label="Registered"><?= e(date('d M Y', strtotime($r['created_at']))) ?><small><?= e(date('h:i A', strtotime($r['created_at']))) ?></small></td>
            <td class="actions-col">
              <a href="view.php?id=<?= (int) $r['id'] ?>" class="btn btn-sm btn-primary">View</a>
              <form method="post" action="delete.php" onsubmit="return confirm('Delete registration <?= e($r['reg_id']) ?>?');">
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
page_end('../');
