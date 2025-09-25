<?php
require __DIR__.'/includes/auth.php';
require_login();
require_role('admin');

// Handle article deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error = 'Invalid request token';
    } else {
        try {
            require_once __DIR__.'/includes/db.php';
            $stmt = $pdo->prepare("DELETE FROM kb_articles WHERE id = ?");
            $stmt->execute([$_POST['article_id']]);
            $success = 'Article deleted successfully';
        } catch (Exception $e) {
            error_log('KB deletion error: ' . $e->getMessage());
            $error = 'Error deleting article';
        }
    }
}

// Handle status toggle
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'toggle_status') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error = 'Invalid request token';
    } else {
        try {
            require_once __DIR__.'/includes/db.php';
            $stmt = $pdo->prepare("UPDATE kb_articles SET status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
            $new_status = $_POST['current_status'] === 'published' ? 'draft' : 'published';
            $stmt->execute([$new_status, $_POST['article_id']]);
            $success = 'Article status updated successfully';
        } catch (Exception $e) {
            error_log('KB status update error: ' . $e->getMessage());
            $error = 'Error updating article status';
        }
    }
}

// Get all articles
try {
    require_once __DIR__.'/includes/db.php';
    $stmt = $pdo->query("
        SELECT ka.*, 
               u_created.name as created_by_name, u_created.email as created_by_email,
               u_updated.name as updated_by_name, u_updated.email as updated_by_email
        FROM kb_articles ka
        LEFT JOIN users u_created ON ka.created_by = u_created.id
        LEFT JOIN users u_updated ON ka.updated_by = u_updated.id
        ORDER BY ka.created_at DESC
    ");
    $articles = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    error_log('KB admin error: ' . $e->getMessage());
    $articles = [];
    $error = 'Error loading articles';
}

// Get statistics
$stats = [
    'total' => count($articles),
    'published' => count(array_filter($articles, fn($a) => $a['status'] === 'published')),
    'draft' => count(array_filter($articles, fn($a) => $a['status'] === 'draft'))
];

require __DIR__.'/includes/header.php';
?>

<style>
.kb-card {
    transition: all 0.2s ease;
}
.kb-card:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}
.status-published { 
    background-color: #D1FAE5; 
    color: #065F46; 
}
.status-draft { 
    background-color: #FEF3C7; 
    color: #92400E; 
}
.action-button {
    transition: all 0.2s ease;
}
.action-button:hover {
    transform: scale(1.05);
}
</style>

<div class="mb-8">
    <div class="flex items-center justify-between mb-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Knowledge Base Admin</h1>
            <p class="text-gray-600 mt-2">Create and manage knowledge base articles for the team.</p>
        </div>
        <div class="flex gap-3">
            <a href="knowledge-base.php" 
               class="inline-flex items-center gap-2 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                </svg>
                View Public KB
            </a>
            <a href="admin-kb-edit.php" 
               class="inline-flex items-center gap-2 px-6 py-2 bg-black text-white rounded-lg hover:bg-gray-800 transition-colors font-medium">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                Create Article
            </a>
        </div>
    </div>
</div>

<?php if (!empty($success)): ?>
    <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
        <div class="flex items-center gap-2">
            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
            <span class="text-green-800 font-medium"><?= htmlspecialchars($success) ?></span>
        </div>
    </div>
<?php endif; ?>

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

<!-- Statistics Cards -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">Total Articles</p>
                <p class="text-3xl font-bold text-gray-900"><?= $stats['total'] ?></p>
            </div>
            <div class="p-3 bg-blue-100 rounded-lg">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                </svg>
            </div>
        </div>
    </div>
    
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">Published</p>
                <p class="text-3xl font-bold text-green-600"><?= $stats['published'] ?></p>
            </div>
            <div class="p-3 bg-green-100 rounded-lg">
                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
        </div>
    </div>
    
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">Drafts</p>
                <p class="text-3xl font-bold text-yellow-600"><?= $stats['draft'] ?></p>
            </div>
            <div class="p-3 bg-yellow-100 rounded-lg">
                <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                </svg>
            </div>
        </div>
    </div>
</div>

