<?php
/**
 * ICS Calendar File Generator for Education Schedule
 */

// Start session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// No login required - this is a public feature for the education schedule
// Accept both GET and POST for flexibility
$session = $_POST['session'] ?? $_GET['session'] ?? null;
$exportAll = $_POST['export_all'] ?? $_GET['export_all'] ?? null;

if (!$session && !$exportAll) {
    http_response_code(400);
    echo 'Invalid request';
    exit;
}

// Load the education schedule data
require_once __DIR__.'/../includes/announcement-helpers.php';
$announcements = loadAllAnnouncements();
$educationAnnouncement = null;

foreach ($announcements as $announcement) {
    if ($announcement['id'] === 'static-education-2025') {
        $educationAnnouncement = $announcement;
        break;
    }
}

if (!$educationAnnouncement || !isset($educationAnnouncement['education_data'])) {
    http_response_code(404);
    echo 'Education schedule not found';
    exit;
}

function generateICS($sessions) {
    $ics = "BEGIN:VCALENDAR\r\n";
    $ics .= "VERSION:2.0\r\n";
    $ics .= "PRODID:-//J. Joseph Salon//Education Schedule 2025//EN\r\n";
    $ics .= "CALSCALE:GREGORIAN\r\n";
    $ics .= "METHOD:PUBLISH\r\n";
    $ics .= "X-WR-CALNAME:JJS Education Schedule 2025\r\n";
    $ics .= "X-WR-TIMEZONE:America/New_York\r\n";
    
    foreach ($sessions as $session) {
        // Parse date and time
        $date = str_replace('-', '', $session['date']); // Convert to YYYYMMDD
        
        // Parse time range
        $timeParts = explode(' - ', $session['time']);
        $startTime = date('His', strtotime($timeParts[0]));
        $endTime = isset($timeParts[1]) ? date('His', strtotime($timeParts[1])) : date('His', strtotime($timeParts[0]) + 7200); // Default 2 hours
        
        $uid = md5($session['date'] . $session['topic']) . '@jjosephsalon.com';
        
        $ics .= "BEGIN:VEVENT\r\n";
        $ics .= "UID:" . $uid . "\r\n";
        $ics .= "DTSTAMP:" . gmdate('Ymd\THis\Z') . "\r\n";
        $ics .= "DTSTART;TZID=America/New_York:" . $date . "T" . $startTime . "\r\n";
        $ics .= "DTEND;TZID=America/New_York:" . $date . "T" . $endTime . "\r\n";
        $ics .= "SUMMARY:JJS Training: " . $session['topic'] . "\r\n";
        $ics .= "DESCRIPTION:Instructor: " . $session['instructor'] . "\\n";
        $ics .= "Topic: " . $session['topic'] . "\\n";
        $ics .= "Time: " . $session['time'] . "\r\n";
        $ics .= "LOCATION:J. Joseph Salon\r\n";
        $ics .= "STATUS:CONFIRMED\r\n";
        $ics .= "END:VEVENT\r\n";
    }
    
    $ics .= "END:VCALENDAR\r\n";
    
    return $ics;
}

// Process the request
if ($exportAll) {
    // Export all sessions
    $sessions = $educationAnnouncement['education_data']['sessions'];
    $icsContent = generateICS($sessions);
    $filename = 'jjs-education-schedule-2025.ics';
} elseif ($session) {
    // Export single session
    $sessionData = json_decode($session, true);
    if (!$sessionData || !isset($sessionData['date']) || !isset($sessionData['topic'])) {
        http_response_code(400);
        echo 'Invalid session data';
        exit;
    }
    $icsContent = generateICS([$sessionData]);
    $filename = 'jjs-training-' . date('M-d', strtotime($sessionData['date'])) . '.ics';
} else {
    http_response_code(400);
    echo 'No session data provided';
    exit;
}

// Send ICS file
header('Content-Type: text/calendar; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Content-Length: ' . strlen($icsContent));
header('Cache-Control: must-revalidate');
header('Pragma: public');

echo $icsContent;
exit;