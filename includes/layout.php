<?php
/** Shared page header/footer used by every page (form, result fallback, admin panel). */

declare(strict_types=1);

/**
 * @param string $base  Relative path to the site root ('' or '../').
 * @param bool   $hero  Show the big fest poster with countdown (registration page only).
 */
function page_start(string $title, string $base = '', string $subtitle = '', string $nav = '', bool $hero = false): void
{
    $subtitle = $subtitle !== '' ? $subtitle : e(FEST_TAGLINE);
    $events = fest_events();
    ?>
<!DOCTYPE html>
<html lang="en" class="no-js">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($title) ?></title>
  <meta name="description" content="<?= e(FEST_NAME . ' - ' . FEST_TAGLINE . '. Online event registration.') ?>">
  <meta name="theme-color" content="#121212">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,500..800&family=Instrument+Sans:wght@400..700&family=JetBrains+Mono:wght@400;500;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= e($base) ?>assets/css/style.css">
  <script>document.documentElement.className = 'js';</script>
</head>
<body>
  <a class="skip-link" href="#main">Skip to content</a>

  <?php if ($hero): ?>
  <div class="ticker" aria-hidden="true">
    <div class="ticker-track">
      <?php for ($i = 0; $i < 2; $i++): ?>
        <span>
          <?php foreach ($events as $ev): ?><b><?= e($ev['name']) ?></b><i>&#10022;</i><?php endforeach; ?>
        </span>
      <?php endfor; ?>
    </div>
  </div>
  <?php endif; ?>

  <header class="site-header<?= $hero ? ' has-hero' : '' ?>">
    <div class="brand">
      <a class="logo" href="<?= e($base) ?>./" aria-label="<?= e(FEST_NAME) ?> home">
        <svg viewBox="0 0 24 24" width="24" height="24" fill="currentColor" aria-hidden="true"><path d="M13.5 1.5 3 14h8l-1.5 8.5L21 10h-8l.5-8.5z"/></svg>
      </a>
      <div class="brand-text">
        <span class="brand-name"><?= e(FEST_NAME) ?></span>
        <span class="brand-sub"><?= $subtitle ?></span>
      </div>
      <?= $nav ?>
    </div>

    <?php if ($hero): ?>
    <div class="hero">
      <p class="hero-kicker"><span class="live-dot"></span>Registrations open &middot; <?= count($events) ?> events &middot; 3 days</p>
      <h1 class="hero-title">
        <span class="hero-word">TechVerse</span>
        <span class="hero-year">2027</span>
      </h1>
      <p class="hero-sub">Code, build and compete with students from across the country. Pick up to <?= MAX_EVENTS ?> events, bring your team, and walk away with your pass in under three minutes.</p>

      <dl class="hero-facts">
        <div>
          <dt>Dates</dt>
          <dd><?= e(FEST_DATES) ?></dd>
        </div>
        <div>
          <dt>Venue</dt>
          <dd><?= e(FEST_VENUE) ?></dd>
        </div>
        <div class="hero-countdown">
          <dt>Starts in</dt>
          <dd class="countdown" data-start="<?= e(FEST_START) ?>" aria-label="Time until the fest starts">
            <span><strong data-unit="d">--</strong>d</span>
            <span><strong data-unit="h">--</strong>h</span>
            <span><strong data-unit="m">--</strong>m</span>
            <span><strong data-unit="s">--</strong>s</span>
          </dd>
        </div>
      </dl>
    </div>
    <?php else: ?>
    <h1 class="visually-hidden"><?= e($title) ?></h1>
    <?php endif; ?>
  </header>

  <main class="container" id="main">
    <?php
}

/** @param string $base Relative path to the site root. @param bool $app Load the registration-form script. */
function page_end(string $base = '', bool $app = false): void
{
    ?>
  </main>

  <footer class="site-footer">
    <span><?= e(FEST_NAME) ?> &middot; <?= e(FEST_TAGLINE) ?></span>
    <span>HTML &middot; CSS &middot; JavaScript &middot; jQuery &middot; PHP</span>
    <a href="<?= e($base) ?>admin/">Organiser login &rarr;</a>
  </footer>

  <script src="<?= e($base) ?>assets/vendor/qrcode-generator-1.4.4.js"></script>
  <script src="<?= e($base) ?>assets/js/qr.js"></script>
  <?php if ($app): ?>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js" integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
  <script>window.jQuery || document.write('<script src="<?= e($base) ?>assets/vendor/jquery-3.7.1.min.js"><\/script>');</script>
  <script src="<?= e($base) ?>assets/js/app.js"></script>
  <?php endif; ?>
</body>
</html>
    <?php
}
