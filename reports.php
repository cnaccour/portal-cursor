<?php
require __DIR__.'/includes/auth.php';
require_login();
require __DIR__.'/includes/header.php';

// Load all reports using ShiftReportManager
require __DIR__.'/includes/shift-report-manager.php';
$shiftManager = ShiftReportManager::getInstance();
$reports = $shiftManager->getShiftReports();

// Get filter/sort parameters
$sortBy = $_GET['sort'] ?? 'date';
$filterLocation = $_GET['location'] ?? '';
$filterUser = $_GET['user'] ?? '';
$filterShiftType = $_GET['shift_type'] ?? '';
$dateFrom = $_GET['date_from'] ?? '';
$dateTo = $_GET['date_to'] ?? '';
$searchQuery = $_GET['search'] ?? '';

// Handle preset date ranges
$datePreset = $_GET['preset'] ?? '';
if ($datePreset) {
    switch ($datePreset) {
        case 'today':
            $dateFrom = $dateTo = date('Y-m-d');
            break;
        case 'week':
            $dateFrom = date('Y-m-d', strtotime('monday this week'));
            $dateTo = date('Y-m-d');
            break;
        case 'month':
            $dateFrom = date('Y-m-01');
            $dateTo = date('Y-m-d');
            break;
        case 'last30':
            $dateFrom = date('Y-m-d', strtotime('-30 days'));
            $dateTo = date('Y-m-d');
            break;
    }
}

// Build filters for efficient database query
$filters = [
    'sort' => $sortBy,
    'search' => $searchQuery
];

if ($filterLocation) {
    $filters['location'] = $filterLocation;
}

if ($filterShiftType) {
    $filters['shift_type'] = $filterShiftType;
}

if ($dateFrom) {
    $filters['date_from'] = $dateFrom;
}

if ($dateTo) {
    $filters['date_to'] = $dateTo;
}

// Get filtered reports from database
$filteredReports = $shiftManager->getShiftReports($filters);

// Apply user filter if specified (after getting other filter options)
if ($filterUser) {
    $filteredReports = array_filter($filteredReports, function($r) use ($filterUser) {
        return $r['user'] === $filterUser;
    });
}

// Get unique values for filters
$allLocations = array_unique(array_column($reports, 'location'));
$allUsers = array_unique(array_column($reports, 'user'));
$allShiftTypes = ['morning', 'evening']; // Standard shift types
sort($allLocations);
sort($allUsers);
?>

<div class="mb-6">
  <h1 class="text-2xl font-semibold">Shift Reports</h1>
</div>

