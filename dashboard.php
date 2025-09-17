<?php
require __DIR__.'/includes/auth.php';
require_login();
require __DIR__.'/includes/header.php';
?>

<h1 class="text-2xl font-semibold mb-4">Dashboard</h1>
<p class="text-gray-600 mb-6">
  Hello, <?= htmlspecialchars($_SESSION['name'] ?? 'User') ?> — you are now logged in.
</p>

<!-- Actions -->
<div class="mb-8 space-x-4">
  <a href="/forms/example-form.php" class="px-4 py-2 rounded-lg bg-gray-900 text-white">
    Go to Example Form
  </a>
  <a href="/forms/morning-shift.php" class="px-4 py-2 rounded-lg bg-blue-600 text-white">
    Go to Morning Shift Report
  </a>
  <a href="/logout.php" class="px-4 py-2 rounded-lg bg-gray-200 text-gray-800">
    Logout
  </a>
</div>

<!-- Recent Example Form Submissions -->
<h2 class="text-lg font-semibold mb-3">Recent Example Form Submissions</h2>
<div class="bg-white rounded-xl border p-4 mb-8">
  <?php
  $file = __DIR__ . '/submissions.txt';
  if (!file_exists($file) || filesize($file) === 0) {
    echo "<p class='text-gray-500'>No submissions yet.</p>";
  } else {
    $lines = array_reverse(file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES));
    echo "<ul class='divide-y'>";
    foreach (array_slice($lines, 0, 5) as $line) {
      $row = json_decode($line, true);
      if (!$row) continue;
      echo "<li class='py-2 text-sm'>";
      echo "<span class='font-medium'>" . htmlspecialchars($row['user']) . "</span>";
      echo " @ <span class='text-gray-500'>" . htmlspecialchars($row['time']) . "</span><br>";
      echo "Location: " . htmlspecialchars($row['location']);
      echo " | Shipments: " . htmlspecialchars($row['shipments']);
      if (!empty($row['vendor'])) {
        echo " | Vendor: " . htmlspecialchars($row['vendor']);
      }
      if (!empty($row['notes'])) {
        echo " | Notes: " . htmlspecialchars($row['notes']);
      }
      echo "</li>";
    }
    echo "</ul>";
  }
  ?>
</div>

<!-- Recent Morning Shift Reports -->
<h2 class="text-lg font-semibold mb-3">Recent Morning Shift Reports</h2>
<div class="bg-white rounded-xl border p-4">
  <?php
  $file = __DIR__ . '/morning-shift.txt';
  if (!file_exists($file) || filesize($file) === 0) {
    echo "<p class='text-gray-500'>No morning shift reports yet.</p>";
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
      echo "Date: " . htmlspecialchars($row['shift_date']);
      echo " | Location: " . htmlspecialchars($row['location']);
      echo " | Reviews: " . htmlspecialchars($row['reviews']);
      echo " | Checklist Done: " . count($row['checklist']) . " items";
      if (!empty($row['refunds'])) {
        echo " | Refunds: " . count($row['refunds']);
      }
      echo " <a href='/reports/view.php?id={$id}' class='text-blue-600 underline ml-2'>View</a>";
      echo "</li>";
    }
    echo "</ul>";
  }
  ?>
</div>

<?php require __DIR__.'/includes/footer.php'; ?>