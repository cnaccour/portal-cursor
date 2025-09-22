<?php
require __DIR__.'/includes/auth.php';
// Forms are accessible to all users - no login required
require __DIR__.'/includes/header.php';
?>

<h1 class="text-2xl font-semibold mb-6">Forms</h1>
<p class="text-gray-600 mb-8">Select a form to fill out below.</p>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
  
  <!-- Shift Report -->
  <a href="forms/shift-reports.php" class="group block bg-white rounded-lg border border-gray-200 hover:border-gray-300 hover:shadow-md transition-all duration-200 p-6">
    <div class="flex items-center justify-between mb-3">
      <h3 class="text-lg font-semibold text-gray-900 group-hover:text-gray-700">Shift Report</h3>
      <span class="text-xs bg-yellow-50 text-yellow-700 px-2 py-1 rounded border border-yellow-200">Sign-in required</span>
    </div>
    <p class="text-gray-600 text-sm">Complete daily shift checklist and report for morning or evening shifts.</p>
    <div class="mt-4 flex items-center text-sm text-gray-500 group-hover:text-gray-700">
      <span>Open form</span>
      <svg class="w-4 h-4 ml-1 group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
      </svg>
    </div>
  </a>

  <!-- Time Off Request -->
  <a href="forms/time-off-request.php" class="group block bg-white rounded-lg border border-gray-200 hover:border-gray-300 hover:shadow-md transition-all duration-200 p-6">
    <div class="flex items-center justify-between mb-3">
      <h3 class="text-lg font-semibold text-gray-900 group-hover:text-gray-700">Time Off Request</h3>
      <span class="text-xs bg-green-50 text-green-700 px-2 py-1 rounded border border-green-200">Open access</span>
    </div>
    <p class="text-gray-600 text-sm">Submit requests for vacation, personal days, sick leave, or other time off needs.</p>
    <div class="mt-4 flex items-center text-sm text-gray-500 group-hover:text-gray-700">
      <span>Open form</span>
      <svg class="w-4 h-4 ml-1 group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
      </svg>
    </div>
  </a>

</div>

<?php require __DIR__.'/includes/footer.php'; ?>