<!-- Articles List -->
<div class="bg-white rounded-xl border border-gray-200 shadow-sm">
    <div class="p-6 border-b border-gray-200">
        <h2 class="text-xl font-semibold text-gray-900">Articles</h2>
    </div>
    
    <?php if (empty($articles)): ?>
        <div class="p-8 text-center">
            <svg class="w-16 h-16 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
            </svg>
            <h3 class="text-lg font-semibold text-gray-900 mb-2">No Articles Yet</h3>
            <p class="text-gray-600 mb-4">Get started by creating your first knowledge base article.</p>
            <a href="admin-kb-edit.php" class="inline-flex items-center gap-2 px-4 py-2 bg-black text-white rounded-lg hover:bg-gray-800 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                Create Article
            </a>
        </div>
    <?php else: ?>
        <div class="divide-y divide-gray-200">
            <?php foreach ($articles as $article): ?>
                <div class="kb-card p-6">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div class="flex items-center gap-3 mb-2">
                                <h3 class="text-lg font-semibold text-gray-900">
                                    <?= htmlspecialchars($article['title']) ?>
                                </h3>
                                <span class="status-<?= $article['status'] ?> px-2 py-1 text-xs font-medium rounded-full">
                                    <?= ucfirst($article['status']) ?>
                                </span>
                                <?php if (!($article['allow_print'] ?? 1)): ?>
                                    <span class="px-2 py-1 text-xs font-medium rounded-full bg-red-100 text-red-800" title="Printing disabled for this article">
                                        🚫 No Print
                                    </span>
                                <?php endif; ?>
                                <?php if (!($article['enable_sections'] ?? 1)): ?>
                                    <span class="px-2 py-1 text-xs font-medium rounded-full bg-orange-100 text-orange-800" title="Collapsible sections disabled for this article">
                                        📄 Plain View
                                    </span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="flex items-center gap-4 text-sm text-gray-600 mb-3">
                                <?php if (!empty($article['category'])): ?>
                                    <div class="flex items-center gap-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                                        </svg>
                                        <?= htmlspecialchars($article['category']) ?>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <?= date('M j, Y', strtotime($article['created_at'])) ?>
                                </div>
                                
                                <?php if (!empty($article['created_by_name'])): ?>
                                    <div class="flex items-center gap-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                        </svg>
                                        Created by <?= htmlspecialchars($article['created_by_name']) ?>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if ($article['updated_at'] !== $article['created_at']): ?>
                                    <div class="flex items-center gap-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                        </svg>
                                        Updated <?= date('M j, Y', strtotime($article['updated_at'])) ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <?php if (!empty($article['tags'])): ?>
                                <?php 
                                $tags = json_decode($article['tags'], true) ?: []; 
                                if (!empty($tags)):
                                ?>
                                    <div class="flex items-center gap-2 mb-3">
                                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                                        </svg>
                                        <div class="flex flex-wrap gap-1">
                                            <?php foreach ($tags as $tag): ?>
                                                <span class="px-2 py-1 bg-gray-100 text-gray-700 text-xs rounded"><?= htmlspecialchars($tag) ?></span>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                        
                        <div class="flex items-center gap-2 ml-4">
                            <?php if ($article['status'] === 'published'): ?>
                                <a href="kb-article.php?slug=<?= urlencode($article['slug']) ?>" 
                                   target="_blank"
                                   class="action-button p-2 text-gray-400 hover:text-blue-600 rounded-lg hover:bg-blue-50 transition-colors"
                                   title="View Article">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                </a>
                            <?php endif; ?>
                            
                            <a href="admin-kb-edit.php?id=<?= $article['id'] ?>" 
                               class="action-button p-2 text-gray-400 hover:text-yellow-600 rounded-lg hover:bg-yellow-50 transition-colors"
                               title="Edit Article">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                                </svg>
                            </a>
                            
                            <form method="post" class="inline" 
                                  onsubmit="return confirm('Toggle article status?')">
                                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                <input type="hidden" name="action" value="toggle_status">
                                <input type="hidden" name="article_id" value="<?= $article['id'] ?>">
                                <input type="hidden" name="current_status" value="<?= $article['status'] ?>">
                                <button type="submit" 
                                        class="action-button p-2 text-gray-400 hover:text-green-600 rounded-lg hover:bg-green-50 transition-colors"
                                        title="<?= $article['status'] === 'published' ? 'Make Draft' : 'Publish' ?>">
                                    <?php if ($article['status'] === 'published'): ?>
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                                        </svg>
                                    <?php else: ?>
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    <?php endif; ?>
                                </button>
                            </form>
                            
                            <form method="post" class="inline" 
                                  onsubmit="return confirm('Are you sure you want to delete this article? This action cannot be undone.')">
                                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="article_id" value="<?= $article['id'] ?>">
                                <button type="submit" 
                                        class="action-button p-2 text-gray-400 hover:text-red-600 rounded-lg hover:bg-red-50 transition-colors"
                                        title="Delete Article">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require __DIR__.'/includes/footer.php'; ?>