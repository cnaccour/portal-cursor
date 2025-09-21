<?php
session_start(); // Start session at the beginning
require __DIR__.'/includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = trim($_POST['email'] ?? '');
  $pass  = $_POST['password'] ?? '';

  // Database query to find user
  try {
    $stmt = $pdo->prepare("SELECT id, name, email, password_hash, role FROM users WHERE email = ? AND deleted_at IS NULL");
    $stmt->execute([$email]);
    $u = $stmt->fetch();
  } catch (Exception $e) {
    error_log('Login query failed: ' . $e->getMessage());
    $u = null;
  }

  if ($u && password_verify($pass, $u['password_hash'])) {
    session_regenerate_id(true); // Prevent session fixation attacks
    $_SESSION['user_id'] = $u['id'];
    $_SESSION['name'] = $u['name'];
    $_SESSION['role'] = $u['role'] ?? 'staff'; // Default to staff if no role set
    $_SESSION['email'] = $u['email']; // Add email to session
    
    // Generate CSRF token
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    
    header('Location: dashboard.php');
    exit;
  } else {
    $err = "Invalid email or password.";
  }
}

require __DIR__.'/includes/header.php';
?>

<div class="max-w-md mx-auto bg-white p-6 rounded-xl border mt-10">
  <h1 class="text-xl font-semibold mb-4">Login</h1>
  <?php if (!empty($err)): ?>
    <div class="text-amber-700 bg-amber-50 border border-amber-200 rounded-lg p-3 mb-3"><?= htmlspecialchars($err) ?></div>
  <?php endif; ?>
  <form method="post" class="space-y-3">
    <div>
      <label class="block text-sm mb-1">Email</label>
      <input class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500" type="email" name="email" required>
    </div>
    <div>
      <label class="block text-sm mb-1">Password</label>
      <input class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500" type="password" name="password" required>
    </div>
    <button class="mt-2 px-4 py-2 rounded-lg bg-gray-900 text-white">Sign in</button>
  </form>
</div>

<?php require __DIR__.'/includes/footer.php'; ?>