<!-- Mobile-First Search & Controls -->
<div class="bg-white rounded-md border mb-6" x-data="{ showFilters: false }">
  <!-- Search Bar - Full Width on Mobile -->
  <div class="p-4 border-b">
    <form method="GET" class="relative">
      <div class="relative">
        <input type="search" 
               name="search" 
               value="<?= htmlspecialchars($searchQuery) ?>"
               placeholder="Search reports by user, location, notes..." 
               aria-label="Search reports"
               enterkeyhint="search"
               class="w-full border rounded-md px-4 py-3 pl-10 text-sm focus:outline-none focus:ring-2 focus:border-transparent" 
               style="--tw-ring-color: #AF831A;">
        <svg class="absolute left-3 top-3.5 h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21 21-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
        </svg>
      </div>
      
      <!-- Preserve other parameters -->
      <input type="hidden" name="sort" value="<?= htmlspecialchars($sortBy) ?>">
      <?php if ($filterLocation): ?><input type="hidden" name="location" value="<?= htmlspecialchars($filterLocation) ?>"><?php endif; ?>
      <?php if ($filterUser): ?><input type="hidden" name="user" value="<?= htmlspecialchars($filterUser) ?>"><?php endif; ?>
      <?php if ($filterShiftType): ?><input type="hidden" name="shift_type" value="<?= htmlspecialchars($filterShiftType) ?>"><?php endif; ?>
      <?php if ($dateFrom): ?><input type="hidden" name="date_from" value="<?= htmlspecialchars($dateFrom) ?>"><?php endif; ?>
      <?php if ($dateTo): ?><input type="hidden" name="date_to" value="<?= htmlspecialchars($dateTo) ?>"><?php endif; ?>
    </form>
  </div>

  <!-- Controls Row - Mobile Responsive -->
  <div class="p-4">
    <!-- Sort Control - Full width on mobile -->
    <div class="mb-4">
      <label class="block text-sm font-medium text-gray-700 mb-2">Sort by:</label>
      <select onchange="updateUrl('sort', this.value)" 
              class="w-full border rounded-md px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2" 
              style="--tw-ring-color: #AF831A;">
        <option value="date" <?= $sortBy === 'date' ? 'selected' : '' ?>>Latest Date</option>
        <option value="time" <?= $sortBy === 'time' ? 'selected' : '' ?>>Recently Submitted</option>
        <option value="user" <?= $sortBy === 'user' ? 'selected' : '' ?>>User Name</option>
        <option value="location" <?= $sortBy === 'location' ? 'selected' : '' ?>>Location</option>
        <option value="type" <?= $sortBy === 'type' ? 'selected' : '' ?>>Shift Type</option>
      </select>
    </div>

    <!-- Filter and Clear Controls -->
    <div class="flex items-center justify-between gap-3">
      <button @click="showFilters = !showFilters" 
              class="flex-1 flex items-center justify-center gap-2 px-4 py-2 border rounded-md hover:bg-gray-50 text-sm font-medium transition-colors"
              :class="showFilters ? 'bg-gray-100 border-gray-300' : 'bg-white'">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 2v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
        </svg>
        Filters
        <?php 
        $activeFilters = ($filterLocation ? 1 : 0) + ($filterUser ? 1 : 0) + ($filterShiftType ? 1 : 0) + (($dateFrom || $dateTo) ? 1 : 0);
        if ($activeFilters > 0): ?>
          <span class="px-1.5 py-0.5 text-xs rounded-full text-white" style="background-color: #AF831A;">
            <?= $activeFilters ?>
          </span>
        <?php endif; ?>
      </button>

      <?php if ($filterLocation || $filterUser || $filterShiftType || $dateFrom || $dateTo || $searchQuery): ?>
        <a href="?sort=<?= htmlspecialchars($sortBy) ?>" 
           class="px-4 py-2 text-sm text-red-600 hover:text-red-800 font-medium transition-colors border border-red-200 rounded-md hover:bg-red-50">
          Clear All
        </a>
      <?php endif; ?>
    </div>
  </div>

  <!-- Advanced Filters - Mobile Friendly -->
  <div x-show="showFilters" x-transition class="px-4 pb-4 border-t bg-gray-50">
    <div class="pt-4 space-y-4">
      <!-- Date Range & Presets -->
      <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700 mb-2">Date Range</label>
        <div class="flex flex-wrap gap-2 mb-3">
          <button onclick="setDatePreset('today')" 
                  class="px-3 py-1 text-xs rounded-full border hover:bg-gray-100 transition-colors <?= $datePreset === 'today' ? 'bg-gray-100 border-gray-400' : 'bg-white border-gray-300' ?>">
            Today
          </button>
          <button onclick="setDatePreset('week')" 
                  class="px-3 py-1 text-xs rounded-full border hover:bg-gray-100 transition-colors <?= $datePreset === 'week' ? 'bg-gray-100 border-gray-400' : 'bg-white border-gray-300' ?>">
            This Week
          </button>
          <button onclick="setDatePreset('month')" 
                  class="px-3 py-1 text-xs rounded-full border hover:bg-gray-100 transition-colors <?= $datePreset === 'month' ? 'bg-gray-100 border-gray-400' : 'bg-white border-gray-300' ?>">
            This Month
          </button>
          <button onclick="setDatePreset('last30')" 
                  class="px-3 py-1 text-xs rounded-full border hover:bg-gray-100 transition-colors <?= $datePreset === 'last30' ? 'bg-gray-100 border-gray-400' : 'bg-white border-gray-300' ?>">
            Last 30 Days
          </button>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
          <div>
            <label class="block text-xs text-gray-600 mb-1">From Date</label>
            <input type="date" 
                   value="<?= htmlspecialchars($dateFrom) ?>"
                   onchange="updateUrl('date_from', this.value)"
                   class="w-full border rounded-md px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2" 
                   style="--tw-ring-color: #AF831A;">
          </div>
          <div>
            <label class="block text-xs text-gray-600 mb-1">To Date</label>
            <input type="date" 
                   value="<?= htmlspecialchars($dateTo) ?>"
                   onchange="updateUrl('date_to', this.value)"
                   class="w-full border rounded-md px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2" 
                   style="--tw-ring-color: #AF831A;">
          </div>
        </div>
      </div>
      
      <!-- Other Filters -->
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <!-- Location Filter -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Location</label>
          <select onchange="updateUrl('location', this.value)" 
                  class="w-full border rounded-md px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2" 
                  style="--tw-ring-color: #AF831A;">
            <option value="">All Locations</option>
            <?php foreach ($allLocations as $loc): ?>
              <option value="<?= htmlspecialchars($loc) ?>" 
                      <?= $filterLocation === $loc ? 'selected' : '' ?>>
                <?= htmlspecialchars($loc) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- User Filter -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">User</label>
          <select onchange="updateUrl('user', this.value)" 
                  class="w-full border rounded-md px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2" 
                  style="--tw-ring-color: #AF831A;">
            <option value="">All Users</option>
            <?php foreach ($allUsers as $user): ?>
              <option value="<?= htmlspecialchars($user) ?>" 
                      <?= $filterUser === $user ? 'selected' : '' ?>>
                <?= htmlspecialchars($user) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        
        <!-- Shift Type Filter -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Shift Type</label>
          <select onchange="updateUrl('shift_type', this.value)" 
                  class="w-full border rounded-md px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2" 
                  style="--tw-ring-color: #AF831A;">
            <option value="">All Shifts</option>
            <option value="morning" <?= $filterShiftType === 'morning' ? 'selected' : '' ?>>Morning Shifts</option>
            <option value="evening" <?= $filterShiftType === 'evening' ? 'selected' : '' ?>>Evening Shifts</option>
          </select>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Modern Mobile-First Reports List -->
