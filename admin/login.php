<?php
declare(strict_types=1);
require __DIR__ . '/bootstrap.php';

start_secure_session();
if (is_admin()) {
    header('Location: index.php');
    exit;
}

$error = '';
$configured = admin_password() !== '';

if ($configured && ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    $password = $_POST['password'] ?? '';
    if (is_string($password) && hash_equals(admin_password(), $password)) {
        session_regenerate_id(true);
        $_SESSION['is_admin'] = true;
        header('Location: index.php');
        exit;
    }
    usleep(800000); // slow down password guessing
    $error = 'Incorrect password. Please try again.';
}

page_start('Admin Login', '../', 'Admin Panel');
?>
<section class="card login-card">
  <h2>Admin Login</h2>
  <?php if (!$configured): ?>
    <p class="form-alert">
      The admin panel is disabled because no password is set.<br>
      Set the <code>ADMIN_PASSWORD</code> environment variable on the server and reload this page.
    </p>
  <?php else: ?>
    <p class="hint">Sign in to view and manage fest registrations.</p>
    <form method="post" autocomplete="off">
      <div class="field">
        <label for="password">Password</label>
        <input type="password" id="password" name="password" required autofocus>
      </div>
      <?php if ($error): ?><p class="form-alert"><?= e($error) ?></p><?php endif; ?>
      <button type="submit" class="btn btn-primary btn-block">Sign in</button>
    </form>
  <?php endif; ?>
  <p class="hint back-link"><a href="../">&larr; Back to the registration form</a></p>
</section>
<?php
page_end('../');
