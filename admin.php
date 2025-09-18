<?php
require __DIR__.'/includes/auth.php';
require_login();
require_role('admin'); // Only admins can access this page
require __DIR__.'/includes/db.php';
require __DIR__.'/includes/header.php';

// Generate CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$message = '';

// Handle role updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_role') {
    // CSRF protection
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
        $message = '<div class="bg-red-50 border border-red-200 rounded-xl p-4 text-red-800">Security error: Invalid token.</div>';
    } else {
        $user_id = (int)($_POST['user_id'] ?? 0);
        $new_role = $_POST['new_role'] ?? '';
        
        // Validate role
        if (!in_array($new_role, get_all_roles())) {
            $message = '<div class="bg-red-50 border border-red-200 rounded-xl p-4 text-red-800">Invalid role selected.</div>';
        } else {
            // Find and update user in mock array
            foreach ($mock_users as &$user) {
                if ($user['id'] === $user_id) {
                    $user['role'] = $new_role;
                    $message = '<div class="bg-black text-white rounded-md p-4">Role updated successfully for ' . htmlspecialchars($user['name']) . '.</div>';
                    
                    // Update current session if changing own role
                    if ($user_id === $_SESSION['user_id']) {
                        $_SESSION['role'] = $new_role;
                    }
                    break;
                }
            }
            unset($user); // Break reference
            
            // Note: In production, this would be a database update
            // For now, the change only persists for the current session
        }
    }
}
?>

<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-semibold">Admin Panel</h1>
    <div class="text-sm text-gray-600">
        User Management
    </div>
</div>

<?php if ($message): ?>
    <?= $message ?>
    <div class="mb-6"></div>
<?php endif; ?>

<div class="bg-white rounded-xl border">
    <div class="p-6 border-b">
        <h2 class="text-lg font-semibold">User Roles Management</h2>
        <p class="text-sm text-gray-600 mt-1">Manage user permissions and access levels.</p>
    </div>
    
    <div class="divide-y">
        <?php foreach ($mock_users as $user): ?>
        <div class="p-6">
            <div class="flex items-center justify-between">
                <div class="flex-grow">
                    <div class="flex items-center gap-4">
                        <div>
                            <h3 class="font-semibold text-gray-900"><?= htmlspecialchars($user['name']) ?></h3>
                            <p class="text-sm text-gray-500"><?= htmlspecialchars($user['email']) ?></p>
                        </div>
                        <div>
                            <span class="px-2 py-1 text-xs font-medium rounded-full
                                <?php 
                                switch ($user['role']) {
                                    case 'admin': echo 'bg-red-100 text-red-800'; break;
                                    case 'manager': echo 'text-white'; echo '\" style=\"background-color: #AF831A;'; break;
                                    case 'support': echo 'bg-black text-white'; break;
                                    case 'staff': echo 'bg-gray-100 text-gray-800'; break;
                                    default: echo 'bg-gray-100 text-gray-600';
                                }
                                ?>">
                                <?= htmlspecialchars(get_role_display_name($user['role'])) ?>
                            </span>
                        </div>
                    </div>
                </div>
                
                <form method="POST" class="flex items-center gap-3">
                    <input type="hidden" name="action" value="update_role">
                    <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                    
                    <select name="new_role" class="border rounded-lg px-3 py-1 text-sm" onchange="this.form.submit()">
                        <?php foreach (get_all_roles() as $role): ?>
                        <option value="<?= htmlspecialchars($role) ?>" 
                                <?= $user['role'] === $role ? 'selected' : '' ?>>
                            <?= htmlspecialchars(get_role_display_name($role)) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                    
                    <noscript>
                        <button type="submit" class="px-3 py-1 text-sm bg-black text-white rounded-lg hover:bg-gray-800">
                            Update
                        </button>
                    </noscript>
                </form>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<div class="mt-6 bg-yellow-50 border border-yellow-200 rounded-xl p-4">
    <h3 class="font-semibold text-yellow-800 mb-2">Development Note</h3>
    <p class="text-sm text-yellow-700">
        Role changes are currently stored in memory only and will reset on server restart. 
        In production, these changes would be saved to the database permanently.
    </p>
</div>


<?php require __DIR__.'/includes/footer.php'; ?>