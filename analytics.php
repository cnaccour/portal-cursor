<?php
require __DIR__.'/includes/auth.php';
require __DIR__.'/includes/shift-report-manager.php';
require_login();

// Require manager level or higher for analytics
if ($_SESSION['role_level'] < 4) {
    header('Location: /dashboard.php');
    exit;
}

require __DIR__.'/includes/header.php';

$shiftManager = ShiftReportManager::getInstance();

// Get date range for analytics (default: last 30 days)
$endDate = $_GET['end_date'] ?? date('Y-m-d');
$startDate = $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days'));
$location = $_GET['location'] ?? '';

// Get analytics data
$analytics = $shiftManager->getAnalytics([
    'start_date' => $startDate,
    'end_date' => $endDate,
    'location' => $location
]);

// Get all locations for filter
$allReports = $shiftManager->getShiftReports();
$allLocations = array_unique(array_column($allReports, 'location'));
sort($allLocations);
?>

<div class="mb-6">
  <h1 class="text-2xl font-semibold">Analytics Dashboard</h1>
  <p class="text-gray-600">Performance insights and trends for shift reports</p>
</div>

<!-- Date Range & Location Filters -->
<div class="bg-white rounded-xl border p-4 mb-6">
  <form method="GET" class="flex flex-wrap gap-4 items-end">
    <div class="flex-1 min-w-40">
      <label class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
      <input type="date" name="start_date" value="<?= htmlspecialchars($startDate) ?>"
             class="w-full border rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2" 
             style="--tw-ring-color: #AF831A;">
    </div>
    <div class="flex-1 min-w-40">
      <label class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
      <input type="date" name="end_date" value="<?= htmlspecialchars($endDate) ?>"
             class="w-full border rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2" 
             style="--tw-ring-color: #AF831A;">
    </div>
    <div class="flex-1 min-w-48">
      <label class="block text-sm font-medium text-gray-700 mb-1">Location</label>
      <select name="location" class="w-full border rounded-md px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2" 
              style="--tw-ring-color: #AF831A;">
        <option value="">All Locations</option>
        <?php foreach ($allLocations as $loc): ?>
          <option value="<?= htmlspecialchars($loc) ?>" <?= $location === $loc ? 'selected' : '' ?>>
            <?= htmlspecialchars($loc) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <button type="submit" class="px-6 py-2 rounded-md text-white font-medium" 
            style="background-color: #AF831A;">
      Update Report
    </button>
  </form>
</div>

<!-- Summary Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
  <div class="bg-white rounded-xl border p-6">
    <div class="flex items-center justify-between">
      <div>
        <p class="text-sm font-medium text-gray-500">Total Reports</p>
        <p class="text-2xl font-semibold text-gray-900"><?= number_format($analytics['total_reports']) ?></p>
      </div>
      <div class="w-10 h-10 rounded-full flex items-center justify-center" style="background-color: #AF831A;">
        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
        </svg>
      </div>
    </div>
    <?php if ($analytics['reports_change'] !== null): ?>
      <p class="text-xs text-gray-500 mt-2">
        <span class="<?= $analytics['reports_change'] >= 0 ? 'text-green-600' : 'text-red-600' ?>">
          <?= $analytics['reports_change'] >= 0 ? '+' : '' ?><?= number_format($analytics['reports_change'], 1) ?>%
        </span> vs previous period
      </p>
    <?php endif; ?>
  </div>

  <div class="bg-white rounded-xl border p-6">
    <div class="flex items-center justify-between">
      <div>
        <p class="text-sm font-medium text-gray-500">Avg Daily Reports</p>
        <p class="text-2xl font-semibold text-gray-900"><?= number_format($analytics['avg_daily_reports'], 1) ?></p>
      </div>
      <div class="w-10 h-10 rounded-full bg-blue-500 flex items-center justify-center">
        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
        </svg>
      </div>
    </div>
  </div>

  <div class="bg-white rounded-xl border p-6">
    <div class="flex items-center justify-between">
      <div>
        <p class="text-sm font-medium text-gray-500">Total Reviews</p>
        <p class="text-2xl font-semibold text-gray-900"><?= number_format($analytics['total_reviews']) ?></p>
      </div>
      <div class="w-10 h-10 rounded-full bg-green-500 flex items-center justify-center">
        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
        </svg>
      </div>
    </div>
    <p class="text-xs text-gray-500 mt-2">
      Avg: <?= number_format($analytics['avg_reviews_per_report'], 1) ?> per report
    </p>
  </div>

  <div class="bg-white rounded-xl border p-6">
    <div class="flex items-center justify-between">
      <div>
        <p class="text-sm font-medium text-gray-500">Active Locations</p>
        <p class="text-2xl font-semibold text-gray-900"><?= number_format($analytics['unique_locations']) ?></p>
      </div>
      <div class="w-10 h-10 rounded-full bg-purple-500 flex items-center justify-center">
        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
        </svg>
      </div>
    </div>
  </div>
