<?php
require __DIR__.'/includes/auth.php';
require_login();
require_role('admin');
require __DIR__.'/includes/header.php';
?>

<div class="mb-6">
    <h1 class="text-2xl font-semibold flex items-center gap-3">
        <svg class="w-6 h-6" style="color: #AF831A;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
        </svg>
        Forms Administration
    </h1>
    <p class="text-gray-600 mt-2">Manage and review form submissions across the portal.</p>
</div>

<!-- Coming Soon Notice -->
<div class="bg-white rounded-xl border shadow-sm p-8 text-center">
    <svg class="w-16 h-16 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
    </svg>
    <h3 class="text-lg font-semibold text-gray-900 mb-2">Forms Administration</h3>
    <p class="text-gray-600 mb-4">This section will allow you to manage and review all form submissions including time off requests, shift reports, and other team communications.</p>
    <div class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 text-gray-700 rounded-lg">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        Coming Soon
    </div>
</div>

<?php require __DIR__.'/includes/footer.php'; ?>