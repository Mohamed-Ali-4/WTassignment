<?php
/**
 * @var array $d      Application data (escaped on output with e()).
 * @var bool  $admin  True when shown inside the admin panel.
 */
?>
<article class="card result">
  <div class="success-banner">
    <svg class="checkmark" viewBox="0 0 52 52" aria-hidden="true">
      <circle cx="26" cy="26" r="25"/>
      <path d="M14 27l8 8 16-16"/>
    </svg>
    <?php if ($admin): ?>
    <h2>Application Details</h2>
    <p>Submitted by <?= e($d['first_name'] . ' ' . $d['last_name']) ?></p>
    <?php else: ?>
    <h2>Application Submitted Successfully!</h2>
    <p>Thank you, <?= e($d['first_name']) ?>. Your application has been received. Please keep a copy for your records.</p>
    <?php endif; ?>
  </div>

  <div class="app-meta">
    <div><small>Application No.</small><strong><?= e($d['app_id']) ?></strong></div>
    <div><small>Submitted on</small><strong><?= e($d['submitted']) ?></strong></div>
    <div><small>Status</small><span class="status-badge">Received</span></div>
  </div>

  <div class="profile">
    <?php if ($d['photo']): ?>
      <img class="profile-photo" src="<?= e($d['photo']) ?>" alt="Photo of <?= e($d['full_name']) ?>">
    <?php else: ?>
      <div class="profile-initials" aria-hidden="true"><?= e($d['initials']) ?></div>
    <?php endif; ?>
    <div>
      <h3><?= e($d['full_name']) ?></h3>
      <p><?= e($d['program']) ?> &middot; <?= e($d['mode']) ?></p>
      <p><?= e($d['email']) ?></p>
    </div>
  </div>

  <div class="details">
    <section class="detail-block">
      <h4>Personal Details</h4>
      <table>
        <tr><th>Full name</th><td><?= e($d['full_name']) ?></td></tr>
        <tr><th>Date of birth</th><td><?= e($d['dob_fmt']) ?> (<?= (int) $d['age'] ?> yrs)</td></tr>
        <tr><th>Gender</th><td><?= e($d['gender']) ?></td></tr>
        <tr><th>Nationality</th><td><?= e($d['nationality']) ?></td></tr>
        <tr><th>Category</th><td><?= e($d['category']) ?></td></tr>
      </table>
    </section>

    <section class="detail-block">
      <h4>Academic Details</h4>
      <table>
        <tr><th>Program</th><td><?= e($d['program']) ?></td></tr>
        <tr><th>Study mode</th><td><?= e($d['mode']) ?></td></tr>
        <tr><th>Qualification</th><td><?= e($d['qualification']) ?></td></tr>
        <tr><th>Score</th><td><?= e($d['percentage']) ?>%</td></tr>
        <tr><th>Passed in</th><td><?= e($d['passing_year']) ?></td></tr>
        <tr><th>Institution</th><td><?= e($d['institution']) ?></td></tr>
      </table>
    </section>

    <section class="detail-block">
      <h4>Contact Details</h4>
      <table>
        <tr><th>Email</th><td><?= e($d['email']) ?></td></tr>
        <tr><th>Mobile</th><td><?= e($d['phone']) ?></td></tr>
        <tr><th>Address</th><td><?= nl2br(e($d['address'])) ?></td></tr>
        <tr><th>City / State</th><td><?= e($d['city']) ?>, <?= e($d['state']) ?></td></tr>
        <tr><th>PIN / ZIP</th><td><?= e($d['pincode']) ?></td></tr>
      </table>
    </section>

    <section class="detail-block">
      <h4>Areas of Interest</h4>
      <table>
        <tr><td>
          <?php if ($d['interests']): ?>
            <div class="tags">
              <?php foreach ($d['interests'] as $interest): ?>
                <span class="tag"><?= e($interest) ?></span>
              <?php endforeach; ?>
            </div>
          <?php else: ?>
            <span class="optional">None selected</span>
          <?php endif; ?>
        </td></tr>
      </table>
    </section>

    <?php if ($d['statement'] !== ''): ?>
    <section class="detail-block full">
      <h4>Statement of Purpose</h4>
      <p class="sop"><?= e($d['statement']) ?></p>
    </section>
    <?php endif; ?>
  </div>

  <div class="result-actions">
    <button type="button" class="btn btn-primary js-print" onclick="window.print()">Print / Save as PDF</button>
    <?php if ($admin): ?>
    <a href="index.php" class="btn btn-ghost">&larr; Back to all applications</a>
    <?php else: ?>
    <a href="index.html" class="btn btn-ghost js-new">New Application</a>
    <?php endif; ?>
  </div>
</article>
