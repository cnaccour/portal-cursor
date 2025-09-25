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
$show_deleted = isset($_GET['show_deleted']) && $_GET['show_deleted'] === '1';
$all_users = UserManager::getAllUsers($show_deleted);
$deleted_users = UserManager::getAllUsers(true); // Get all including deleted for count
$deleted_count = count(array_filter($deleted_users, fn($u) => isset($u['status']) && $u['status'] === 'deleted'));
?>

<!-- User Management Header with Actions -->
<div class="bg-white rounded-xl border mb-6">
    <div class="p-4 sm:p-6 border-b">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-lg font-semibold text-gray-900">User Management</h2>
                <p class="text-sm text-gray-600 mt-1">Manage user accounts, roles, and permissions.</p>
            </div>
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                <button onclick="openInviteModal()" class="px-4 py-3 sm:py-2 bg-black text-white rounded-lg hover:bg-gray-800 transition-colors flex items-center justify-center gap-2 text-sm sm:text-base">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Invite User
                </button>
                <button onclick="toggleDeletedUsers()" id="viewDeletedBtn" class="px-4 py-3 sm:py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors text-sm sm:text-base">
                    View Deleted
                </button>
            </div>
        </div>
    </div>
    
    <!-- User Statistics -->
    <div class="px-4 sm:px-6 py-4 bg-gray-50 border-b">
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <div class="text-center">
                <div class="text-lg sm:text-2xl font-bold text-gray-900"><?= count($all_users) ?></div>
                <div class="text-xs sm:text-sm text-gray-600">Total Users</div>
            </div>
            <div class="text-center">
                <div class="text-lg sm:text-2xl font-bold text-red-600"><?= count(array_filter($all_users, fn($u) => $u['role'] === 'admin')) ?></div>
                <div class="text-xs sm:text-sm text-gray-600">Administrators</div>
            </div>
            <div class="text-center">
                <div class="text-lg sm:text-2xl font-bold text-yellow-600"><?= count(array_filter($all_users, fn($u) => $u['role'] === 'manager')) ?></div>
                <div class="text-xs sm:text-sm text-gray-600">Managers</div>
            </div>
            <div class="text-center">
                <div class="text-lg sm:text-2xl font-bold text-gray-600"><?= count(array_filter($all_users, fn($u) => in_array($u['role'], ['staff', 'support']))) ?></div>
                <div class="text-xs sm:text-sm text-gray-600">Staff Members</div>
            </div>
            <?php if ($deleted_count > 0): ?>
            <div class="text-center col-span-2 sm:col-span-1">
                <div class="text-lg sm:text-2xl font-bold text-red-600"><?= $deleted_count ?></div>
                <div class="text-xs sm:text-sm text-gray-600">Deleted Users</div>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Users List -->
    <div class="divide-y">
        <?php if (empty($all_users)): ?>
        <div class="p-6 sm:p-8 text-center text-gray-500">
            <svg class="w-12 h-12 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
            </svg>
            <p class="text-lg font-medium mb-2">No users found</p>
            <p class="text-sm">Invite your first user to get started.</p>
        </div>
        <?php else: ?>
            <?php foreach ($all_users as $user): ?>
            <div class="p-4 sm:p-6 hover:bg-gray-50 transition-colors">
                <div class="flex flex-col sm:flex-row sm:items-center gap-4">
                    <!-- User Info -->
                    <div class="flex-grow">
                        <div class="flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-3">
                            <h3 class="font-semibold text-gray-900 text-base sm:text-lg"><?= htmlspecialchars($user['name']) ?></h3>
                            
                            <!-- Mobile: Badges in new row, Desktop: Inline -->
                            <div class="flex flex-wrap items-center gap-2">
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
                                
                                <!-- Status Badge with Toggle -->
                                <?php 
                                $user_status = $user['status'] ?? 'active';
                                $is_deleted = $user_status === 'deleted';
                                $is_active = $user_status === 'active';
                                ?>
                                <?php if (!$is_deleted): ?>
                                <button onclick="toggleUserStatus(<?= $user['id'] ?>, '<?= $is_active ? 'inactive' : 'active' ?>')" 
                                        class="px-3 py-2 sm:px-2 sm:py-1 text-xs font-medium rounded-full transition-colors cursor-pointer
                                    <?php 
                                    switch ($user_status) {
                                        case 'active': echo 'bg-green-100 text-green-800 hover:bg-green-200'; break;
                                        case 'inactive': echo 'bg-gray-100 text-gray-600 hover:bg-gray-200'; break;
                                        default: echo 'bg-gray-100 text-gray-600 hover:bg-gray-200';
                                    }
                                    ?>"
                                    title="Click to toggle status">
                                    <?= ucfirst($user_status) ?>
                                </button>
                                <?php else: ?>
                                <span class="px-3 py-2 sm:px-2 sm:py-1 text-xs font-medium rounded-full bg-red-100 text-red-800">
                                    Deleted
                                </span>
                                <?php endif; ?>
                                
                                <!-- Current User Indicator -->
                                <?php if ($user['id'] == $_SESSION['user_id']): ?>
                                <span class="px-2 py-1 text-xs font-medium rounded-full bg-blue-50 text-blue-600">
                                    You
                                </span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="mt-2 sm:mt-1">
                            <p class="text-sm text-gray-500"><?= htmlspecialchars($user['email']) ?></p>
                            <?php if (isset($user['created_at'])): ?>
                            <p class="text-xs text-gray-400 mt-1">Joined <?= date('M j, Y', strtotime($user['created_at'])) ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Actions -->
                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 mt-4 sm:mt-0">
                        <!-- Role Change Form -->
                        <form method="POST" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
                            <input type="hidden" name="action" value="update_role">
                            <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                            
                            <select name="new_role" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 min-w-0 sm:min-w-32" onchange="this.form.submit()">
                                <?php foreach (get_all_roles() as $role): ?>
                                <option value="<?= htmlspecialchars($role) ?>" 
                                        <?= $user['role'] === $role ? 'selected' : '' ?>>
                                    <?= htmlspecialchars(get_role_display_name($role)) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                            
                            <noscript>
                                <button type="submit" class="px-3 py-2 text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                                    Update
                                </button>
                            </noscript>
                        </form>
                        
                        <!-- Action Buttons -->
                        <?php if ($is_deleted): ?>
                            <!-- Restore Button for Deleted Users -->
                            <button onclick="restoreUser(<?= $user['id'] ?>, '<?= htmlspecialchars($user['name']) ?>')" 
                                    class="px-4 py-2 text-sm bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors flex items-center justify-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                </svg>
                                Restore
                            </button>
                        <?php else: ?>
                            <!-- More Actions Menu for Active Users -->
                            <div class="relative w-full sm:w-auto" x-data="{ open: false }">
                                <button @click="open = !open" @click.outside="open = false" 
                                        class="w-full sm:w-auto px-4 py-2 text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors flex items-center justify-center gap-2">
                                    <span class="text-sm">Actions</span>
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"></path>
                                    </svg>
                                </button>
                                
                                <div x-show="open" x-cloak x-transition 
                                     class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border py-1 z-50">
                                    <button onclick="showResetPasswordModal(<?= $user['id'] ?>, '<?= htmlspecialchars($user['name']) ?>')" 
                                            class="w-full text-left px-4 py-3 text-sm text-gray-700 hover:bg-gray-50">
                                        Reset Password
                                    </button>
                                    <div class="border-t my-1"></div>
                                    <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                    <button onclick="deleteUser(<?= $user['id'] ?>, '<?= htmlspecialchars($user['name']) ?>')" 
                                            class="w-full text-left px-4 py-3 text-sm text-red-600 hover:bg-red-50">
                                        Delete User
                                    </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- System Status and Notes -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6">
    <!-- Development Status -->
    <div class="bg-blue-50 border border-blue-200 rounded-xl p-4">
        <h3 class="font-semibold text-blue-800 mb-2 flex items-center text-sm sm:text-base">
            <svg class="w-4 h-4 sm:w-5 sm:h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
            </svg>
            Development Mode
        </h3>
        <p class="text-xs sm:text-sm text-blue-700">
            User management is using <?= UserManager::getInstance()->isUsingMockMode() ? 'mock data' : 'database storage' ?>. 
            Changes <?= UserManager::getInstance()->isUsingMockMode() ? 'will reset on server restart' : 'are saved permanently' ?>.
        </p>
        <?php if (UserManager::getInstance()->isUsingMockMode()): ?>
        <p class="text-xs text-blue-600 mt-2">
            To enable database storage, run the migration scripts in /database/migrations/
        </p>
        <?php endif; ?>
    </div>
    
    <!-- Quick Actions -->
    <div class="bg-gray-50 border border-gray-200 rounded-xl p-4">
        <h3 class="font-semibold text-gray-800 mb-3 text-sm sm:text-base">Quick Actions</h3>
        <div class="space-y-2">
            <button class="w-full text-left px-3 py-2 text-xs sm:text-sm text-gray-700 hover:bg-gray-100 rounded-lg transition-colors">
                📊 View User Activity Logs
            </button>
            <button onclick="showInvitationsTab()" class="w-full text-left px-3 py-2 text-xs sm:text-sm text-gray-700 hover:bg-gray-100 rounded-lg transition-colors">
                📧 Manage Pending Invitations
            </button>
            <button class="w-full text-left px-3 py-2 text-xs sm:text-sm text-gray-700 hover:bg-gray-100 rounded-lg transition-colors">
                🔐 Configure Role Permissions
            </button>
        </div>
    </div>