</div>

<!-- Charts Row -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
  <!-- Reports Timeline Chart -->
  <div class="bg-white rounded-xl border p-6">
    <h3 class="text-lg font-semibold mb-4">Reports Timeline</h3>
    <canvas id="reportsChart" width="400" height="200"></canvas>
  </div>

  <!-- Location Distribution Chart -->
  <div class="bg-white rounded-xl border p-6">
    <h3 class="text-lg font-semibold mb-4">Reports by Location</h3>
    <canvas id="locationChart" width="400" height="200"></canvas>
  </div>
</div>

<!-- Performance Tables -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
  <!-- Top Performers -->
  <div class="bg-white rounded-xl border p-6">
    <h3 class="text-lg font-semibold mb-4">Top Performers</h3>
    <div class="space-y-3">
      <?php foreach ($analytics['top_performers'] as $performer): ?>
        <div class="flex items-center justify-between py-2 border-b border-gray-100">
          <span class="font-medium"><?= htmlspecialchars($performer['user']) ?></span>
          <div class="text-right">
            <span class="text-sm text-gray-500"><?= $performer['report_count'] ?> reports</span>
            <br><span class="text-xs text-gray-400"><?= number_format($performer['avg_reviews'], 1) ?> avg reviews</span>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- Location Performance -->
  <div class="bg-white rounded-xl border p-6">
    <h3 class="text-lg font-semibold mb-4">Location Performance</h3>
    <div class="space-y-3">
      <?php foreach ($analytics['location_performance'] as $location): ?>
        <div class="flex items-center justify-between py-2 border-b border-gray-100">
          <span class="font-medium"><?= htmlspecialchars($location['location']) ?></span>
          <div class="text-right">
            <span class="text-sm text-gray-500"><?= $location['report_count'] ?> reports</span>
            <br><span class="text-xs text-gray-400"><?= number_format($location['avg_reviews'], 1) ?> avg reviews</span>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<!-- Weekly Trends -->
<div class="bg-white rounded-xl border p-6 mb-8">
  <h3 class="text-lg font-semibold mb-4">Weekly Trends</h3>
  <div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200">
      <thead class="bg-gray-50">
        <tr>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Week</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reports</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reviews</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Avg Reviews/Report</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Trend</th>
        </tr>
      </thead>
      <tbody class="bg-white divide-y divide-gray-200">
        <?php foreach ($analytics['weekly_trends'] as $index => $week): ?>
          <tr class="<?= $index % 2 === 0 ? 'bg-white' : 'bg-gray-50' ?>">
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
              <?= htmlspecialchars($week['week_start']) ?>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
              <?= number_format($week['report_count']) ?>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
              <?= number_format($week['total_reviews']) ?>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
              <?= number_format($week['avg_reviews'], 1) ?>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm">
              <?php if ($week['trend'] > 0): ?>
                <span class="text-green-600">↗ +<?= number_format($week['trend'], 1) ?>%</span>
              <?php elseif ($week['trend'] < 0): ?>
                <span class="text-red-600">↘ <?= number_format($week['trend'], 1) ?>%</span>
              <?php else: ?>
                <span class="text-gray-500">→ 0%</span>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Chart.js for Charts -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Reports Timeline Chart
const reportsCtx = document.getElementById('reportsChart').getContext('2d');
new Chart(reportsCtx, {
    type: 'line',
    data: {
        labels: <?= json_encode(array_column($analytics['daily_reports'], 'date')) ?>,
        datasets: [{
            label: 'Daily Reports',
            data: <?= json_encode(array_column($analytics['daily_reports'], 'count')) ?>,
            borderColor: '#AF831A',
            backgroundColor: 'rgba(175, 131, 26, 0.1)',
            tension: 0.4,
            fill: true
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    stepSize: 1
                }
            }
        },
        plugins: {
            legend: {
                display: false
            }
        }
    }
});

// Location Distribution Chart  
const locationCtx = document.getElementById('locationChart').getContext('2d');
new Chart(locationCtx, {
    type: 'doughnut',
    data: {
        labels: <?= json_encode(array_column($analytics['location_distribution'], 'location')) ?>,
        datasets: [{
            data: <?= json_encode(array_column($analytics['location_distribution'], 'count')) ?>,
            backgroundColor: [
                '#AF831A',
                '#3B82F6',
                '#10B981',
                '#8B5CF6',
                '#F59E0B',
                '#EF4444',
                '#6B7280'
            ]
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});
</script>

<?php require __DIR__.'/includes/footer.php'; ?>