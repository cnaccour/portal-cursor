<?php
/**
 * Announcement Helper Functions
 * Common utilities for working with announcements
 */

/**
 * Sort announcements with pinned first, then by date (most recent first)
 * @param array $announcements Array of announcements to sort
 * @return array Sorted announcements
 */
function sortAnnouncementsByPriorityAndDate(array $announcements): array
{
    usort($announcements, function($a, $b) {
        if ($a['pinned'] !== $b['pinned']) {
            return $b['pinned'] <=> $a['pinned'];
        }
        return strtotime($b['date_created']) <=> strtotime($a['date_created']);
    });
    
    return $announcements;
}

/**
 * Get unique categories from announcements
 * @param array $announcements Array of announcements
 * @return array Sorted array of unique categories
 */
function getAnnouncementCategories(array $announcements): array
{
    $categories = array_unique(array_column($announcements, 'category'));
    sort($categories);
    return $categories;
}

/**
 * Filter announcements by category
 * @param array $announcements Array of announcements
 * @param string $category Category to filter by
 * @return array Filtered announcements
 */
function filterAnnouncementsByCategory(array $announcements, string $category): array
{
    return array_filter($announcements, function($announcement) use ($category) {
        return $announcement['category'] === $category;
    });
}

/**
 * Filter out expired announcements
 * @param array $announcements Array of announcements
 * @return array Non-expired announcements
 */
function filterActiveAnnouncements(array $announcements): array
{
    $today = date('Y-m-d');
    
    return array_filter($announcements, function($announcement) use ($today) {
        return empty($announcement['expiration_date']) || 
               $announcement['expiration_date'] >= $today;
    });
}

/**
 * Get announcement by ID
 * @param array $announcements Array of announcements
 * @param string $id Announcement ID to find
 * @return array|null Found announcement or null
 */
function findAnnouncementById(array $announcements, string $id): ?array
{
    foreach ($announcements as $announcement) {
        if ($announcement['id'] === $id) {
            return $announcement;
        }
    }
    return null;
}

/**
 * Load all announcements (static + dynamic) with error handling
 * @return array Combined announcements array
 */
function loadAllAnnouncements(): array
{
    try {
        // Load static announcements
        $staticAnnouncements = include __DIR__.'/static-announcements.php';
        
        // Load dynamic announcements with better error handling
        $dynamicAnnouncements = [];
        $dynamicFile = __DIR__.'/../storage/dynamic-announcements.json';
        
        if (file_exists($dynamicFile)) {
            $content = file_get_contents($dynamicFile);
            if ($content !== false) {
                $decoded = json_decode($content, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $dynamicAnnouncements = $decoded;
                }
            }
        }
        
        // Load attachment information for dynamic announcements
        foreach ($dynamicAnnouncements as &$announcement) {
            $announcement['attachments'] = loadAnnouncementAttachments($announcement['id']);
        }
        unset($announcement);
        
        return array_merge($staticAnnouncements ?: [], $dynamicAnnouncements);
        
    } catch (Exception $e) {
        // Log error in production, return empty array for now
        error_log("Error loading announcements: " . $e->getMessage());
        return [];
    }
}

/**
 * Load attachment information for an announcement
 * @param string $announcementId The announcement ID
 * @return array Array of attachment information
 */
function loadAnnouncementAttachments(string $announcementId): array
{
    $attachments = [];
    $attachmentDir = __DIR__.'/../attached_assets/announcements/'.preg_replace('/[^a-zA-Z0-9-_]/', '', $announcementId);
    
    if (is_dir($attachmentDir)) {
        $files = scandir($attachmentDir);
        foreach ($files as $filename) {
            if ($filename !== '.' && $filename !== '..' && is_file($attachmentDir.'/'.$filename)) {
                // Extract original filename from stored format (timestamp_originalname)
                $parts = explode('_', $filename, 2);
                $originalName = isset($parts[1]) ? $parts[1] : $filename;
                $filePath = $attachmentDir.'/'.$filename;
                
                $attachments[] = [
                    'filename' => $filename,
                    'original_name' => $originalName,
                    'file_size' => filesize($filePath),
                    'mime_type' => mime_content_type($filePath),
                    'upload_date' => date('Y-m-d H:i:s', filemtime($filePath))
                ];
            }
        }
    }
    
    return $attachments;
}

/**
 * Get announcements formatted for display (sorted and filtered)
 * @param bool $includeExpired Whether to include expired announcements
 * @param string|null $categoryFilter Optional category filter
 * @return array Formatted announcements ready for display
 */
function getDisplayAnnouncements(bool $includeExpired = false, ?string $categoryFilter = null): array
{
    $announcements = loadAllAnnouncements();
    
    if (!$includeExpired) {
        $announcements = filterActiveAnnouncements($announcements);
    }
    
    if ($categoryFilter) {
        $announcements = filterAnnouncementsByCategory($announcements, $categoryFilter);
    }
    
    return sortAnnouncementsByPriorityAndDate($announcements);
}
?>