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
  <script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>
  <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
  <style>
    [x-cloak] { display: none !important; }
  </style>
  
  <?php if (!empty($_SESSION['user_id'])): ?>
  <script>
    // CSRF token for API calls
    window.csrfToken = '<?= $_SESSION['csrf_token'] ?>';
    
    // Complete Notification Bell Component
    document.addEventListener('alpine:init', () => {
      Alpine.data('notificationBell', () => ({
        open: false,
        loading: false,
        notifications: [],
        unreadCount: 0,
        
        async toggle() {
          this.open = !this.open;
          if (this.open && this.notifications.length === 0) {
            await this.loadNotifications();
          }
        },
        
        async loadNotifications() {
          this.loading = true;
          try {
            const response = await fetch('/api/notifications.php');
            if (response.ok) {
              const data = await response.json();
              if (data.success) {
                this.notifications = data.notifications || [];
                this.unreadCount = data.unread_count || 0;
              }
            }
          } catch (error) {
            console.error('Error loading notifications:', error);
          } finally {
            this.loading = false;
          }
        },

        async markAsRead(notificationId) {
          try {
            const response = await fetch('/api/notifications/mark-read.php', {
              method: 'POST',
              headers: { 'Content-Type': 'application/json' },
              body: JSON.stringify({
                notification_id: notificationId,
                csrf_token: window.csrfToken || ''
              })
            });
            const data = await response.json();
            if (data.success) {
              const notification = this.notifications.find(n => n.id === notificationId);
              if (notification) {
                notification.is_read = true;
                this.unreadCount = Math.max(0, data.unread_count ?? this.unreadCount - 1);
                if (notification.link_url) {
                  this.open = false;
                  window.location.href = notification.link_url;
                }
              }
            }
          } catch (error) {
            console.error('Error marking notification as read:', error);
          }
        },
        
        async markAllRead() {
          try {
            const response = await fetch('/api/notifications/mark-all-read.php', {
              method: 'POST',
              headers: { 'Content-Type': 'application/json' },
              body: JSON.stringify({
                csrf_token: window.csrfToken || ''
              })
            });
            const data = await response.json();
            if (data.success) {
              this.notifications.forEach(n => n.is_read = true);
              this.unreadCount = 0;
            }
          } catch (error) {
            console.error('Error marking all as read:', error);
          }
        },

        formatDate(dateString) {
          const date = new Date(dateString);
          const diffMs = Date.now() - date.getTime();
          const minutes = Math.floor(diffMs / 60000);
          const hours = Math.floor(diffMs / 3600000);
          const days = Math.floor(diffMs / 86400000);
          
          if (minutes < 1) return 'Just now';
          if (minutes < 60) return `${minutes}m ago`;
          if (hours < 24) return `${hours}h ago`;
          if (days < 7) return `${days}d ago`;
          return date.toLocaleDateString();
        }
      }));
    });
  </script>
  <?php endif; ?>
