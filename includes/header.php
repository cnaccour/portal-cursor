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
  <link href="assets/css/output.css" rel="stylesheet">
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
        
        <!-- Announcements -->
        <a href="/announcements.php" class="flex items-center gap-2 px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-100 hover:text-gray-900 transition-colors">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #AF831A;">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"></path>
          </svg>
          Announcements
        </a>
        
        <!-- Forms -->
        <a href="/forms.php" class="flex items-center gap-2 px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-100 hover:text-gray-900 transition-colors">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #AF831A;">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
          </svg>
          Forms
        </a>
        
        
        <!-- Separator -->
        <div class="w-px h-4 bg-gray-300 mx-2"></div>
        
        <!-- Notification Bell -->
        <div class="relative" x-data="{
          open: false,
          unreadCount: 0,
          notifications: [],
          loading: false,
          
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
              console.error('Failed to load notifications:', error);
            } finally {
              this.loading = false;
            }
          },
          
          formatDate(dateString) {
            const date = new Date(dateString);
            const now = new Date();
            const diffMs = now - date;
            const diffMins = Math.floor(diffMs / 60000);
            const diffHours = Math.floor(diffMs / 3600000);
            const diffDays = Math.floor(diffMs / 86400000);
            
            if (diffMins < 1) return 'Just now';
            if (diffMins < 60) return diffMins + 'm ago';
            if (diffHours < 24) return diffHours + 'h ago';
            if (diffDays < 7) return diffDays + 'd ago';
            
            return date.toLocaleDateString();
          },
          
          async handleNotificationClick(notification, evt) {
            try {
              if (!evt || !evt.isTrusted) {
                console.warn('Blocked untrusted notification click');
                return;
              }
              
              if (!notification.is_read) {
                const response = await fetch('/api/notifications/mark-read.php', {
                  method: 'POST',
                  headers: {
                    'Content-Type': 'application/json',
                  },
                  body: JSON.stringify({
                    notification_id: notification.id,
                    csrf_token: window.csrfToken
                  })
                });
                
                if (response.ok) {
                  const data = await response.json();
                  if (data.success) {
                    notification.is_read = true;
                    this.unreadCount = Math.max(0, this.unreadCount - 1);
                  }
                }
              }
              
              if (notification.link_url) {
                this.open = false;
                window.location.assign(notification.link_url);
              }
            } catch (error) {
              console.error('Error handling notification click:', error);
            }
          }
        }" x-init="loadNotifications()">
          <button @click="open = !open; if (open) loadNotifications()" 
                  class="relative flex items-center justify-center w-10 h-10 rounded-lg hover:bg-gray-100 transition-colors">
            <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
            </svg>
            <!-- Unread badge -->
            <span x-show="unreadCount > 0" x-cloak
                  class="absolute -top-1 -right-1 bg-red-600 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center font-medium"
                  x-text="unreadCount > 99 ? '99+' : unreadCount"></span>
          </button>
          
          <!-- Dropdown content -->
          <div x-show="open" x-cloak
               @click.outside="open = false"
               class="absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-lg border py-2 z-50 max-h-96 overflow-hidden flex flex-col">
            <!-- Header -->
            <div class="px-4 py-3 border-b">
              <div class="flex items-center justify-between">
                <div class="font-medium text-gray-900">Notifications</div>
                <span x-show="unreadCount > 0" class="text-sm text-gray-500" x-text="unreadCount + ' unread'"></span>
              </div>
            </div>
            
            <!-- Loading state -->
            <div x-show="loading" class="px-4 py-8 text-center text-gray-500 text-sm">
              Loading notifications...
            </div>
            
            <!-- Empty state -->
            <div x-show="!loading && notifications.length === 0" class="px-4 py-8 text-center text-gray-500 text-sm">
              <svg class="w-8 h-8 mx-auto mb-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
              </svg>
              No notifications yet
            </div>
            
            <!-- Notifications list -->
            <div x-show="!loading && notifications.length > 0" class="flex-1 overflow-y-auto">
              <template x-for="notification in notifications" :key="notification.id">
                <div class="px-4 py-3 hover:bg-gray-50 border-b border-gray-100 cursor-pointer"
                     :class="!notification.is_read ? 'bg-blue-50' : ''"
                     @click="handleNotificationClick(notification, $event)">
                  <div class="flex items-start gap-3">
                    <div class="flex-shrink-0 mt-1">
                      <svg class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10 2L3 7v11h14V7l-7-5z"/>
                      </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                      <div class="flex items-center justify-between">
                        <p class="text-sm font-medium text-gray-900" x-text="notification.title"></p>
                        <span class="text-xs text-gray-500" x-text="formatDate(notification.created_at)"></span>
                      </div>
                      <p class="text-sm text-gray-600 mt-1 line-clamp-2" x-text="notification.message"></p>
                      <div class="flex items-center mt-2" x-show="!notification.is_read">
                        <span class="inline-block w-2 h-2 bg-blue-600 rounded-full"></span>
                        <span class="ml-1 text-xs text-blue-600 font-medium">New</span>
                      </div>
                    </div>
                  </div>
                </div>
              </template>
            </div>
          </div>
        </div>
        
        <!-- User Profile -->
        <div class="relative" x-data="{ open: false, submenuOpen: false }">
          <button @click="open = !open" 
                  class="flex items-center justify-center w-8 h-8 bg-gray-800 text-white rounded-full text-sm font-medium hover:bg-gray-700 transition-colors">
            <?= strtoupper(substr($_SESSION['name'] ?? 'User', 0, 1)) ?>
          </button>
          
          <!-- User dropdown -->
          <div x-show="open" x-cloak x-transition 
               @click.outside="open = false; submenuOpen = false" 
               class="absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-lg border py-2 z-50">
            
            <!-- User info header -->
            <div class="px-4 py-3 border-b border-gray-100">
              <div class="font-medium text-gray-900"><?= htmlspecialchars($_SESSION['name'] ?? 'User') ?></div>
              <div class="text-sm text-gray-500"><?= htmlspecialchars(get_role_display_name($_SESSION['role'] ?? 'viewer')) ?></div>
            </div>
            
            <!-- Admin submenu trigger (if admin) -->
            <?php if (has_role('admin')): ?>
              <div @mouseenter="submenuOpen = true" @mouseleave="submenuOpen = false"
                   x-transition 
                   class="relative">
                <button class="w-full flex items-center gap-3 px-4 py-2 text-left text-gray-700 hover:bg-gray-100 transition-colors">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                  </svg>
                  Admin
                  <svg class="w-4 h-4 ml-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                  </svg>
                </button>
                
                <!-- Admin submenu -->
                <div x-show="submenuOpen" x-cloak
                     @mouseenter="submenuOpen = true" @mouseleave="submenuOpen = false"
                     class="absolute left-full top-0 ml-1 w-48 bg-white rounded-lg shadow-lg border py-2 z-50">
                  <a href="/admin.php" 
                     class="flex items-center gap-3 px-4 py-2 text-gray-700 hover:bg-gray-100 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                    </svg>
                    User Management
                  </a>
                  
                  <a href="/admin-announcements.php" 
                     class="flex items-center gap-3 px-4 py-2 text-gray-700 hover:bg-gray-100 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"></path>
                    </svg>
                    Manage Announcements
                  </a>
                  
                  <a href="/analytics.php" 
                     class="flex items-center gap-3 px-4 py-2 text-gray-700 hover:bg-gray-100 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                    Analytics
                  </a>
                </div>
              </div>
            <?php endif; ?>
            
            <!-- Logout -->
            <a href="/logout.php" 
               class="flex items-center gap-3 px-4 py-3 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors font-medium">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
              </svg>
              Logout
            </a>
          </div>
        </div>
      <?php else: ?>
        <!-- Unauthenticated navigation -->
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
        
        <a href="/login.php" class="flex items-center gap-2 px-4 py-2 bg-black text-white rounded-lg hover:bg-gray-800 transition-colors font-medium">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
          </svg>
          Login
        </a>
      <?php endif; ?>
    </nav>

    <!-- Mobile menu button -->
    <div class="md:hidden flex items-center">
      <button @click="mobileMenuOpen = !mobileMenuOpen" 
              class="md:hidden flex items-center justify-center w-10 h-10 rounded-lg hover:bg-gray-100 transition-colors">
        <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path x-show="!mobileMenuOpen" x-cloak stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
          <path x-show="mobileMenuOpen" x-cloak stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
        </svg>
      </button>
    </div>
  </div>

  <!-- Mobile menu -->
  <div x-show="mobileMenuOpen" x-cloak
       x-transition:enter="transition ease-out duration-200"
       x-transition:enter-start="opacity-0 transform -translate-y-2"
       x-transition:enter-end="opacity-100 transform translate-y-0"
       x-transition:leave="transition ease-in duration-150"
       x-transition:leave-start="opacity-100 transform translate-y-0"
       x-transition:leave-end="opacity-0 transform -translate-y-2"
       class="md:hidden bg-white border-t">
    <nav class="px-4 pt-2 pb-4 space-y-2">
      <?php if (!empty($_SESSION['user_id'])): ?>
        <!-- Authenticated mobile menu -->
        <a href="/dashboard.php" 
           class="flex items-center gap-3 px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-100 hover:text-gray-900 transition-colors">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"></path>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5a2 2 0 012-2h4a2 2 0 012 2v6H8V5z"></path>
          </svg>
          Dashboard
        </a>
        
        <a href="/announcements.php" 
           class="flex items-center gap-3 px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-100 hover:text-gray-900 transition-colors">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"></path>
          </svg>
          Announcements
        </a>
        
        <a href="/forms.php" 
           class="flex items-center gap-3 px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-100 hover:text-gray-900 transition-colors">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
          </svg>
          Forms
        </a>
        
        <!-- Admin menu items (if admin) -->
        <?php if (has_role('admin')): ?>
          <div class="border-t pt-2 mt-2">
            <div class="text-xs font-medium text-gray-500 uppercase tracking-wide px-3 py-2">Admin</div>
            
            <a href="/admin.php" 
               class="flex items-center gap-3 px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-100 hover:text-gray-900 transition-colors">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
              </svg>
              User Management
            </a>
            
            <a href="/admin-announcements.php" 
               class="flex items-center gap-3 px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-100 hover:text-gray-900 transition-colors">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"></path>
              </svg>
              Manage Announcements
            </a>
            
            <a href="/analytics.php" 
               class="flex items-center gap-3 px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-100 hover:text-gray-900 transition-colors">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
              </svg>
              Analytics
            </a>
          </div>
        <?php endif; ?>
        
        <!-- User profile & logout -->
        <div class="border-t pt-2 mt-2">
          <div class="px-3 py-2">
            <div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($_SESSION['name'] ?? 'User') ?></div>
            <div class="text-xs text-gray-500"><?= htmlspecialchars(get_role_display_name($_SESSION['role'] ?? 'viewer')) ?></div>
          </div>
          
          <a href="/logout.php" 
             class="flex items-center gap-3 px-4 py-3 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors font-medium">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
            </svg>
            Logout
          </a>
        </div>
      <?php else: ?>
        <!-- Unauthenticated mobile menu -->
        <a href="/" 
           class="flex items-center gap-3 px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-100 hover:text-gray-900 transition-colors">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
          </svg>
          Home
        </a>
        
        <a href="/announcements.php" 
           class="flex items-center gap-3 px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-100 hover:text-gray-900 transition-colors">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"></path>
          </svg>
          Announcements
        </a>
        
        <a href="/login.php" 
           class="flex items-center gap-3 px-4 py-3 bg-black text-white rounded-lg hover:bg-gray-800 transition-colors font-medium">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
          </svg>
          Login
        </a>
      <?php endif; ?>
    </nav>
  </div>
</header>

<main class="max-w-7xl mx-auto px-4 py-6">