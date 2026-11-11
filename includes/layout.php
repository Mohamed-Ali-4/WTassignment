<?php
/** Shared page header/footer for server-rendered pages (submit fallback + admin panel). */

declare(strict_types=1);

function page_start(string $title, string $base = '', string $subtitle = 'Academic Year 2026&ndash;27 &middot; Application Form', string $nav = ''): void
{
    ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($title) ?></title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= e($base) ?>assets/css/style.css">
</head>
<body>
  <div class="bg-shapes" aria-hidden="true"><span></span><span></span><span></span></div>
  <header class="site-header">
    <div class="brand">
      <div class="logo" aria-hidden="true">
        <svg viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 10 12 5 2 10l10 5 10-5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>
      </div>
      <div>
        <h1>Online Admission Portal</h1>
        <p><?= $subtitle ?></p>
      </div>
      <?= $nav ?>
    </div>
  </header>
  <main class="container">
    <?php
}

function page_end(): void
{
    ?>
  </main>
  <footer class="site-footer">
    <p>Built with HTML5, CSS3, JavaScript, jQuery &amp; PHP &middot; Web Technologies Assignment</p>
  </footer>
</body>
</html>
    <?php
}