<?php if (empty($filteredReports)): ?>
  <div class="bg-white rounded-md border p-8 text-center">
    <div class="max-w-sm mx-auto">
      <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
      </svg>
      <p class="text-gray-500 text-lg font-medium mb-2">No reports found</p>
      <p class="text-sm text-gray-400 mb-4">No shift reports match your current search criteria.</p>
      <?php if ($filterLocation || $filterUser || $searchQuery): ?>
        <a href="?sort=<?= htmlspecialchars($sortBy) ?>" 
           class="inline-flex items-center px-4 py-2 rounded-md text-sm font-medium text-white transition-colors" 
           style="background-color: #AF831A;" 
           onmouseover="this.style.backgroundColor='#8B6914'" 
           onmouseout="this.style.backgroundColor='#AF831A'">
          Clear filters to see all reports
        </a>
      <?php endif; ?>
    </div>
  </div>
<?php else: ?>
  <div class="space-y-4" x-data="{ showAll: false, isMobile: window.innerWidth < 768 }" x-cloak>
    <?php foreach ($filteredReports as $index => $report): ?>
      <div class="bg-white rounded-md border hover:border-gray-300 transition-all duration-200 overflow-hidden report-card"
           x-show="!isMobile || showAll || <?= $index ?> < 3"
           x-transition
           data-index="<?= $index ?>"
           x-cloak>
        <!-- Header Section -->
        <div class="p-4 border-b bg-gray-50">
          <div class="flex items-center justify-between gap-3">
            <!-- Left: User info with shift indicator -->
            <div class="flex items-center gap-3 flex-1 min-w-0">
              <h3 class="text-lg font-semibold text-gray-900 truncate"><?= htmlspecialchars($report['user']) ?></h3>
              <span class="inline-flex items-center justify-center w-7 h-5 rounded text-xs font-bold shrink-0 px-1" 
                    style="<?= ($report['shift_type'] ?? 'morning') === 'morning' ? 'background-color: #8B6914; color: white;' : 'background-color: #374151; color: white;' ?>"
                    aria-label="<?= ucfirst(($report['shift_type'] ?? 'morning')) ?> Shift"
                    title="<?= ucfirst(($report['shift_type'] ?? 'morning')) ?> Shift">
                <?= ($report['shift_type'] ?? 'morning') === 'morning' ? 'AM' : 'PM' ?>
                <span class="sr-only"><?= ucfirst(($report['shift_type'] ?? 'morning')) ?> shift</span>
              </span>
            </div>
            
            <!-- Right: Location (compact) -->
            <div class="text-sm text-gray-500 font-medium text-right truncate max-w-[40%] ml-3">
              <?= htmlspecialchars($report['location']) ?>
            </div>
          </div>
        </div>
        
        <!-- Content Section -->
        <div class="p-4">
          <!-- Quick Stats Row -->
          <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-4">
            <div class="text-center p-3 bg-gray-50 rounded-md">
              <div class="text-lg font-semibold text-gray-900"><?= htmlspecialchars($report['reviews'] ?? '0') ?></div>
              <div class="text-xs text-gray-500 font-medium">Reviews</div>
            </div>
            
            <div class="text-center p-3 bg-gray-50 rounded-md">
              <div class="text-lg font-semibold text-gray-900"><?= count(array_filter($report['checklist'] ?? [])) ?></div>
              <div class="text-xs text-gray-500 font-medium">Checklist Items</div>
            </div>
            
            <?php if (!empty($report['refunds']) && is_array($report['refunds'])): ?>
              <div class="text-center p-3 bg-red-50 rounded-md">
                <div class="text-lg font-semibold text-red-600"><?= count($report['refunds']) ?></div>
                <div class="text-xs text-red-500 font-medium">Refunds</div>
              </div>
            <?php else: ?>
              <div class="text-center p-3 bg-green-50 rounded-md">
                <div class="text-lg font-semibold text-green-600">0</div>
                <div class="text-xs text-green-500 font-medium">Refunds</div>
              </div>
            <?php endif; ?>
            
            <div class="text-center p-3 rounded-md <?= (!empty($report['shipments']['status']) && $report['shipments']['status'] === 'yes') ? 'bg-green-50' : 'bg-gray-50' ?>">
              <div class="text-lg font-semibold <?= (!empty($report['shipments']['status']) && $report['shipments']['status'] === 'yes') ? 'text-green-600' : 'text-gray-400' ?>">
                <?= (!empty($report['shipments']['status']) && $report['shipments']['status'] === 'yes') ? '✓' : '—' ?>
              </div>
              <div class="text-xs font-medium <?= (!empty($report['shipments']['status']) && $report['shipments']['status'] === 'yes') ? 'text-green-500' : 'text-gray-500' ?>">Shipments</div>
            </div>
          </div>
          
          <!-- Action Buttons -->
          <div class="flex flex-col sm:flex-row gap-3">
            <a href="/reports/view.php?id=<?= $report['id'] ?>" 
               class="flex-1 inline-flex items-center justify-center px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition-colors">
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
              </svg>
              View Full Details
            </a>
            
            <button onclick="printReport(<?= $report['id'] ?>)" 
                    class="flex-1 inline-flex items-center justify-center px-4 py-2 border border-transparent rounded-md text-sm font-medium text-white bg-black hover:bg-gray-800 transition-colors">
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
              </svg>
              Print Report
            </button>
          </div>
          
          <!-- Metadata Footer -->
          <div class="mt-4 pt-3 border-t flex items-center text-xs text-gray-400">
            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            Submitted: <?= htmlspecialchars($report['time']) ?>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
    
    <!-- Load More Button - Only show on mobile when there are more than 3 reports -->
    <?php if (count($filteredReports) > 3): ?>
      <div class="text-center mt-6 block sm:hidden" x-show="!showAll">
        <button @click="showAll = true" 
                class="w-full px-6 py-3 bg-black text-white rounded-md hover:bg-gray-800 text-sm font-medium transition-colors">
          Load More Reports (<?= count($filteredReports) - 3 ?> more)
        </button>
      </div>
      
      <div class="text-center mt-6 block sm:hidden" x-show="showAll">
        <button @click="showAll = false; window.scrollTo({top: 0, behavior: 'smooth'})" 
                class="w-full px-6 py-3 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 text-sm font-medium transition-colors">
          Show Less Reports
        </button>
      </div>
    <?php endif; ?>
  </div>
<?php endif; ?>

<script>
function updateUrl(param, value) {
  const url = new URL(window.location);
  if (value) {
    url.searchParams.set(param, value);
  } else {
    url.searchParams.delete(param);
  }
  window.location = url.toString();
}

function printReport(reportId) {
  // Open the report view page in a new window
  const printWindow = window.open(`/reports/view.php?id=${reportId}`, '_blank');
  
  // Wait for the page to load, then trigger print
  printWindow.addEventListener('load', function() {
    // Small delay to ensure the page is fully rendered
    setTimeout(function() {
      printWindow.print();
      
      // Close the window after printing (optional)
      printWindow.addEventListener('afterprint', function() {
        printWindow.close();
      });
    }, 500);
  });
}
</script>

<?php require __DIR__.'/includes/footer.php'; ?>