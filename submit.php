<?php
/**
 * Registration handler (back end).
 *
 *  1. Validates and sanitises the submission       (includes/functions.php)
 *  2. Saves it to the SQLite database               (includes/db.php)
 *  3. Responds:
 *       - AJAX request (jQuery)  -> JSON { success, html } or { success:false, errors }
 *       - Plain form POST (no JS) -> full HTML page showing the pass or the errors
 */

declare(strict_types=1);

require __DIR__ . '/includes/config.php';
require __DIR__ . '/includes/functions.php';
require __DIR__ . '/includes/db.php';
require __DIR__ . '/includes/layout.php';

$isAjax = strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    header('Location: ./');
    exit;
}

$data = [];
$serverError = false;

// Upload larger than post_max_size: PHP silently empties $_POST/$_FILES
if (empty($_POST) && (int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
    $errors = ['photo' => 'The uploaded file is too large.'];
} else {
    [$data, $errors] = validate_registration();
}

if (!$errors) {
    try {
        $data = save_registration($data);
    } catch (Throwable $ex) {
        error_log('Could not save registration: ' . $ex->getMessage());
        $serverError = true;
    }
}

if ($isAjax) {
    header('Content-Type: application/json; charset=utf-8');
    if ($errors) {
        http_response_code(422);
        echo json_encode(['success' => false, 'message' => 'Please correct the highlighted fields.', 'errors' => $errors]);
    } elseif ($serverError) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Sorry, we could not save your registration. Please try again.', 'errors' => []]);
    } else {
        echo json_encode(['success' => true, 'html' => render_pass($data)]);
    }
    exit;
}

// ---------- Non-JavaScript fallback: render a full page ----------
if ($serverError) {
    http_response_code(500);
    $errors = ['server' => 'Sorry, we could not save your registration. Please try again.'];
}
page_start($errors ? 'Please fix your registration' : 'Registration Confirmed');
if ($errors): ?>
    <section class="card">
      <h2>Some details need your attention</h2>
      <ul class="error-list">
        <?php foreach ($errors as $msg): ?><li><?= e($msg) ?></li><?php endforeach; ?>
      </ul>
      <div class="result-actions">
        <a class="btn btn-primary" href="javascript:history.back()">&larr; Go back and fix</a>
      </div>
    </section>
<?php else:
    echo render_pass($data);
endif;
page_end();