</div>

<!-- Invite User Modal -->
<div id="inviteModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-xl shadow-xl max-w-md w-full mx-4">
        <div class="p-6 border-b">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900">Invite New User</h3>
                <button onclick="closeInviteModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </div>
        
        <form id="inviteForm" class="p-6">
            <div class="space-y-4">
                <div>
                    <label for="inviteEmail" class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
                    <input type="email" id="inviteEmail" name="email" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           placeholder="user@example.com">
                </div>
                
                <div>
                    <label for="inviteRole" class="block text-sm font-medium text-gray-700 mb-2">Role</label>
                    <select id="inviteRole" name="role" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Select a role...</option>
                        <option value="viewer">Viewer - Read-only access</option>
                        <option value="staff">Staff Member - Basic access</option>
                        <option value="support">Support Specialist - Customer support functions</option>
                        <option value="manager">Manager - Location and staff management</option>
                        <option value="admin">Administrator - Full system access</option>
                    </select>
                </div>
                
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <div class="flex">
                        <svg class="w-5 h-5 text-blue-600 mt-0.5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                        </svg>
                        <div>
                            <p class="text-sm text-blue-800 font-medium">Invitation Details</p>
                            <p class="text-xs text-blue-700 mt-1">The user will receive an email with a secure link to complete their registration. The invitation will expire in 7 days.</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="flex justify-end gap-3 mt-6">
                <button type="button" onclick="closeInviteModal()" 
                        class="px-4 py-2 text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">
                    Cancel
                </button>
                <button type="submit" 
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    Send Invitation
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Invitations Management Section (Hidden by default) -->
<div id="invitationsSection" class="hidden mt-6">
    <div class="bg-white rounded-xl border">
        <div class="p-4 sm:p-6 border-b">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">Pending Invitations</h2>
                    <p class="text-sm text-gray-600 mt-1">Manage sent invitations and track their status.</p>
                </div>
                <button onclick="hideInvitationsTab()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </div>
        
        <div id="invitationsList" class="divide-y">
            <!-- Invitations will be loaded here -->
        </div>
    </div>
