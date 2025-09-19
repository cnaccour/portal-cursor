<?php
require __DIR__.'/includes/auth.php';
require_login();
require_role('admin'); // Only admins can access this page
require __DIR__.'/includes/db.php';
require __DIR__.'/includes/user-manager.php';
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
            // Update user role using UserManager
            $success = UserManager::updateUserRole($user_id, $new_role, $_SESSION['user_id']);
            
            if ($success) {
                // Get updated user info
                $updated_user = UserManager::getUserById($user_id);
                $message = '<div class="bg-green-50 border border-green-200 rounded-xl p-4 text-green-800">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        Role updated successfully for ' . htmlspecialchars($updated_user['name'] ?? 'user') . '.
                    </div>
                </div>';
                
                // Update current session if changing own role
                if ($user_id === $_SESSION['user_id']) {
                    $_SESSION['role'] = $new_role;
                }
            } else {
                $message = '<div class="bg-red-50 border border-red-200 rounded-xl p-4 text-red-800">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                        </svg>
                        Failed to update user role. Please try again.
                    </div>
                </div>';
            }
        }
    }
}
?>

<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-semibold">Admin Panel</h1>
</div>

<?php if ($message): ?>
    <?= $message ?>
    <div class="mb-6"></div>
<?php endif; ?>

<?php 
// Get all users using UserManager
$all_users = UserManager::getAllUsers(false); // Don't include deleted users by default
?>

