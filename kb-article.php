<?php
require __DIR__.'/includes/auth.php';
// Articles are accessible to everyone - no login required

// Get article by slug
$article = null;
if (isset($_GET['slug'])) {
    try {
        require_once __DIR__.'/includes/db.php';
        
        $stmt = $pdo->prepare("SELECT * FROM kb_articles WHERE slug = ? AND status = 'published'");
        $stmt->execute([$_GET['slug']]);
        $article = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$article) {
            $error = 'Article not found or not published';
        }
    } catch (Exception $e) {
        error_log('KB article error: ' . $e->getMessage());
        $error = 'Error loading article';
    }
} else {
    $error = 'No article specified';
}

require __DIR__.'/includes/header.php';
?>

<style>
.article-content {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    line-height: 1.7;
}

.article-content h1 {
    font-size: 2rem;
    font-weight: bold;
    color: #1F2937;
    margin: 2rem 0 1rem 0;
    border-bottom: 2px solid #AF831A;
    padding-bottom: 0.5rem;
}

.article-content h2 {
    font-size: 1.5rem;
    font-weight: bold;
    color: #374151;
    margin: 1.5rem 0 0.75rem 0;
}

.article-content h3 {
    font-size: 1.25rem;
    font-weight: semibold;
    color: #4B5563;
    margin: 1.25rem 0 0.5rem 0;
}

.article-content h4 {
    font-size: 1.125rem;
    font-weight: medium;
    color: #6B7280;
    margin: 1rem 0 0.5rem 0;
}

.article-content p {
    margin: 0.75rem 0;
    color: #374151;
}

.article-content ul, .article-content ol {
    margin: 0.75rem 0;
    padding-left: 1.5rem;
}

.article-content li {
    margin: 0.25rem 0;
    color: #374151;
}

.article-content code {
    background-color: #F3F4F6;
    border: 1px solid #D1D5DB;
    border-radius: 4px;
    padding: 0.125rem 0.25rem;
    font-size: 0.875rem;
    font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
    color: #DC2626;
}

.article-content pre {
    background-color: #1F2937;
    color: #F9FAFB;
    border-radius: 8px;
    padding: 1rem;
    margin: 1rem 0;
    overflow-x: auto;
    font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
    font-size: 0.875rem;
}

.article-content pre code {
    background: none;
    border: none;
    padding: 0;
    color: inherit;
}

.article-content table {
    width: 100%;
    margin: 1rem 0;
    border-collapse: collapse;
    border: 1px solid #D1D5DB;
    border-radius: 8px;
    overflow: hidden;
}

.article-content th, .article-content td {
    padding: 0.75rem;
    text-align: left;
    border-bottom: 1px solid #D1D5DB;
}

.article-content th {
    background-color: #F3F4F6;
    font-weight: bold;
    color: #374151;
}

.article-content tr:nth-child(even) {
    background-color: #F9FAFB;
}

.article-content img {
    max-width: 100%;
    height: auto;
    border-radius: 8px;
    margin: 1rem 0;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}

.article-content blockquote {
    border-left: 4px solid #AF831A;
    background-color: #FFFBF0;
    padding: 1rem;
    margin: 1rem 0;
    border-radius: 0 8px 8px 0;
}

.article-content a {
    color: #AF831A;
    text-decoration: underline;
}

.article-content a:hover {
    color: #92400E;
}

.article-actions {
    position: sticky;
    top: 20px;
    float: right;
    background: white;
    border: 1px solid #E5E7EB;
    border-radius: 8px;
    padding: 1rem;
    margin: 0 0 2rem 2rem;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    width: 200px;
}

@media (max-width: 768px) {
    .article-actions {
        position: relative;
        float: none;
        margin: 0 0 1rem 0;
        width: auto;
    }
}

.breadcrumb {
    background: #F9FAFB;
    border: 1px solid #E5E7EB;
    border-radius: 8px;
    padding: 0.75rem 1rem;
    margin-bottom: 1.5rem;
}

.tag-list {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    margin: 1rem 0;
}

.tag-item {
    background: #AF831A;
    color: white;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-size: 0.75rem;
    font-weight: medium;
}
</style>

<?php if (!empty($error)): ?>
    <div class="mb-8">
        <div class="bg-red-50 border border-red-200 rounded-lg p-8 text-center">
            <svg class="w-16 h-16 mx-auto mb-4 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <h1 class="text-2xl font-bold text-red-900 mb-2">Article Not Found</h1>
            <p class="text-red-700 mb-4"><?= htmlspecialchars($error) ?></p>
            <a href="knowledge-base.php" class="inline-flex items-center gap-2 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Back to Knowledge Base
            </a>
        </div>
    </div>
