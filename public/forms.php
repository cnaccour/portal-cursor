<?php
require __DIR__.'/includes/auth.php';
// Forms are accessible to all users - no login required
require __DIR__.'/includes/header.php';
?>

<h1 class="text-2xl font-semibold mb-6">Forms</h1>

<?php if (isset($_GET['ok'])): ?>
<div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg">
  <div class="flex items-center">
    <svg class="w-5 h-5 text-green-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
      <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
    </svg>
    <span class="text-green-800 font-medium">Form submitted successfully!</span>
  </div>
</div>
<?php endif; ?>

<?php if (isset($_GET['error'])): ?>
<div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
  <div class="flex items-center">
    <svg class="w-5 h-5 text-red-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
      <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
    </svg>
    <span class="text-red-800 font-medium"><?= htmlspecialchars($_GET['error']) ?></span>
  </div>
</div>
<?php endif; ?>

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
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
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