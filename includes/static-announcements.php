<?php
/**
 * Static Announcements Configuration
 * These announcements are managed by developers and cannot be edited through the web interface
 */

return [
    [
        'id' => 'demo-announcement-001',
        'title' => 'Welcome to the J. Joseph Salon Portal',
        'content' => 'This is a demo announcement to showcase the new announcement system. You can view important updates, policy changes, and salon news here. This system supports rich formatting and categorization for better organization.',
        'category' => 'general',
        'author' => 'Management',
        'date_created' => '2024-01-15',
        'expiration_date' => null, // null means no expiration
        'location_specific' => false, // false means salon-wide
        'pinned' => true,
        'priority' => 1
    ],
    [
        'id' => 'demo-announcement-002', 
        'title' => 'New Shift Reporting System Active',
        'content' => 'The new digital shift reporting system is now live! All staff can submit their morning and evening shift reports through this portal. Please ensure you complete your reports at the end of each shift.',
        'category' => 'system',
        'author' => 'IT Department',
        'date_created' => '2024-01-10',
        'expiration_date' => null, // Changed to not expire so filtering shows
        'location_specific' => false,
        'pinned' => false,
        'priority' => 2
    ]
];