</head>
<body class="bg-gray-50 text-gray-900">
<header class="bg-white border-b" x-data="{ mobileMenuOpen: false }">
  <div class="max-w-7xl mx-auto px-4 h-16 flex items-center justify-between">
    <!-- Logo - icon only on mobile, with text on desktop -->
    <a href="/" class="flex items-center gap-3">
      <img src="/assets/images/logo.png" alt="J. Joseph Salon Logo" class="h-10 w-auto">
      <div class="hidden md:block">
        <div class="leading-tight">
          <span class="font-semibold text-gray-900">JJS</span>
          <span class="text-gray-600"> - </span>
          <span class="text-sm text-gray-600">Portal</span>
        </div>
      </div>
    </a>

    <!-- Desktop Navigation - hidden on mobile -->
    <nav class="hidden md:flex text-sm items-center gap-1">
      <?php if (!empty($_SESSION['user_id'])): ?>
        <!-- Home/Dashboard first -->
        <a href="/dashboard.php" class="flex items-center gap-2 px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-100 hover:text-gray-900 transition-colors">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #AF831A;">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"></path>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5a2 2 0 012-2h4a2 2 0 012 2v6H8V5z"></path>
          </svg>
          Dashboard
        </a>
        
        <!-- Separator -->
        <div class="w-px h-4 bg-gray-300 mx-2"></div>
        
        <a href="/announcements.php" class="flex items-center gap-2 px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-100 hover:text-gray-900 transition-colors">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #AF831A;">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"></path>
          </svg>
          Announcements
        </a>
        
        <!-- Notification Bell -->
        <div class="relative" x-data="notificationBell" @click.outside="open = false">
          <button @click="toggle()" 
                  class="relative flex items-center justify-center w-10 h-10 rounded-lg hover:bg-gray-100 transition-colors">
            <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
            </svg>
            <!-- Unread badge -->
            <span x-show="unreadCount > 0" x-cloak
                  class="absolute -top-1 -right-1 bg-red-600 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center font-medium"
                  x-text="unreadCount > 99 ? '99+' : unreadCount"></span>
          </button>
          
          <!-- Dropdown -->
          <div x-show="open" x-cloak
               x-transition:enter="transition ease-out duration-200"
               x-transition:enter-start="opacity-0 transform scale-95"
               x-transition:enter-end="opacity-100 transform scale-100"
               x-transition:leave="transition ease-in duration-150"
               x-transition:leave-start="opacity-100 transform scale-100"
               x-transition:leave-end="opacity-0 transform scale-95"
               class="absolute right-0 mt-2 w-80 bg-white rounded-xl border shadow-xl z-50">
            
            <!-- Header -->
            <div class="px-4 py-3 border-b">
              <div class="flex items-center justify-between">
                <h3 class="font-semibold text-gray-900">Notifications</h3>
                <button x-show="unreadCount > 0" @click="markAllRead()"
                        class="text-xs text-blue-600 hover:text-blue-800">
                  Mark all read
                </button>
              </div>
            </div>
            
            <!-- Loading State -->
            <div x-show="loading" class="px-4 py-8 text-center text-gray-500">
              <svg class="animate-spin h-5 w-5 mx-auto text-gray-400" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              <p class="mt-2">Loading notifications...</p>
            </div>
            
            <!-- Notifications List -->
            <div x-show="!loading && notifications.length > 0" class="max-h-96 overflow-y-auto">
              <template x-for="notification in notifications" :key="notification.id">
                <div @click="markAsRead(notification.id)"
                     :class="notification.is_read ? 'bg-white' : 'bg-blue-50'"
                     class="px-4 py-3 hover:bg-gray-50 cursor-pointer border-b transition-colors">
                  <div class="flex items-start gap-3">
                    <!-- Icon -->
                    <div class="flex-shrink-0 mt-0.5">
                      <div :class="notification.icon === 'info' ? 'bg-blue-100 text-blue-600' : 
                                   notification.icon === 'success' ? 'bg-green-100 text-green-600' :
                                   notification.icon === 'warning' ? 'bg-yellow-100 text-yellow-600' :
                                   notification.icon === 'error' ? 'bg-red-100 text-red-600' :
                                   'bg-gray-100 text-gray-600'"
                           class="w-8 h-8 rounded-full flex items-center justify-center">
                        <svg x-show="notification.icon === 'info'" class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                          <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                        </svg>
                        <svg x-show="notification.icon === 'success'" class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                          <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        <svg x-show="notification.icon === 'warning'" class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                          <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                        </svg>
                        <svg x-show="notification.icon === 'error'" class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                          <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                        </svg>
                        <svg x-show="!notification.icon || notification.icon === 'default'" class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                          <path d="M10 2a6 6 0 00-6 6v3.586l-.707.707A1 1 0 004 14h12a1 1 0 00.707-1.707L16 11.586V8a6 6 0 00-6-6zM10 18a3 3 0 01-3-3h6a3 3 0 01-3 3z"></path>
                        </svg>
                      </div>
                    </div>
                    
                    <!-- Content -->
                    <div class="flex-1 min-w-0">
                      <p class="text-sm font-medium text-gray-900" x-text="notification.title"></p>
                      <p class="text-sm text-gray-600 mt-0.5" x-text="notification.message"></p>
                      <p class="text-xs text-gray-400 mt-1" x-text="formatDate(notification.created_at)"></p>
                    </div>
                    
                    <!-- Unread indicator -->
                    <div x-show="!notification.is_read" class="flex-shrink-0">
                      <div class="w-2 h-2 bg-blue-600 rounded-full"></div>
                    </div>
                  </div>
                </div>
              </template>
            </div>
            
            <!-- Empty State -->
            <div x-show="!loading && notifications.length === 0" class="px-4 py-8 text-center">
              <svg class="w-12 h-12 mx-auto text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
              </svg>
              <p class="text-sm text-gray-500 mt-3">No notifications yet</p>
            </div>
          </div>
        </div>
        
        <a href="/forms.php" class="flex items-center gap-2 px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-100 hover:text-gray-900 transition-colors">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #AF831A;">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
          </svg>
          Forms
        </a>
        
        <a href="/reports.php" class="flex items-center gap-2 px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-100 hover:text-gray-900 transition-colors">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #AF831A;">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v1a1 1 0 001 1h4a1 1 0 001-1v-1m3-2V8a2 2 0 00-2-2H8a2 2 0 00-2 2v7m12 0a2 2 0 01-2 2H8a2 2 0 01-2-2m12 0l-4-4m0 0l-4 4m4-4V3"></path>
          </svg>
          Reports
        </a>
        
        <?php if (has_role('admin')): ?>
          <a href="/admin.php" class="flex items-center gap-2 px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-100 hover:text-gray-900 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #AF831A;">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
            </svg>
            Admin
          </a>
        <?php endif; ?>
        
        <!-- Separator -->
        <div class="w-px h-4 bg-gray-300 mx-2"></div>
        
        <!-- User profile and role display -->
        <div class="text-gray-700 px-3 py-2 rounded-lg bg-gray-100">
          <div class="flex items-center gap-2">
            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
            </svg>
            <span class="text-sm"><?= htmlspecialchars($_SESSION['user_email']) ?></span>
            <span class="text-xs font-medium px-2 py-0.5 bg-black text-white rounded">
              <?= get_role_display_name($_SESSION['user_role']) ?>
            </span>
          </div>
        </div>
        
        <a href="/logout.php" class="flex items-center gap-2 px-3 py-2 rounded-lg text-gray-700 hover:bg-red-50 hover:text-red-700 transition-colors">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
          </svg>
          Logout
        </a>
      <?php else: ?>
        <!-- Home first for logged out users -->
        <a href="/" class="flex items-center gap-2 px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-100 hover:text-gray-900 transition-colors">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
          </svg>
          Home
        </a>
        
        <a href="/announcements.php" class="flex items-center gap-2 px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-100 hover:text-gray-900 transition-colors">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"></path>
          </svg>
          Announcements
        </a>
        
        <!-- Separator -->
        <div class="w-px h-4 bg-gray-300 mx-2"></div>
        
        <!-- Distinguished Login Button -->
        <a href="/login.php" class="flex items-center gap-2 px-4 py-2 bg-black text-white rounded-lg hover:bg-gray-800 transition-colors font-medium">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
          </svg>
          Login
        </a>
      <?php endif; ?>
    </nav>

    <!-- Mobile Menu Button - visible only on mobile -->
    <button @click="mobileMenuOpen = !mobileMenuOpen" 
            class="md:hidden flex items-center justify-center w-10 h-10 rounded-lg hover:bg-gray-100 transition-colors">
      <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path x-show="!mobileMenuOpen" x-cloak stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
        <path x-show="mobileMenuOpen" x-cloak stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
      </svg>
    </button>
  </div>

  <!-- Mobile Menu - slides down when open -->
  <div x-show="mobileMenuOpen" x-cloak
       x-transition:enter="transition ease-out duration-200"
       x-transition:enter-start="opacity-0 transform -translate-y-2"
       x-transition:enter-end="opacity-100 transform translate-y-0"
       x-transition:leave="transition ease-in duration-150"
       x-transition:leave-start="opacity-100 transform translate-y-0"
       x-transition:leave-end="opacity-0 transform -translate-y-2"
       class="md:hidden bg-white border-t shadow-lg">
    <nav class="px-4 py-4 space-y-1">
      <?php if (!empty($_SESSION['user_id'])): ?>
        <!-- Home/Dashboard first for logged in users -->
        <a href="/dashboard.php" 
           @click="mobileMenuOpen = false"
           class="flex items-center gap-3 px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-100 hover:text-gray-900 transition-colors">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #AF831A;">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"></path>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5a2 2 0 012-2h4a2 2 0 012 2v6H8V5z"></path>
          </svg>
          Dashboard
        </a>
        
        <a href="/announcements.php" 
           @click="mobileMenuOpen = false"
           class="flex items-center gap-3 px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-100 hover:text-gray-900 transition-colors">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #AF831A;">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"></path>
          </svg>
          Announcements
        </a>
        
        <a href="/forms.php" 
           @click="mobileMenuOpen = false"
           class="flex items-center gap-3 px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-100 hover:text-gray-900 transition-colors">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #AF831A;">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
          </svg>
          Forms
        </a>
        
        <a href="/reports.php" 
           @click="mobileMenuOpen = false"
           class="flex items-center gap-3 px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-100 hover:text-gray-900 transition-colors">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #AF831A;">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v1a1 1 0 001 1h4a1 1 0 001-1v-1m3-2V8a2 2 0 00-2-2H8a2 2 0 00-2 2v7m12 0a2 2 0 01-2 2H8a2 2 0 01-2-2m12 0l-4-4m0 0l-4 4m4-4V3"></path>
          </svg>
          Reports
        </a>
        
        <?php if (has_role('admin')): ?>
          <a href="/admin.php" 
             @click="mobileMenuOpen = false"
             class="flex items-center gap-3 px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-100 hover:text-gray-900 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #AF831A;">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
            </svg>
            Admin
          </a>
        <?php endif; ?>
        
        <!-- Separator -->
        <hr class="border-gray-200 my-3">
        
        <!-- User profile and role display for mobile -->
        <div class="px-3 py-2 bg-gray-100 rounded-lg">
          <div class="flex items-center gap-2 mb-1">
            <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
            </svg>
            <span class="text-sm"><?= htmlspecialchars($_SESSION['user_email']) ?></span>
          </div>
          <span class="inline-block text-xs font-medium px-2 py-0.5 bg-black text-white rounded">
            <?= get_role_display_name($_SESSION['user_role']) ?>
          </span>
        </div>
        
        <a href="/logout.php" 
           @click="mobileMenuOpen = false"
           class="flex items-center gap-3 px-3 py-2 rounded-lg text-gray-700 hover:bg-red-50 hover:text-red-700 transition-colors">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
          </svg>
          Logout
        </a>
      <?php else: ?>
        <!-- Home first for logged out users -->
        <a href="/" 
           @click="mobileMenuOpen = false"
           class="flex items-center gap-3 px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-100 hover:text-gray-900 transition-colors">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
          </svg>
          Home
        </a>
        
        <a href="/announcements.php" 
           @click="mobileMenuOpen = false"
           class="flex items-center gap-3 px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-100 hover:text-gray-900 transition-colors">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 717 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"></path>
          </svg>
          Announcements
        </a>
        
        <!-- Separator -->
        <hr class="border-gray-200 my-3">
        
        <!-- Distinguished Login Button -->
        <a href="/login.php" 
           @click="mobileMenuOpen = false"
           class="flex items-center gap-3 px-4 py-3 bg-black text-white rounded-lg hover:bg-gray-800 transition-colors font-medium">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
          </svg>
          Login
        </a>
      <?php endif; ?>
    </nav>
  </div>
</header>