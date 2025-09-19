<?php
require __DIR__.'/../includes/auth.php';
require_login();
require __DIR__.'/../includes/header.php';

// Get shift report by ID using ShiftReportManager
require __DIR__ . '/../includes/shift-report-manager.php';
$id = intval($_GET['id'] ?? 0);
$shiftManager = ShiftReportManager::getInstance();
$row = $shiftManager->getShiftReport($id);

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

<div class="mt-6 flex gap-3 print-hide">
  <a href="/reports.php" class="px-4 py-2 rounded-lg bg-gray-200 text-gray-800 hover:bg-gray-300 transition-colors">← Back to Reports</a>
  <button onclick="printReport()" class="px-4 py-2 rounded-lg bg-black text-white hover:bg-gray-800 transition-colors font-medium">Print Report</button>
</div>

<style>
@media print {
  /* Hide non-essential elements when printing */
  header, nav, .print-hide {
    display: none !important;
  }
  
  /* Optimize page layout for printing */
  body {
    background: white !important;
    color: black !important;
    font-size: 12pt;
    line-height: 1.4;
  }
  
  .bg-white {
    background: white !important;
    box-shadow: none !important;
    border: 1px solid #ddd !important;
  }
  
  /* Improve typography for print */
  h1 {
    font-size: 18pt !important;
    margin-bottom: 12pt !important;
    color: black !important;
  }
  
  h2 {
    font-size: 14pt !important;
    margin: 8pt 0 4pt 0 !important;
    color: black !important;
  }
  
  p, li {
    font-size: 11pt !important;
    margin: 2pt 0 !important;
    color: black !important;
  }
  
  /* Ensure proper spacing */
  .space-y-6 > * + * {
    margin-top: 16pt !important;
  }
  
  /* Add page break controls */
  .page-break-after {
    page-break-after: always;
  }
  
  .page-break-inside {
    page-break-inside: avoid;
  }
  
  /* Header for printed pages */
  @page {
    margin: 1in;
    @top-center {
      content: "J. Joseph Salon - Shift Report";
      font-size: 10pt;
      color: #666;
    }
    @bottom-right {
      content: "Page " counter(page);
      font-size: 10pt;
      color: #666;
    }
  }
}
</style>

<script>
function printReport() {
  // Add print-specific title
  const originalTitle = document.title;
  document.title = 'J. Joseph Salon - <?= ucfirst($row['shift_type'] ?? 'Morning') ?> Shift Report - <?= htmlspecialchars($row['shift_date']) ?>';
  
  // Print the page
  window.print();
  
  // Restore original title
  document.title = originalTitle;
}
</script>

<?php require __DIR__.'/../includes/footer.php'; ?>