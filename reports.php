<?php
require __DIR__.'/includes/auth.php';
require_login();
require __DIR__.'/includes/header.php';

// Load all reports
$file = __DIR__ . '/morning-shift.txt';
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

// Filter reports
$filteredReports = $reports;
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

<!-- Filters & Sort -->
<div class="bg-white p-4 rounded-xl border mb-6" x-data="{ showFilters: false }">
  <div class="flex flex-wrap items-center gap-4">
    <!-- Sort dropdown -->
    <div>
      <label class="text-sm text-gray-600 mr-2">Sort by:</label>
      <select onchange="updateUrl('sort', this.value)" class="border rounded px-3 py-1 text-sm">
        <option value="date" <?= $sortBy === 'date' ? 'selected' : '' ?>>Date (Newest)</option>
        <option value="time" <?= $sortBy === 'time' ? 'selected' : '' ?>>Submitted (Newest)</option>
        <option value="user" <?= $sortBy === 'user' ? 'selected' : '' ?>>User (A-Z)</option>
        <option value="location" <?= $sortBy === 'location' ? 'selected' : '' ?>>Location (A-Z)</option>
      </select>
    </div>

    <!-- Filter toggles -->
    <button @click="showFilters = !showFilters" 
            class="px-3 py-1 text-sm border rounded hover:bg-gray-50"
            :class="showFilters ? 'bg-gray-100' : ''">
      Filters <?= ($filterLocation || $filterUser) ? '(' . (($filterLocation ? 1 : 0) + ($filterUser ? 1 : 0)) . ')' : '' ?>
    </button>

    <!-- Clear filters -->
    <?php if ($filterLocation || $filterUser): ?>
      <a href="?sort=<?= htmlspecialchars($sortBy) ?>" 
         class="text-sm text-red-600 hover:underline">Clear Filters</a>
    <?php endif; ?>
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
      <a href="?sort=<?= htmlspecialchars($sortBy) ?>" class="text-blue-600 hover:underline mt-2 inline-block">
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
              <span class="px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded">
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
          
          <a href="/reports/view.php?id=<?= $report['id'] ?>" 
             class="px-3 py-1 text-sm bg-gray-100 hover:bg-gray-200 rounded transition-colors">
            View Details
          </a>
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
</script>

<?php require __DIR__.'/includes/footer.php'; ?>