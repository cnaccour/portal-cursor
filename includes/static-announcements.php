<?php
/**
 * Static Announcements Configuration
 * These announcements are managed by developers and cannot be edited through the web interface
 */

return [
    [
        'id' => 'static-education-2025',
        'title' => 'In House Education Schedule 2025',
        'content' => generateEducationScheduleHtml(),
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
        ['date' => '08/19/25', 'session' => 'JJS Business Retention', 'time' => '9:00 AM - 11:00 PM', 'instructor' => 'Mikayla Santulo', 'status' => 'completed'],
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