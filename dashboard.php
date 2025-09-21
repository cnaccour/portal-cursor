<?php
require __DIR__.'/includes/auth.php';
require __DIR__.'/includes/header.php';

// Check if user is logged in, if not show login prompt
if (empty($_SESSION['user_id'])) {
?>

<div class="max-w-2xl mx-auto text-center py-16">
  <div class="bg-white rounded-xl border shadow-sm p-8">
    <svg class="w-16 h-16 mx-auto mb-4" style="color: #AF831A;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"></path>
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5a2 2 0 012-2h4a2 2 0 012 2v6H8V5z"></path>
    </svg>
    <h1 class="text-2xl font-semibold text-gray-900 mb-3">Welcome to JJS Team Portal</h1>
    <p class="text-gray-600 mb-6">Please log in to access your dashboard and team resources.</p>
    <a href="login.php" class="inline-flex items-center gap-2 px-6 py-3 bg-black text-white rounded-lg hover:bg-gray-800 transition-colors font-medium">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
      </svg>
      Login to Dashboard
    </a>
  </div>
</div>

<?php 
require __DIR__.'/includes/footer.php';
exit;
}
?>

<div class="mb-6">
  <h1 class="text-2xl font-semibold">Dashboard</h1>
  <p class="text-gray-600 mt-2">
    Hello, <?= htmlspecialchars($_SESSION['name'] ?? 'User') ?> — Welcome to your dashboard.
  </p>
</div>

<!-- Quick Actions -->
<div class="mb-8 flex gap-3 flex-wrap">
  <a href="forms/shift-reports.php" class="px-4 py-2 rounded-md bg-black text-white hover:bg-gray-800 transition-colors text-sm font-medium">
    Create Shift Report
  </a>
  <a href="reports.php" class="px-4 py-2 rounded-md bg-black text-white hover:bg-gray-800 transition-colors text-sm font-medium">
    View All Reports
  </a>
  <a href="announcements.php" class="px-4 py-2 rounded-md border border-gray-300 text-gray-700 hover:bg-gray-50 transition-colors text-sm font-medium">
    Announcements
  </a>
</div>

