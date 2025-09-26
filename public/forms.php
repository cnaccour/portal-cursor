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
      <svg class="w-5 h-5" fill="none" stroke="#AF831A" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
      </svg>
    </div>
    <p class="text-gray-600 text-sm">Complete daily shift checklist and report for morning or evening shifts.</p>
    <div class="mt-4">
      <div class="inline-flex items-center gap-2 px-4 py-2 bg-black text-white rounded-lg text-sm font-medium group-hover:bg-gray-800 transition-colors">
        <span>Open form</span>
        <svg class="w-4 h-4 group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
        </svg>
      </div>
    </div>
  </a>

  <!-- Time Off Request -->
  <a href="forms/time-off-request.php" class="group block bg-white rounded-lg border border-gray-200 hover:border-gray-300 hover:shadow-md transition-all duration-200 p-6">
    <div class="flex items-center justify-between mb-3">
      <h3 class="text-lg font-semibold text-gray-900 group-hover:text-gray-700">Time Off Request</h3>
      <svg class="w-5 h-5" fill="none" stroke="#AF831A" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"></path>
      </svg>
    </div>
    <p class="text-gray-600 text-sm">Submit requests for vacation, personal days, sick leave, or other time off needs.</p>
    <div class="mt-4">
      <div class="inline-flex items-center gap-2 px-4 py-2 bg-black text-white rounded-lg text-sm font-medium group-hover:bg-gray-800 transition-colors">
        <span>Open form</span>
        <svg class="w-4 h-4 group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
        </svg>
      </div>
    </div>
  </a>

  <!-- Bi-Weekly Report -->
  <a href="forms/bi-weekly-report.php" class="group block bg-white rounded-lg border border-gray-200 hover:border-gray-300 hover:shadow-md transition-all duration-200 p-6">
    <div class="flex items-center justify-between mb-3">
      <h3 class="text-lg font-semibold text-gray-900 group-hover:text-gray-700">Bi-Weekly Report</h3>
      <svg class="w-5 h-5" fill="none" stroke="#AF831A" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3M5 11h14M5 19h14M7 11v8m10-8v8"/>
      </svg>
    </div>
    <p class="text-gray-600 text-sm">Submit the manager’s bi-weekly report for apprentices, including ratings and feedback.</p>
    <div class="mt-4">
      <div class="inline-flex items-center gap-2 px-4 py-2 bg-black text-white rounded-lg text-sm font-medium group-hover:bg-gray-800 transition-colors">
        <span>Open form</span>
        <svg class="w-4 h-4 group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
        </svg>
      </div>
    </div>
  </a>

</div>

<?php require __DIR__.'/includes/footer.php'; ?>