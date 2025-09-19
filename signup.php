<?php
/**
 * Public Signup Page
 * Allows invited users to complete their registration
 */

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/invitation-manager.php';

// Set security headers to prevent token leakage
header('Referrer-Policy: no-referrer');
header("Content-Security-Policy: default-src 'self'; style-src 'self' 'unsafe-inline'; script-src 'self' 'unsafe-inline'; img-src 'self' data:; connect-src 'self'");

// Initialize invitation manager
$invitationManager = InvitationManager::getInstance();

// Check if user is already logged in
session_start();
if (isset($_SESSION['user_id'])) {
    header('Location: /dashboard.php');
    exit;
}

// Generate CSRF token for form
if (!isset($_SESSION['signup_csrf_token'])) {
    $_SESSION['signup_csrf_token'] = bin2hex(random_bytes(32));
}

// Get and validate token
$token = $_GET['token'] ?? '';
$invitation = null;
$error_message = '';

if (empty($token)) {
    $error_message = 'Invalid or missing invitation token.';
} else {
    $invitation = $invitationManager->getInvitationByToken($token);
    
    if (!$invitation) {
        $error_message = 'Invalid invitation token.';
    } elseif ($invitation['status'] !== 'pending') {
        $error_message = 'This invitation is no longer active.';
    } elseif (strtotime($invitation['expires_at']) < time()) {
        $error_message = 'This invitation has expired.';
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $invitation && empty($error_message)) {
    try {
        // CSRF protection 
        $provided_token = $_POST['csrf_token'] ?? '';
        $session_token = $_SESSION['signup_csrf_token'] ?? '';
        
        if (empty($provided_token) || empty($session_token)) {
            throw new InvalidArgumentException('Security token missing. Please refresh and try again.');
        }
        
        if (!hash_equals($session_token, $provided_token)) {
            throw new InvalidArgumentException('Invalid security token. Please refresh and try again.');
        }
        // Validate input
        $name = trim($_POST['name'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        if (empty($name)) {
            throw new InvalidArgumentException('Please enter your full name.');
        }
        
        if (strlen($name) < 2) {
            throw new InvalidArgumentException('Name must be at least 2 characters long.');
        }
        
        if (empty($password)) {
            throw new InvalidArgumentException('Please enter a password.');
        }
        
        if (strlen($password) < 8) {
            throw new InvalidArgumentException('Password must be at least 8 characters long.');
        }
        
        if ($password !== $confirm_password) {
            throw new InvalidArgumentException('Passwords do not match.');
        }
        
        // Accept invitation and create user
        $user_id = $invitationManager->acceptInvitation($token, $name, $password);
        
        // Log the user in
        $_SESSION['user_id'] = $user_id;
        $_SESSION['user_email'] = $invitation['email'];
        $_SESSION['user_name'] = $name;
        $_SESSION['user_role'] = $invitation['role'];
        
        // Generate CSRF token for authenticated session
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        
        // Clear signup CSRF token
        unset($_SESSION['signup_csrf_token']);
        
        // Regenerate session ID for security
        session_regenerate_id(true);
        
        // Redirect to dashboard
        header('Location: /dashboard.php?welcome=1');
        exit;
        
    } catch (Exception $e) {
        $error_message = $e->getMessage();
    }
}

// Get role display name for invitation details
function getRoleDisplayName($role) {
    $role_names = [
        'admin' => 'Administrator',
        'manager' => 'Manager',
        'support' => 'Support Specialist',
        'staff' => 'Staff Member',
        'viewer' => 'Viewer'
    ];
    return $role_names[$role] ?? ucfirst($role);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="referrer" content="no-referrer">
    <title>Complete Your Registration - J. Joseph Salon</title>
    
    <!-- Security: Self-hosted styles to prevent token leakage to CDNs -->
    <style>
        /* Tailwind CSS Reset and Base Styles */
        *, ::before, ::after { box-sizing: border-box; border-width: 0; border-style: solid; border-color: #e5e7eb; }
        ::before, ::after { --tw-content: ''; }
        html { line-height: 1.5; -webkit-text-size-adjust: 100%; -moz-tab-size: 4; tab-size: 4; font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji"; }
        body { margin: 0; line-height: inherit; }
        
        /* Tailwind Utilities - Custom subset for signup page */
        .bg-gradient-to-br { background-image: linear-gradient(to bottom right, var(--tw-gradient-stops)); }
        .from-blue-50 { --tw-gradient-from: #eff6ff; --tw-gradient-to: rgb(239 246 255 / 0); --tw-gradient-stops: var(--tw-gradient-from), var(--tw-gradient-to); }
        .to-indigo-100 { --tw-gradient-to: #e0e7ff; }
        .min-h-screen { min-height: 100vh; }
        .flex { display: flex; }
        .items-center { align-items: center; }
        .justify-center { justify-content: center; }
        .py-12 { padding-top: 3rem; padding-bottom: 3rem; }
        .px-4 { padding-left: 1rem; padding-right: 1rem; }
        .sm\\:px-6 { padding-left: 1.5rem; padding-right: 1.5rem; }
        .lg\\:px-8 { padding-left: 2rem; padding-right: 2rem; }
        .max-w-md { max-width: 28rem; }
        .w-full { width: 100%; }
        .text-center { text-align: center; }
        .mb-8 { margin-bottom: 2rem; }
        .mx-auto { margin-left: auto; margin-right: auto; }
        .h-16 { height: 4rem; }
        .w-16 { width: 4rem; }
        .bg-gradient-to-br { background-image: linear-gradient(to bottom right, var(--tw-gradient-stops)); }
        .from-blue-600 { --tw-gradient-from: #2563eb; --tw-gradient-to: rgb(37 99 235 / 0); --tw-gradient-stops: var(--tw-gradient-from), var(--tw-gradient-to); }
        .to-purple-600 { --tw-gradient-to: #9333ea; }
        .rounded-full { border-radius: 9999px; }
        .mb-4 { margin-bottom: 1rem; }
        .h-8 { height: 2rem; }
        .w-8 { width: 2rem; }
        .text-white { color: rgb(255 255 255); }
        .text-3xl { font-size: 1.875rem; line-height: 2.25rem; }
        .font-bold { font-weight: 700; }
        .text-gray-900 { color: rgb(17 24 39); }
        .text-gray-600 { color: rgb(75 85 99); }
        .mt-2 { margin-top: 0.5rem; }
        .bg-white { background-color: rgb(255 255 255); }
        .rounded-xl { border-radius: 0.75rem; }
        .shadow-lg { box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1); }
        .border { border-width: 1px; }
        .border-red-200 { border-color: rgb(254 202 202); }
        .p-6 { padding: 1.5rem; }
        .justify-center { justify-content: center; }
        .h-12 { height: 3rem; }
        .w-12 { width: 3rem; }
        .bg-red-100 { background-color: rgb(254 226 226); }
        .h-6 { height: 1.5rem; }
        .w-6 { width: 1.5rem; }
        .text-red-600 { color: rgb(220 38 38); }
        .text-lg { font-size: 1.125rem; line-height: 1.75rem; }
        .font-semibold { font-weight: 600; }
        .mb-2 { margin-bottom: 0.5rem; }
        .mb-6 { margin-bottom: 1.5rem; }
        .text-sm { font-size: 0.875rem; line-height: 1.25rem; }
        .inline-flex { display: inline-flex; }
        .px-4 { padding-left: 1rem; padding-right: 1rem; }
        .py-2 { padding-top: 0.5rem; padding-bottom: 0.5rem; }
        .bg-gray-600 { background-color: rgb(75 85 99); }
        .rounded-lg { border-radius: 0.5rem; }
        .hover\\:bg-gray-700:hover { background-color: rgb(55 65 81); }
        .transition-colors { transition-property: color, background-color, border-color, text-decoration-color, fill, stroke; transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1); transition-duration: 150ms; }
        .w-4 { width: 1rem; }
        .h-4 { height: 1rem; }
        .mr-2 { margin-right: 0.5rem; }
        .bg-blue-50 { background-color: rgb(239 246 255); }
        .border-blue-200 { border-color: rgb(191 219 254); }
        .mb-3 { margin-bottom: 0.75rem; }
        .w-5 { width: 1.25rem; }
        .h-5 { height: 1.25rem; }
        .text-blue-600 { color: rgb(37 99 235); }
        .text-blue-800 { color: rgb(30 64 175); }
        .space-y-2 > * + * { margin-top: 0.5rem; }
        .justify-between { justify-content: space-between; }
        .text-blue-700 { color: rgb(29 78 216); }
        .font-medium { font-weight: 500; }
        .text-blue-900 { color: rgb(30 58 138); }
        .space-y-6 > * + * { margin-top: 1.5rem; }
        .block { display: block; }
        .text-gray-700 { color: rgb(55 65 81); }
        .py-3 { padding-top: 0.75rem; padding-bottom: 0.75rem; }
        .px-3 { padding-left: 0.75rem; padding-right: 0.75rem; }
        .border-gray-300 { border-color: rgb(209 213 219); }
        .focus\\:ring-2:focus { box-shadow: var(--tw-ring-inset) 0 0 0 calc(2px + var(--tw-ring-offset-width)) var(--tw-ring-color); }
        .focus\\:ring-blue-500:focus { --tw-ring-color: rgb(59 130 246); }
        .focus\\:border-blue-500:focus { border-color: rgb(59 130 246); }
        .text-xs { font-size: 0.75rem; line-height: 1rem; }
        .text-gray-500 { color: rgb(107 114 128); }
        .mt-1 { margin-top: 0.25rem; }
        .bg-gray-50 { background-color: rgb(249 250 251); }
        .border-gray-200 { border-color: rgb(229 231 235); }
        .items-start { align-items: flex-start; }
        .mt-0\\.5 { margin-top: 0.125rem; }
        .mr-3 { margin-right: 0.75rem; }
        .bg-gradient-to-r { background-image: linear-gradient(to right, var(--tw-gradient-stops)); }
        .hover\\:from-blue-700:hover { --tw-gradient-from: #1d4ed8; --tw-gradient-to: rgb(29 78 216 / 0); --tw-gradient-stops: var(--tw-gradient-from), var(--tw-gradient-to); }
        .hover\\:to-purple-700:hover { --tw-gradient-to: #7c3aed; }
        .focus\\:ring-offset-2:focus { --tw-ring-offset-width: 2px; }
        .duration-200 { transition-duration: 200ms; }
        .transform { transform: translate(var(--tw-translate-x), var(--tw-translate-y)) rotate(var(--tw-rotate)) skewX(var(--tw-skew-x)) skewY(var(--tw-skew-y)) scaleX(var(--tw-scale-x)) scaleY(var(--tw-scale-y)); }
        .hover\\:scale-\\[1\\.02\\]:hover { --tw-scale-x: 1.02; --tw-scale-y: 1.02; }
        .mt-6 { margin-top: 1.5rem; }
        .mt-8 { margin-top: 2rem; }
        
        /* Form input focus styles */
        input:focus { outline: 2px solid transparent; outline-offset: 2px; }
        
        /* Button hover and focus styles */
        button:focus { outline: 2px solid transparent; outline-offset: 2px; }
        
        @media (min-width: 640px) {
            .sm\\:px-6 { padding-left: 1.5rem; padding-right: 1.5rem; }
        }
        
        @media (min-width: 1024px) {
            .lg\\:px-8 { padding-left: 2rem; padding-right: 2rem; }
        }
        
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-gradient-to-br from-blue-50 to-indigo-100 min-h-screen">
    <div class="flex items-center justify-center min-h-screen py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full">
            <!-- Header -->
            <div class="text-center mb-8">
                <div class="mx-auto h-16 w-16 bg-gradient-to-br from-blue-600 to-purple-600 rounded-full flex items-center justify-center mb-4">
                    <svg class="h-8 w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                    </svg>
                </div>
                <h1 class="text-3xl font-bold text-gray-900">J. Joseph Salon</h1>
                <p class="text-gray-600 mt-2">Complete Your Registration</p>
            </div>

            <?php if (!empty($error_message)): ?>
            <!-- Error State -->
            <div class="bg-white rounded-xl shadow-lg border border-red-200 p-6">
                <div class="flex items-center justify-center mb-4">
                    <div class="h-12 w-12 bg-red-100 rounded-full flex items-center justify-center">
                        <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.996-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                        </svg>
                    </div>
                </div>
                <div class="text-center">
                    <h2 class="text-lg font-semibold text-gray-900 mb-2">Unable to Complete Registration</h2>
                    <p class="text-red-600 mb-4"><?= htmlspecialchars($error_message) ?></p>
                    <p class="text-sm text-gray-600 mb-6">
                        If you believe this is an error, please contact your administrator for assistance.
                    </p>
                    <a href="/login.php" 
                       class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 15l-3-3m0 0l3-3m-3 3h8M3 12a9 9 0 1118 0 9 9 0 01-18 0z"></path>
                        </svg>
                        Go to Login
                    </a>
                </div>
            </div>
            
            <?php else: ?>
            <!-- Registration Form -->
            <div class="bg-white rounded-xl shadow-lg border p-6">
                <!-- Invitation Details -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                    <div class="flex items-center mb-3">
                        <svg class="w-5 h-5 text-blue-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"></path>
                            <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"></path>
                        </svg>
                        <h3 class="text-sm font-semibold text-blue-800">Invitation Details</h3>
                    </div>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-blue-700">Email:</span>
                            <span class="font-medium text-blue-900"><?= htmlspecialchars($invitation['email']) ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-blue-700">Role:</span>
                            <span class="font-medium text-blue-900"><?= htmlspecialchars(getRoleDisplayName($invitation['role'])) ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-blue-700">Expires:</span>
                            <span class="font-medium text-blue-900"><?= date('M j, Y', strtotime($invitation['expires_at'])) ?></span>
                        </div>
                    </div>
                </div>

                <form method="POST" action="" class="space-y-6">
                    <?php if ($invitation): ?>
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['signup_csrf_token']) ?>">
                    <?php endif; ?>
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Full Name</label>
                        <input type="text" id="name" name="name" required
                               value="<?= htmlspecialchars($_POST['name'] ?? '') ?>"
                               class="w-full px-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                               placeholder="Enter your full name">
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-2">Password</label>
                        <input type="password" id="password" name="password" required
                               class="w-full px-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                               placeholder="Create a strong password">
                        <p class="text-xs text-gray-500 mt-1">Must be at least 8 characters long</p>
                    </div>

                    <div>
                        <label for="confirm_password" class="block text-sm font-medium text-gray-700 mb-2">Confirm Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" required
                               class="w-full px-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                               placeholder="Confirm your password">
                    </div>

                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                        <div class="flex items-start">
                            <svg class="w-5 h-5 text-gray-500 mt-0.5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                            </svg>
                            <div>
                                <p class="text-sm text-gray-700 font-medium">What happens next?</p>
                                <ul class="text-xs text-gray-600 mt-1 space-y-1">
                                    <li>• Your account will be created with the specified role</li>
                                    <li>• You'll be automatically logged in</li>
                                    <li>• You'll have access to the team portal features</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <button type="submit" 
                            class="w-full px-4 py-3 bg-gradient-to-r from-blue-600 to-purple-600 text-white font-semibold rounded-lg hover:from-blue-700 hover:to-purple-700 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-200 transform hover:scale-[1.02]">
                        Complete Registration
                    </button>
                </form>

                <div class="mt-6 text-center">
                    <p class="text-xs text-gray-500">
                        By completing registration, you agree to our terms of service and privacy policy.
                    </p>
                </div>
            </div>
            <?php endif; ?>

            <!-- Footer -->
            <div class="text-center mt-8">
                <p class="text-sm text-gray-600">
                    Need help? Contact your administrator.
                </p>
            </div>
        </div>
    </div>

    <script>
        // Basic client-side validation
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form');
            const password = document.getElementById('password');
            const confirmPassword = document.getElementById('confirm_password');
            
            if (form) {
                form.addEventListener('submit', function(e) {
                    if (password.value !== confirmPassword.value) {
                        e.preventDefault();
                        alert('Passwords do not match. Please check and try again.');
                        confirmPassword.focus();
                        return false;
                    }
                    
                    if (password.value.length < 8) {
                        e.preventDefault();
                        alert('Password must be at least 8 characters long.');
                        password.focus();
                        return false;
                    }
                });
                
                // Real-time password confirmation validation
                confirmPassword.addEventListener('input', function() {
                    if (this.value && password.value && this.value !== password.value) {
                        this.setCustomValidity('Passwords do not match');
                    } else {
                        this.setCustomValidity('');
                    }
                });
            }
        });
    </script>
</body>
</html>