<?php
/**
 * Event pass + registration summary.
 *
 * @var array $d          Registration data (always escaped on output with e()).
 * @var bool  $admin      True inside the admin panel.
 * @var array $allEvents  fest_events()
 */
$isTeam = $d['participation'] === 'Team';
?>
<article class="card result">
  <div class="success-banner">
    <?php if ($admin): ?>
      <h2>Registration <?= e($d['reg_id']) ?></h2>
      <p>Received on <?= e($d['submitted']) ?></p>
    <?php else: ?>
      <svg class="checkmark" viewBox="0 0 52 52" aria-hidden="true"><circle cx="26" cy="26" r="25"/><path d="M14 27l8 8 16-16"/></svg>
      <h2>You're registered, <?= e($d['first_name']) ?>!</h2>
      <p>Here is your event pass. Print it or save it as a PDF and show it at the registration desk.</p>
    <?php endif; ?>
  </div>

  <!-- ===== The pass (ticket style) ===== -->
  <div class="pass">
    <div class="pass-main">
      <div class="pass-head">
        <div>
          <span class="pass-fest"><?= e(FEST_NAME) ?></span>
          <span class="pass-sub"><?= e(FEST_DATES) ?> &middot; <?= e(FEST_VENUE) ?></span>
        </div>
        <span class="pass-badge"><?= $isTeam ? 'TEAM PASS' : 'PARTICIPANT' ?></span>
      </div>

      <div class="pass-person">
        <?php if (!empty($d['photo'])): ?>
          <img class="pass-photo" src="<?= e($d['photo']) ?>" alt="Photo of <?= e($d['full_name']) ?>">
        <?php else: ?>
          <div class="pass-photo pass-initials" aria-hidden="true"><?= e($d['initials']) ?></div>
        <?php endif; ?>
        <div>
          <h3><?= e($d['full_name']) ?></h3>
          <p><?= e($d['college']) ?></p>
          <p class="muted"><?= e($d['department']) ?> &middot; <?= e($d['year']) ?></p>
          <?php if ($isTeam): ?><p class="pass-team">Team <strong><?= e($d['team_name']) ?></strong> &middot; <?= count($d['team_members']) + 1 ?> members</p><?php endif; ?>
        </div>
      </div>

      <ul class="pass-events">
        <?php foreach ($d['events'] as $key): $ev = $allEvents[$key] ?? null; if (!$ev) { continue; } ?>
          <li>
            <span class="event-icon"><?= event_icon($ev['icon']) ?></span>
            <span><strong><?= e($ev['name']) ?></strong><small><?= e($ev['date']) ?> &middot; <?= e($ev['venue']) ?></small></span>
          </li>
        <?php endforeach; ?>
      </ul>
    </div>

    <div class="pass-stub">
      <div class="qr" data-qr="<?= e($d['qr']) ?>" role="img" aria-label="QR code for <?= e($d['reg_id']) ?>"></div>
      <small>Registration No.</small>
      <strong class="pass-id"><?= e($d['reg_id']) ?></strong>
      <small>Fee</small>
      <strong><?= rupees((int) $d['fee']) ?></strong>
      <span class="status-badge">Pay at desk</span>
    </div>
  </div>

  <!-- ===== Full details ===== -->
  <div class="details">
    <section class="detail-block">
      <h4>Participant</h4>
      <table>
        <tr><th>Name</th><td><?= e($d['full_name']) ?></td></tr>
        <tr><th>Email</th><td><?= e($d['email']) ?></td></tr>
        <tr><th>Mobile</th><td><?= e($d['phone']) ?></td></tr>
        <tr><th>Date of birth</th><td><?= e($d['dob_fmt']) ?></td></tr>
        <tr><th>Gender</th><td><?= e($d['gender']) ?></td></tr>
      </table>
    </section>

    <section class="detail-block">
      <h4>College</h4>
      <table>
        <tr><th>College</th><td><?= e($d['college']) ?></td></tr>
        <tr><th>Roll number</th><td><?= e($d['roll_no']) ?></td></tr>
        <tr><th>Department</th><td><?= e($d['department']) ?></td></tr>
        <tr><th>Year</th><td><?= e($d['year']) ?></td></tr>
        <tr><th>City / State</th><td><?= e($d['city']) ?>, <?= e($d['state']) ?></td></tr>
      </table>
    </section>

    <section class="detail-block">
      <h4>Participation</h4>
      <table>
        <tr><th>Type</th><td><?= e($d['participation']) ?></td></tr>
        <?php if ($isTeam): ?>
          <tr><th>Team name</th><td><?= e($d['team_name']) ?></td></tr>
          <tr><th>Members</th><td>
            <ol class="member-list">
              <li><?= e($d['full_name']) ?> <span class="tag">Leader</span></li>
              <?php foreach ($d['team_members'] as $m): ?><li><?= e($m) ?></li><?php endforeach; ?>
            </ol>
          </td></tr>
        <?php endif; ?>
        <tr><th>T-shirt</th><td><?= e($d['tshirt']) ?></td></tr>
        <tr><th>Food</th><td><?= e($d['food']) ?></td></tr>
        <tr><th>Hostel stay</th><td><?= e($d['accommodation']) ?></td></tr>
      </table>
    </section>

    <section class="detail-block">
      <h4>Fee Summary</h4>
      <table>
        <?php foreach ($d['events'] as $key): $ev = $allEvents[$key] ?? null; if (!$ev) { continue; } ?>
          <tr><th><?= e($ev['name']) ?></th><td class="num"><?= rupees($ev['fee']) ?></td></tr>
        <?php endforeach; ?>
        <?php if ($d['accommodation'] === 'Yes'): ?>
          <tr><th>Hostel stay</th><td class="num"><?= rupees(ACCOMMODATION_FEE) ?></td></tr>
        <?php endif; ?>
        <tr class="total"><th>Total</th><td class="num"><?= rupees((int) $d['fee']) ?></td></tr>
      </table>
    </section>
  </div>

  <div class="result-actions">
    <button type="button" class="btn btn-primary" onclick="window.print()">Print / Save Pass as PDF</button>
    <?php if ($admin): ?>
      <a href="index.php" class="btn btn-ghost">&larr; Back to all registrations</a>
    <?php else: ?>
      <a href="./" class="btn btn-ghost js-new">Register Another Participant</a>
    <?php endif; ?>
  </div>
</article>
