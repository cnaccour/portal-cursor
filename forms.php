<?php
require __DIR__.'/includes/auth.php';
// Forms are accessible to all users - no login required
require __DIR__.'/includes/header.php';
?>

<h1 class="text-2xl font-semibold mb-6">Forms</h1>
<p class="text-gray-600 mb-8">Select a form to fill out below.</p>

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
  
  <!-- Shift Report -->
  <div class="bg-white p-6 rounded-lg border hover:border-gray-300 transition-colors">
    <h3 class="text-lg font-semibold mb-2">Shift Report</h3>
    <p class="text-gray-600 mb-4">Complete daily shift checklist and report for morning or evening shifts.</p>
    <a href="forms/shift-reports.php" class="inline-flex px-4 py-2 rounded-md bg-black text-white hover:bg-gray-800 transition-colors">
      Create Shift Report →
    </a>
  </div>

  <!-- Time Off Request -->
  <div class="bg-white p-6 rounded-lg border hover:border-gray-300 transition-colors">
    <h3 class="text-lg font-semibold mb-3">Time Off Request</h3>
    <p class="text-gray-600 mb-4">Submit requests for vacation, personal days, sick leave, or other time off needs.</p>
    <a href="forms/time-off-request.php" class="inline-flex px-4 py-2 rounded-md bg-black text-white hover:bg-gray-800 transition-colors">
      Request Time Off →
    </a>
  </div>

</div>

<?php require __DIR__.'/includes/footer.php'; ?>