</div>

<script>
// User Management JavaScript Functions
function toggleDeletedUsers() {
    const currentUrl = new URL(window.location);
    const showDeleted = currentUrl.searchParams.get('show_deleted');
    
    if (showDeleted === '1') {
        currentUrl.searchParams.delete('show_deleted');
        document.getElementById('viewDeletedBtn').textContent = 'View Deleted';
    } else {
        currentUrl.searchParams.set('show_deleted', '1');
        document.getElementById('viewDeletedBtn').textContent = 'View Active';
    }
    
    window.location.href = currentUrl.toString();
}

function deleteUser(userId, userName) {
    if (!confirm(`Are you sure you want to delete ${userName}? This action can be undone by restoring the user.`)) {
        return;
    }
    
    const formData = new FormData();
    formData.append('user_id', userId);
    formData.append('csrf_token', '<?= $_SESSION['csrf_token'] ?>');
    
    fetch('./api/users/delete-user.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(data.message, 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showNotification(data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('An error occurred while deleting the user.', 'error');
    });
}

function restoreUser(userId, userName) {
    if (!confirm(`Are you sure you want to restore ${userName}?`)) {
        return;
    }
    
    const formData = new FormData();
    formData.append('user_id', userId);
    formData.append('csrf_token', '<?= $_SESSION['csrf_token'] ?>');
    
    fetch('./api/users/restore-user.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(data.message, 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showNotification(data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('An error occurred while restoring the user.', 'error');
    });
}

