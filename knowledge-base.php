<?php
require __DIR__.'/includes/auth.php';
// Knowledge base is accessible to everyone - no login required

// Get published articles
$articles = [];
$categories = [];
try {
    require_once __DIR__.'/includes/db.php';
    
    $stmt = $pdo->query("SELECT * FROM kb_articles WHERE status = 'published' ORDER BY created_at DESC");
    $articles = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Extract unique categories
    foreach ($articles as $article) {
        if (!empty($article['category']) && !in_array($article['category'], $categories)) {
            $categories[] = $article['category'];
        }
    }
    sort($categories);
    
} catch (Exception $e) {
    error_log('Knowledge base error: ' . $e->getMessage());
    $error = 'Error loading articles';
}

require __DIR__.'/includes/header.php';
?>

<style>
.kb-card {
    transition: all 0.2s ease;
}
.kb-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.1);
}
.category-filter {
    transition: all 0.2s ease;
}
.category-filter:hover {
    background-color: #F3F4F6;
}
.category-filter.active {
    background-color: #1F2937;
    color: white;
}
.category-filter.active[data-category]:not([data-category="all"]) {
    background-color: #AF831A;
    color: white;
}
.search-input:focus {
    outline: none;
    border-color: #AF831A;
    box-shadow: 0 0 0 3px rgba(175, 131, 26, 0.1);
}
</style>

<div class="mb-8">
    <div class="flex items-center justify-between mb-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Knowledge Base</h1>
            <p class="text-gray-600 mt-2">Find guides, instructions, and resources for the team.</p>
        </div>
        
        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
            <div class="flex gap-3">
                <a href="admin-kb.php" 
                   class="inline-flex items-center gap-2 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    Manage KB
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php if (!empty($error)): ?>
    <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
        <div class="flex items-center gap-2">
            <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01"></path>
            </svg>
            <span class="text-red-800 font-medium"><?= htmlspecialchars($error) ?></span>
        </div>
    </div>
<?php endif; ?>

<!-- Search and Filter -->
<div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 mb-8">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Search -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Search Articles</label>
            <div class="relative">
                <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
                <input type="text" 
                       id="search-input" 
                       placeholder="Search for articles, topics, or keywords..."
                       class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg search-input">
            </div>
        </div>
        
        <!-- Category Filter -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Filter by Category</label>
            <div class="flex flex-wrap gap-2">
                <button class="category-filter active px-3 py-2 text-sm rounded-lg border border-gray-300" 
                        data-category="all">
                    All Categories
                </button>
                <?php foreach ($categories as $category): ?>
                    <button class="category-filter px-3 py-2 text-sm rounded-lg border border-gray-300" 
                            data-category="<?= htmlspecialchars($category) ?>">
                        <?= htmlspecialchars($category) ?>
                    </button>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<!-- Articles Grid -->
<?php if (empty($articles)): ?>
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-12 text-center">
        <svg class="w-16 h-16 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
        </svg>
        <h3 class="text-xl font-semibold text-gray-900 mb-2">No Articles Available</h3>
        <p class="text-gray-600 mb-6">Knowledge base articles will appear here once they are published.</p>
        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
            <a href="admin-kb.php" class="inline-flex items-center gap-2 px-6 py-2 bg-black text-white rounded-lg hover:bg-gray-800 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                Create First Article
            </a>
        <?php endif; ?>
    </div>
