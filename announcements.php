<?php
// Note: No auth required - announcements are public
require __DIR__.'/includes/header.php';

// Load static announcements
$staticAnnouncements = include __DIR__.'/includes/static-announcements.php';

// Filter out expired announcements
$activeAnnouncements = array_filter($staticAnnouncements, function($announcement) {
    return empty($announcement['expiration_date']) || strtotime($announcement['expiration_date']) > time();
});

// Sort announcements: pinned first, then by date (most recent first)
usort($activeAnnouncements, function($a, $b) {
    // Pinned announcements come first
    if ($a['pinned'] !== $b['pinned']) {
        return $b['pinned'] <=> $a['pinned'];
    }
    // Then sort by date (newest first)
    return strtotime($b['date_created']) <=> strtotime($a['date_created']);
});

// Get unique categories
$categories = array_unique(array_column($activeAnnouncements, 'category'));
sort($categories);
?>

<!-- Search and Filter Header -->
<div class="flex gap-4 mb-6">
    <!-- Search Bar -->
    <div class="flex-1 relative">
        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
        </div>
        <input type="text" 
               placeholder="Search announcements..."
               class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
    </div>
    
    <!-- Filter Dropdown -->
    <div class="relative">
        <button class="flex items-center gap-2 px-4 py-3 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707v4.586a1 1 0 01-.293.707l-2 2A1 1 0 0110 21v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
            </svg>
            <span>All Types</span>
            <span class="text-gray-500 text-sm"><?= count($activeAnnouncements) ?> Total</span>
        </button>
    </div>
</div>

<!-- Category Filter Buttons -->
<div class="flex gap-2 mb-6 overflow-x-auto">
    <button class="px-4 py-2 bg-gray-900 text-white rounded-lg text-sm font-medium transition-colors whitespace-nowrap">
        All
    </button>
    <button class="px-4 py-2 bg-gray-100 text-gray-700 hover:bg-gray-200 rounded-lg text-sm font-medium transition-colors whitespace-nowrap">
        General
    </button>
    <button class="px-4 py-2 bg-gray-100 text-gray-700 hover:bg-gray-200 rounded-lg text-sm font-medium transition-colors whitespace-nowrap">
        Training
    </button>
    <button class="px-4 py-2 bg-gray-100 text-gray-700 hover:bg-gray-200 rounded-lg text-sm font-medium transition-colors whitespace-nowrap">
        Schedule
    </button>
    <button class="px-4 py-2 bg-gray-100 text-gray-700 hover:bg-gray-200 rounded-lg text-sm font-medium transition-colors whitespace-nowrap">
        Policy
    </button>
    <button class="px-4 py-2 bg-gray-100 text-gray-700 hover:bg-gray-200 rounded-lg text-sm font-medium transition-colors whitespace-nowrap">
        Events
    </button>
    <button class="px-4 py-2 bg-gray-100 text-gray-700 hover:bg-gray-200 rounded-lg text-sm font-medium transition-colors whitespace-nowrap">
        Safety
    </button>
</div>

<?php if (empty($activeAnnouncements)): ?>
    <div class="bg-white rounded-xl border p-8 text-center">
        <p class="text-gray-500">No announcements at this time.</p>
    </div>
<?php else: ?>
    <!-- Announcements List -->
    <div class="space-y-4">
        <?php foreach ($activeAnnouncements as $announcement): ?>
        <div class="bg-white rounded-lg border border-gray-200 hover:border-gray-300 transition-colors announcement-item"
             data-category="<?= htmlspecialchars($announcement['category']) ?>"
             data-title="<?= htmlspecialchars($announcement['title']) ?>"
             data-content="<?= htmlspecialchars($announcement['content']) ?>"
             x-show="(selectedCategory === 'all' || selectedCategory === '<?= htmlspecialchars($announcement['category']) ?>') && 
                     (searchTerm === '' || 
                      '<?= addslashes($announcement['title']) ?>'.toLowerCase().includes(searchTerm.toLowerCase()) || 
                      '<?= addslashes($announcement['content']) ?>'.toLowerCase().includes(searchTerm.toLowerCase()))">
            <div class="p-6">
                <div class="flex items-start gap-4">
                    <!-- Pin Icon -->
                    <div class="flex-shrink-0 mt-1">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"></path>
                        </svg>
                    </div>
                    
                    <!-- Content -->
                    <div class="flex-1 min-w-0">
                        <!-- Title -->
                        <h3 class="text-lg font-semibold text-gray-900 mb-2"><?= htmlspecialchars($announcement['title']) ?></h3>
                        
                        <!-- Description -->
                        <p class="text-gray-600 text-sm leading-relaxed mb-4"><?= htmlspecialchars($announcement['content']) ?></p>
                        
                        <!-- Date -->
                        <div class="flex items-center text-xs text-gray-500">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span><?= date('M j, Y', strtotime($announcement['date_created'])) ?></span>
                        </div>
                    </div>
                    
                    <!-- Right Side Tags and Arrow -->
                    <div class="flex items-center gap-2">
                        <!-- Priority Badge -->
                        <?php if ($announcement['pinned']): ?>
                            <span class="px-2 py-1 bg-red-100 text-red-800 text-xs font-medium rounded">High</span>
                        <?php endif; ?>
                        
                        <!-- Category Tag -->
                        <span class="px-2 py-1 bg-gray-100 text-gray-700 text-xs font-medium rounded capitalize"><?= htmlspecialchars($announcement['category']) ?></span>
                        
                        <!-- Arrow -->
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

</div>
<?php endif; ?>

<?php require __DIR__.'/includes/footer.php'; ?>