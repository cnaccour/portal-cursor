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
        <!-- Hero Section -->
        <div class="mb-8 bg-gradient-to-r from-black to-gray-800 text-white p-6 rounded-xl">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h3 class="text-2xl font-bold mb-2">2025 Training Schedule</h3>
                    <p class="text-gray-200 text-sm">
                        <?= htmlspecialchars($educationData['description']) ?>
                    </p>
                </div>
                <!-- Calendar Export Disabled
                <button type="button" onclick="return exportAllToCalendar(event)" 
                        class="inline-flex items-center justify-center px-6 py-3 bg-white text-black rounded-lg hover:bg-gray-100 transition-all transform hover:scale-105 text-sm font-semibold shadow-lg whitespace-nowrap">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    Export All to Calendar
                </button>
                -->
            </div>
        </div>
        
        <!-- Mobile View - Cards -->
        <div class="block lg:hidden">
            <div class="space-y-4">
                <?php foreach ($educationData['sessions'] as $index => $session): 
                    $sessionDate = $session['status_date'];
                    $isCompleted = strtotime($sessionDate) < strtotime($currentDate);
                    $isUpcoming = !$isCompleted;
                ?>
                <div class="bg-white rounded-lg border <?= $isCompleted ? 'border-gray-200 opacity-75' : 'border-gray-300 shadow-sm' ?> overflow-hidden">
                    <!-- Date Header -->
                    <div class="<?= $isCompleted ? 'bg-gray-100' : 'bg-black' ?> <?= $isCompleted ? 'text-gray-700' : 'text-white' ?> px-4 py-2">
                        <div class="flex items-center justify-between">
                            <span class="font-semibold text-sm">
                                <?= date('F j, Y', strtotime($session['date'])) ?>
                            </span>
                            <?php if ($isCompleted): ?>
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-200 text-gray-700">
                                    Completed
                                </span>
                            <?php else: ?>
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-500 text-white">
                                    Upcoming
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Session Details -->
                    <div class="p-4 space-y-3">
                        <div>
                            <h4 class="font-semibold text-gray-900 text-base mb-1">
                                <?= htmlspecialchars($session['topic']) ?>
                            </h4>
                        </div>
                        
                        <div class="flex items-center text-sm text-gray-600 gap-4">
                            <div class="flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <span><?= htmlspecialchars($session['time']) ?></span>
                            </div>
                            <div class="flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                                <span><?= htmlspecialchars($session['instructor']) ?></span>
                            </div>
                        </div>
                        
                        <?php if ($isUpcoming): ?>
                        <!-- Calendar Button Disabled
                        <button type="button" onclick="return addToCalendar(<?= htmlspecialchars(json_encode($session), ENT_QUOTES) ?>, event)" 
                                class="w-full mt-3 inline-flex items-center justify-center px-4 py-2 border border-black text-black rounded-lg hover:bg-black hover:text-white transition-colors text-sm font-medium">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                      d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            Add to Calendar
                        </button>
                        -->
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- Desktop View - Enhanced Table -->
        <div class="hidden lg:block">
            <div class="bg-white rounded-xl border shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="bg-gray-50 border-b">
                                <th class="text-left px-6 py-4 font-semibold text-sm text-gray-900">Date</th>
                                <th class="text-left px-6 py-4 font-semibold text-sm text-gray-900">Training Session</th>
                                <th class="text-left px-6 py-4 font-semibold text-sm text-gray-900">Time</th>
                                <th class="text-left px-6 py-4 font-semibold text-sm text-gray-900">Instructor</th>
                                <th class="text-left px-6 py-4 font-semibold text-sm text-gray-900">Status</th>
                                <th class="text-center px-6 py-4 font-semibold text-sm text-gray-900">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php foreach ($educationData['sessions'] as $index => $session): 
                                $sessionDate = $session['status_date'];
                                $isCompleted = strtotime($sessionDate) < strtotime($currentDate);
                                $isUpcoming = !$isCompleted;
                            ?>
                            <tr class="<?= $isCompleted ? 'bg-gray-50 text-gray-600' : 'bg-white hover:bg-gray-50' ?> transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium">
                                        <?= date('M j', strtotime($session['date'])) ?>
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        <?= date('l', strtotime($session['date'])) ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm font-semibold <?= $isCompleted ? 'text-gray-600' : 'text-gray-900' ?>">
                                        <?= htmlspecialchars($session['topic']) ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm flex items-center gap-1">
                                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        <?= htmlspecialchars($session['time']) ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm">
                                        <?= htmlspecialchars($session['instructor']) ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php if ($isCompleted): ?>
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-700">
                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                            </svg>
                                            Completed
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            <span class="w-2 h-2 bg-green-500 rounded-full mr-2 animate-pulse"></span>
                                            Upcoming
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 text-center whitespace-nowrap">
                                    <?php if ($isUpcoming): ?>
                                    <!-- Calendar Button Disabled
                                    <button type="button" onclick="return addToCalendar(<?= htmlspecialchars(json_encode($session), ENT_QUOTES) ?>, event)" 
                                            class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-xs font-medium text-gray-700 bg-white hover:bg-gray-50 hover:border-gray-400 transition-all transform hover:scale-105"
                                            title="Add to Calendar">
                                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                  d="M12 4v16m8-8H4"></path>
                                        </svg>
                                        Add to Calendar
                                    </button>
                                    -->
                                    <span class="text-xs text-gray-500">Training Session</span>
                                    <?php else: ?>
                                    <span class="text-xs text-gray-400">—</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <!-- Footer Note -->
        <div class="mt-6 text-center">
            <p class="text-xs text-gray-500">
                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                All sessions run from 9:00 AM - 11:00 AM unless otherwise specified
            </p>
        </div>
    </div>
    
    <!-- Calendar functions are now globally available on the announcements page -->
    
    <?php
    return ob_get_clean();
}