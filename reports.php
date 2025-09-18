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

<!-- Search & Filters -->
<div class="bg-white p-4 rounded-xl border mb-6" x-data="{ showFilters: false }">
  <div class="flex justify-between items-center gap-4">
    <!-- Left side: Search bar -->
    <div class="flex-grow max-w-md">
      <form method="GET" class="relative flex">
        <div class="relative flex-grow">
          <input type="text" 
                 name="search" 
                 value="<?= htmlspecialchars($searchQuery) ?>"
                 placeholder="Search reports..." 
                 class="w-full border rounded-l-lg px-4 py-2 pl-10 text-sm focus:outline-none focus:ring-2" style="--tw-ring-color: #AF831A;"
          <svg class="absolute left-3 top-2.5 h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21 21-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
          </svg>
        </div>
        <button type="submit" class="px-3 py-2 bg-black text-white rounded-r-lg hover:bg-gray-800 text-sm font-medium border border-l-0 border-black">Search</button>
        <!-- Preserve other parameters -->
        <input type="hidden" name="sort" value="<?= htmlspecialchars($sortBy) ?>">
        <?php if ($filterLocation): ?><input type="hidden" name="location" value="<?= htmlspecialchars($filterLocation) ?>"><?php endif; ?>
        <?php if ($filterUser): ?><input type="hidden" name="user" value="<?= htmlspecialchars($filterUser) ?>"><?php endif; ?>
      </form>
    </div>

    <!-- Right side: Sort & Filters -->
    <div class="flex items-center gap-4">
      <!-- Sort dropdown -->
      <div class="flex items-center">
        <label class="text-sm text-gray-600 mr-2">Sort:</label>
        <select onchange="updateUrl('sort', this.value)" class="border rounded px-3 py-1 text-sm">
          <option value="date" <?= $sortBy === 'date' ? 'selected' : '' ?>>Date</option>
          <option value="time" <?= $sortBy === 'time' ? 'selected' : '' ?>>Submitted</option>
          <option value="user" <?= $sortBy === 'user' ? 'selected' : '' ?>>User</option>
          <option value="location" <?= $sortBy === 'location' ? 'selected' : '' ?>>Location</option>
        </select>
      </div>

      <!-- Filter toggles -->
      <button @click="showFilters = !showFilters" 
              class="px-3 py-1 text-sm border rounded hover:bg-gray-50"
              :class="showFilters ? 'bg-gray-100' : ''">
        Filters <?= ($filterLocation || $filterUser) ? '(' . (($filterLocation ? 1 : 0) + ($filterUser ? 1 : 0)) . ')' : '' ?>
      </button>

      <!-- Clear filters -->
      <?php if ($filterLocation || $filterUser || $searchQuery): ?>
        <a href="?sort=<?= htmlspecialchars($sortBy) ?>" 
           class="text-sm text-red-600 hover:underline">Clear All</a>
      <?php endif; ?>
    </div>
  </div>

  <!-- Filter dropdowns -->
  <div x-show="showFilters" x-transition class="mt-4 flex flex-wrap gap-4 pt-3 border-t">
    <div>
      <label class="text-sm text-gray-600 mr-2">Location:</label>
      <select onchange="updateUrl('location', this.value)" class="border rounded px-3 py-1 text-sm">
        <option value="">All Locations</option>
        <?php foreach ($allLocations as $loc): ?>
          <option value="<?= htmlspecialchars($loc) ?>" 
                  <?= $filterLocation === $loc ? 'selected' : '' ?>>
            <?= htmlspecialchars($loc) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <div>
      <label class="text-sm text-gray-600 mr-2">User:</label>
      <select onchange="updateUrl('user', this.value)" class="border rounded px-3 py-1 text-sm">
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

<!-- Reports List -->
<?php if (empty($filteredReports)): ?>
  <div class="bg-white rounded-xl border p-8 text-center">
    <p class="text-gray-500">No reports found matching your criteria.</p>
    <?php if ($filterLocation || $filterUser): ?>
      <a href="?sort=<?= htmlspecialchars($sortBy) ?>" class="hover:underline mt-2 inline-block hover:opacity-75" style="color: #AF831A;"
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
              <span class="px-2 py-1 text-xs rounded" style="background-color: #FEF3E2; color: #8B5A00;"
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
                <span class="text-green-600">✓ Shipments</span>
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
                    class="px-3 py-1 text-sm bg-black text-white hover:bg-gray-800 rounded-lg transition-colors font-medium">
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