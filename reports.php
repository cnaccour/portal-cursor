<?php
require __DIR__.'/includes/auth.php';
require_login();
require __DIR__.'/includes/header.php';

// Load all reports
$file = __DIR__ . '/shift-reports.txt';
$reports = [];
if (file_exists($file)) {
  $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
  foreach ($lines as $index => $line) {
    $row = json_decode($line, true);
    if ($row) {
      $row['id'] = $index;  // Add ID for linking
      $reports[] = $row;
    }
  }
}

// Get filter/sort parameters
$sortBy = $_GET['sort'] ?? 'date';
$filterLocation = $_GET['location'] ?? '';
$filterUser = $_GET['user'] ?? '';
$searchQuery = $_GET['search'] ?? '';

// Filter reports
$filteredReports = $reports;

// Search filter
if ($searchQuery) {
  $searchQuery = trim($searchQuery);
  $filteredReports = array_filter($filteredReports, function($r) use ($searchQuery) {
    $searchText = strtolower($searchQuery);
    return (
      stripos(($r['user'] ?? ''), $searchText) !== false ||
      stripos(($r['location'] ?? ''), $searchText) !== false ||
      stripos(($r['notes'] ?? ''), $searchText) !== false ||
      stripos(implode(' ', (array)($r['checklist'] ?? [])), $searchText) !== false
    );
  });
}

if ($filterLocation) {
  $filteredReports = array_filter($filteredReports, function($r) use ($filterLocation) {
    return $r['location'] === $filterLocation;
  });
}
if ($filterUser) {
  $filteredReports = array_filter($filteredReports, function($r) use ($filterUser) {
    return $r['user'] === $filterUser;
  });
}

// Sort reports
usort($filteredReports, function($a, $b) use ($sortBy) {
  switch ($sortBy) {
    case 'date':
      return strcmp($b['shift_date'], $a['shift_date']); // newest first
    case 'user':
      return strcmp($a['user'], $b['user']);
    case 'location':
      return strcmp($a['location'], $b['location']);
    case 'time':
      return strcmp($b['time'], $a['time']); // newest first
    default:
      return 0;
  }
});

// Get unique values for filters
$allLocations = array_unique(array_column($reports, 'location'));
$allUsers = array_unique(array_column($reports, 'user'));
sort($allLocations);
sort($allUsers);
?>

<div class="flex justify-between items-center mb-6">
  <h1 class="text-2xl font-semibold">Shift Reports</h1>
  <div class="text-sm text-gray-600">
    <?= count($filteredReports) ?> of <?= count($reports) ?> reports
  </div>
</div>

<!-- Mobile-First Search & Controls -->
<div class="bg-white rounded-md border mb-6" x-data="{ showFilters: false }">
  <!-- Search Bar - Full Width on Mobile -->
  <div class="p-4 border-b">
    <form method="GET" class="relative">
      <div class="relative">
        <input type="text" 
               name="search" 
               value="<?= htmlspecialchars($searchQuery) ?>"
               placeholder="Search reports by user, location, notes..." 
               class="w-full border rounded-md px-4 py-3 pl-10 text-sm focus:outline-none focus:ring-2 focus:border-transparent" 
               style="--tw-ring-color: #AF831A;">
        <svg class="absolute left-3 top-3.5 h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21 21-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
        </svg>
      </div>
      
      <!-- Search Button -->
      <div class="mt-3 flex justify-end">
        <button type="submit" 
                class="px-4 py-2 bg-black text-white rounded-md hover:bg-gray-800 text-sm font-medium transition-colors">
          Search Reports
        </button>
      </div>
      
      <!-- Preserve other parameters -->
      <input type="hidden" name="sort" value="<?= htmlspecialchars($sortBy) ?>">
      <?php if ($filterLocation): ?><input type="hidden" name="location" value="<?= htmlspecialchars($filterLocation) ?>"><?php endif; ?>
      <?php if ($filterUser): ?><input type="hidden" name="user" value="<?= htmlspecialchars($filterUser) ?>"><?php endif; ?>
    </form>
  </div>

  <!-- Controls Row - Mobile Responsive -->
  <div class="p-4 space-y-4 sm:space-y-0 sm:flex sm:items-center sm:justify-between">
    <!-- Sort Control -->
    <div class="flex items-center gap-3">
      <label class="text-sm font-medium text-gray-700">Sort by:</label>
      <select onchange="updateUrl('sort', this.value)" 
              class="border rounded-md px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2" 
              style="--tw-ring-color: #AF831A;">
        <option value="date" <?= $sortBy === 'date' ? 'selected' : '' ?>>Latest Date</option>
        <option value="time" <?= $sortBy === 'time' ? 'selected' : '' ?>>Recently Submitted</option>
        <option value="user" <?= $sortBy === 'user' ? 'selected' : '' ?>>User Name</option>
        <option value="location" <?= $sortBy === 'location' ? 'selected' : '' ?>>Location</option>
      </select>
    </div>

    <!-- Filter and Clear Controls -->
    <div class="flex items-center gap-3">
      <button @click="showFilters = !showFilters" 
              class="flex items-center gap-2 px-4 py-2 border rounded-md hover:bg-gray-50 text-sm font-medium transition-colors"
              :class="showFilters ? 'bg-gray-100 border-gray-300' : 'bg-white'">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 2v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
        </svg>
        Filters
        <?php if ($filterLocation || $filterUser): ?>
          <span class="px-1.5 py-0.5 text-xs rounded-full text-white" style="background-color: #AF831A;">
            <?= (($filterLocation ? 1 : 0) + ($filterUser ? 1 : 0)) ?>
          </span>
        <?php endif; ?>
      </button>

      <?php if ($filterLocation || $filterUser || $searchQuery): ?>
        <a href="?sort=<?= htmlspecialchars($sortBy) ?>" 
           class="px-3 py-2 text-sm text-red-600 hover:text-red-800 font-medium transition-colors">
          Clear All
        </a>
      <?php endif; ?>
    </div>
  </div>

  <!-- Advanced Filters - Mobile Friendly -->
  <div x-show="showFilters" x-transition class="px-4 pb-4 border-t bg-gray-50">
    <div class="pt-4 space-y-4">
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <!-- Location Filter -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Filter by Location</label>
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
          <label class="block text-sm font-medium text-gray-700 mb-2">Filter by User</label>
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
      </div>
    </div>
  </div>
