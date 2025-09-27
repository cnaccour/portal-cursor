<?php
require __DIR__ . '/includes/auth.php';
require_login();
require __DIR__ . '/includes/db.php';
require __DIR__ . '/includes/header.php';

// Current user
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare('SELECT name, email FROM users WHERE id = ?');
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$name = $user['name'] ?? ($_SESSION['name'] ?? '');
$email = $user['email'] ?? ($_SESSION['email'] ?? '');
$first_name = $name ? explode(' ', $name, 2)[0] : '';
$last_name = $name ? (explode(' ', $name, 2)[1] ?? '') : '';

// Ensure CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<div class="max-w-2xl mx-auto">
  <div class="mb-6">
    <h1 class="text-2xl font-semibold">Account Settings</h1>
    <p class="text-sm text-gray-600 mt-1">Update your profile information.</p>
  </div>

  <div class="bg-white rounded-xl border">
    <form id="profileForm" class="p-6 space-y-4">
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">First Name</label>
          <input type="text" name="first_name" value="<?= htmlspecialchars($first_name) ?>" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Last Name</label>
          <input type="text" name="last_name" value="<?= htmlspecialchars($last_name) ?>" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
        </div>
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
        <input type="email" name="email" value="<?= htmlspecialchars($email) ?>" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
      </div>
      <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
      <div class="flex justify-end">
        <button type="submit" class="px-4 py-2 text-white rounded-lg transition-colors" style="background-color:#000">Save Changes</button>
      </div>
    </form>
  </div>
</div>

<script>
function showBanner(type, message) {
  const banner = document.createElement('div');
  const isSuccess = type === 'success';
  banner.className = 'fixed top-4 right-4 z-50 px-4 py-3 rounded-lg shadow-lg border ' + (isSuccess ? 'bg-green-50 border-green-200 text-green-800' : 'bg-red-50 border-red-200 text-red-800');
  banner.innerHTML = `<div class="flex items-start gap-2">
      <svg class="w-5 h-5 mt-0.5" fill="currentColor" viewBox="0 0 20 20">${isSuccess?'<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>':'<path fill-rule="evenodd" d="M18 10A8 8 0 112 10a8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>'}</svg>
      <div class="text-sm">${message.replace(/</g,'&lt;')}</div>
    </div>`;
  document.body.appendChild(banner);
  setTimeout(() => { if (banner.parentNode) banner.parentNode.removeChild(banner); }, isSuccess ? 2500 : 3500);
}

document.getElementById('profileForm').addEventListener('submit', async (e) => {
  e.preventDefault();
  const fd = new FormData(e.target);
  try {
    const resp = await fetch('api/users/update-profile.php', { method: 'POST', body: fd });
    const data = await resp.json();
    if (data.success) {
      showBanner('success', data.message);
    } else {
      showBanner('error', data.message || 'Failed to update profile');
    }
  } catch (err) {
    showBanner('error', 'An error occurred while updating your profile');
  }
});
</script>

<?php require __DIR__ . '/includes/footer.php'; ?>

