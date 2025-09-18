<?php
require __DIR__.'/includes/auth.php';
require_login();
require __DIR__.'/includes/header.php';
?>

<h1 class="text-2xl font-semibold mb-6">Forms</h1>
<p class="text-gray-600 mb-8">Select a form to fill out below.</p>

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
  
  <!-- Shift Report -->
  <div class="bg-white p-6 rounded-xl border hover:border-gray-300 transition-colors">
    <h3 class="text-lg font-semibold mb-2">Shift Report</h3>
    <p class="text-gray-600 mb-4">Complete daily shift checklist and report for morning or evening shifts.</p>
    <a href="/forms/shift-reports.php" class="inline-flex px-4 py-2 rounded-md text-white transition-colors" style="background-color: #AF831A;" onmouseover="this.style.backgroundColor='#8B6914'" onmouseout="this.style.backgroundColor='#AF831A'">
      Create Shift Report →
    </a>
  </div>

</div>

<?php require __DIR__.'/includes/footer.php'; ?>