</div>

<!-- Reports List -->
<?php if (empty($filteredReports)): ?>
  <div class="bg-white rounded-xl border p-8 text-center">
    <p class="text-gray-500">No reports found matching your criteria.</p>
    <?php if ($filterLocation || $filterUser): ?>
      <a href="?sort=<?= htmlspecialchars($sortBy) ?>" class="mt-2 inline-block hover:underline" style="color: #AF831A;">
        Clear filters to see all reports
      </a>
    <?php endif; ?>
  </div>
<?php else: ?>
  <div class="space-y-3">
    <?php foreach ($filteredReports as $report): ?>
      <div class="bg-white rounded-xl border p-4 hover:border-gray-300 transition-colors">
        <div class="flex justify-between items-start">
          <div class="flex-grow">
            <div class="flex items-center gap-4 mb-2">
              <h3 class="font-semibold"><?= htmlspecialchars($report['user']) ?></h3>
              <span class="px-2 py-1 text-xs rounded text-white" style="background-color: #AF831A;">
                <?= ucfirst(htmlspecialchars($report['shift_type'] ?? 'Morning')) ?> Shift
              </span>
              <span class="text-sm text-gray-600"><?= htmlspecialchars($report['shift_date']) ?></span>
              <span class="text-sm text-gray-500"><?= htmlspecialchars($report['location']) ?></span>
            </div>
            
            <div class="text-sm text-gray-600 space-x-4">
              <span>Reviews: <?= htmlspecialchars($report['reviews']) ?></span>
              <span>Checklist: <?= count(array_filter($report['checklist'] ?? [])) ?> items</span>
              <?php if (!empty($report['refunds']) && is_array($report['refunds'])): ?>
                <span>Refunds: <?= count($report['refunds']) ?></span>
              <?php endif; ?>
              <?php if (!empty($report['shipments']['status']) && $report['shipments']['status'] === 'yes'): ?>
                <span class="text-black">✓ Shipments</span>
              <?php endif; ?>
            </div>
            
            <div class="text-xs text-gray-400 mt-1">
              Submitted: <?= htmlspecialchars($report['time']) ?>
            </div>
          </div>
          
          <div class="flex flex-col gap-2">
            <a href="/reports/view.php?id=<?= $report['id'] ?>" 
               class="px-3 py-1 text-sm bg-gray-100 hover:bg-gray-200 rounded transition-colors text-center">
              View Details
            </a>
            <button onclick="printReport(<?= $report['id'] ?>)" 
                    class="px-3 py-1 text-sm bg-black text-white hover:bg-gray-800 rounded-r-md transition-colors font-medium">
              Print
            </button>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
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