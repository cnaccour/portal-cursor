<?php
/**
 * Combined Announcements Loader
 * Loads both static and dynamic announcements
 */

// Load static announcements
$staticAnnouncements = include __DIR__.'/static-announcements.php';

// Load dynamic announcements (outside web root for security)
$dynamicAnnouncements = [];
$dynamicFile = __DIR__.'/../../storage/dynamic-announcements.json';
if (file_exists($dynamicFile)) {
    $content = file_get_contents($dynamicFile);
    if ($content) {
        $dynamicAnnouncements = json_decode($content, true) ?: [];
    }
}

// Combine both arrays
$allAnnouncements = array_merge($staticAnnouncements, $dynamicAnnouncements);

return $allAnnouncements;