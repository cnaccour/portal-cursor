<?php
/**
 * Static Announcements Configuration
 * These announcements are managed by developers and cannot be edited through the web interface
 */

return [
    [
        'id' => 'demo-announcement-001',
        'title' => 'In House Education Schedule 2025',
        'content' => 'Complete 2025 education schedule with monthly training sessions on social media, styling techniques, product knowledge, and business development. Sessions run from January through November with expert instructors.',
        'category' => 'Training',
        'author' => 'Management',
        'date_created' => '2025-01-01',
        'expiration_date' => null,
        'location_specific' => false,
        'pinned' => true,
        'priority' => 1
    ],
    [
        'id' => 'demo-announcement-002', 
        'title' => 'Blackout Dates & Holidays 2025',
        'content' => 'Complete 2025 calendar of holiday closures, blocked-out dates for time off requests, seize the season periods, and extended Saturday hours. Please review all important dates and plan accordingly.',
        'category' => 'Policy',
        'author' => 'HR Department',
        'date_created' => '2025-01-03',
        'expiration_date' => null,
        'location_specific' => false,
        'pinned' => true,
        'priority' => 2
    ],
    [
        'id' => 'demo-announcement-003',
        'title' => 'New Shift Reporting System Active',
        'content' => 'The new digital shift reporting system is now live! All staff can submit their morning and evening shift reports through this portal. Please ensure you complete your reports at the end of each shift.',
        'category' => 'General',
        'author' => 'IT Department',
        'date_created' => '2024-12-15',
        'expiration_date' => null,
        'location_specific' => false,
        'pinned' => false,
        'priority' => 3
    ]
];