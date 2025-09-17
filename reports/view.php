<?php
require __DIR__.'/../includes/auth.php';
require_login();
require __DIR__.'/../includes/header.php';

// Load all reports
$file = __DIR__ . '/../morning-shift.txt';
$lines = file_exists($file) ? file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) : [];

// Get report by index (from query string)
$id = isset($_GET['id']) ? (int) $_GET['id'] : -1;
if ($id < 0 || $id >= count($lines)) {
  echo "<p class='text-red-600'>Invalid report ID.</p>";
  require __DIR__.'/../includes/footer.php';
  exit;
}

$row = json_decode($lines[$id], true);
if (!$row) {
  echo "<p class='text-red-600'>Report not found.</p>";
  require __DIR__.'/../includes/footer.php';
  exit;
}
?>

<h1 class="text-2xl font-semibold mb-6"><?= ucfirst($row['shift_type'] ?? 'Morning') ?> Shift Report</h1>

<div class="bg-white rounded-xl border p-6 space-y-6">

  <div>
    <h2 class="text-lg font-semibold mb-2">Submitted By</h2>
    <p><?= htmlspecialchars($row['user']) ?> @ <?= htmlspecialchars($row['time']) ?></p>
  </div>

  <div>
    <h2 class="text-lg font-semibold mb-2">Shift Info</h2>
    <p>Type: <?= ucfirst(htmlspecialchars($row['shift_type'] ?? 'Morning')) ?> Shift</p>
    <p>Date: <?= htmlspecialchars($row['shift_date']) ?></p>
    <p>Location: <?= htmlspecialchars($row['location']) ?></p>
  </div>

  <div>
    <h2 class="text-lg font-semibold mb-2">Checklist</h2>
    <?php if (!empty($row['checklist'])): ?>
      <ul class="list-disc list-inside text-sm">
        <?php foreach ($row['checklist'] as $item): ?>
          <li><?= htmlspecialchars($item) ?></li>
        <?php endforeach; ?>
      </ul>
    <?php else: ?>
      <p class="text-gray-500">No checklist items marked.</p>
    <?php endif; ?>
  </div>

  <div>
    <h2 class="text-lg font-semibold mb-2">Customer Reviews</h2>
    <p><?= htmlspecialchars($row['reviews']) ?> reviews</p>
  </div>

  <div>
    <h2 class="text-lg font-semibold mb-2">Shipments & Deliveries</h2>
    <?php if (!empty($row['shipments']['status']) && $row['shipments']['status'] === 'yes'): ?>
      <p><strong>Vendor:</strong> <?= htmlspecialchars($row['shipments']['vendor']) ?></p>
      <p><strong>Notes:</strong> <?= htmlspecialchars($row['shipments']['notes']) ?></p>
    <?php else: ?>
      <p class="text-gray-500">No shipments reported.</p>
    <?php endif; ?>
  </div>

  <div>
    <h2 class="text-lg font-semibold mb-2">Refunds & Returns</h2>
    <?php if (!empty($row['refunds']) && is_array($row['refunds'])): ?>
      <ul class="divide-y">
        <?php foreach ($row['refunds'] as $r): ?>
          <li class="py-3">
            <p><strong>Amount:</strong> $<?= htmlspecialchars($r['amount'] ?? '0.00') ?></p>
            <p><strong>Reason:</strong> <?= htmlspecialchars($r['reason'] ?? '') ?></p>
            <p><strong>Customer:</strong> <?= htmlspecialchars($r['customer'] ?? '') ?></p>
            <p><strong>Service/Product:</strong> <?= htmlspecialchars($r['service'] ?? '') ?></p>
            <p><strong>Notes:</strong> <?= htmlspecialchars($r['notes'] ?? '') ?></p>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php else: ?>
      <p class="text-gray-500">No refunds reported.</p>
    <?php endif; ?>
  </div>

  <div>
    <h2 class="text-lg font-semibold mb-2">Shift Notes</h2>
    <p><?= nl2br(htmlspecialchars($row['notes'] ?? '')) ?></p>
  </div>

</div>

<div class="mt-6">
  <a href="/dashboard.php" class="px-4 py-2 rounded-lg bg-gray-200 text-gray-800">← Back to Dashboard</a>
</div>

<?php require __DIR__.'/../includes/footer.php'; ?>