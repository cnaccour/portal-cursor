<?php
require __DIR__.'/../includes/auth.php';
require_login();
require __DIR__.'/../includes/header.php';
?>

<h1 class="text-2xl font-semibold mb-6">Morning Shift Report</h1>

<form x-data="shiftForm()" method="post" action="/api/save-morning-shift.php" @submit="preparePayload"
      class="space-y-6">

  <!-- Shift Information -->
  <section class="bg-white p-6 rounded-xl border">
    <h2 class="text-lg font-semibold mb-4">Shift Information</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div>
        <label class="block text-sm mb-1">Shift Date</label>
        <input type="date" name="shift_date" class="w-full border rounded-lg px-3 py-2"
               :value="today">
      </div>
      <div>
        <label class="block text-sm mb-1">Salon Location <span class="text-red-500">*</span></label>
        <select name="location" required class="w-full border rounded-lg px-3 py-2">
          <option value="">Choose…</option>
          <option>Lutz</option>
          <option>Land O’ Lakes</option>
          <option>Citrus Park</option>
          <option>Odessa</option>
          <option>Wesley Chapel</option>
        </select>
      </div>
    </div>
  </section>

  <!-- Morning Duties Checklist -->
  <section class="bg-white p-6 rounded-xl border" x-data>
    <div class="flex items-center justify-between mb-4">
      <h2 class="text-lg font-semibold">Morning Duties Checklist</h2>
      <div class="text-right">
        <div class="text-xs text-gray-500"><span x-text="progress + '%'"></span> Complete</div>
        <div class="w-40 h-2 bg-gray-200 rounded-full overflow-hidden">
          <div class="h-full bg-gray-900" :style="`width:${progress}%`"></div>
        </div>
      </div>
    </div>

    <ul class="space-y-3">
      <template x-for="(item, idx) in checklist" :key="idx">
        <li class="flex items-start gap-3 p-3 rounded-lg border border-gray-200 hover:bg-gray-50">
          <input type="checkbox" class="h-5 w-5 rounded border-gray-300 text-gray-900 focus:ring-gray-900"
                 :id="'c'+idx" x-model="item.done" @change="calcProgress">
          <label :for="'c'+idx" class="text-sm text-gray-800" x-text="item.label"></label>
          <input type="hidden" :name="'checklist['+idx+']'" :value="item.done ? item.label : ''">
        </li>
      </template>
    </ul>
  </section>

  <!-- Customer Reviews -->
  <section class="bg-white p-6 rounded-xl border">
    <h2 class="text-lg font-semibold mb-4">Customer Reviews</h2>
    <div class="md:w-1/3">
      <label class="block text-sm mb-1">Number of reviews received during your shift</label>
      <input type="number" min="0" name="reviews_count" class="w-full border rounded-lg px-3 py-2" value="0">
    </div>
  </section>

  <!-- Shipments & Deliveries -->
  <section class="bg-white p-6 rounded-xl border" x-data="{v:''}">
    <h2 class="text-lg font-semibold mb-4">Shipments &amp; Deliveries</h2>
    <fieldset class="space-y-2">
      <label class="flex items-center gap-2">
        <input type="radio" class="h-4 w-4 border-gray-300 text-gray-900 focus:ring-gray-900"
               name="shipments" value="yes" @change="v='yes'">
        Yes, shipments were received
      </label>
      <label class="flex items-center gap-2">
        <input type="radio" class="h-4 w-4 border-gray-300 text-gray-900 focus:ring-gray-900"
               name="shipments" value="no" @change="v='no'">
        No shipments received
      </label>
    </fieldset>

    <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4" x-show="v==='yes'">
      <div>
        <label class="block text-sm mb-1">Carrier / Vendor</label>
        <input type="text" class="w-full border rounded-lg px-3 py-2" name="shipment_vendor">
      </div>
      <div>
        <label class="block text-sm mb-1">Items / Notes</label>
        <input type="text" class="w-full border rounded-lg px-3 py-2" name="shipment_notes">
      </div>
    </div>
  </section>

  <!-- Refunds & Returns -->
  <section class="bg-white p-6 rounded-xl border" 
           x-data="{active:false, refunds:[], totalCount:0, totalAmount:0, 
                    calcTotals() { this.totalCount=this.refunds.length; this.totalAmount=this.refunds.reduce((s,r)=>s+(parseFloat(r.amount)||0),0);} }">

    <h2 class="text-lg font-semibold mb-4">Refunds &amp; Returns</h2>

    <fieldset class="space-y-2 mb-4">
      <label class="flex items-center gap-2">
        <input type="radio" class="h-4 w-4 border-gray-300 text-gray-900 focus:ring-gray-900"
               name="refunds_toggle" value="yes" @change="active=true">
        Yes, refunds were processed
      </label>
      <label class="flex items-center gap-2">
        <input type="radio" class="h-4 w-4 border-gray-300 text-gray-900 focus:ring-gray-900"
               name="refunds_toggle" value="no" @change="active=false; refunds=[]; totalCount=0; totalAmount=0">
        No refunds processed
      </label>
    </fieldset>

    <!-- Summary + Add Button -->
    <div x-show="active" class="mb-4">
      <div class="flex items-center justify-between bg-gray-50 p-3 rounded-lg border">
        <div class="text-sm">
          <strong>Refund Summary</strong><br>
          Total Refunds: <span x-text="totalCount"></span> |
          Total Amount: $<span x-text="totalAmount.toFixed(2)"></span>
        </div>
        <button type="button" @click="refunds.push({amount:'',reason:'',customer:'',service:'',notes:''}); calcTotals()"
                class="px-3 py-1.5 rounded-lg bg-gray-900 text-white text-sm">
          + Add Refund
        </button>
      </div>
    </div>

    <!-- Refund Entries -->
    <template x-for="(r, i) in refunds" :key="i">
      <div class="mt-4 p-4 border rounded-lg space-y-3 bg-white shadow-sm">
        <div>
          <label class="block text-sm mb-1">Refund Amount ($)</label>
          <input type="number" step="0.01" min="0" class="w-full border rounded-lg px-3 py-2"
                 x-model.number="r.amount" name="refunds[][amount]" @input="calcTotals">
        </div>
        <div>
          <label class="block text-sm mb-1">Refund Reason</label>
          <select class="w-full border rounded-lg px-3 py-2"
                  x-model="r.reason" name="refunds[][reason]">
            <option value="">Select reason</option>
            <option>Service Issue</option>
            <option>Product Return</option>
            <option>Scheduling Error</option>
            <option>Other</option>
          </select>
        </div>
        <div>
          <label class="block text-sm mb-1">Customer Name</label>
          <input type="text" class="w-full border rounded-lg px-3 py-2"
                 x-model="r.customer" name="refunds[][customer]">
        </div>
        <div>
          <label class="block text-sm mb-1">Service / Product</label>
          <input type="text" class="w-full border rounded-lg px-3 py-2"
                 x-model="r.service" name="refunds[][service]">
        </div>
        <div>
          <label class="block text-sm mb-1">Additional Notes</label>
          <textarea class="w-full border rounded-lg px-3 py-2"
                    x-model="r.notes" name="refunds[][notes]"></textarea>
        </div>
        <div class="text-right">
          <button type="button" @click="refunds.splice(i,1); calcTotals()"
                  class="text-sm text-red-600">Remove</button>
        </div>
      </div>
    </template>
  </section>

  <!-- Shift Notes -->
  <section class="bg-white p-6 rounded-xl border">
    <h2 class="text-lg font-semibold mb-4">Shift Notes &amp; Comments</h2>
    <textarea name="notes" rows="5" class="w-full border rounded-lg px-3 py-2"
              placeholder="Share any important information, incidents, or observations from your shift…"></textarea>
  </section>

  <!-- Actions -->
  <div class="flex items-center justify-end gap-3">
    <button type="submit" class="px-4 py-2 rounded-lg bg-gray-900 text-white">
      Submit Morning Shift Report
    </button>
  </div>

  <!-- Hidden JSON payload -->
  <input type="hidden" name="__json" x-model="jsonPayload">