<!-- User Management Header with Actions -->
<div class="bg-white rounded-xl border mb-6">
    <div class="p-6 border-b">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-lg font-semibold text-gray-900">User Management</h2>
                <p class="text-sm text-gray-600 mt-1">Manage user accounts, roles, and permissions.</p>
            </div>
            <div class="flex items-center gap-3">
                <button class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Invite User
                </button>
                <button class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                    View Deleted
                </button>
            </div>
        </div>
    </div>
    
    <!-- User Statistics -->
    <div class="px-6 py-4 bg-gray-50 border-b">
        <div class="grid grid-cols-4 gap-4">
            <div class="text-center">
                <div class="text-2xl font-bold text-gray-900"><?= count($all_users) ?></div>
                <div class="text-sm text-gray-600">Total Users</div>
            </div>
            <div class="text-center">
                <div class="text-2xl font-bold text-red-600"><?= count(array_filter($all_users, fn($u) => $u['role'] === 'admin')) ?></div>
                <div class="text-sm text-gray-600">Administrators</div>
            </div>
            <div class="text-center">
                <div class="text-2xl font-bold text-yellow-600"><?= count(array_filter($all_users, fn($u) => $u['role'] === 'manager')) ?></div>
                <div class="text-sm text-gray-600">Managers</div>
            </div>
            <div class="text-center">
                <div class="text-2xl font-bold text-gray-600"><?= count(array_filter($all_users, fn($u) => in_array($u['role'], ['staff', 'support']))) ?></div>
                <div class="text-sm text-gray-600">Staff Members</div>
            </div>
        </div>
    </div>
    
    <!-- Users List -->
    <div class="divide-y">
        <?php if (empty($all_users)): ?>
        <div class="p-8 text-center text-gray-500">
            <svg class="w-12 h-12 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
            </svg>
            <p class="text-lg font-medium mb-2">No users found</p>
            <p class="text-sm">Invite your first user to get started.</p>
        </div>
        <?php else: ?>
            <?php foreach ($all_users as $user): ?>
            <div class="p-6 hover:bg-gray-50 transition-colors">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <!-- User Avatar -->
                        <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full flex items-center justify-center text-white font-semibold">
                            <?= strtoupper(substr($user['name'], 0, 1)) ?>
                        </div>
                        
                        <!-- User Info -->
                        <div class="flex-grow">
                            <div class="flex items-center gap-3">
                                <h3 class="font-semibold text-gray-900"><?= htmlspecialchars($user['name']) ?></h3>
                                
                                <!-- Role Badge -->
                                <span class="px-2 py-1 text-xs font-medium rounded-full
                                    <?php 
                                    switch ($user['role']) {
                                        case 'admin': echo 'bg-red-100 text-red-800'; break;
                                        case 'manager': echo 'bg-yellow-100 text-yellow-800'; break;
                                        case 'support': echo 'bg-blue-100 text-blue-800'; break;
                                        case 'staff': echo 'bg-green-100 text-green-800'; break;
                                        default: echo 'bg-gray-100 text-gray-600';
                                    }
                                    ?>">
                                    <?= htmlspecialchars(get_role_display_name($user['role'])) ?>
                                </span>
                                
                                <!-- Status Badge -->
                                <?php if (isset($user['status'])): ?>
                                <span class="px-2 py-1 text-xs font-medium rounded-full
                                    <?php 
                                    switch ($user['status']) {
                                        case 'active': echo 'bg-green-100 text-green-800'; break;
                                        case 'inactive': echo 'bg-gray-100 text-gray-600'; break;
                                        case 'deleted': echo 'bg-red-100 text-red-800'; break;
                                        default: echo 'bg-gray-100 text-gray-600';
                                    }
                                    ?>">
                                    <?= ucfirst($user['status'] ?? 'active') ?>
                                </span>
                                <?php endif; ?>
                                
                                <!-- Current User Indicator -->
                                <?php if ($user['id'] == $_SESSION['user_id']): ?>
                                <span class="px-2 py-1 text-xs font-medium rounded-full bg-blue-50 text-blue-600">
                                    You
                                </span>
                                <?php endif; ?>
                            </div>
                            <p class="text-sm text-gray-500 mt-1"><?= htmlspecialchars($user['email']) ?></p>
                            <?php if (isset($user['created_at'])): ?>
                            <p class="text-xs text-gray-400 mt-1">Joined <?= date('M j, Y', strtotime($user['created_at'])) ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Actions -->
                    <div class="flex items-center gap-3">
                        <!-- Role Change Form -->
                        <form method="POST" class="flex items-center gap-2">
                            <input type="hidden" name="action" value="update_role">
                            <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                            
                            <select name="new_role" class="border border-gray-300 rounded-lg px-3 py-1 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500" onchange="this.form.submit()">
                                <?php foreach (get_all_roles() as $role): ?>
                                <option value="<?= htmlspecialchars($role) ?>" 
                                        <?= $user['role'] === $role ? 'selected' : '' ?>>
                                    <?= htmlspecialchars(get_role_display_name($role)) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                            
                            <noscript>
                                <button type="submit" class="px-3 py-1 text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                                    Update
                                </button>
                            </noscript>
                        </form>
                        
                        <!-- More Actions Menu -->
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open" @click.outside="open = false" 
                                    class="p-2 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"></path>
                                </svg>
                            </button>
                            
                            <div x-show="open" x-cloak x-transition 
                                 class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border py-1 z-50">
                                <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">View Profile</a>
                                <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Reset Password</a>
                                <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">View Activity</a>
                                <div class="border-t my-1"></div>
                                <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                <a href="#" class="block px-4 py-2 text-sm text-red-600 hover:bg-red-50">Delete User</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- System Status and Notes -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Development Status -->
    <div class="bg-blue-50 border border-blue-200 rounded-xl p-4">
        <h3 class="font-semibold text-blue-800 mb-2 flex items-center">
            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
            </svg>
            Development Mode
        </h3>
        <p class="text-sm text-blue-700">
            User management is using <?= UserManager::getInstance()->use_mock ? 'mock data' : 'database storage' ?>. 
            Changes <?= UserManager::getInstance()->use_mock ? 'will reset on server restart' : 'are saved permanently' ?>.
        </p>
        <?php if (UserManager::getInstance()->use_mock): ?>
        <p class="text-xs text-blue-600 mt-2">
            To enable database storage, run the migration scripts in /database/migrations/
        </p>
        <?php endif; ?>
    </div>
    
    <!-- Quick Actions -->
    <div class="bg-gray-50 border border-gray-200 rounded-xl p-4">
        <h3 class="font-semibold text-gray-800 mb-3">Quick Actions</h3>
        <div class="space-y-2">
            <button class="w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-lg transition-colors">
                📊 View User Activity Logs
            </button>
            <button class="w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-lg transition-colors">
                📧 Manage Pending Invitations
            </button>
            <button class="w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-lg transition-colors">
                🔐 Configure Role Permissions
            </button>
        </div>
    </div>
</div>


<?php require __DIR__.'/includes/footer.php'; ?>