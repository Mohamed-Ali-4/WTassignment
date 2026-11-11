<?php
/**
 * Form handler.
 *
 *  - AJAX request (jQuery)  -> JSON { success, html } or { success:false, errors }
 *  - Plain form POST (no JS) -> full HTML page showing the result or the errors
 */

declare(strict_types=1);

require __DIR__ . '/includes/functions.php';

$isAjax = strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    header('Location: index.html');
    exit;
}

// Upload larger than post_max_size: PHP silently empties $_POST/$_FILES
if (empty($_POST) && (int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
    $errors = ['photo' => 'The uploaded file is too large.'];
} else {
    [$data, $errors] = validate_application();
}

if ($isAjax) {
    header('Content-Type: application/json; charset=utf-8');
    if ($errors) {
        http_response_code(422);
        echo json_encode(['success' => false, 'message' => 'Please correct the highlighted fields.', 'errors' => $errors]);
    } else {
        echo json_encode(['success' => true, 'html' => render_result($data)]);
    }
    exit;
}

// ---------- Non-JavaScript fallback: render a full page ----------
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $errors ? 'Please fix your application' : 'Application Submitted' ?></title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/style.css">
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
        <p>Academic Year 2026&ndash;27 &middot; Application Form</p>
      </div>
    </div>
  </header>
  <main class="container">
    <?php if ($errors): ?>
      <section class="card">
        <h2>Some details need your attention</h2>
        <ul class="error-list">
          <?php foreach ($errors as $msg): ?><li><?= e($msg) ?></li><?php endforeach; ?>
        </ul>
        <div class="result-actions">
          <a class="btn btn-primary" href="javascript:history.back()">&larr; Go back and fix</a>
        </div>
      </section>
    <?php else: ?>
      <?= render_result($data) ?>
    <?php endif; ?>
  </main>
  <footer class="site-footer">
    <p>Built with HTML5, CSS3, JavaScript, jQuery &amp; PHP &middot; Web Technologies Assignment</p>
  </footer>
</body>
</html>
