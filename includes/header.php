<?php 
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__.'/auth.php'; // Required for has_role and get_role_display_name functions
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>JJS Team Portal</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-gray-50 text-gray-900">
<header class="bg-white border-b">
  <div class="max-w-7xl mx-auto px-4 h-16 flex items-center justify-between">
    <a href="/" class="flex items-center gap-3">
      <img src="/assets/images/logo.png" alt="J. Joseph Salon Logo" class="h-10 w-auto">
      <div>
        <div class="font-semibold text-gray-900 leading-tight">J. Joseph Salon</div>
        <div class="text-sm text-gray-600 -mt-1">Portal</div>
      </div>
    </a>
    <nav class="text-sm flex items-center gap-4">
      <a href="/announcements.php" class="hover:underline">Announcements</a>
      <?php if (!empty($_SESSION['user_id'])): ?>
        <a href="/dashboard.php" class="hover:underline">Dashboard</a>
        <a href="/forms.php" class="hover:underline">Forms</a>
        <a href="/reports.php" class="hover:underline">Reports</a>
        
        <!-- User Account Dropdown -->
        <div class="relative" x-data="{ open: false }">
          <button @click="open = !open" @click.away="open = false" 
                  class="flex items-center justify-center w-8 h-8 bg-gray-800 text-white rounded-full text-sm font-medium hover:bg-gray-700 transition-colors">
            <?= strtoupper(substr($_SESSION['name'] ?? 'U', 0, 1)) ?>
          </button>
          
          <div x-show="open" x-transition 
               class="absolute right-0 mt-2 w-64 bg-white rounded-lg shadow-lg border py-2 z-50">
            <!-- Account Info -->
            <div class="px-4 py-3 border-b">
              <div class="font-medium text-gray-900">Account</div>
              <div class="text-sm text-gray-500"><?= htmlspecialchars($_SESSION['email'] ?? 'user@example.com') ?></div>
            </div>
            
            <!-- Settings -->
            <a href="/dashboard.php" class="flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-gray-50">
              <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
              </svg>
              Settings
            </a>
            
            <?php if (has_role('admin')): ?>
            <!-- Admin Tools -->
            <a href="/admin.php" class="flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-gray-50">
              <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
              </svg>
              Admin Tools
              <svg class="w-4 h-4 ml-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
              </svg>
            </a>
            <?php endif; ?>
            
            <!-- Sign Out -->
            <a href="/logout.php" class="flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-gray-50">
              <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
              </svg>
              Sign Out
            </a>
          </div>
        </div>
      <?php else: ?>
        <a href="/" class="hover:underline">Home</a>
        <a href="/login.php" class="hover:underline">Login</a>
      <?php endif; ?>
    </nav>
  </div>
</header>
<main class="max-w-7xl mx-auto px-4 py-8">