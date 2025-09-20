<?php
/**
 * Education Schedule Renderer
 * Renders the 2025 Education Schedule with calendar integration
 */

function renderEducationSchedule($educationData) {
    if (empty($educationData) || !isset($educationData['sessions'])) {
        return '';
    }
    
    $currentDate = date('Y-m-d');
    ob_start();
    ?>
    
    <div class="education-schedule-2025">
        <!-- Description -->
        <div class="mb-6 p-4 bg-gray-50 rounded-lg">
            <p class="text-gray-700">
                <?= htmlspecialchars($educationData['description']) ?>
            </p>
        </div>
        
        <!-- Schedule Header -->
        <div class="mb-4">
            <h3 class="text-lg font-semibold mb-3">2025 Training Schedule</h3>
            
            <!-- Export All Button -->
            <div class="flex justify-end mb-4">
                <button onclick="exportAllToCalendar()" 
                        class="inline-flex items-center px-4 py-2 bg-black text-white rounded-md hover:bg-gray-800 transition-colors text-sm font-medium">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    Export All to Calendar
                </button>
            </div>
        </div>
        
        <!-- Schedule Table -->
        <div class="overflow-x-auto">
            <table class="w-full border-collapse">
                <thead>
                    <tr class="bg-gray-100 border-b">
                        <th class="text-left p-3 font-semibold text-sm">Date</th>
                        <th class="text-left p-3 font-semibold text-sm">Training Session</th>
                        <th class="text-left p-3 font-semibold text-sm">Time</th>
                        <th class="text-left p-3 font-semibold text-sm">Instructor</th>
                        <th class="text-left p-3 font-semibold text-sm">Status</th>
                        <th class="text-center p-3 font-semibold text-sm">Calendar</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($educationData['sessions'] as $index => $session): 
                        $sessionDate = $session['status_date'];
                        $isCompleted = strtotime($sessionDate) < strtotime($currentDate);
                        $isUpcoming = !$isCompleted;
                        $rowClass = $isCompleted ? 'bg-gray-50 text-gray-600' : 'bg-white hover:bg-gray-50';
                    ?>
                    <tr class="<?= $rowClass ?> border-b transition-colors">
                        <td class="p-3 text-sm">
                            <?= date('m/d/25', strtotime($session['date'])) ?>
                        </td>
                        <td class="p-3 text-sm font-medium">
                            <?= htmlspecialchars($session['topic']) ?>
                        </td>
                        <td class="p-3 text-sm">
                            <?= htmlspecialchars($session['time']) ?>
                        </td>
                        <td class="p-3 text-sm">
                            <?= htmlspecialchars($session['instructor']) ?>
                        </td>
                        <td class="p-3">
                            <?php if ($isCompleted): ?>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                    Completed
                                </span>
                            <?php else: ?>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    Upcoming
                                </span>
                            <?php endif; ?>
                        </td>
                        <td class="p-3 text-center">
                            <?php if ($isUpcoming): ?>
                            <button onclick="addToCalendar(<?= htmlspecialchars(json_encode($session), ENT_QUOTES) ?>)" 
                                    class="inline-flex items-center px-3 py-1 border border-gray-300 rounded-md text-xs font-medium text-gray-700 bg-white hover:bg-gray-50 transition-colors"
                                    title="Add to Calendar">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                          d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                <span class="ml-1">Add to Calendar</span>
                            </button>
                            <?php else: ?>
                            <span class="text-xs text-gray-500">—</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Footer Note -->
        <div class="mt-6 text-xs text-gray-500">
            <p>All sessions run from 9:00 AM - 11:00 AM unless otherwise specified</p>
        </div>
    </div>
    
    <!-- JavaScript for Calendar Export -->
    <script>
    function addToCalendar(session) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '/api/generate-ics.php';
        form.target = '_blank';
        
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'session';
        input.value = JSON.stringify(session);
        
        form.appendChild(input);
        document.body.appendChild(form);
        form.submit();
        document.body.removeChild(form);
    }
    
    function exportAllToCalendar() {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '/api/generate-ics.php';
        form.target = '_blank';
        
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'export_all';
        input.value = 'true';
        
        form.appendChild(input);
        document.body.appendChild(form);
        form.submit();
        document.body.removeChild(form);
    }
    </script>
    
    <?php
    return ob_get_clean();
}