<?php else: ?>
    <div id="articles-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php foreach ($articles as $article): ?>
            <div class="kb-card bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden article-item" 
                 data-category="<?= htmlspecialchars($article['category'] ?? '') ?>"
                 data-search-text="<?= htmlspecialchars(strtolower($article['title'] . ' ' . ($article['category'] ?? '') . ' ' . strip_tags($article['content']))) ?>">
                
                <div class="p-6">
                    <!-- Category Badge -->
                    <?php if (!empty($article['category'])): ?>
                        <div class="mb-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $article['category'] === 'Setup Guides' ? 'text-white' : 'bg-blue-100 text-blue-800' ?>" <?= $article['category'] === 'Setup Guides' ? 'style="background-color: #AF831A;"' : '' ?>>
                                <?= htmlspecialchars($article['category']) ?>
                            </span>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Title -->
                    <h3 class="text-lg font-semibold text-gray-900 mb-3 line-clamp-2">
                        <?= htmlspecialchars($article['title']) ?>
                    </h3>
                    
                    <!-- Content Preview -->
                    <div class="text-sm text-gray-600 mb-4 line-clamp-3">
                        <?php
                        if ($article['slug'] === 'email-setup-instructions') {
                            echo 'Complete guide for setting up email accounts on iPhone, Android, and web browsers with IMAP and SMTP configuration details.';
                        } else {
                            $excerpt = strip_tags($article['content']);
                            echo htmlspecialchars(substr($excerpt, 0, 150)) . (strlen($excerpt) > 150 ? '...' : '');
                        }
                        ?>
                    </div>
                    
                    <!-- Tags -->
                    <?php if (!empty($article['tags'])): ?>
                        <?php 
                        $tags = json_decode($article['tags'], true) ?: []; 
                        if (!empty($tags)):
                        ?>
                            <div class="mb-4">
                                <div class="flex flex-wrap gap-1">
                                    <?php foreach (array_slice($tags, 0, 3) as $tag): ?>
                                        <span class="px-2 py-1 bg-gray-100 text-gray-700 text-xs rounded"><?= htmlspecialchars($tag) ?></span>
                                    <?php endforeach; ?>
                                    <?php if (count($tags) > 3): ?>
                                        <span class="px-2 py-1 bg-gray-100 text-gray-700 text-xs rounded">+<?= count($tags) - 3 ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                    
                    <!-- Footer -->
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2 text-xs text-gray-500">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <?= date('M j, Y', strtotime($article['created_at'])) ?>
                        </div>
                        
                        <a href="kb-article.php?slug=<?= urlencode($article['slug']) ?>" 
                           class="inline-flex items-center gap-1 text-sm font-medium text-black hover:text-gray-700 transition-colors">
                            Read More
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    
    <!-- No Results Message (Hidden by default) -->
    <div id="no-results" class="bg-white rounded-xl border border-gray-200 shadow-sm p-8 text-center" style="display: none;">
        <svg class="w-12 h-12 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
        </svg>
        <h3 class="text-lg font-semibold text-gray-900 mb-2">No Articles Found</h3>
        <p class="text-gray-600">Try adjusting your search or filter criteria.</p>
    </div>
<?php endif; ?>

<script>
// Search and filter functionality
let selectedCategory = 'all';
let searchTerm = '';

const searchInput = document.getElementById('search-input');
const categoryButtons = document.querySelectorAll('.category-filter');
const articleItems = document.querySelectorAll('.article-item');
const articlesGrid = document.getElementById('articles-grid');
const noResults = document.getElementById('no-results');

// Search input handler
searchInput.addEventListener('input', function(e) {
    searchTerm = e.target.value.toLowerCase().trim();
    filterArticles();
});

// Category filter handlers
categoryButtons.forEach(button => {
    button.addEventListener('click', function() {
        // Remove active class from all buttons
        categoryButtons.forEach(btn => btn.classList.remove('active'));
        // Add active class to clicked button
        this.classList.add('active');
        
        selectedCategory = this.dataset.category;
        filterArticles();
    });
});

function filterArticles() {
    let visibleCount = 0;
    
    articleItems.forEach(item => {
        const itemCategory = item.dataset.category;
        const itemSearchText = item.dataset.searchText;
        
        let matchesCategory = selectedCategory === 'all' || itemCategory === selectedCategory;
        let matchesSearch = searchTerm === '' || itemSearchText.includes(searchTerm);
        
        if (matchesCategory && matchesSearch) {
            item.style.display = 'block';
            visibleCount++;
        } else {
            item.style.display = 'none';
        }
    });
    
    // Show/hide no results message
    if (visibleCount === 0 && articleItems.length > 0) {
        articlesGrid.style.display = 'none';
        noResults.style.display = 'block';
    } else {
        articlesGrid.style.display = 'grid';
        noResults.style.display = 'none';
    }
}
</script>

<?php require __DIR__.'/includes/footer.php'; ?>