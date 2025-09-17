<?php
require __DIR__.'/../includes/auth.php';
require_login();
require __DIR__.'/../includes/header.php';
?>

<h1 class="text-xl font-semibold mb-4">Example Form</h1>

<form x-data="{ ship: 'no' }" method="post" action="/api/save-form.php"
      class="bg-white p-6 rounded-xl border space-y-4">

  <!-- Location -->
  <div>
    <label class="block text-sm mb-1">Location</label>
    <select name="location" required class="w-full border rounded-lg px-3 py-2">
      <option value="">Choose…</option>
      <option>Lutz</option>
      <option>Land O’ Lakes</option>
      <option>Citrus Park</option>
      <option>Odessa</option>
      <option>Wesley Chapel</option>
    </select>
  </div>

  <!-- Shipments -->
  <fieldset class="space-y-2">
    <legend class="text-sm font-medium">Shipments received?</legend>
    <label class="flex items-center gap-2">
      <input type="radio" name="shipments" value="yes" @change="ship='yes'">
      Yes
    </label>
    <label class="flex items-center gap-2">
      <input type="radio" name="shipments" value="no" @change="ship='no'" checked>
      No
    </label>
  </fieldset>

  <!-- Conditional fields -->
  <div x-show="ship==='yes'" class="grid sm:grid-cols-2 gap-3">
    <div>
      <label class="block text-sm mb-1">Vendor</label>
      <input type="text" name="vendor" class="w-full border rounded-lg px-3 py-2">
    </div>
    <div>
      <label class="block text-sm mb-1">Notes</label>
      <input type="text" name="notes" class="w-full border rounded-lg px-3 py-2">
    </div>
  </div>

  <!-- Submit -->
  <button class="px-4 py-2 rounded-lg bg-gray-900 text-white">
    Submit
  </button>
</form>

<?php require __DIR__.'/../includes/footer.php'; ?>