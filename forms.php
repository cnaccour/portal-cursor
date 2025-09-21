<?php
require __DIR__.'/includes/auth.php';
require_login();
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
    <div class="flex items-center gap-3 mb-3">
      <svg class="w-6 h-6" style="color: #AF831A;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3a2 2 0 012-2h4a2 2 0 012 2v4m-6 8h6M7 21h10a2 2 0 002-2V9a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
      </svg>
      <h3 class="text-lg font-semibold">Time Off Request</h3>
    </div>
    <p class="text-gray-600 mb-4">Submit requests for vacation, personal days, sick leave, or other time off needs.</p>
    <a href="forms/time-off-request.php" class="inline-flex px-4 py-2 rounded-md bg-black text-white hover:bg-gray-800 transition-colors">
      Request Time Off →
    </a>
  </div>

</div>

<?php require __DIR__.'/includes/footer.php'; ?>