</form>

<script>
function shiftForm() {
  return {
    today: new Date().toISOString().slice(0,10),
    checklist: [
      {label: 'Count your drawer – verify cash is correct and properly closed from previous night', done:false},
      {label: 'Prepare daily cleaning sheet – ensure all stylists are included', done:false},
      {label: 'Organize coffee area – stock K-cups, fill Keurig, restock cups, creamer, sugar, and snacks', done:false},
      {label: 'Clean lobby – restock candles, clean tables, dust sofas, sweep and mop as needed', done:false},
      {label: 'Clean exterior windows and door glass', done:false},
      {label: 'Replace used mop heads and place in laundry bin for washing', done:false},
      {label: 'Check supply inventory – cards, paper, pens, stamps, bags, and printer rolls', done:false},
      {label: 'Complete follow-up calls for services from 2 days ago', done:false},
      {label: 'Check and respond to any voicemails', done:false},
      {label: 'Contact all missed appointment requests from previous day', done:false},
      {label: 'Follow up on weekly cancellations for rescheduling', done:false},
      {label: 'Check out all used color tubes and boxes from color bar bowl', done:false},
      {label: 'Organize front desk – professional appearance, no clutter or dust', done:false},
      {label: 'Send end-of-shift summary email to managers and incoming front desk staff', done:false},
      {label: 'Close and balance drawer – notify Eliana of any discrepancies', done:false},
    ],
    progress: 0,
    jsonPayload: '',
    calcProgress() {
      const total = this.checklist.length;
      const done = this.checklist.filter(i => i.done).length;
      this.progress = Math.round((done/total)*100);
    },
    preparePayload(e) {
      const form = new FormData(e.target);
      const payload = Object.fromEntries(form.entries());
      payload.checklist = this.checklist.filter(i=>i.done).map(i=>i.label);
      payload.refunds = (this.refunds || []).filter(r =>
        r.amount || r.reason || r.customer || r.service || r.notes
      );
      this.jsonPayload = JSON.stringify(payload);
    }
  }
}
</script>

<?php require __DIR__.'/../includes/footer.php'; ?>