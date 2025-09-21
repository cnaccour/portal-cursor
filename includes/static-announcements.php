<?php
/**
 * Static Announcements Configuration
 * These announcements are managed by developers and cannot be edited through the web interface
 */

// Create notification for the static education schedule (only once)
static $notificationInitialized = false;

if (!$notificationInitialized) {
    try {
        if (file_exists(__DIR__ . '/notification-manager.php')) {
            require_once __DIR__ . '/notification-manager.php';
            
            // Simple check to avoid duplicate notifications
            $notificationFile = __DIR__ . '/../storage/education-notification-created.flag';
            if (!file_exists($notificationFile)) {
                NotificationManager::notify_roles(['admin', 'manager', 'support', 'staff', 'viewer'], [
                    'type' => 'announcement',
                    'title' => 'New: In House Education Schedule 2025',
                    'message' => 'The complete 2025 training schedule is now available. View all upcoming education sessions.',
                    'link_url' => '/announcements.php',
                    'icon' => 'announcement'
                ]);
                
                // Create flag file to prevent duplicate notifications
                @file_put_contents($notificationFile, date('Y-m-d H:i:s'));
            }
        }
    } catch (Exception $e) {
        // Silently handle if notification system is not available
    }
    $notificationInitialized = true;
}

return [
    [
        'id' => 'static-education-2025',
        'title' => 'In House Education Schedule 2025',
        'content' => generateEducationScheduleHtml(),
        'excerpt' => 'Complete 2025 education schedule with monthly training sessions on social media, styling techniques, product knowledge, and business development. All sessions run from 9:00 AM - 11:00 AM unless otherwise specified.',
        'category' => 'training',
        'author' => 'Management',
        'location_specific' => false,
        'pinned' => true,
        'priority' => 1,
        'expiration_date' => null,
        'date_created' => '2025-01-01 00:00:00',
        'date_modified' => '2025-01-01 00:00:00',
        'attachments' => [],
        'static' => true, // Mark as static to prevent editing
        'admin_deletable' => true // Allow admin deletion
    ],
    [
        'id' => 'static-blackout-dates-2025',
        'title' => 'Blackout Dates & Holidays 2025',
        'content' => generateBlackoutDatesHtml(),
        'excerpt' => 'Complete 2025 calendar of holiday closures, blocked-out dates for time off requests, seize the season periods, and extended Saturday hours. Includes 10 holiday closures, 9 blackout periods, and special extended hours for busy seasons.',
        'category' => 'general',
        'author' => 'Management',
        'location_specific' => false,
        'pinned' => true,
        'priority' => 2,
        'expiration_date' => null,
        'date_created' => '2025-01-03 00:00:00',
        'date_modified' => '2025-01-03 00:00:00',
        'attachments' => [],
        'static' => true, // Mark as static to prevent editing
        'admin_deletable' => true // Allow admin deletion
    ]
];

/**
 * Generate the mobile-first responsive HTML for the education schedule
 */
