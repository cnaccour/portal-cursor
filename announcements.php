<?php
// Note: No auth required - announcements are public
require __DIR__.'/includes/header.php';

// Load all announcements (static + dynamic)
$allAnnouncements = include __DIR__.'/includes/all-announcements.php';

// Filter out expired announcements
$activeAnnouncements = array_filter($allAnnouncements, function($announcement) {
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

<div x-data="{ 
    selectedCategory: 'all', 
    searchTerm: '',
    modalOpen: false,
    selectedAnnouncement: null,
    openModal(announcement) {
        this.selectedAnnouncement = announcement;
        this.modalOpen = true;
    },
    closeModal() {
        this.modalOpen = false;
        this.selectedAnnouncement = null;
    }
}">

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
               x-model="searchTerm"
               placeholder="Search announcements..."
               class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
    </div>
    
    <!-- Filter Dropdown -->
    <div class="relative">
        <button class="flex items-center gap-2 px-4 py-3 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707v4.586a1 1 0 01-.293.707l-2 2A1 1 0 0110 21v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
            </svg>
            <span x-text="selectedCategory === 'all' ? 'All Types' : selectedCategory.charAt(0).toUpperCase() + selectedCategory.slice(1)"></span>
            <span class="text-gray-500 text-sm">
                <span x-text="document.querySelectorAll('.announcement-item:not([style*=\"display: none\"])').length"></span> Total
            </span>
        </button>
    </div>
</div>

<!-- Category Filter Buttons -->
<div class="flex gap-2 mb-6 overflow-x-auto">
    <button @click="selectedCategory = 'all'"
            :class="selectedCategory === 'all' ? 'bg-gray-900 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
            class="px-4 py-2 rounded-lg text-sm font-medium transition-colors whitespace-nowrap">
        All
    </button>
    <?php foreach ($categories as $category): ?>
    <button @click="selectedCategory = '<?= strtolower($category) ?>'"
            :class="selectedCategory === '<?= strtolower($category) ?>' ? 'bg-gray-900 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
            class="px-4 py-2 rounded-lg text-sm font-medium transition-colors whitespace-nowrap capitalize">
        <?= htmlspecialchars($category) ?>
    </button>
    <?php endforeach; ?>
</div>

<?php if (empty($activeAnnouncements)): ?>
    <div class="bg-white rounded-xl border p-8 text-center">
        <p class="text-gray-500">No announcements at this time.</p>
    </div>
<?php else: ?>
    <!-- Announcements List -->
    <div class="space-y-4">
        <?php foreach ($activeAnnouncements as $announcement): ?>
        <div class="bg-white rounded-lg border border-gray-200 hover:border-gray-300 transition-colors announcement-item cursor-pointer"
             data-category="<?= strtolower($announcement['category']) ?>"
             data-search="<?= strtolower($announcement['title'] . ' ' . $announcement['content']) ?>"
             x-show="(selectedCategory === 'all' || selectedCategory === $el.dataset.category) && (searchTerm === '' || $el.dataset.search.includes(searchTerm.toLowerCase()))"
             @click="openModal(<?= htmlspecialchars(json_encode($announcement), ENT_QUOTES) ?>)"
             style="display: block">
            <div class="p-6">
                <div class="flex items-start gap-4">
                    <!-- Pin Icon (only for pinned announcements) -->
                    <?php if ($announcement['pinned']): ?>
                    <div class="flex-shrink-0 mt-1">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"></path>
                        </svg>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Content -->
                    <div class="flex-1 min-w-0">
                        <!-- Title -->
                        <h3 class="text-lg font-semibold text-gray-900 mb-2"><?= htmlspecialchars($announcement['title']) ?></h3>
                        
                        <!-- Description (Excerpt) -->
                        <div class="text-gray-600 text-sm leading-relaxed mb-4">
                            <?php 
                            // For excerpts, strip HTML tags and show plain text
                            $plainContent = strip_tags($announcement['content']);
                            $excerpt = strlen($plainContent) > 150 
                                ? substr($plainContent, 0, 150) . '...' 
                                : $plainContent;
                            echo htmlspecialchars($excerpt);
                            ?>
                        </div>
                        
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
                        
                        <!-- Attachment Indicator -->
                        <?php if (!empty($announcement['attachments']) && count($announcement['attachments']) > 0): ?>
                            <div class="flex items-center gap-1 px-2 py-1 bg-blue-100 text-blue-800 text-xs font-medium rounded" title="<?= count($announcement['attachments']) ?> attachment(s)">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path>
                                </svg>
                                <span><?= count($announcement['attachments']) ?></span>
                            </div>
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
<?php endif; ?>

<!-- Modal for Full Announcement -->
<div x-show="modalOpen" 
     class="fixed inset-0 z-50 overflow-y-auto"
     style="display: none;">
    
    <!-- Background overlay -->
    <div class="fixed inset-0 bg-black bg-opacity-50" @click="closeModal()"></div>
    
    <!-- Modal panel -->
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto relative z-10">
            
            <!-- Modal Header -->
            <div class="flex items-center justify-between p-6 border-b border-gray-200">
                <div class="flex items-center gap-3">
                    <!-- Pin Icon -->
                    <template x-if="selectedAnnouncement && selectedAnnouncement.pinned">
                        <svg class="w-5 h-5 text-red-500" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/>
                        </svg>
                    </template>
                    
                    <!-- Title -->
                    <h2 class="text-xl font-semibold text-gray-900" x-text="selectedAnnouncement?.title"></h2>
                </div>
                
                <!-- Close button -->
                <button @click="closeModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <!-- Modal Body -->
            <div class="p-6">
                <!-- Announcement metadata -->
                <div class="flex items-center gap-4 mb-4 text-sm text-gray-500">
                    <!-- Date -->
                    <div class="flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span x-text="selectedAnnouncement && new Date(selectedAnnouncement.date_created).toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' })"></span>
                    </div>
                    
                    <!-- Category -->
                    <span class="px-2 py-1 bg-gray-100 text-gray-700 text-xs font-medium rounded capitalize" x-text="selectedAnnouncement?.category"></span>
                    
                    <!-- Priority -->
                    <template x-if="selectedAnnouncement && selectedAnnouncement.pinned">
                        <span class="px-2 py-1 bg-red-100 text-red-800 text-xs font-medium rounded">High Priority</span>
                    </template>
                </div>
                
                <!-- Full content -->
                <div class="prose prose-sm max-w-none">
                    <div class="text-gray-700 leading-relaxed" x-html="selectedAnnouncement?.content"></div>
                </div>
                
                <!-- Expiration notice if applicable -->
                <template x-if="selectedAnnouncement && selectedAnnouncement.expiration_date">
                    <div class="mt-4 p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                        <p class="text-yellow-800 text-sm">
                            <strong>Note:</strong> This announcement expires on 
                            <span x-text="new Date(selectedAnnouncement.expiration_date).toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' })"></span>
                        </p>
                    </div>
                </template>
            </div>
            
            <!-- Modal Footer -->
            <div class="px-6 py-4 border-t border-gray-200 bg-gray-50 rounded-b-lg">
                <button @click="closeModal()" 
                        class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

</div>

<?php require __DIR__.'/includes/footer.php'; ?>