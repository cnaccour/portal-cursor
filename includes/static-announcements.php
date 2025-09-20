<?php
/**
 * Static Announcements Configuration
 * These announcements are managed by developers and cannot be edited through the web interface
 * Currently empty - all announcements are managed dynamically through the admin interface
 */

return [
    // Static Education Schedule Announcement
    [
        'id' => 'static-education-2025',
        'title' => 'In House Education Schedule 2025',
        'content' => '<education-schedule-2025></education-schedule-2025>', // Special tag for custom rendering
        'category' => 'training',
        'author' => 'J. Joseph Salon',
        'date_created' => '2025-01-01',
        'date_modified' => date('Y-m-d H:i:s'),
        'pinned' => true,
        'priority' => 1,
        'expiration_date' => null,
        'is_static' => true, // Mark as uneditable
        'attachments' => [],
        'education_data' => [
            'description' => 'Complete 2025 education schedule with monthly training sessions on social media, styling techniques, product knowledge, and business development.',
            'sessions' => [
                ['date' => '2025-01-14', 'time' => '9:00 AM - 11:00 AM', 'instructor' => 'Annalie Wingate', 'topic' => 'Social Media', 'status_date' => '2025-01-14'],
                ['date' => '2025-01-21', 'time' => '9:00 AM - 11:00 AM', 'instructor' => 'Angelica Samanchik', 'topic' => 'Intro to Vivids', 'status_date' => '2025-01-21'],
                ['date' => '2025-02-04', 'time' => '9:00 AM - 11:00 AM', 'instructor' => 'Christina Flynn', 'topic' => 'Pre Keratin Smoothing Certification', 'status_date' => '2025-02-04'],
                ['date' => '2025-02-18', 'time' => '9:00 AM - 11:00 AM', 'instructor' => 'TBA', 'topic' => 'Product Knowledge', 'status_date' => '2025-02-18'],
                ['date' => '2025-03-04', 'time' => '9:00 AM - 11:00 AM', 'instructor' => 'Phoebe Vecchione/Natalie Santos', 'topic' => 'Shampoo Massage/curly styling', 'status_date' => '2025-03-04'],
                ['date' => '2025-03-18', 'time' => '9:00 AM - 11:00 AM', 'instructor' => 'Jekarerma F.', 'topic' => 'Updo How To', 'status_date' => '2025-03-18'],
                ['date' => '2025-04-01', 'time' => '9:00 AM - 11:00 AM', 'instructor' => 'Mike Wilkins', 'topic' => 'Consultation', 'status_date' => '2025-04-01'],
                ['date' => '2025-04-15', 'time' => '9:00 AM - 11:00 AM', 'instructor' => 'Mikayla Santullo', 'topic' => 'JJS Business / Retention', 'status_date' => '2025-04-15'],
                ['date' => '2025-04-29', 'time' => '9:00 AM - 12:00 PM', 'instructor' => 'Annalie Wingate', 'topic' => 'Social Media', 'status_date' => '2025-04-29'],
                ['date' => '2025-05-13', 'time' => '9:00 AM - 11:00 AM', 'instructor' => 'Angelica Samanchik', 'topic' => 'Intro to Vivids', 'status_date' => '2025-05-13'],
                ['date' => '2025-05-27', 'time' => '9:00 AM - 11:00 AM', 'instructor' => 'Christina Flynn', 'topic' => 'Pre Keratin Smoothing Certification', 'status_date' => '2025-05-27'],
                ['date' => '2025-06-10', 'time' => '9:00 AM - 11:00 AM', 'instructor' => 'TBA', 'topic' => 'Product Knowledge', 'status_date' => '2025-06-10'],
                ['date' => '2025-06-24', 'time' => '9:00 AM - 11:00 AM', 'instructor' => 'Phoebe Vecchione/Natalie Santos', 'topic' => 'Shampoo Massage/curly styling', 'status_date' => '2025-06-24'],
                ['date' => '2025-07-08', 'time' => '9:00 AM - 11:00 AM', 'instructor' => 'Jekarerma F.', 'topic' => 'Updo How To', 'status_date' => '2025-07-08'],
                ['date' => '2025-07-22', 'time' => '9:00 AM - 11:00 AM', 'instructor' => 'Mike Wilkins', 'topic' => 'Consultation', 'status_date' => '2025-07-22'],
                ['date' => '2025-08-05', 'time' => '9:00 AM - 11:00 AM', 'instructor' => 'Annalie Wingate', 'topic' => 'Social Media', 'status_date' => '2025-08-05'],
                ['date' => '2025-08-19', 'time' => '9:00 AM - 11:00 AM', 'instructor' => 'Mikayla Santullo', 'topic' => 'JJS Business Retention', 'status_date' => '2025-08-19'],
                ['date' => '2025-09-02', 'time' => '9:00 AM - 11:00 AM', 'instructor' => 'Angelica Samanchik', 'topic' => 'Intro to Vivids', 'status_date' => '2025-09-02'],
                ['date' => '2025-09-16', 'time' => '9:00 AM - 11:00 AM', 'instructor' => 'Christina Flynn', 'topic' => 'Pre Keratin Smoothing Certification', 'status_date' => '2025-09-16'],
                ['date' => '2025-09-30', 'time' => '9:00 AM - 11:00 AM', 'instructor' => 'TBA', 'topic' => 'Product Knowledge', 'status_date' => '2025-09-30'],
                ['date' => '2025-10-14', 'time' => '9:00 AM - 11:00 AM', 'instructor' => 'Phoebe Vecchione/Natalie Santos', 'topic' => 'Shampoo Massage/curly styling', 'status_date' => '2025-10-14'],
                ['date' => '2025-10-28', 'time' => '9:00 AM - 11:00 AM', 'instructor' => 'Jekarerma F.', 'topic' => 'Updo How To', 'status_date' => '2025-10-28'],
                ['date' => '2025-11-11', 'time' => '9:00 AM - 11:00 AM', 'instructor' => 'Mike Wilkins', 'topic' => 'Consultation', 'status_date' => '2025-11-11'],
                ['date' => '2025-11-25', 'time' => '9:00 AM - 11:00 AM', 'instructor' => 'Mikayla Santullo', 'topic' => 'JJS Business / Retention', 'status_date' => '2025-11-25']
            ]
        ]
    ]
];