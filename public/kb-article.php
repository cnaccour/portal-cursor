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
    list-style-position: outside;
}
.article-content ul { list-style-type: disc; }
.article-content ol { list-style-type: decimal; }
.article-content ul ul { list-style-type: circle; }
.article-content ul ul ul { list-style-type: square; }

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
    color: #374151;
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
    display: none;
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

/* KB layout additions */
.kb-toc {
    position: sticky;
    top: 1rem;
    background: #FFFFFF;
    border: 1px solid #E5E7EB;
    border-radius: 8px;
    padding: 0.75rem;
}
.kb-toc h3 { font-size: 0.875rem; font-weight: 600; color: #111827; margin-bottom: 0.5rem; }
.kb-toc a { display: block; font-size: 0.875rem; color: #374151; padding: 0.25rem 0.5rem; border-radius: 6px; text-decoration: none; }
.kb-toc a:hover { background: #F3F4F6; color: #111827; }
.kb-toc .active { background: #FFFBF0; color: #92400E; }

.kb-section { border: 1px solid #E5E7EB; border-radius: 8px; margin: 1rem 0; overflow: hidden; }
.kb-section-header { display:flex; align-items:center; justify-content: space-between; padding: 0.75rem 1rem; cursor: pointer; background: #F9FAFB; }
.kb-section-title { font-weight: 600; color: #111827; font-size: 1rem; margin: 0; }
.kb-section-actions { display:flex; gap: 0.25rem; }
.kb-icon-btn { display:inline-flex; align-items:center; justify-content:center; width: 32px; height: 32px; border-radius: 6px; border: 1px solid #E5E7EB; background: #FFFFFF; color: #6B7280; }
.kb-icon-btn:hover { background: #FFFBF0; color: #92400E; border-color: #F59E0B; }
.kb-section-body { padding: 1rem; display: block; }
.kb-section.collapsed .kb-section-body { display: none; }

/* Fallback layout to ensure sidebar on desktop even if Tailwind responsive classes are missing */
.kb-layout { display: block; }
/* Flex fallback to guarantee two columns on desktop */
@media (min-width: 768px) {
    .kb-flex { display: flex; gap: 1.5rem; }
    .kb-toc { display: block; flex: 0 0 25%; }
    .kb-main { flex: 1 1 auto; }
}
@media (max-width: 767px) { .kb-toc { display: none; } }
/* Hide mobile-only print on desktop */
@media (min-width: 768px) { .kb-mobile-print { display: none !important; } }

@media print {
    .kb-toc, .breadcrumb, .article-actions, .tag-list, .kb-section-actions, .no-print { display: none !important; }
    .kb-section { border: none; page-break-inside: avoid; }
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
            <span class="text-gray-400">→</span>
            <?php if (!empty($article['category'])): ?>
                <span class="text-gray-600"><?= htmlspecialchars($article['category']) ?></span>
                <span class="text-gray-400">→</span>
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
                <?php if ($article['allow_print'] ?? 1): ?>
                <button onclick="window.print()" 
                        class="w-full flex items-center gap-2 px-3 py-2 text-sm bg-gray-100 text-gray-700 rounded hover:bg-gray-200 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                    </svg>
                    Print Article
                </button>
                <?php endif; ?>
                
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
                <span class="px-3 py-1 text-white text-sm font-medium rounded-full" style="background-color: #AF831A;">
                    <?= htmlspecialchars($article['category']) ?>
                </span>
            <?php endif; ?>
            <span class="text-sm text-gray-500">
                <?= date('F j, Y', strtotime($article['created_at'])) ?>
            </span>
        </div>
        
        <h1 class="text-2xl font-bold text-gray-900 mb-4">
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

    <!-- Article Content with TOC and collapsible sections -->
    <div class="kb-layout kb-flex grid grid-cols-1 <?= ($article['enable_sections'] ?? 1) ? 'md:grid-cols-4' : 'md:grid-cols-1' ?> gap-6">
        <?php if ($article['enable_sections'] ?? 1): ?>
        <aside class="kb-toc md:col-span-1">
            <h3>Sections</h3>
            <nav id="kb-toc"></nav>
            <?php if (($article['allow_print'] ?? 1) && ($article['enable_sections'] ?? 1)): ?>
            <button onclick="printFullManual()" class="no-print mt-3 w-full px-3 py-2 text-sm font-medium rounded" style="background-color:#AF831A; color:white;" onmouseover="this.style.backgroundColor='#8B6914'" onmouseout="this.style.backgroundColor='#AF831A'">Print Full Manual</button>
            <?php endif; ?>
        </aside>
        <?php endif; ?>

        <div class="kb-main bg-white rounded-xl border border-gray-200 shadow-sm p-4 md:p-8 article-content max-w-none <?= ($article['enable_sections'] ?? 1) ? 'md:col-span-3' : 'md:col-span-1' ?>" id="kb-content">
            <?php if (($article['allow_print'] ?? 1) && ($article['enable_sections'] ?? 1)): ?>
            <div class="no-print mb-4 kb-mobile-print">
                <button onclick="printFullManual()" class="w-full px-3 py-2 text-sm font-medium rounded" style="background-color:#AF831A; color:white;" onmouseover="this.style.backgroundColor='#8B6914'" onmouseout="this.style.backgroundColor='#AF831A'">Print Full Manual</button>
            </div>
            <?php endif; ?>
            <?php
            if ($article['slug'] === 'email-setup-instructions') {
                include __DIR__.'/templates/email-setup-article.php';
            } else {
                $content = $article['content'];
                $title_pattern = '/^\s*<h[12][^>]*>.*?' . preg_quote(htmlspecialchars($article['title']), '/') . '.*?<\/h[12]>\s*/i';
                $content = preg_replace($title_pattern, '', $content);
                $content = preg_replace('/^\s*<h[12][^>]*>[^<]*' . preg_quote('📧', '/') . '[^<]*<\/h[12]>\s*/i', '', $content);
                echo $content;
            }
            ?>
        </div>
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

function printFullManual() {
    // Temporarily expand all sections for printing
    const allSections = document.querySelectorAll('.kb-section');
    const collapsedSections = [];
    
    // Track which sections were collapsed and expand them
    allSections.forEach((section, index) => {
        if (section.classList.contains('collapsed')) {
            collapsedSections.push(index);
            section.classList.remove('collapsed');
        }
    });
    
    // Print the page
    window.print();
    
    // Restore collapsed state after a short delay (to ensure print dialog has opened)
    setTimeout(() => {
        collapsedSections.forEach(index => {
            if (allSections[index]) {
                allSections[index].classList.add('collapsed');
            }
        });
    }, 100);
}

// Utility to strip emojis
function stripEmojis(str){
    return str.replace(/[\u2700-\u27BF]|[\uE000-\uF8FF]|[\uFE00-\uFE0F]|\u24C2|\uD83C[\uDC00-\uDFFF]|\uD83D[\uDC00-\uDFFF]|\uD83E[\uDD00-\uDFFF]/g, '');
}

// Email setup functionality (for email-setup-instructions article)
function setupEmailFunctionality() {
    // Use a small delay to ensure DOM elements are ready
    setTimeout(function() {
        // Password toggle functionality
        const toggleBtn = document.getElementById('toggle-password');
        const passwordDisplay = document.getElementById('password-display');
        let isPasswordVisible = false;
        
        if (toggleBtn && passwordDisplay) {
            toggleBtn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                isPasswordVisible = !isPasswordVisible;
                if (isPasswordVisible) {
                    passwordDisplay.textContent = 'salon123';
                } else {
                    passwordDisplay.textContent = '••••••••';
                }
            });
        }
    }, 100);
}

// Copy to clipboard functionality (global)
function copyToClipboard(text) {
    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(text).then(function() {
            showCopySuccess();
        }).catch(function(err) {
            console.error('Clipboard API failed:', err);
            fallbackCopy(text);
        });
    } else {
        fallbackCopy(text);
    }
}

function fallbackCopy(text) {
    // Fallback for older browsers
    const textArea = document.createElement('textarea');
    textArea.value = text;
    textArea.style.position = 'fixed';
    textArea.style.left = '-999999px';
    textArea.style.top = '-999999px';
    document.body.appendChild(textArea);
    textArea.focus();
    textArea.select();
    
    try {
        document.execCommand('copy');
        showCopySuccess();
    } catch (err) {
        console.error('Fallback copy failed:', err);
        alert('Failed to copy to clipboard: ' + text);
    }
    
    document.body.removeChild(textArea);
}

function showCopySuccess() {
    const button = event.target.closest('button');
    if (!button) return;
    
    const originalHTML = button.innerHTML;
    
    button.innerHTML = `
        <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
        </svg>
    `;
    
    setTimeout(function() {
        button.innerHTML = originalHTML;
    }, 2000);
}

// Build collapsible sections from h2 headers and create TOC
document.addEventListener('DOMContentLoaded', function() {
    try {
        const content = document.getElementById('kb-content');
        if (!content) return;
        const toc = document.getElementById('kb-toc');
        
        // Setup email functionality if this is the email setup article
        setupEmailFunctionality();
        
        // Check if sections are enabled for this article
        const enableSections = <?= ($article['enable_sections'] ?? 1) ? 'true' : 'false' ?>;
        if (!enableSections) {
            // If sections are disabled, just strip emojis and return
            content.innerHTML = stripEmojis(content.innerHTML);
            content.querySelectorAll('h1,h2,h3,h4').forEach(el => {
                el.textContent = stripEmojis(el.textContent);
            });
            return;
        }
        
    // Remove emojis everywhere first (content and headings)
    content.innerHTML = stripEmojis(content.innerHTML);
    let rawSections = Array.from(content.querySelectorAll('.kb-src-section'));
    // If wrappers exist but are not direct children (e.g., nested), hoist them in order and clear content first
    if (rawSections.length > 0) {
        const hoisted = rawSections.map(n => n.cloneNode(true));
        content.innerHTML = '';
        hoisted.forEach(n => content.appendChild(n));
        rawSections = Array.from(content.querySelectorAll('.kb-src-section'));
    }
    const usingWrappers = rawSections.length > 0;
    const headings = usingWrappers ? [] : Array.from(content.querySelectorAll('h2'));
    if (!usingWrappers && headings.length === 0) return;

    let sectionIndex = 0;
    const total = usingWrappers ? rawSections.length : headings.length;
    (usingWrappers ? rawSections : headings).forEach((node, idx) => {
        sectionIndex++;
        const secId = 'sec-' + sectionIndex;
        // Wrap section
        const wrapper = document.createElement('div');
        wrapper.className = 'kb-section';
        const header = document.createElement('div');
        header.className = 'kb-section-header';
        const title = document.createElement('h3');
        title.className = 'kb-section-title';
        let sectionTitle = '';
        if (usingWrappers) {
            sectionTitle = node.getAttribute('data-title') || (node.querySelector('h2') ? node.querySelector('h2').textContent : 'Section ' + sectionIndex);
        } else {
            sectionTitle = node.textContent || ('Section ' + sectionIndex);
        }
        sectionTitle = stripEmojis(sectionTitle.trim());
        title.textContent = sectionIndex + '. ' + sectionTitle;
        header.appendChild(title);

        const actions = document.createElement('div');
        actions.className = 'kb-section-actions';
        // Print button (only if printing is allowed)
        const allowPrint = <?= ($article['allow_print'] ?? 1) ? 'true' : 'false' ?>;
        if (allowPrint) {
            const printBtn = document.createElement('button');
            printBtn.className = 'kb-icon-btn no-print';
            printBtn.title = 'Print Section';
            printBtn.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>';
            printBtn.addEventListener('click', function(e){
                e.stopPropagation();
                // Print only this section: open a new window with section HTML
                const secHtml = wrapper.outerHTML;
                const w = window.open('', '_blank');
                w.document.write('<html><head><title>' + (title.textContent || 'Section') + '</title><style>@media print{.no-print{display:none}}</style></head><body>' + secHtml + '<script>window.onload=function(){window.print();}<\/script></body></html>');
                w.document.close();
            });
            actions.appendChild(printBtn);
        }

        // Collapse toggle icon
        const toggleIcon = document.createElement('button');
        toggleIcon.className = 'kb-icon-btn no-print';
        toggleIcon.title = 'Collapse/Expand';
        toggleIcon.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>';
        actions.appendChild(toggleIcon);

        header.appendChild(actions);
        wrapper.appendChild(header);

        // Build body until next h2
        const body = document.createElement('div');
        body.className = 'kb-section-body';
        if (usingWrappers) {
            const tmp = document.createElement('div');
            tmp.innerHTML = node.innerHTML;
            const firstH2 = tmp.querySelector('h2');
            if (firstH2) firstH2.remove();
            body.innerHTML = stripEmojis(tmp.innerHTML);
            node.replaceWith(document.createComment('section-replaced'));
        } else {
            const nextH2 = headings[idx + 1] || null;
            const range = document.createRange();
            range.setStartAfter(node);
            if (nextH2) {
                range.setEndBefore(nextH2);
            } else {
                range.setEnd(content, content.childNodes.length);
            }
            const frag = range.extractContents();
            body.appendChild(frag);
        }
        wrapper.id = secId;
        wrapper.appendChild(body);
        // Default collapsed state: collapse all sections except first one (always)
        if (sectionIndex !== 1) {
            wrapper.classList.add('collapsed');
        }
        if (usingWrappers) {
            content.appendChild(wrapper);
        } else {
            node.replaceWith(wrapper);
        }

        // Toggle on header click
        header.addEventListener('click', function(){ wrapper.classList.toggle('collapsed'); });

        // TOC entry
        if (toc) {
            const a = document.createElement('a');
            a.href = '#' + secId;
            a.textContent = title.textContent;
            a.addEventListener('click', function(e){
                e.preventDefault();
                const target = document.getElementById(secId);
                if (target) {
                    target.classList.remove('collapsed');
                    target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    // update hash for deep-linking
                    history.replaceState(null, '', '#' + secId);
                }
            });
            toc.appendChild(a);
        }
    });

    // Highlight active TOC entry on scroll
    if (toc) {
        const links = Array.from(toc.querySelectorAll('a'));
        const sections = links.map(a => {
            const href = a.getAttribute('href');
            return href ? document.getElementById(href.substring(1)) : null;
        }).filter(sec => sec !== null);
        
        if (sections.length > 0) {
            const obs = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    const idx = sections.indexOf(entry.target);
                    if (idx >= 0) {
                        const link = links[idx];
                        if (entry.isIntersecting) {
                            links.forEach(l => l.classList.remove('active'));
                            link.classList.add('active');
                        }
                    }
                });
            }, { rootMargin: '0px 0px -70% 0px', threshold: 0.1 });
            sections.forEach(sec => obs.observe(sec));
        }
    }
    
    // Strip emojis from existing headings (h1-h4) for display
    content.querySelectorAll('h1,h2,h3,h4').forEach(el => {
        el.textContent = stripEmojis(el.textContent);
    });

    // If URL has a hash (deep link), open that section and scroll to it
    // But still ensure only first section is open initially, then open the target
    if (location.hash) {
        const target = document.getElementById(location.hash.substring(1));
        if (target) {
            // First collapse all sections except the first one
            document.querySelectorAll('.kb-section').forEach((section, index) => {
                if (index !== 0) { // Keep first section open
                    section.classList.add('collapsed');
                }
            });
            // Then open the target section (if it's not the first one)
            if (target.id !== 'sec-1') {
                target.classList.remove('collapsed');
            }
            setTimeout(() => target.scrollIntoView({ behavior: 'smooth', block: 'start' }), 50);
        }
    }
    } catch (error) {
        console.error('Error initializing KB article:', error);
    }
});
</script>

<?php require __DIR__.'/includes/footer.php'; ?>