function toggleUserStatus(userId, newStatus) {
    const formData = new FormData();
    formData.append('user_id', userId);
    formData.append('status', newStatus);
    formData.append('csrf_token', '<?= $_SESSION['csrf_token'] ?>');
    
    fetch('./api/users/update-status.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(data.message, 'success');
            setTimeout(() => location.reload(), 500);
        } else {
            showNotification(data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('An error occurred while updating user status.', 'error');
    });
}

// Invitation Management Functions
function openInviteModal() {
    document.getElementById('inviteModal').classList.remove('hidden');
    document.getElementById('inviteEmail').focus();
}

function closeInviteModal() {
    document.getElementById('inviteModal').classList.add('hidden');
    document.getElementById('inviteForm').reset();
}

function showInvitationsTab() {
    document.getElementById('invitationsSection').classList.remove('hidden');
    loadInvitations();
    
    // Scroll to invitations section
    document.getElementById('invitationsSection').scrollIntoView({ 
        behavior: 'smooth' 
    });
}

function hideInvitationsTab() {
    document.getElementById('invitationsSection').classList.add('hidden');
}

function loadInvitations() {
    fetch('./api/invitations/list-invitations.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayInvitations(data.invitations);
            } else {
                showNotification('Failed to load invitations: ' + data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error loading invitations:', error);
            showNotification('An error occurred while loading invitations.', 'error');
        });
}

