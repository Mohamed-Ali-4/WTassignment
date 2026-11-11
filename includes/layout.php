<?php
/** Shared page header/footer used by every page (form, result fallback, admin panel). */

declare(strict_types=1);

/**
 * @param string $base  Relative path to the site root ('' or '../').
 * @param bool   $hero  Show the big fest banner with countdown (registration page only).
 */
function page_start(string $title, string $base = '', string $subtitle = '', string $nav = '', bool $hero = false): void
{
    $subtitle = $subtitle !== '' ? $subtitle : e(FEST_TAGLINE);
    ?>
<!DOCTYPE html>
<html lang="en" class="no-js">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($title) ?></title>
  <meta name="description" content="<?= e(FEST_NAME . ' - ' . FEST_TAGLINE . '. Online event registration.') ?>">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= e($base) ?>assets/css/style.css">
  <script>document.documentElement.className = 'js';</script>
</head>
<body>
  <div class="bg-shapes" aria-hidden="true"><span></span><span></span><span></span></div>

  <header class="site-header<?= $hero ? ' has-hero' : '' ?>">
    <div class="brand">
      <a class="logo" href="<?= e($base) ?>./" aria-label="<?= e(FEST_NAME) ?> home">
        <svg viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M13 2 3 14h9l-1 8 10-12h-9l1-8z"/></svg>
      </a>
      <div class="brand-text">
        <h1><?= e(FEST_NAME) ?></h1>
        <p><?= $subtitle ?></p>
      </div>
      <?= $nav ?>
    </div>

    <?php if ($hero): ?>
    <div class="hero">
      <p class="hero-kicker">Registrations open</p>
      <h2 class="hero-title">Code. Build. Compete.</h2>
      <p class="hero-sub">Three days, eight events and plenty of prizes. Register below to get your event pass.</p>
      <ul class="hero-meta">
        <li>
          <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
          <?= e(FEST_DATES) ?>
        </li>
        <li>
          <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
          <?= e(FEST_VENUE) ?>
        </li>
      </ul>
      <div class="countdown" data-start="<?= e(FEST_START) ?>" aria-label="Time until the fest starts">
        <div><strong data-unit="d">--</strong><span>Days</span></div>
        <div><strong data-unit="h">--</strong><span>Hours</span></div>
        <div><strong data-unit="m">--</strong><span>Minutes</span></div>
        <div><strong data-unit="s">--</strong><span>Seconds</span></div>
      </div>
    </div>
    <?php endif; ?>
  </header>

  <main class="container">
    <?php
}

/** @param string $base Relative path to the site root. @param bool $app Load the registration-form script. */
function page_end(string $base = '', bool $app = false): void
{
    ?>
  </main>

  <footer class="site-footer">
    <p><?= e(FEST_NAME) ?> &middot; Built with HTML5, CSS3, JavaScript, jQuery &amp; PHP &middot; <a href="<?= e($base) ?>admin/">Admin</a></p>
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