function generateEducationScheduleHtml() {
    $sessions = [
        ['date' => '01/14/25', 'session' => 'Social Media', 'time' => '9:00 AM - 11:00 AM', 'instructor' => 'Annalee Wingate', 'status' => 'completed'],
        ['date' => '01/21/25', 'session' => 'Intro to Vivids', 'time' => '9:00 AM - 11:00 AM', 'instructor' => 'Angelica Semanchik', 'status' => 'completed'],
        ['date' => '02/04/25', 'session' => 'Pre Keratin Smoothing Certification', 'time' => '9:00 AM - 11:00 AM', 'instructor' => 'Christina Flynn', 'status' => 'completed'],
        ['date' => '02/18/25', 'session' => 'Product Knowledge', 'time' => '9:00 AM - 11:00 AM', 'instructor' => 'TBA', 'status' => 'completed'],
        ['date' => '03/04/25', 'session' => 'Shampoo Massage/curly styling', 'time' => '9:00 AM - 11:00 AM', 'instructor' => 'Phoebe Vecchione/Natalie Santos', 'status' => 'completed'],
        ['date' => '03/18/25', 'session' => 'Updo How To', 'time' => '9:00 AM - 11:00 AM', 'instructor' => 'Jakaterina F.', 'status' => 'completed'],
        ['date' => '04/01/25', 'session' => 'Consultation', 'time' => '9:00 AM - 11:00 AM', 'instructor' => 'Mike Wilkins', 'status' => 'completed'],
        ['date' => '04/15/25', 'session' => 'JJS Business / Retention', 'time' => '9:00 AM - 11:00 AM', 'instructor' => 'Mikayla Santulo', 'status' => 'completed'],
        ['date' => '04/29/25', 'session' => 'Social Media', 'time' => '9:00 AM - 12:00 PM', 'instructor' => 'Annalee Wingate', 'status' => 'completed'],
        ['date' => '05/13/25', 'session' => 'Intro to Vivids', 'time' => '9:00 AM - 11:00 AM', 'instructor' => 'Angelica Semanchik', 'status' => 'completed'],
        ['date' => '05/27/25', 'session' => 'Pre Keratin Smoothing Certification', 'time' => '9:00 AM - 11:00 AM', 'instructor' => 'Christina Flynn', 'status' => 'completed'],
        ['date' => '06/10/25', 'session' => 'Product Knowledge', 'time' => '9:00 AM - 11:00 AM', 'instructor' => 'TBA', 'status' => 'completed'],
        ['date' => '06/24/25', 'session' => 'Shampoo Massage/curly styling', 'time' => '9:00 AM - 11:00 AM', 'instructor' => 'Phoebe Vecchione/Natalie Santos', 'status' => 'completed'],
        ['date' => '07/08/25', 'session' => 'Updo How To', 'time' => '9:00 AM - 11:00 AM', 'instructor' => 'Jakaterina F.', 'status' => 'completed'],
        ['date' => '07/22/25', 'session' => 'Consultation', 'time' => '9:00 AM - 11:00 AM', 'instructor' => 'Mike Wilkins', 'status' => 'completed'],
        ['date' => '08/05/25', 'session' => 'Social Media', 'time' => '9:00 AM - 11:00 AM', 'instructor' => 'Annalee Wingate', 'status' => 'completed'],
        ['date' => '08/19/25', 'session' => 'JJS Business / Retention', 'time' => '9:00 AM - 11:00 AM', 'instructor' => 'Mikayla Santulo', 'status' => 'completed'],
        ['date' => '09/02/25', 'session' => 'Intro to Vivids', 'time' => '9:00 AM - 11:00 AM', 'instructor' => 'Angelica Semanchik', 'status' => 'completed'],
        ['date' => '09/16/25', 'session' => 'Pre Keratin Smoothing Certification', 'time' => '9:00 AM - 11:00 AM', 'instructor' => 'Christina Flynn', 'status' => 'completed'],
        ['date' => '09/30/25', 'session' => 'Product Knowledge', 'time' => '9:00 AM - 11:00 AM', 'instructor' => 'TBA', 'status' => 'upcoming'],
        ['date' => '10/14/25', 'session' => 'Shampoo Massage/curly styling', 'time' => '9:00 AM - 11:00 AM', 'instructor' => 'Phoebe Vecchione/Natalie Santos', 'status' => 'upcoming'],
        ['date' => '10/28/25', 'session' => 'Updo How To', 'time' => '9:00 AM - 11:00 AM', 'instructor' => 'Jakaterina F.', 'status' => 'upcoming'],
        ['date' => '11/11/25', 'session' => 'Consultation', 'time' => '9:00 AM - 11:00 AM', 'instructor' => 'Mike Wilkins', 'status' => 'upcoming'],
        ['date' => '11/25/25', 'session' => 'JJS Business / Retention', 'time' => '9:00 AM - 11:00 AM', 'instructor' => 'Mikayla Santulo', 'status' => 'upcoming']
    ];

    ob_start();
    ?>
    <div class="bg-gradient-to-br from-amber-50 to-orange-50 rounded-xl p-6 mb-6">
        <div class="flex items-center gap-3 mb-4">
            <div class="w-10 h-10 bg-amber-600 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                </svg>
            </div>
            <div>
                <h3 class="text-xl font-bold text-amber-900">2025 Training Schedule</h3>
                <p class="text-amber-700 text-sm">Complete 2025 education schedule with monthly training sessions on social media, styling techniques, product knowledge, and business development.</p>
            </div>
        </div>
    </div>

    <!-- Mobile Cards (visible on mobile only) -->
    <div class="block md:hidden space-y-4">
        <?php foreach ($sessions as $session): ?>
            <?php $isCompleted = $session['status'] === 'completed'; ?>
            <div class="bg-white rounded-lg border shadow-sm p-4 <?= $isCompleted ? 'bg-gray-50' : 'border-l-4 border-l-amber-500' ?>">
                <div class="flex justify-between items-start mb-3">
                    <div class="text-lg font-semibold <?= $isCompleted ? 'text-gray-600' : 'text-gray-900' ?>">
                        <?= htmlspecialchars($session['session']) ?>
                    </div>
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium <?= $isCompleted ? 'bg-gray-100 text-gray-700' : 'bg-green-100 text-green-800' ?>">
                        <?php if ($isCompleted): ?>
                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            Completed
                        <?php else: ?>
                            <span class="w-2 h-2 bg-green-500 rounded-full mr-2"></span>
                            Upcoming
                        <?php endif; ?>
                    </span>
                </div>
                
                <div class="space-y-2 text-sm">
                    <div class="flex items-center gap-2 <?= $isCompleted ? 'text-gray-600' : 'text-gray-700' ?>">
                        <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3a1 1 0 011-1h6a1 1 0 011 1v4m-6 0h6m-6 0V7a1 1 0 00-1 1v0a1 1 0 001 1h6a1 1 0 001-1v0a1 1 0 00-1-1"></path>
                        </svg>
                        <strong><?= htmlspecialchars($session['date']) ?></strong>
                    </div>
                    <div class="flex items-center gap-2 <?= $isCompleted ? 'text-gray-600' : 'text-gray-700' ?>">
                        <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <?= htmlspecialchars($session['time']) ?>
                    </div>
                    <div class="flex items-center gap-2 <?= $isCompleted ? 'text-gray-600' : 'text-gray-700' ?>">
                        <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        <?= htmlspecialchars($session['instructor']) ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Desktop Table (hidden on mobile) -->
    <div class="hidden md:block bg-white rounded-lg border overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Training Session</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Time</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Instructor</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($sessions as $session): ?>
                        <?php $isCompleted = $session['status'] === 'completed'; ?>
                        <tr class="<?= $isCompleted ? 'bg-gray-50 text-gray-600' : 'bg-white hover:bg-gray-50' ?>">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium <?= $isCompleted ? 'text-gray-600' : 'text-gray-900' ?>">
                                <?= htmlspecialchars($session['date']) ?>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm font-semibold <?= $isCompleted ? 'text-gray-600' : 'text-gray-900' ?>">
                                    <?= htmlspecialchars($session['session']) ?>
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
                                        <span class="w-2 h-2 bg-green-500 rounded-full mr-2"></span>
                                        Upcoming
                                    </span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
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
    <?php
    return ob_get_clean();
}

