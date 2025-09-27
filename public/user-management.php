<?php
require __DIR__.'/includes/auth.php';
require_login();
require_role('admin'); // Admin-only
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
            $successChange = UserManager::updateUserRole($user_id, $new_role, $_SESSION['user_id']);
            
            if ($successChange) {
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
    <h1 class="text-2xl font-semibold">User Management</h1>
</div>

<?php if ($message): ?>
    <?= $message ?>
    <div class="mb-6"></div>
<?php endif; ?>

<?php 
// Load users (no deleted view in this page)
$all_users = UserManager::getAllUsers(false);
?>

<!-- Header with Invite -->
<div class="bg-white rounded-xl border mb-6">
    <div class="p-4 sm:p-6 border-b">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-lg font-semibold text-gray-900">Users</h2>
                <p class="text-sm text-gray-600 mt-1">Manage accounts, roles, and invitations.</p>
            </div>
            <div class="flex items-stretch gap-3">
                <button onclick="openInviteModal()" class="px-4 py-3 sm:py-2 bg-black text-white rounded-lg hover:bg-gray-800 transition-colors flex items-center justify-center gap-2 text-sm sm:text-base">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Invite User
                </button>
            </div>
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
            <p class="text-sm">Send your first invitation to get started.</p>
        </div>
        <?php else: ?>
            <?php foreach ($all_users as $user): ?>
            <div class="p-4 sm:p-6 hover:bg-gray-50 transition-colors">
                <div class="flex flex-col sm:flex-row sm:items-center gap-4">
                    <!-- Info -->
                    <div class="flex-grow">
                        <div class="flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-3">
                            <h3 class="font-semibold text-gray-900 text-base sm:text-lg"><?= htmlspecialchars($user['name']) ?></h3>
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-700"><?= htmlspecialchars(get_role_display_name($user['role'])) ?></span>
                                <?php $user_status = $user['status'] ?? 'active'; $is_deleted = $user_status === 'deleted'; $is_active = $user_status === 'active'; ?>
                                <?php if (!$is_deleted): ?>
                                <button onclick="toggleUserStatus(<?= $user['id'] ?>, '<?= $is_active ? 'inactive' : 'active' ?>')"
                                        class="px-3 py-2 sm:px-2 sm:py-1 text-xs font-medium rounded-full transition-colors <?= $is_active ? 'bg-green-100 text-green-800 hover:bg-green-200' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' ?>">
                                    <?= ucfirst($user_status) ?>
                                </button>
                                <?php endif; ?>
                                <?php if ($user['id'] == $_SESSION['user_id']): ?>
                                <span class="px-2 py-1 text-xs font-medium rounded-full bg-blue-50 text-blue-600">You</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="mt-2 sm:mt-1">
                            <p class="text-sm text-gray-500"><?= htmlspecialchars($user['email']) ?></p>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 mt-4 sm:mt-0">
                        <!-- Role Change -->
                        <form method="POST" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
                            <input type="hidden" name="action" value="update_role">
                            <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                            <select name="new_role" class="border border-gray-300 rounded-lg px-3 py-2 text-sm min-w-0 sm:min-w-32" onchange="this.form.submit()">
                                <?php foreach (get_all_roles() as $role): ?>
                                <option value="<?= htmlspecialchars($role) ?>" <?= $user['role'] === $role ? 'selected' : '' ?>>
                                    <?= htmlspecialchars(get_role_display_name($role)) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                            <noscript>
                                <button type="submit" class="px-3 py-2 text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-700">Update</button>
                            </noscript>
                        </form>

                        <!-- More Actions -->
                        <div class="relative w-full sm:w-auto" x-data="{ open: false }">
                            <button @click="open = !open" @click.outside="open = false" 
                                    class="w-full sm:w-auto px-4 py-2 text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors flex items-center justify-center gap-2">
                                <span class="text-sm">Actions</span>
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"></path></svg>
                            </button>
                            <div x-show="open" x-cloak x-transition class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border py-1 z-50">
                                <button onclick="showResetPasswordModal(<?= $user['id'] ?>, '<?= htmlspecialchars($user['name']) ?>')" class="w-full text-left px-4 py-3 text-sm text-gray-700 hover:bg-gray-50">Reset Password</button>
                                <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                <div class="border-t my-1"></div>
                                <button onclick="deleteUser(<?= $user['id'] ?>, '<?= htmlspecialchars($user['name']) ?>')" class="w-full text-left px-4 py-3 text-sm text-red-600 hover:bg-red-50">Delete User</button>
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

<!-- Invitations Section Toggle Buttons -->
<div class="bg-gray-50 border border-gray-200 rounded-xl p-4">
    <h3 class="font-semibold text-gray-800 mb-3 text-sm sm:text-base">Invitations</h3>
    <div class="space-y-2">
        <button onclick="showInvitationsTab()" class="w-full text-left px-3 py-2 text-xs sm:text-sm text-gray-700 hover:bg-gray-100 rounded-lg transition-colors">📧 Manage Pending Invitations</button>
    </div>
</div>

<!-- Invite User Modal -->
<div id="inviteModal" class="fixed inset-0 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-xl shadow-xl max-w-md w-full mx-4 border">
        <div class="p-6 border-b">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900">Invite New User</h3>
                <button onclick="closeInviteModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
        </div>
        <form id="inviteForm" class="p-6">
            <div class="space-y-4">
                <div>
                    <label for="inviteEmail" class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
                    <input type="email" id="inviteEmail" name="email" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="user@example.com">
                </div>
                <div>
                    <label for="inviteRole" class="block text-sm font-medium text-gray-700 mb-2">Role</label>
                    <select id="inviteRole" name="role" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Select a role...</option>
                        <option value="viewer">Viewer</option>
                        <option value="staff">Staff Member</option>
                        <option value="support">Support Specialist</option>
                        <option value="manager">Manager</option>
                        <option value="admin">Administrator</option>
                    </select>
                </div>
                <div class="rounded-lg p-0">
                    <div class="flex">
                        <svg class="w-5 h-5 mt-0.5 mr-3" fill="none" stroke="#AF831A" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M12 2a10 10 0 100 20 10 10 0 000-20z"/></svg>
                        <div>
                            <p class="text-sm font-medium" style="color:#AF831A">Invitation Details</p>
                            <p class="text-xs mt-1 text-gray-600">The user will receive an email with a secure link to complete registration. The invitation expires in 7 days.</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="flex justify-end gap-3 mt-6">
                <button type="button" onclick="closeInviteModal()" class="px-4 py-2 text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">Cancel</button>
                <button type="submit" class="px-4 py-2 text-white rounded-lg transition-colors" style="background-color:#000">Send Invitation</button>
            </div>
        </form>
    </div>
</div>

<!-- Invitations Management Section -->
<div id="invitationsSection" class="hidden mt-6">
    <div class="bg-white rounded-xl border">
        <div class="p-4 sm:p-6 border-b">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">Pending Invitations</h2>
                    <p class="text-sm text-gray-600 mt-1">Manage sent invitations and track their status.</p>
                </div>
                <button onclick="hideInvitationsTab()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
        </div>
        <div id="invitationsList" class="divide-y"></div>
    </div>
</div>

<script>
function openInviteModal() { document.getElementById('inviteModal').classList.remove('hidden'); document.getElementById('inviteEmail').focus(); }
function closeInviteModal() { document.getElementById('inviteModal').classList.add('hidden'); document.getElementById('inviteForm').reset(); }
function showInvitationsTab() { document.getElementById('invitationsSection').classList.remove('hidden'); loadInvitations(); document.getElementById('invitationsSection').scrollIntoView({behavior:'smooth'}); }
function hideInvitationsTab() { document.getElementById('invitationsSection').classList.add('hidden'); }

document.getElementById('inviteForm').addEventListener('submit', function(e) {
  e.preventDefault();
  const formData = new FormData(this);
  formData.append('csrf_token', '<?= $_SESSION['csrf_token'] ?>');
  const btn = this.querySelector('button[type="submit"]');
  const txt = btn.textContent; btn.disabled = true; btn.textContent = 'Sending...';
  fetch('api/invitations/send-invitation.php', { method: 'POST', body: formData })
    .then(r => r.json()).then(data => {
      if (data.success) {
        showBanner('success', data.message);
        closeInviteModal();
        if (!document.getElementById('invitationsSection').classList.contains('hidden')) { loadInvitations(); }
      } else {
        showBanner('error', data.message || 'Failed to send invitation');
      }
    })
    .catch(() => showBanner('error', 'An error occurred while sending the invitation.'))
    .finally(()=>{ btn.disabled = false; btn.textContent = txt; });
});

function loadInvitations() {
  fetch('api/invitations/list-invitations.php')
    .then(r=>r.json()).then(data=>{ if (data.success) { displayInvitations(data.invitations||[]); } else { alert('Failed to load invitations: '+(data.message||'')); }})
    .catch(()=> alert('An error occurred while loading invitations.'));
}

function displayInvitations(invitations) {
  const container = document.getElementById('invitationsList');
  if (!invitations.length) { container.innerHTML = '<div class="p-8 text-center text-gray-500">No pending invitations</div>'; return; }
  const html = invitations.map(inv => {
    const expiresAt = new Date(inv.expires_at);
    const isExpired = expiresAt < new Date();
    const badge = inv.status === 'accepted' ? 'bg-green-100 text-green-800' : (isExpired ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800');
    return `<div class="p-4 sm:p-6 hover:bg-gray-50 transition-colors"><div class="flex flex-col sm:flex-row sm:items-center gap-4"><div class="flex-grow"><div class="flex flex-wrap items-center gap-2"><h3 class="font-semibold text-gray-900">${escapeHtml(inv.email)}</h3><span class="px-2 py-1 text-xs font-medium rounded-full ${badge}">${inv.status.charAt(0).toUpperCase()+inv.status.slice(1)}</span></div><div class="mt-1 text-sm text-gray-500">Expires ${formatDate(inv.expires_at)}</div></div><div class="flex items-center gap-3">${(inv.status==='pending'&&!isExpired)?`<button onclick=\"copyInvitationLink('${inv.token}')\" class=\"px-4 py-2 text-sm bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200\">Copy Link</button><button onclick=\"revokeInvitation(${inv.id}, '${escapeHtml(inv.email)}')\" class=\"px-4 py-2 text-sm bg-red-600 text-white rounded-lg hover:bg-red-700\">Revoke</button>`:''}</div></div></div>`;
  }).join('');
  container.innerHTML = html;
}

// Lightweight notification banner (top-right)
function showBanner(type, message) {
  const banner = document.createElement('div');
  const isSuccess = type === 'success';
  banner.className = 'fixed top-4 right-4 z-50 px-4 py-3 rounded-lg shadow-lg border ' + (isSuccess ? 'bg-green-50 border-green-200 text-green-800' : 'bg-red-50 border-red-200 text-red-800');
  banner.innerHTML = `<div class="flex items-start gap-2">
      <svg class="w-5 h-5 mt-0.5" fill="currentColor" viewBox="0 0 20 20">${isSuccess?'<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>':'<path fill-rule="evenodd" d="M18 10A8 8 0 112 10a8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>'}</svg>
      <div class="text-sm">${escapeHtml(message)}</div>
    </div>`;
  document.body.appendChild(banner);
  setTimeout(() => { if (banner.parentNode) banner.parentNode.removeChild(banner); }, isSuccess ? 2500 : 3500);
}

function escapeHtml(t){const d=document.createElement('div');d.textContent=t;return d.innerHTML}
function formatDate(s){const d=new Date(s);const now=new Date();const diff=(d-now)/(1000*60*60*24);if(diff<0)return 'Expired';if(Math.round(diff)===0)return 'Today';if(Math.round(diff)===1)return 'Tomorrow';return `in ${Math.round(diff)} days`;}
function copyInvitationLink(token){const u=`${location.protocol}//${location.host}/signup.php?token=${encodeURIComponent(token)}`;navigator.clipboard.writeText(u).then(()=>alert('Invitation link copied')).catch(()=>{const a=document.createElement('textarea');a.value=u;document.body.appendChild(a);a.select();document.execCommand('copy');document.body.removeChild(a);alert('Invitation link copied');});}
function revokeInvitation(id,email){if(!confirm(`Revoke invitation for ${email}?`))return;const fd=new FormData();fd.append('invitation_id',id);fd.append('csrf_token','<?= $_SESSION['csrf_token'] ?>');fetch('api/invitations/revoke-invitation.php',{method:'POST',body:fd}).then(r=>r.json()).then(d=>{if(d.success){loadInvitations();}else{alert(d.message||'Failed to revoke');}}).catch(()=>alert('Error revoking invitation'));}

function toggleUserStatus(userId, newStatus){const fd=new FormData();fd.append('user_id',userId);fd.append('status',newStatus);fd.append('csrf_token','<?= $_SESSION['csrf_token'] ?>');fetch('api/users/update-status.php',{method:'POST',body:fd}).then(r=>r.json()).then(d=>{if(d.success){location.reload();}else{alert(d.message||'Failed to update status');}}).catch(()=>alert('Error updating status'));}
function deleteUser(userId, userName){if(!confirm(`Delete ${userName}? This is a soft delete and can be restored.`))return;const fd=new FormData();fd.append('user_id',userId);fd.append('csrf_token','<?= $_SESSION['csrf_token'] ?>');fetch('api/users/delete-user.php',{method:'POST',body:fd}).then(r=>r.json()).then(d=>{if(d.success){location.reload();}else{alert(d.message||'Failed to delete');}}).catch(()=>alert('Error deleting user'));}

let currentResetUserId=null;
function showResetPasswordModal(userId, userName){currentResetUserId=userId;document.getElementById('resetUserName').textContent=userName;document.getElementById('resetPasswordModal').classList.remove('hidden');document.getElementById('newPassword').focus();}
function closeResetPasswordModal(){document.getElementById('resetPasswordModal').classList.add('hidden');document.getElementById('resetPasswordForm').reset();currentResetUserId=null;}
async function resetPassword(e){e.preventDefault();const np=document.getElementById('newPassword').value;const cp=document.getElementById('confirmPassword').value;if(np!==cp){alert('Passwords do not match.');return;}if(np.length<6){alert('Password must be at least 6 characters.');return;}try{const resp=await fetch('api/users/reset-password.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({user_id:currentResetUserId,new_password:np,csrf_token:window.csrfToken})});const data=await resp.json();if(data.success){alert('Password reset successfully');closeResetPasswordModal();}else{alert('Error: '+(data.error||'Failed to reset password'));}}catch(err){alert('An error occurred while resetting the password.');}}
document.addEventListener('keydown',function(e){if(e.key==='Escape'){closeResetPasswordModal();}});
</script>

<!-- Reset Password Modal -->
<div id="resetPasswordModal" class="fixed inset-0 hidden z-50" onclick="closeResetPasswordModal()">
  <div class="flex items-center justify-center min-h-screen p-4">
    <div class="bg-white rounded-lg max-w-md w-full p-6" onclick="event.stopPropagation()">
      <h3 class="text-lg font-semibold text-gray-900 mb-4">Reset Password</h3>
      <p class="text-sm text-gray-600 mb-4">Enter a new password for <span id="resetUserName" class="font-medium"></span></p>
      <form id="resetPasswordForm" onsubmit="resetPassword(event)">
        <div class="mb-4">
          <label for="newPassword" class="block text-sm font-medium text-gray-700 mb-2">New Password</label>
          <input type="password" id="newPassword" name="newPassword" required minlength="6" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Enter new password (min 6 characters)">
        </div>
        <div class="mb-6">
          <label for="confirmPassword" class="block text-sm font-medium text-gray-700 mb-2">Confirm Password</label>
          <input type="password" id="confirmPassword" name="confirmPassword" required minlength="6" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Confirm new password">
        </div>
        <div class="flex gap-3 justify-end">
          <button type="button" onclick="closeResetPasswordModal()" class="px-4 py-2 text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">Cancel</button>
          <button type="submit" class="px-4 py-2 bg-black text-white rounded-lg hover:bg-gray-800 transition-colors">Reset Password</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php require __DIR__.'/includes/footer.php'; ?>