function displayInvitations(invitations) {
    const container = document.getElementById('invitationsList');
    
    if (invitations.length === 0) {
        container.innerHTML = `
            <div class="p-8 text-center text-gray-500">
                <svg class="w-12 h-12 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 7.89a2 2 0 002.83 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                </svg>
                <p class="text-lg font-medium mb-2">No pending invitations</p>
                <p class="text-sm">All invitations have been accepted or expired.</p>
            </div>
        `;
        return;
    }
    
    const html = invitations.map(invitation => {
        const expiresAt = new Date(invitation.expires_at);
        const isExpired = expiresAt < new Date();
        const statusClass = {
            'pending': isExpired ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800',
            'accepted': 'bg-green-100 text-green-800',
            'expired': 'bg-red-100 text-red-800',
            'revoked': 'bg-gray-100 text-gray-800'
        };
        
        return `
            <div class="p-4 sm:p-6 hover:bg-gray-50 transition-colors">
                <div class="flex flex-col sm:flex-row sm:items-center gap-4">
                    <div class="flex-grow">
                        <div class="flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-3">
                            <h3 class="font-semibold text-gray-900">${escapeHtml(invitation.email)}</h3>
                            <span class="px-2 py-1 text-xs font-medium rounded-full ${statusClass[invitation.status] || 'bg-gray-100 text-gray-600'}">
                                ${invitation.status.charAt(0).toUpperCase() + invitation.status.slice(1)}
                            </span>
                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800">
                                ${getRoleDisplayName(invitation.role)}
                            </span>
                        </div>
                        <div class="mt-2 sm:mt-1">
                            <p class="text-sm text-gray-500">
                                Invited by ${escapeHtml(invitation.invited_by_name || 'Unknown')} • 
                                Expires ${formatDate(invitation.expires_at)}
                            </p>
                        </div>
                    </div>
                    
                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                        ${invitation.status === 'pending' && !isExpired ? `
                            <button onclick="copyInvitationLink('${invitation.token}')" 
                                    class="px-4 py-2 text-sm bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                                Copy Link
                            </button>
                            <button onclick="revokeInvitation(${invitation.id}, '${escapeHtml(invitation.email)}')" 
                                    class="px-4 py-2 text-sm bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                                Revoke
                            </button>
                        ` : ''}
                    </div>
                </div>
            </div>
        `;
    }).join('');
    
    container.innerHTML = html;
}

function copyInvitationLink(token) {
    const domain = window.location.host;
    const protocol = window.location.protocol;
    const inviteUrl = `${protocol}//${domain}/signup.php?token=${encodeURIComponent(token)}`;
    
    navigator.clipboard.writeText(inviteUrl).then(() => {
        showNotification('Invitation link copied to clipboard', 'success');
    }).catch(() => {
        // Fallback for older browsers
        const textArea = document.createElement('textarea');
        textArea.value = inviteUrl;
        document.body.appendChild(textArea);
        textArea.select();
        document.execCommand('copy');
        document.body.removeChild(textArea);
        showNotification('Invitation link copied to clipboard', 'success');
    });
}

function revokeInvitation(invitationId, email) {
    if (!confirm(`Are you sure you want to revoke the invitation for ${email}?`)) {
        return;
    }
    
    const formData = new FormData();
    formData.append('invitation_id', invitationId);
    formData.append('csrf_token', '<?= $_SESSION['csrf_token'] ?>');
    
    fetch('./api/invitations/revoke-invitation.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(data.message, 'success');
            loadInvitations(); // Reload the list
        } else {
            showNotification(data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('An error occurred while revoking the invitation.', 'error');
    });
}

// Utility functions
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function getRoleDisplayName(role) {
    const roleNames = {
        'admin': 'Administrator',
        'manager': 'Manager',
        'support': 'Support Specialist',
        'staff': 'Staff Member',
        'viewer': 'Viewer'
    };
    return roleNames[role] || role.charAt(0).toUpperCase() + role.slice(1);
}

function formatDate(dateString) {
    const date = new Date(dateString);
    const now = new Date();
    const diffTime = date - now;
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
    
    if (diffDays < 0) {
        return 'Expired';
    } else if (diffDays === 0) {
        return 'Today';
    } else if (diffDays === 1) {
        return 'Tomorrow';
    } else {
        return `in ${diffDays} days`;
    }
}

// Handle invite form submission
document.getElementById('inviteForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    formData.append('csrf_token', '<?= $_SESSION['csrf_token'] ?>');
    
    // Disable submit button
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.textContent;
    submitBtn.disabled = true;
    submitBtn.textContent = 'Sending...';
    
    fetch('./api/invitations/send-invitation.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(data.message, 'success');
            closeInviteModal();
            
            // Refresh invitations list if it's visible
            if (!document.getElementById('invitationsSection').classList.contains('hidden')) {
                loadInvitations();
            }
        } else {
            showNotification(data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('An error occurred while sending the invitation.', 'error');
    })
    .finally(() => {
        // Re-enable submit button
        submitBtn.disabled = false;
        submitBtn.textContent = originalText;
    });
});