<!-- Dashboard Widgets - Role Based -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
  
  <?php 
  // Get user's specific role for exclusive widget display
  $userRole = $_SESSION['role'] ?? 'viewer';
  
  if ($userRole === 'admin'): ?>
    <!-- Admin: Recent Shift Reports Widget (Refunds ≥1 Only) -->
    <div class="bg-white rounded-xl border shadow-sm">
      <div class="p-4 border-b border-gray-100">
        <div class="flex items-center justify-between">
          <h3 class="font-semibold text-gray-900">Recent Shift Reports</h3>
          <a href="reports.php" class="text-sm text-gray-500 hover:text-gray-700 flex items-center gap-1">
            View All
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
            </svg>
          </a>
        </div>
      </div>
      <div class="p-4">
        <?php
        require_once __DIR__ . '/includes/shift-report-manager.php';
        $shiftManager = ShiftReportManager::getInstance();
        // Get the latest 5 reports
        $recentReports = $shiftManager->getShiftReports(['limit' => 5]);
        
        if (empty($recentReports)) {
          echo "<div class='text-center py-8 text-gray-500'>";
          echo "<svg class='w-12 h-12 mx-auto mb-3 text-gray-300' fill='none' stroke='currentColor' viewBox='0 0 24 24'>";
          echo "<path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z'></path>";
          echo "</svg>";
          echo "<p>No shift reports yet</p>";
          echo "</div>";
        } else {
          echo "<div class='space-y-3'>";
          foreach ($recentReports as $row) {
            $refundCount = !empty($row['refunds']) && is_array($row['refunds']) ? count($row['refunds']) : 0;
            echo "<div class='flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors'>";
            echo "<div class='flex-1 min-w-0'>";
            echo "<div class='flex items-center gap-2'>";
            echo "<span class='font-medium text-sm text-gray-900'>" . htmlspecialchars($row['user']) . "</span>";
            echo "<span class='text-xs text-gray-500'>•</span>";
            echo "<span class='text-xs text-gray-500'>" . htmlspecialchars($row['shift_date']) . "</span>";
            echo "</div>";
            echo "<div class='flex items-center gap-3 text-xs text-gray-600 mt-1'>";
            echo "<span class='flex items-center gap-1'>";
            echo "<svg class='w-3 h-3' fill='none' stroke='currentColor' viewBox='0 0 24 24'>";
            echo "<path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z'></path>";
            echo "<path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M15 11a3 3 0 11-6 0 3 3 0 016 0z'></path>";
            echo "</svg>";
            echo htmlspecialchars($row['location']);
            echo "</span>";
            if ($refundCount > 0) {
              echo "<span class='px-2 py-1 bg-orange-100 text-orange-800 rounded-full font-medium flex items-center gap-1'>";
              echo "<svg class='w-3 h-3' fill='none' stroke='currentColor' viewBox='0 0 24 24'>";
              echo "<path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z'></path>";
              echo "</svg>";
              echo "{$refundCount} refund" . ($refundCount > 1 ? 's' : '') . "";
              echo "</span>";
            }
            echo "</div>";
            echo "</div>";
            echo "<a href='reports/view.php?id=" . htmlspecialchars($row['id'], ENT_QUOTES) . "' class='ml-3 px-3 py-1 text-xs font-medium rounded-md border border-gray-300 hover:bg-gray-50 transition-colors'>";
            echo "View";
            echo "</a>";
            echo "</div>";
          }
          echo "</div>";
        }
        ?>
      </div>
    </div>
  
  <?php elseif ($userRole === 'manager'): ?>
    <!-- Manager Widget -->
    <div class="bg-white rounded-xl border shadow-sm">
      <div class="p-4 border-b border-gray-100">
        <h3 class="font-semibold text-gray-900">Team Overview</h3>
      </div>
      <div class="p-4">
        <div class="text-center py-8 text-gray-500">
          <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
          </svg>
          <p>Team analytics coming soon</p>
        </div>
      </div>
    </div>
  
  
  <?php elseif ($userRole === 'support'): ?>
    <!-- Support Widget -->
    <div class="bg-white rounded-xl border shadow-sm">
      <div class="p-4 border-b border-gray-100">
        <h3 class="font-semibold text-gray-900">Support Tickets</h3>
      </div>
      <div class="p-4">
        <div class="text-center py-8 text-gray-500">
          <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192L5.636 18.364M12 12l8.485 8.485M12 12L3.515 3.515M12 12l8.485-8.485M12 12L3.515 20.485"></path>
          </svg>
          <p>Support dashboard coming soon</p>
        </div>
      </div>
    </div>
  
  <?php elseif ($userRole === 'staff'): ?>
    <!-- Staff Widget -->
    <div class="bg-white rounded-xl border shadow-sm">
      <div class="p-4 border-b border-gray-100">
        <h3 class="font-semibold text-gray-900">My Shifts</h3>
      </div>
      <div class="p-4">
        <div class="text-center py-8 text-gray-500">
          <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
          <p>Your shift history will appear here</p>
        </div>
      </div>
    </div>
  
  <?php elseif ($userRole === 'viewer'): ?>
    <!-- Viewer Widget -->
    <div class="bg-white rounded-xl border shadow-sm">
      <div class="p-4 border-b border-gray-100">
        <h3 class="font-semibold text-gray-900">Quick Info</h3>
      </div>
      <div class="p-4">
        <div class="text-center py-8 text-gray-500">
          <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
          <p>Information dashboard coming soon</p>
        </div>
      </div>
    </div>
  <?php endif; ?>
  
  <!-- Universal: Recent Announcements Widget -->
  <div class="bg-white rounded-xl border shadow-sm">
    <div class="p-4 border-b border-gray-100">
      <div class="flex items-center justify-between">
        <h3 class="font-semibold text-gray-900">Recent Announcements</h3>
        <a href="announcements.php" class="text-sm text-gray-500 hover:text-gray-700 flex items-center gap-1">
          View All
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
          </svg>
        </a>
      </div>
    </div>
    <div class="p-4">
      <?php
      // Get recent announcements (last 3) - include both static and dynamic
      require_once __DIR__ . '/includes/announcement-helpers.php';
      $allAnnouncements = loadAllAnnouncements();
      
      // Filter out expired announcements
      $activeAnnouncements = array_filter($allAnnouncements, function($announcement) {
        return empty($announcement['expiration_date']) || strtotime($announcement['expiration_date']) > time();
      });
      
      // Sort by date_modified or date_created (most recent first)
      usort($activeAnnouncements, function($a, $b) {
        $dateA = $a['date_modified'] ?? $a['date_created'];
        $dateB = $b['date_modified'] ?? $b['date_created'];
        return strtotime($dateB) - strtotime($dateA);
      });
      
      $announcements = array_slice($activeAnnouncements, 0, 3);
      
      if (empty($announcements)) {
        echo "<div class='text-center py-8 text-gray-500'>";
        echo "<svg class='w-12 h-12 mx-auto mb-3 text-gray-300' fill='none' stroke='currentColor' viewBox='0 0 24 24'>";
        echo "<path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z'></path>";
        echo "</svg>";
        echo "<p>No announcements yet</p>";
        echo "</div>";
      } else {
        echo "<div class='space-y-3'>";
        foreach ($announcements as $announcement) {
          echo "<div class='p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors'>";
          echo "<div class='font-medium text-sm text-gray-900 mb-1'>" . htmlspecialchars($announcement['title']) . "</div>";
          $preview = strlen($announcement['content']) > 100 ? substr(strip_tags($announcement['content']), 0, 100) . '...' : strip_tags($announcement['content']);
          echo "<div class='text-xs text-gray-600 mb-2'>" . htmlspecialchars($preview) . "</div>";
          $displayDate = $announcement['date_modified'] ?? $announcement['date_created'];
          echo "<div class='text-xs text-gray-500'>" . htmlspecialchars(date('M j, Y', strtotime($displayDate))) . "</div>";
          echo "</div>";
        }
        echo "</div>";
      }
      ?>
    </div>
  </div>
  
</div>

<?php require __DIR__.'/includes/footer.php'; ?>