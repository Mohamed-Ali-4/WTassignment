<?php
/**
 * Registration page (front end). Event cards and choice lists are generated from includes/config.php
 * so the form always matches what the server accepts.
 */

declare(strict_types=1);

require __DIR__ . '/includes/config.php';
require __DIR__ . '/includes/functions.php';
require __DIR__ . '/includes/layout.php';

page_start(FEST_NAME . ' - Event Registration', '', '', '', true);
?>
<section id="form-section" class="card">
  <ol class="stepper" aria-label="Form progress">
    <li class="active" data-step="1"><span class="dot">01</span><span class="label">Participant</span></li>
    <li data-step="2"><span class="dot">02</span><span class="label">College</span></li>
    <li data-step="3"><span class="dot">03</span><span class="label">Events &amp; Team</span></li>
  </ol>
  <div class="progress"><div class="progress-bar" style="width:33.33%"></div></div>

  <form id="regForm" action="submit.php" method="post" enctype="multipart/form-data" novalidate>

    <!-- ===================== STEP 1: Participant ===================== -->
    <fieldset class="step active" data-step="1">
      <legend><span class="legend-no">01</span>Who are you?</legend>

      <div class="photo-row">
        <label for="photo" class="photo-upload" title="Upload a photo for your event pass">
          <img id="photoPreview" alt="" hidden>
          <span class="photo-placeholder">
            <svg viewBox="0 0 24 24" width="34" height="34" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4.4 3.6-8 8-8s8 3.6 8 8"/></svg>
            <small>Add photo</small>
          </span>
        </label>
        <input type="file" id="photo" name="photo" accept="image/png,image/jpeg,image/webp" class="visually-hidden">
        <div class="photo-help">
          <strong>Photo for your pass</strong> <span class="optional">(optional)</span>
          <p>JPG, PNG or WEBP, max 2&nbsp;MB.</p>
          <span class="error" data-for="photo"></span>
        </div>
      </div>

      <div class="grid two">
        <div class="field">
          <label for="first_name">First name <span class="req">*</span></label>
          <input type="text" id="first_name" name="first_name" maxlength="50" autocomplete="given-name" placeholder="e.g. Mohamed" required>
          <span class="error" data-for="first_name"></span>
        </div>
        <div class="field">
          <label for="last_name">Last name <span class="req">*</span></label>
          <input type="text" id="last_name" name="last_name" maxlength="50" autocomplete="family-name" placeholder="e.g. Ali" required>
          <span class="error" data-for="last_name"></span>
        </div>
      </div>

      <div class="grid two">
        <div class="field">
          <label for="email">Email address <span class="req">*</span></label>
          <input type="email" id="email" name="email" maxlength="100" autocomplete="email" placeholder="you@example.com" required>
          <span class="hint">Your pass details will be linked to this email.</span>
          <span class="error" data-for="email"></span>
        </div>
        <div class="field">
          <label for="phone">Mobile number <span class="req">*</span></label>
          <input type="tel" id="phone" name="phone" maxlength="15" autocomplete="tel" placeholder="10-digit mobile number" required>
          <span class="error" data-for="phone"></span>
        </div>
      </div>

      <div class="grid two">
        <div class="field">
          <label for="dob">Date of birth <span class="req">*</span></label>
          <input type="date" id="dob" name="dob" required>
          <span class="hint" id="ageHint"></span>
          <span class="error" data-for="dob"></span>
        </div>
        <div class="field">
          <span class="label">Gender <span class="req">*</span></span>
          <div class="pill-group" role="radiogroup" aria-label="Gender">
            <?php foreach (ALLOWED_GENDERS as $g): ?>
              <label class="pill"><input type="radio" name="gender" value="<?= e($g) ?>" required><span><?= e($g) ?></span></label>
            <?php endforeach; ?>
          </div>
          <span class="error" data-for="gender"></span>
        </div>
      </div>

      <div class="actions">
        <span></span>
        <button type="button" class="btn btn-primary next">Next <span aria-hidden="true">&rarr;</span></button>
      </div>
    </fieldset>

    <!-- ===================== STEP 2: College ===================== -->
    <fieldset class="step" data-step="2">
      <legend><span class="legend-no">02</span>Where do you study?</legend>

      <div class="grid two">
        <div class="field">
          <label for="college">College / University <span class="req">*</span></label>
          <input type="text" id="college" name="college" maxlength="120" autocomplete="organization" placeholder="Full name of your college" required>
          <span class="error" data-for="college"></span>
        </div>
        <div class="field">
          <label for="roll_no">Roll / Register number <span class="req">*</span></label>
          <input type="text" id="roll_no" name="roll_no" maxlength="20" placeholder="e.g. 22CS1045" required>
          <span class="error" data-for="roll_no"></span>
        </div>
      </div>

      <div class="field">
        <label for="department">Department <span class="req">*</span></label>
        <select id="department" name="department" required>
          <option value="">Select your department</option>
          <?php foreach (ALLOWED_DEPARTMENTS as $d): ?>
            <option><?= e($d) ?></option>
          <?php endforeach; ?>
        </select>
        <span class="error" data-for="department"></span>
      </div>

      <div class="field">
        <span class="label">Year of study <span class="req">*</span></span>
        <div class="pill-group" role="radiogroup" aria-label="Year of study">
          <?php foreach (ALLOWED_YEARS as $y): ?>
            <label class="pill"><input type="radio" name="year" value="<?= e($y) ?>" required><span><?= e($y) ?></span></label>
          <?php endforeach; ?>
        </div>
        <span class="error" data-for="year"></span>
      </div>

      <div class="grid two">
        <div class="field">
          <label for="city">City <span class="req">*</span></label>
          <input type="text" id="city" name="city" maxlength="50" autocomplete="address-level2" required>
          <span class="error" data-for="city"></span>
        </div>
        <div class="field">
          <label for="state">State <span class="req">*</span></label>
          <input type="text" id="state" name="state" maxlength="50" autocomplete="address-level1" required>
          <span class="error" data-for="state"></span>
        </div>
      </div>

      <div class="actions">
        <button type="button" class="btn btn-ghost prev"><span aria-hidden="true">&larr;</span> Back</button>
        <button type="button" class="btn btn-primary next">Next <span aria-hidden="true">&rarr;</span></button>
      </div>
    </fieldset>

    <!-- ===================== STEP 3: Events & Team ===================== -->
    <fieldset class="step" data-step="3">
      <legend><span class="legend-no">03</span>Pick your battles</legend>

      <div class="field">
        <span class="label">Choose your events <span class="req">*</span> <span class="optional">(up to <?= MAX_EVENTS ?>)</span></span>
        <div class="event-grid" data-max="<?= MAX_EVENTS ?>">
          <?php $n = 0; foreach (fest_events() as $key => $ev): $n++; ?>
            <label class="event-card">
              <input type="checkbox" name="events[]" value="<?= e($key) ?>" data-fee="<?= (int) $ev['fee'] ?>" data-name="<?= e($ev['name']) ?>">
              <span class="event-inner">
                <span class="event-top">
                  <span class="event-icon"><?= event_icon($ev['icon']) ?></span>
                  <span class="event-cat"><span class="event-no"><?= sprintf('%02d', $n) ?></span> <?= e($ev['category']) ?></span>
                  <span class="event-check" aria-hidden="true"></span>
                </span>
                <strong class="event-name"><?= e($ev['name']) ?></strong>
                <span class="event-blurb"><?= e($ev['blurb']) ?></span>
                <span class="event-foot">
                  <span><?= e($ev['date']) ?> &middot; <?= e($ev['venue']) ?></span>
                  <b><?= rupees($ev['fee']) ?></b>
                </span>
              </span>
            </label>
          <?php endforeach; ?>
        </div>
        <span class="hint" id="eventHint">Pick at least one event.</span>
        <span class="error" data-for="events"></span>
      </div>

      <div class="field">
        <span class="label">Participation <span class="req">*</span></span>
        <div class="pill-group" role="radiogroup" aria-label="Participation">
          <label class="pill"><input type="radio" name="participation" value="Solo" checked><span>Solo</span></label>
          <label class="pill"><input type="radio" name="participation" value="Team"><span>Team</span></label>
        </div>
        <span class="error" data-for="participation"></span>
      </div>

      <div class="team-box" id="teamBox">
        <div class="field">
          <label for="team_name">Team name <span class="req">*</span></label>
          <input type="text" id="team_name" name="team_name" maxlength="50" placeholder="e.g. Byte Busters">
          <span class="error" data-for="team_name"></span>
        </div>
        <div class="field">
          <span class="label">Team members <span class="optional">(besides you, up to <?= MAX_TEAM_MEMBERS ?>)</span></span>
          <div class="members">
            <?php for ($i = 1; $i <= MAX_TEAM_MEMBERS; $i++): ?>
              <div class="member-row" data-index="<?= $i ?>">
                <span class="member-num"><?= $i + 1 ?></span>
                <input type="text" name="team_members[]" maxlength="60" placeholder="Member <?= $i + 1 ?> full name" aria-label="Team member <?= $i + 1 ?> name">
                <button type="button" class="icon-btn remove-member" aria-label="Remove member">&times;</button>
              </div>
            <?php endfor; ?>
          </div>
          <button type="button" class="btn btn-ghost btn-sm" id="addMember">+ Add member</button>
          <span class="error" data-for="team_members"></span>
        </div>
      </div>

      <div class="grid three">
        <div class="field">
          <label for="tshirt">T-shirt size <span class="req">*</span></label>
          <select id="tshirt" name="tshirt" required>
            <option value="">Select</option>
            <?php foreach (ALLOWED_TSHIRTS as $t): ?><option><?= e($t) ?></option><?php endforeach; ?>
          </select>
          <span class="error" data-for="tshirt"></span>
        </div>
        <div class="field">
          <span class="label">Food preference <span class="req">*</span></span>
          <div class="pill-group" role="radiogroup" aria-label="Food preference">
            <?php foreach (ALLOWED_FOOD as $i => $f): ?>
              <label class="pill"><input type="radio" name="food" value="<?= e($f) ?>" <?= $i === 0 ? 'checked' : '' ?>><span><?= e($f) ?></span></label>
            <?php endforeach; ?>
          </div>
          <span class="error" data-for="food"></span>
        </div>
        <div class="field">
          <span class="label">Hostel stay <span class="optional">(+<?= rupees(ACCOMMODATION_FEE) ?>)</span></span>
          <div class="pill-group" role="radiogroup" aria-label="Hostel accommodation">
            <label class="pill"><input type="radio" name="accommodation" value="No" checked><span>No</span></label>
            <label class="pill"><input type="radio" name="accommodation" value="Yes" data-fee="<?= ACCOMMODATION_FEE ?>"><span>Yes</span></label>
          </div>
          <span class="error" data-for="accommodation"></span>
        </div>
      </div>

      <div class="fee-box" aria-live="polite">
        <div class="fee-head">
          <span>Total payable</span>
          <strong id="feeTotal"><?= rupees(0) ?></strong>
        </div>
        <ul id="feeLines"><li class="muted">No events selected yet.</li></ul>
        <p class="hint">Fee is payable at the registration desk when you collect your kit.</p>
      </div>

      <div class="field">
        <label class="check">
          <input type="checkbox" id="declaration" name="declaration" value="1" required>
          <span>I confirm the details above are correct and I agree to follow the fest's code of conduct.</span>
        </label>
        <span class="error" data-for="declaration"></span>
      </div>

      <div class="actions">
        <button type="button" class="btn btn-ghost prev"><span aria-hidden="true">&larr;</span> Back</button>
        <div class="right">
          <button type="reset" class="btn btn-ghost" id="resetBtn">Reset</button>
          <button type="submit" class="btn btn-success" id="submitBtn">
            <span class="btn-text">Register Now</span>
            <span class="spinner" aria-hidden="true"></span>
          </button>
        </div>
      </div>
    </fieldset>

    <div class="form-alert" role="alert" hidden></div>
  </form>
</section>

<!-- The event pass is injected here by jQuery after a successful registration -->
<section id="result-section" aria-live="polite"></section>
<?php
page_end('', true);