// Close modal when clicking outside
document.getElementById('inviteModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeInviteModal();
    }
});

function showNotification(message, type) {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg transition-all duration-300 ${
        type === 'success' ? 'bg-green-500 text-white' : 'bg-red-500 text-white'
    }`;
    notification.textContent = message;
    
    // Add to page
    document.body.appendChild(notification);
    
    // Remove after 3 seconds
    setTimeout(() => {
        notification.style.opacity = '0';
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

// Update button text based on current view
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const showDeleted = urlParams.get('show_deleted');
    
    if (showDeleted === '1') {
        document.getElementById('viewDeletedBtn').textContent = 'View Active';
    }
});
</script>

<!-- Reset Password Modal -->
<div id="resetPasswordModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50" onclick="closeResetPasswordModal()">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg max-w-md w-full p-6" onclick="event.stopPropagation()">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Reset Password</h3>
            <p class="text-sm text-gray-600 mb-4">
                Enter a new password for <span id="resetUserName" class="font-medium"></span>
            </p>
            
            <form id="resetPasswordForm" onsubmit="resetPassword(event)">
                <div class="mb-4">
                    <label for="newPassword" class="block text-sm font-medium text-gray-700 mb-2">
                        New Password
                    </label>
                    <input 
                        type="password" 
                        id="newPassword" 
                        name="newPassword"
                        required
                        minlength="6"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        placeholder="Enter new password (min 6 characters)"
                    >
                </div>
                
                <div class="mb-6">
                    <label for="confirmPassword" class="block text-sm font-medium text-gray-700 mb-2">
                        Confirm Password
                    </label>
                    <input 
                        type="password" 
                        id="confirmPassword" 
                        name="confirmPassword"
                        required
                        minlength="6"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        placeholder="Confirm new password"
                    >
                </div>
                
                <div class="flex gap-3 justify-end">
                    <button 
                        type="button" 
                        onclick="closeResetPasswordModal()"
                        class="px-4 py-2 text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors"
                    >
                        Cancel
                    </button>
                    <button 
                        type="submit" 
                        class="px-4 py-2 bg-black text-white rounded-lg hover:bg-gray-800 transition-colors"
                    >
                        Reset Password
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let currentResetUserId = null;

function showResetPasswordModal(userId, userName) {
    currentResetUserId = userId;
    document.getElementById('resetUserName').textContent = userName;
    document.getElementById('resetPasswordModal').classList.remove('hidden');
    document.getElementById('newPassword').focus();
}

function closeResetPasswordModal() {
    document.getElementById('resetPasswordModal').classList.add('hidden');
    document.getElementById('resetPasswordForm').reset();
    currentResetUserId = null;
}

async function resetPassword(event) {
    event.preventDefault();
    
    const newPassword = document.getElementById('newPassword').value;
    const confirmPassword = document.getElementById('confirmPassword').value;
    
    // Validate password match
    if (newPassword !== confirmPassword) {
        alert('Passwords do not match. Please try again.');
        return;
    }
    
    // Validate password strength
    if (newPassword.length < 6) {
        alert('Password must be at least 6 characters long.');
        return;
    }
    
    try {
        const response = await fetch('./api/users/reset-password.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                user_id: currentResetUserId,
                new_password: newPassword,
                csrf_token: window.csrfToken
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            alert('Password reset successfully!');
            closeResetPasswordModal();
        } else {
            alert('Error: ' + (data.error || 'Failed to reset password'));
        }
    } catch (error) {
        console.error('Reset password error:', error);
        alert('An error occurred while resetting the password.');
    }
}

// Close modal with Escape key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeResetPasswordModal();
    }
});
</script>

<?php require __DIR__.'/includes/footer.php'; ?>