/**
 * Generate the mobile-first responsive HTML for the blackout dates & holidays
 */
function generateBlackoutDatesHtml() {
    ob_start();
    ?>
    <div class="mb-8">
        <div class="text-left">
            <h3 class="text-xl font-bold text-gray-900 mb-6">Blackout Dates & Holidays 2025</h3>
            <p class="text-gray-700 text-sm leading-relaxed">Complete 2025 calendar of holiday closures, blocked-out dates for time off requests, seize the season periods, and extended Saturday hours. Please review all important dates and plan accordingly.</p>
        </div>
    </div>

    <!-- Quick Reference Stats -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
        <div class="bg-white rounded-lg border p-4 text-center">
            <div class="text-2xl font-bold text-red-600">10</div>
            <div class="text-sm text-gray-600">Holiday Closures</div>
        </div>
        <div class="bg-white rounded-lg border p-4 text-center">
            <div class="text-2xl font-bold text-orange-600">9</div>
            <div class="text-sm text-gray-600">Blackout Periods</div>
        </div>
        <div class="bg-white rounded-lg border p-4 text-center">
            <div class="text-2xl font-bold text-yellow-600">3</div>
            <div class="text-sm text-gray-600">High Volume</div>
        </div>
        <div class="bg-white rounded-lg border p-4 text-center">
            <div class="text-2xl font-bold text-green-600">4</div>
            <div class="text-sm text-gray-600">Extended Hours</div>
        </div>
    </div>

    <!-- Holiday Closures Section -->
    <div class="bg-white rounded-lg border mb-6" x-data="{ open: true }">
        <button @click="open = !open" class="w-full px-6 py-4 flex items-center justify-between text-left hover:bg-gray-50">
            <div class="flex items-center gap-3">
                <div class="w-6 h-6 bg-red-100 rounded-full flex items-center justify-center">
                    <span class="text-red-600 text-xs font-bold">10</span>
                </div>
                <h3 class="text-lg font-semibold text-gray-900">Holiday Closures</h3>
            </div>
            <svg class="w-5 h-5 text-gray-400 transition-transform" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
            </svg>
        </button>
        
        <div x-show="open" x-transition class="px-6 pb-6">
            <div class="space-y-3">
                <div class="flex justify-between items-center py-3 border-b border-gray-100">
                    <div>
                        <div class="font-medium text-gray-900">New Year's Day</div>
                        <div class="text-sm text-gray-600">January 1, 2025 (Wednesday)</div>
                    </div>
                    <span class="px-3 py-1 bg-red-100 text-red-800 text-sm font-medium rounded-full">Closed</span>
                </div>
                
                <div class="flex justify-between items-center py-3 border-b border-gray-100">
                    <div>
                        <div class="font-medium text-gray-900">Easter</div>
                        <div class="text-sm text-gray-600">April 20-21, 2025 (Sunday and Monday)</div>
                    </div>
                    <span class="px-3 py-1 bg-red-100 text-red-800 text-sm font-medium rounded-full">Closed</span>
                </div>
                
                <div class="flex justify-between items-center py-3 border-b border-gray-100">
                    <div>
                        <div class="font-medium text-gray-900">Mother's Day</div>
                        <div class="text-sm text-gray-600">May 11, 2025 (Sunday)</div>
                    </div>
                    <span class="px-3 py-1 bg-red-100 text-red-800 text-sm font-medium rounded-full">Closed</span>
                </div>
                
                <div class="flex justify-between items-center py-3 border-b border-gray-100">
                    <div>
                        <div class="font-medium text-gray-900">Memorial Day</div>
                        <div class="text-sm text-gray-600">May 25-26, 2025 (Sunday and Monday)</div>
                    </div>
                    <span class="px-3 py-1 bg-red-100 text-red-800 text-sm font-medium rounded-full">Closed</span>
                </div>
                
                <div class="flex justify-between items-center py-3 border-b border-gray-100">
                    <div>
                        <div class="font-medium text-gray-900">Independence Day</div>
                        <div class="text-sm text-gray-600">July 4, 2025 (Friday)</div>
                    </div>
                    <span class="px-3 py-1 bg-red-100 text-red-800 text-sm font-medium rounded-full">Closed</span>
                </div>
                
                <div class="flex justify-between items-center py-3 border-b border-gray-100">
                    <div>
                        <div class="font-medium text-gray-900">Labor Day</div>
                        <div class="text-sm text-gray-600">September 1, 2025 (Monday)</div>
                    </div>
                    <span class="px-3 py-1 bg-red-100 text-red-800 text-sm font-medium rounded-full">Closed</span>
                </div>
                
                <div class="flex justify-between items-center py-3 border-b border-gray-100">
                    <div>
                        <div class="font-medium text-gray-900">Thanksgiving</div>
                        <div class="text-sm text-gray-600">November 26-30, 2025 (Wednesday-Sunday)</div>
                    </div>
                    <span class="px-3 py-1 bg-red-100 text-red-800 text-sm font-medium rounded-full">Closed</span>
                </div>
                
                <div class="flex justify-between items-center py-3 border-b border-gray-100">
                    <div>
                        <div class="font-medium text-gray-900">Christmas</div>
                        <div class="text-sm text-gray-600">December 24-27, 2025 (Wednesday-Saturday)</div>
                    </div>
                    <span class="px-3 py-1 bg-red-100 text-red-800 text-sm font-medium rounded-full">Closed</span>
                </div>
                
                <div class="flex justify-between items-center py-3 border-b border-gray-100">
                    <div>
                        <div class="font-medium text-gray-900">New Year's Eve</div>
                        <div class="text-sm text-gray-600">December 31, 2025</div>
                    </div>
                    <span class="px-3 py-1 bg-yellow-100 text-yellow-800 text-sm font-medium rounded-full">Limited Hours</span>
                </div>
                
                <div class="flex justify-between items-center py-3">
                    <div>
                        <div class="font-medium text-gray-900">New Year's Day 2026</div>
                        <div class="text-sm text-gray-600">January 1, 2026 (Wednesday)</div>
                    </div>
                    <span class="px-3 py-1 bg-red-100 text-red-800 text-sm font-medium rounded-full">Closed</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Blocked-Out Dates Section -->
    <div class="bg-white rounded-lg border mb-6" x-data="{ open: true }">
        <button @click="open = !open" class="w-full px-6 py-4 flex items-center justify-between text-left hover:bg-gray-50">
            <div class="flex items-center gap-3">
                <div class="w-6 h-6 bg-orange-100 rounded-full flex items-center justify-center">
                    <span class="text-orange-600 text-xs font-bold">9</span>
                </div>
                <h3 class="text-lg font-semibold text-gray-900">Blocked-Out Dates</h3>
            </div>
            <svg class="w-5 h-5 text-gray-400 transition-transform" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
            </svg>
        </button>
        
        <div x-show="open" x-transition class="px-6 pb-6">
            <div class="space-y-3">
                <div class="flex justify-between items-center py-3 border-b border-gray-100">
                    <div>
                        <div class="font-medium text-gray-900">Valentine's Day</div>
                        <div class="text-sm text-gray-600">February 9-14</div>
                        <div class="text-xs text-gray-500">No time off allowed</div>
                    </div>
                    <span class="px-3 py-1 bg-orange-100 text-orange-800 text-sm font-medium rounded-full">No Time Off</span>
                </div>
                
                <div class="flex justify-between items-center py-3 border-b border-gray-100">
                    <div>
                        <div class="font-medium text-gray-900">Easter</div>
                        <div class="text-sm text-gray-600">April 19-20</div>
                        <div class="text-xs text-gray-500">No time off allowed</div>
                    </div>
                    <span class="px-3 py-1 bg-orange-100 text-orange-800 text-sm font-medium rounded-full">No Time Off</span>
                </div>
                
                <div class="flex justify-between items-center py-3 border-b border-gray-100">
                    <div>
                        <div class="font-medium text-gray-900">Mother's Day</div>
                        <div class="text-sm text-gray-600">May 9-10</div>
                        <div class="text-xs text-gray-500">No time off allowed (excluding mothers on May 10)</div>
                    </div>
                    <span class="px-3 py-1 bg-orange-100 text-orange-800 text-sm font-medium rounded-full">No Time Off</span>
                </div>
                
                <div class="flex justify-between items-center py-3 border-b border-gray-100">
                    <div>
                        <div class="font-medium text-gray-900">Memorial Day</div>
                        <div class="text-sm text-gray-600">May 24</div>
                        <div class="text-xs text-gray-500">No time off allowed</div>
                    </div>
                    <span class="px-3 py-1 bg-orange-100 text-orange-800 text-sm font-medium rounded-full">No Time Off</span>
                </div>
                
                <div class="flex justify-between items-center py-3 border-b border-gray-100">
                    <div>
                        <div class="font-medium text-gray-900">Prom and Graduation</div>
                        <div class="text-sm text-gray-600">May-June</div>
                        <div class="text-xs text-gray-500">Exact dates from Nassau location TSM Weekly & Hillsborough Guidance</div>
                    </div>
                    <span class="px-3 py-1 bg-orange-100 text-orange-800 text-sm font-medium rounded-full">No Time Off</span>
                </div>
                
                <div class="flex justify-between items-center py-3 border-b border-gray-100">
                    <div>
                        <div class="font-medium text-gray-900">Independence Day</div>
                        <div class="text-sm text-gray-600">June 27-July 2</div>
                        <div class="text-xs text-gray-500">No time off allowed</div>
                    </div>
                    <span class="px-3 py-1 bg-orange-100 text-orange-800 text-sm font-medium rounded-full">No Time Off</span>
                </div>
                
                <div class="flex justify-between items-center py-3 border-b border-gray-100">
                    <div>
                        <div class="font-medium text-gray-900">Back-to-School</div>
                        <div class="text-sm text-gray-600">August 7-12</div>
                        <div class="text-xs text-gray-500">No time off allowed</div>
                    </div>
                    <span class="px-3 py-1 bg-orange-100 text-orange-800 text-sm font-medium rounded-full">No Time Off</span>
                </div>
                
                <div class="flex justify-between items-center py-3 border-b border-gray-100">
                    <div>
                        <div class="font-medium text-gray-900">Halloween</div>
                        <div class="text-sm text-gray-600">October 26-29</div>
                        <div class="text-xs text-gray-500">No time off allowed</div>
                    </div>
                    <span class="px-3 py-1 bg-orange-100 text-orange-800 text-sm font-medium rounded-full">No Time Off</span>
                </div>
                
                <div class="flex justify-between items-center py-3">
                    <div>
                        <div class="font-medium text-gray-900">Thanksgiving & Christmas</div>
                        <div class="text-sm text-gray-600">November 1-December 31</div>
                        <div class="text-xs text-gray-500">No time off allowed</div>
                    </div>
                    <span class="px-3 py-1 bg-orange-100 text-orange-800 text-sm font-medium rounded-full">No Time Off</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Seize the Season Periods Section -->
    <div class="bg-white rounded-lg border mb-6" x-data="{ open: true }">
        <button @click="open = !open" class="w-full px-6 py-4 flex items-center justify-between text-left hover:bg-gray-50">
            <div class="flex items-center gap-3">
                <div class="w-6 h-6 bg-yellow-100 rounded-full flex items-center justify-center">
                    <span class="text-yellow-600 text-xs font-bold">3</span>
                </div>
                <h3 class="text-lg font-semibold text-gray-900">Seize the Season Periods</h3>
            </div>
            <svg class="w-5 h-5 text-gray-400 transition-transform" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
            </svg>
        </button>
        
        <div x-show="open" x-transition class="px-6 pb-6">
            <div class="space-y-3">
                <div class="flex justify-between items-center py-3 border-b border-gray-100">
                    <div>
                        <div class="font-medium text-gray-900">Thanksgiving</div>
                        <div class="text-sm text-gray-600">November 12-26, 2025</div>
                        <div class="text-xs text-gray-500">High-volume sales week</div>
                    </div>
                    <span class="px-3 py-1 bg-yellow-100 text-yellow-800 text-sm font-medium rounded-full">High Volume</span>
                </div>
                
                <div class="flex justify-between items-center py-3 border-b border-gray-100">
                    <div>
                        <div class="font-medium text-gray-900">Christmas</div>
                        <div class="text-sm text-gray-600">December 7-23, 2025</div>
                        <div class="text-xs text-gray-500">High-volume sales week</div>
                    </div>
                    <span class="px-3 py-1 bg-yellow-100 text-yellow-800 text-sm font-medium rounded-full">High Volume</span>
                </div>
                
                <div class="flex justify-between items-center py-3">
                    <div>
                        <div class="font-medium text-gray-900">New Year</div>
                        <div class="text-sm text-gray-600">December 26-31, 2025</div>
                        <div class="text-xs text-gray-500">High-volume sales week</div>
                    </div>
                    <span class="px-3 py-1 bg-yellow-100 text-yellow-800 text-sm font-medium rounded-full">High Volume</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Saturday Extended Hours Section -->
    <div class="bg-white rounded-lg border mb-6" x-data="{ open: true }">
        <button @click="open = !open" class="w-full px-6 py-4 flex items-center justify-between text-left hover:bg-gray-50">
            <div class="flex items-center gap-3">
                <div class="w-6 h-6 bg-green-100 rounded-full flex items-center justify-center">
                    <span class="text-green-600 text-xs font-bold">4</span>
                </div>
                <h3 class="text-lg font-semibold text-gray-900">Saturday Extended Hours</h3>
            </div>
            <svg class="w-5 h-5 text-gray-400 transition-transform" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
            </svg>
        </button>
        
        <div x-show="open" x-transition class="px-6 pb-6">
            <div class="space-y-3">
                <div class="flex justify-between items-center py-3 border-b border-gray-100">
                    <div>
                        <div class="font-medium text-gray-900">Valentine's Day</div>
                        <div class="text-sm text-gray-600">Saturday, February 8, 2025</div>
                        <div class="text-xs text-gray-500">9 AM-7 PM</div>
                    </div>
                    <span class="px-3 py-1 bg-green-100 text-green-800 text-sm font-medium rounded-full">Extended Hours</span>
                </div>
                
                <div class="flex justify-between items-center py-3 border-b border-gray-100">
                    <div>
                        <div class="font-medium text-gray-900">Easter</div>
                        <div class="text-sm text-gray-600">Saturday, April 19, 2025</div>
                        <div class="text-xs text-gray-500">9 AM-7 PM</div>
                    </div>
                    <span class="px-3 py-1 bg-green-100 text-green-800 text-sm font-medium rounded-full">Extended Hours</span>
                </div>
                
                <div class="flex justify-between items-center py-3 border-b border-gray-100">
                    <div>
                        <div class="font-medium text-gray-900">Thanksgiving</div>
                        <div class="text-sm text-gray-600">Saturdays, November 8, 15, and 22, 2025</div>
                        <div class="text-xs text-gray-500">9 AM-7 PM</div>
                    </div>
                    <span class="px-3 py-1 bg-green-100 text-green-800 text-sm font-medium rounded-full">Extended Hours</span>
                </div>
                
                <div class="flex justify-between items-center py-3">
                    <div>
                        <div class="font-medium text-gray-900">Christmas</div>
                        <div class="text-sm text-gray-600">Saturdays, December 6, 13, and 20, 2025</div>
                        <div class="text-xs text-gray-500">9 AM-7 PM</div>
                    </div>
                    <span class="px-3 py-1 bg-green-100 text-green-800 text-sm font-medium rounded-full">Extended Hours</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer Note -->
    <div class="mt-6 text-center">
        <p class="text-xs text-gray-500">
            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            Please review all dates carefully and plan time off requests accordingly
        </p>
    </div>
    <?php
    return ob_get_clean();
}