<?php else: ?>
    <!-- Breadcrumb -->
    <div class="breadcrumb">
        <nav class="flex items-center gap-2 text-sm">
            <a href="knowledge-base.php" class="text-gray-600 hover:text-gray-900">Knowledge Base</a>
            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
            </svg>
            <?php if (!empty($article['category'])): ?>
                <span class="text-gray-600"><?= htmlspecialchars($article['category']) ?></span>
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            <?php endif; ?>
            <span class="text-gray-900 font-medium"><?= htmlspecialchars($article['title']) ?></span>
        </nav>
    </div>

    <!-- Article Actions (Desktop Sidebar) -->
    <div class="article-actions">
        <div class="space-y-4">
            <div class="text-center">
                <div class="w-12 h-12 bg-gradient-to-br from-yellow-400 to-yellow-600 rounded-lg mx-auto mb-2 flex items-center justify-center">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                    </svg>
                </div>
                <h3 class="font-semibold text-gray-900 text-sm">Article Actions</h3>
            </div>
            
            <div class="space-y-2">
                <button onclick="window.print()" 
                        class="w-full flex items-center gap-2 px-3 py-2 text-sm bg-gray-100 text-gray-700 rounded hover:bg-gray-200 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                    </svg>
                    Print Article
                </button>
                
                <button onclick="shareArticle()" 
                        class="w-full flex items-center gap-2 px-3 py-2 text-sm bg-gray-100 text-gray-700 rounded hover:bg-gray-200 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.367 2.684 3 3 0 00-5.367-2.684z"></path>
                    </svg>
                    Share Link
                </button>
                
                <a href="knowledge-base.php" 
                   class="w-full flex items-center gap-2 px-3 py-2 text-sm bg-gray-100 text-gray-700 rounded hover:bg-gray-200 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Back to KB
                </a>
            </div>
            
            <div class="border-t pt-3">
                <div class="text-xs text-gray-500">
                    <div><strong>Published:</strong> <?= date('M j, Y', strtotime($article['created_at'])) ?></div>
                    <?php if ($article['updated_at'] !== $article['created_at']): ?>
                        <div><strong>Updated:</strong> <?= date('M j, Y', strtotime($article['updated_at'])) ?></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Article Header -->
    <div class="mb-8">
        <div class="flex items-center gap-3 mb-4">
            <?php if (!empty($article['category'])): ?>
                <span class="px-3 py-1 bg-blue-100 text-blue-800 text-sm font-medium rounded-full">
                    <?= htmlspecialchars($article['category']) ?>
                </span>
            <?php endif; ?>
            <span class="text-sm text-gray-500">
                <?= date('F j, Y', strtotime($article['created_at'])) ?>
            </span>
        </div>
        
        <h1 class="text-4xl font-bold text-gray-900 mb-4">
            <?= htmlspecialchars($article['title']) ?>
        </h1>
        
        <?php if (!empty($article['tags'])): ?>
            <?php 
            $tags = json_decode($article['tags'], true) ?: []; 
            if (!empty($tags)):
            ?>
                <div class="tag-list">
                    <?php foreach ($tags as $tag): ?>
                        <span class="tag-item"><?= htmlspecialchars($tag) ?></span>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <!-- Article Content -->
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-8 article-content">
        <?= $article['content'] ?>
    </div>

    <!-- Footer Actions (Mobile) -->
    <div class="mt-8 bg-white rounded-xl border border-gray-200 shadow-sm p-6 md:hidden">
        <div class="flex items-center justify-between">
            <h3 class="font-semibold text-gray-900">Article Actions</h3>
            <div class="flex items-center gap-2">
                <button onclick="window.print()" 
                        class="p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                    </svg>
                </button>
                <button onclick="shareArticle()" 
                        class="p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.367 2.684 3 3 0 00-5.367-2.684z"></path>
                    </svg>
                </button>
            </div>
        </div>
    </div>
<?php endif; ?>

<script>
function shareArticle() {
    if (navigator.share) {
        navigator.share({
            title: '<?= addslashes($article['title']) ?>',
            url: window.location.href
        }).catch(console.error);
    } else {
        // Fallback to copying URL
        navigator.clipboard.writeText(window.location.href).then(() => {
            alert('Article link copied to clipboard!');
        }).catch(() => {
            // Further fallback
            const textArea = document.createElement('textarea');
            textArea.value = window.location.href;
            document.body.appendChild(textArea);
            textArea.select();
            document.execCommand('copy');
            document.body.removeChild(textArea);
            alert('Article link copied to clipboard!');
        });
    }
}
</script>

<?php require __DIR__.'/includes/footer.php'; ?>