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

<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-semibold">Announcements</h1>
    <div class="text-sm text-gray-600">
        <?= count($activeAnnouncements) ?> active announcement<?= count($activeAnnouncements) !== 1 ? 's' : '' ?>
    </div>
</div>

<?php if (empty($activeAnnouncements)): ?>
    <div class="bg-white rounded-xl border p-8 text-center">
        <p class="text-gray-500">No announcements at this time.</p>
    </div>
<?php else: ?>
    <!-- Filter and Announcements Container -->
    <div <?= count($categories) > 1 ? 'x-data="{ selectedCategory: \'all\' }"' : '' ?>>
        <!-- Categories Filter (if more than one category) -->
        <?php if (count($categories) > 1): ?>
        <div class="mb-6">
            <div class="flex flex-wrap gap-2">
                <button @click="selectedCategory = 'all'" 
                        :class="selectedCategory === 'all' ? 'bg-black text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'"
                        class="px-3 py-1 rounded-lg text-sm font-medium transition-colors">
                    All Categories
                </button>
                <?php foreach ($categories as $category): ?>
                <button @click="selectedCategory = '<?= htmlspecialchars($category, ENT_QUOTES) ?>'" 
                        :class="selectedCategory === '<?= htmlspecialchars($category, ENT_QUOTES) ?>' ? 'bg-black text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'"
                        class="px-3 py-1 rounded-lg text-sm font-medium transition-colors capitalize">
                    <?= htmlspecialchars($category) ?>
                </button>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Announcements List -->
        <div class="space-y-4">
            <?php foreach ($activeAnnouncements as $announcement): ?>
            <div class="bg-white rounded-xl border transition-all duration-200 hover:border-gray-300 hover:shadow-sm
                        <?= $announcement['pinned'] ? 'border-l-4 border-l-yellow-400 shadow-sm' : '' ?>"
                 data-category="<?= htmlspecialchars($announcement['category']) ?>"
                 <?= count($categories) > 1 ? "x-show=\"selectedCategory === 'all' || selectedCategory === '" . htmlspecialchars($announcement['category'], ENT_QUOTES) . "'\"" : '' ?>>
                
                <div class="p-6">
                    <!-- Header -->
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex-grow">
                            <div class="flex items-center gap-3 mb-2">
                                <h3 class="text-lg font-semibold text-gray-900">
                                    <?= htmlspecialchars($announcement['title']) ?>
                                </h3>
                                
                                <?php if ($announcement['pinned']): ?>
                                <span class="inline-flex items-center px-2 py-1 text-xs font-medium bg-yellow-100 text-yellow-800 rounded-full">
                                    📌 Pinned
                                </span>
                                <?php endif; ?>
                                
                                <span class="inline-flex items-center px-2 py-1 text-xs font-medium bg-gray-100 text-gray-600 rounded-full capitalize">
                                    <?= htmlspecialchars($announcement['category']) ?>
                                </span>
                            </div>
                            
                            <div class="flex items-center text-sm text-gray-500 space-x-4">
                                <span><?= htmlspecialchars($announcement['author']) ?></span>
                                <span>•</span>
                                <span><?= date('M j, Y', strtotime($announcement['date_created'])) ?></span>
                                <?php if (!empty($announcement['expiration_date'])): ?>
                                <span>•</span>
                                <span class="text-orange-600">Expires <?= date('M j, Y', strtotime($announcement['expiration_date'])) ?></span>
                                <?php endif; ?>
                                <?php if ($announcement['location_specific']): ?>
                                <span>•</span>
                                <span class="text-blue-600">Location Specific</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Content -->
                    <div class="text-gray-700 leading-relaxed">
                        <?= nl2br(htmlspecialchars($announcement['content'])) ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>

<?php require __DIR__.'/includes/footer.php'; ?>