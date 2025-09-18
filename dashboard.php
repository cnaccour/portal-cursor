<?php
require __DIR__.'/includes/auth.php';
require_login();
require __DIR__.'/includes/header.php';
?>

<div class="mb-4">
  <h1 class="text-2xl font-semibold">Dashboard</h1>
</div>
<p class="text-gray-600 mb-6">
  Hello, <?= htmlspecialchars($_SESSION['name'] ?? 'User') ?> — you are now logged in.
</p>

<!-- Actions -->
<div class="mb-8 space-x-4">
  <a href="/forms/shift-reports.php" class="px-4 py-2 rounded-md" style="background-color: #AF831A; color: white;">
    Create Shift Report
  </a>
  <a href="/reports.php" class="px-4 py-2 rounded-md bg-black text-white">
    View All Shift Reports
  </a>
</div>

<!-- Recent Shift Reports -->
<h2 class="text-lg font-semibold mb-3">Recent Shift Reports</h2>
<div class="bg-white rounded-xl border p-4">
  <?php
  $file = __DIR__ . '/shift-reports.txt';
  if (!file_exists($file) || filesize($file) === 0) {
    echo "<p class='text-gray-500'>No shift reports yet.</p>";
  } else {
    $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $total = count($lines);
    $lines = array_reverse($lines); // newest first
    echo "<ul class='divide-y'>";
    foreach (array_slice($lines, 0, 5, true) as $revIndex => $line) {
      $row = json_decode($line, true);
      if (!$row) continue;

      // Calculate original index for linking
      $id = $total - 1 - $revIndex;

      echo "<li class='py-3 text-sm'>";
      echo "<span class='font-medium'>" . htmlspecialchars($row['user']) . "</span>";
      echo " @ <span class='text-gray-500'>" . htmlspecialchars($row['time']) . "</span><br>";
      echo ucfirst(htmlspecialchars($row['shift_type'] ?? 'Morning')) . " Shift | Date: " . htmlspecialchars($row['shift_date']);
      echo " | Location: " . htmlspecialchars($row['location']);
      echo " | Reviews: " . htmlspecialchars($row['reviews']);
      echo " | Checklist Done: " . count(array_filter($row['checklist'], function($item) { return !empty($item); })) . " items";
      if (!empty($row['refunds'])) {
        echo " | Refunds: " . count($row['refunds']);
      }
      echo " <a href='/reports/view.php?id={$id}' class='underline ml-2' style='color: #AF831A;'>View</a>";
      echo "</li>";
    }
    echo "</ul>";
  }
  ?>
</div>

<?php require __DIR__.'/includes/footer.php'; ?>