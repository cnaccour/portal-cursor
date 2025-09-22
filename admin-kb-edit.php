<?php
require __DIR__.'/includes/auth.php';
require_login();
require_role('admin');

$article = null;
$isEdit = false;

// Check if editing existing article
if (isset($_GET['id'])) {
    try {
        require_once __DIR__.'/includes/db.php';
        $stmt = $pdo->prepare("SELECT * FROM kb_articles WHERE id = ?");
        $stmt->execute([$_GET['id']]);
        $article = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($article) {
            $isEdit = true;
            // Parse tags if they exist
            if (!empty($article['tags'])) {
                $tags = json_decode($article['tags'], true);
                if (is_array($tags)) {
                    $article['tags_string'] = implode(', ', $tags);
                } else {
                    $article['tags_string'] = '';
                }
            } else {
                $article['tags_string'] = '';
            }
        } else {
            $error = 'Article not found';
        }
    } catch (Exception $e) {
        error_log('KB edit error: ' . $e->getMessage());
        $error = 'Error loading article';
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error = 'Invalid request token';
    } else {
        try {
            require_once __DIR__.'/includes/db.php';
            
            $title = trim($_POST['title'] ?? '');
            $content = $_POST['content'] ?? '';
            $category = trim($_POST['category'] ?? '');
            $tags_string = trim($_POST['tags'] ?? '');
            $status = $_POST['status'] ?? 'draft';
            
            // Validate required fields
            if (empty($title)) {
                throw new Exception('Title is required');
            }
            
            if (empty($content)) {
                throw new Exception('Content is required');
            }
            
            // Generate slug from title
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
            
            // Process tags
            $tags = [];
            if (!empty($tags_string)) {
                $tags = array_map('trim', explode(',', $tags_string));
                $tags = array_filter($tags); // Remove empty tags
            }
            $tags_json = json_encode($tags);
            
            if ($isEdit && $article) {
                // Update existing article
                $stmt = $pdo->prepare("
                    UPDATE kb_articles 
                    SET title = ?, slug = ?, content = ?, category = ?, tags = ?, status = ?, updated_at = CURRENT_TIMESTAMP 
                    WHERE id = ?
                ");
                $stmt->execute([$title, $slug, $content, $category, $tags_json, $status, $article['id']]);
                $success = 'Article updated successfully';
                
                // Reload article data
                $stmt = $pdo->prepare("SELECT * FROM kb_articles WHERE id = ?");
                $stmt->execute([$article['id']]);
                $article = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($article && !empty($article['tags'])) {
                    $tags = json_decode($article['tags'], true);
                    if (is_array($tags)) {
                        $article['tags_string'] = implode(', ', $tags);
                    }
                }
            } else {
                // Create new article
                $stmt = $pdo->prepare("
                    INSERT INTO kb_articles (title, slug, content, category, tags, status) 
                    VALUES (?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([$title, $slug, $content, $category, $tags_json, $status]);
                $article_id = $pdo->lastInsertId();
                $success = 'Article created successfully';
                
                // Redirect to edit mode for the new article
                header("Location: admin-kb-edit.php?id=$article_id");
                exit;
            }
            
        } catch (Exception $e) {
            error_log('KB save error: ' . $e->getMessage());
            $error = $e->getMessage();
        }
    }
}

require __DIR__.'/includes/header.php';
?>

<style>
.form-field {
    transition: all 0.2s ease;
}
.form-field:focus {
    outline: none;
    border-color: #AF831A;
    box-shadow: 0 0 0 3px rgba(175, 131, 26, 0.1);
}
.editor-container {
    border: 1px solid #D1D5DB;
    border-radius: 0.5rem;
}
.editor-container.focused {
    border-color: #AF831A;
    box-shadow: 0 0 0 3px rgba(175, 131, 26, 0.1);
}
.ql-toolbar {
    border-top-left-radius: 0.5rem;
    border-top-right-radius: 0.5rem;
    border-bottom: 1px solid #D1D5DB;
}
.ql-container {
    border-bottom-left-radius: 0.5rem;
    border-bottom-right-radius: 0.5rem;
    min-height: 300px;
}
.tag-input {
    transition: all 0.2s ease;
}
.tag-preview {
    background: #F3F4F6;
    border: 1px solid #D1D5DB;
    border-radius: 0.375rem;
    padding: 0.5rem;
    margin-top: 0.5rem;
    display: none;
}
.tag-preview.show {
    display: block;
}
.tag-item {
    display: inline-block;
    background: #AF831A;
    color: white;
    padding: 0.25rem 0.5rem;
    border-radius: 0.25rem;
    margin: 0.125rem;
    font-size: 0.75rem;
}
</style>

<div class="mb-8">
    <div class="flex items-center justify-between mb-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">
                <?= $isEdit ? 'Edit Article' : 'Create Article' ?>
            </h1>
            <p class="text-gray-600 mt-2">
                <?= $isEdit ? 'Update this knowledge base article.' : 'Create a new knowledge base article for the team.' ?>
            </p>
        </div>
        <div class="flex gap-3">
            <a href="admin-kb.php" 
               class="inline-flex items-center gap-2 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Back to KB Admin
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

<form method="post" class="space-y-6">
    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
    
    <!-- Basic Information -->
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Basic Information</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Article Title <span class="text-red-500">*</span>
                </label>
                <input type="text" name="title" required
                       value="<?= htmlspecialchars($article['title'] ?? '') ?>"
                       placeholder="Enter article title..."
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 form-field">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Category</label>
                <select name="category" class="w-full border border-gray-300 rounded-lg px-3 py-2 form-field">
                    <option value="">Select category...</option>
                    <option value="Policies" <?= ($article['category'] ?? '') === 'Policies' ? 'selected' : '' ?>>Policies</option>
                    <option value="Procedures" <?= ($article['category'] ?? '') === 'Procedures' ? 'selected' : '' ?>>Procedures</option>
                    <option value="Training" <?= ($article['category'] ?? '') === 'Training' ? 'selected' : '' ?>>Training</option>
                    <option value="Systems" <?= ($article['category'] ?? '') === 'Systems' ? 'selected' : '' ?>>Systems</option>
                    <option value="FAQ" <?= ($article['category'] ?? '') === 'FAQ' ? 'selected' : '' ?>>FAQ</option>
                    <option value="Setup Guides" <?= ($article['category'] ?? '') === 'Setup Guides' ? 'selected' : '' ?>>Setup Guides</option>
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                <select name="status" class="w-full border border-gray-300 rounded-lg px-3 py-2 form-field">
                    <option value="draft" <?= ($article['status'] ?? 'draft') === 'draft' ? 'selected' : '' ?>>Draft</option>
                    <option value="published" <?= ($article['status'] ?? 'draft') === 'published' ? 'selected' : '' ?>>Published</option>
                </select>
            </div>
            
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">Tags</label>
                <input type="text" name="tags" 
                       value="<?= htmlspecialchars($article['tags_string'] ?? '') ?>"
                       placeholder="Enter tags separated by commas (e.g., email, setup, imap)"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 form-field tag-input"
                       onkeyup="updateTagPreview(this.value)">
                <p class="text-xs text-gray-500 mt-1">Separate tags with commas</p>
                <div id="tag-preview" class="tag-preview">
                    <div id="tag-items"></div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Article Content -->
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-gray-900">Article Content</h2>
            <div class="flex items-center gap-2">
                <button type="button" onclick="insertEmailSetupTemplate()" 
                        class="text-sm px-3 py-1 bg-blue-100 text-blue-700 rounded hover:bg-blue-200 transition-colors">
                    📧 Email Setup Template
                </button>
                <button type="button" onclick="toggleImageUpload()" 
                        class="text-sm px-3 py-1 bg-gray-100 text-gray-700 rounded hover:bg-gray-200 transition-colors">
                    📷 Insert Image
                </button>
            </div>
        </div>
        
        <div class="editor-container" id="editor-container">
            <div id="editor-toolbar">
                <!-- Quill toolbar will be inserted here -->
            </div>
            <div id="editor" style="min-height: 400px;">
                <?= $article['content'] ?? '' ?>
            </div>
        </div>
        
        <!-- Hidden textarea to store content -->
        <textarea name="content" id="content-input" style="display: none;"></textarea>
        
        <!-- Image Upload Section (hidden by default) -->
        <div id="image-upload-section" class="mt-4 p-4 bg-gray-50 rounded-lg" style="display: none;">
            <h3 class="text-sm font-medium text-gray-700 mb-2">Upload Image</h3>
            <input type="file" id="image-upload" accept="image/*" class="mb-2">
            <button type="button" onclick="uploadImage()" class="px-3 py-1 bg-blue-600 text-white rounded text-sm hover:bg-blue-700">
                Upload & Insert
            </button>
            <button type="button" onclick="toggleImageUpload()" class="px-3 py-1 bg-gray-600 text-white rounded text-sm hover:bg-gray-700 ml-2">
                Cancel
            </button>
        </div>
    </div>
    
    <!-- Action Buttons -->
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
        <div class="flex items-center justify-between">
            <div class="text-sm text-gray-500">
                <?php if ($isEdit && $article): ?>
                    Last updated: <?= date('M j, Y g:i A', strtotime($article['updated_at'])) ?>
                <?php else: ?>
                    Article will be saved as draft by default
                <?php endif; ?>
            </div>
            
            <div class="flex items-center gap-3">
                <a href="admin-kb.php" 
                   class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                    Cancel
                </a>
                <?php if ($isEdit && $article && $article['status'] === 'published'): ?>
                    <a href="kb-article.php?slug=<?= urlencode($article['slug']) ?>" 
                       target="_blank"
                       class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                        Preview
                    </a>
                <?php endif; ?>
                <button type="submit" 
                        class="px-6 py-2 bg-black text-white rounded-lg hover:bg-gray-800 transition-colors font-medium">
                    <?= $isEdit ? 'Update Article' : 'Create Article' ?>
                </button>
            </div>
        </div>
    </div>
</form>

<script>
// Initialize Quill editor
const quill = new Quill('#editor', {
    theme: 'snow',
    modules: {
        toolbar: [
            [{ 'header': [1, 2, 3, false] }],
            ['bold', 'italic', 'underline', 'strike'],
            [{ 'list': 'ordered'}, { 'list': 'bullet' }],
            [{ 'color': [] }, { 'background': [] }],
            ['link', 'code-block'],
            ['clean']
        ]
    },
    placeholder: 'Write your article content here...'
});

// Focus tracking for editor container
const editorContainer = document.getElementById('editor-container');
quill.on('selection-change', function(range) {
    if (range) {
        editorContainer.classList.add('focused');
    } else {
        editorContainer.classList.remove('focused');
    }
});

// Update hidden textarea when content changes
quill.on('text-change', function() {
    document.getElementById('content-input').value = quill.root.innerHTML;
});

// Set initial content
document.getElementById('content-input').value = quill.root.innerHTML;

// Tag preview functionality
function updateTagPreview(tagsString) {
    const preview = document.getElementById('tag-preview');
    const itemsContainer = document.getElementById('tag-items');
    
    if (tagsString.trim()) {
        const tags = tagsString.split(',').map(tag => tag.trim()).filter(tag => tag);
        if (tags.length > 0) {
            itemsContainer.innerHTML = tags.map(tag => 
                `<span class="tag-item">${escapeHtml(tag)}</span>`
            ).join('');
            preview.classList.add('show');
        } else {
            preview.classList.remove('show');
        }
    } else {
        preview.classList.remove('show');
    }
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Initialize tag preview if editing
<?php if ($isEdit && !empty($article['tags_string'])): ?>
updateTagPreview('<?= htmlspecialchars($article['tags_string']) ?>');
<?php endif; ?>

// Image upload functionality
function toggleImageUpload() {
    const section = document.getElementById('image-upload-section');
    section.style.display = section.style.display === 'none' ? 'block' : 'none';
}

function uploadImage() {
    const fileInput = document.getElementById('image-upload');
    const file = fileInput.files[0];
    
    if (!file) {
        alert('Please select an image file');
        return;
    }
    
    if (!file.type.startsWith('image/')) {
        alert('Please select a valid image file');
        return;
    }
    
    const formData = new FormData();
    formData.append('image', file);
    formData.append('csrf_token', '<?= $_SESSION['csrf_token'] ?>');
    
    // Show loading state
    const button = event.target;
    const originalText = button.textContent;
    button.textContent = 'Uploading...';
    button.disabled = true;
    
    fetch('api/kb/upload-image.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Insert image into editor
            const range = quill.getSelection() || { index: 0 };
            quill.insertEmbed(range.index, 'image', data.url);
            
            // Reset upload section
            fileInput.value = '';
            toggleImageUpload();
            
            alert('Image uploaded successfully!');
        } else {
            alert('Upload failed: ' + (data.error || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Upload error:', error);
        alert('Upload failed. Please try again.');
    })
    .finally(() => {
        button.textContent = originalText;
        button.disabled = false;
    });
}

// Email Setup Template
function insertEmailSetupTemplate() {
    const template = `
<h2>📧 Email Setup &amp; Instructions</h2>

<div style="background: #FEF3C7; border: 1px solid #F59E0B; border-radius: 8px; padding: 16px; margin: 16px 0;">
    <h3>⚠️ Important Security Information</h3>
    <p><strong>Default Password:</strong> salon123</p>
    <p><em>Shared across all staff accounts</em></p>
    <p><strong>Security Reminder:</strong> Change your password immediately after initial setup for better account security.</p>
</div>

<h3>🌐 Web Access</h3>
<p>Access email through your browser:</p>

<p><strong>Webmail URL:</strong><br>
<code>webmail.jjosephsalon.com</code></p>

<p><strong>Setup Steps:</strong></p>
<ol>
    <li>Open your web browser</li>
    <li>Navigate to the webmail URL above</li>
    <li>Enter your email credentials</li>
    <li>Click 'Login' to access your account</li>
    <li>Select 'Roundcube' to open your mailbox</li>
</ol>

<h3>📱 iPhone Setup</h3>
<p><strong>IMAP Configuration</strong></p>

<h4>Incoming Mail - IMAP</h4>
<table style="border-collapse: collapse; width: 100%; border: 1px solid #ddd;">
    <tr style="background: #f9f9f9;">
        <td style="border: 1px solid #ddd; padding: 8px; font-weight: bold;">Server</td>
        <td style="border: 1px solid #ddd; padding: 8px;"><code>server.jjosephsalon.com</code></td>
    </tr>
    <tr>
        <td style="border: 1px solid #ddd; padding: 8px; font-weight: bold;">Port</td>
        <td style="border: 1px solid #ddd; padding: 8px;">993</td>
    </tr>
    <tr style="background: #f9f9f9;">
        <td style="border: 1px solid #ddd; padding: 8px; font-weight: bold;">Security</td>
        <td style="border: 1px solid #ddd; padding: 8px;">SSL/TLS</td>
    </tr>
    <tr>
        <td style="border: 1px solid #ddd; padding: 8px; font-weight: bold;">Username</td>
        <td style="border: 1px solid #ddd; padding: 8px;">Your email address</td>
    </tr>
    <tr style="background: #f9f9f9;">
        <td style="border: 1px solid #ddd; padding: 8px; font-weight: bold;">Password</td>
        <td style="border: 1px solid #ddd; padding: 8px;">Your password</td>
    </tr>
</table>

<h4>Outgoing Mail - SMTP</h4>
<table style="border-collapse: collapse; width: 100%; border: 1px solid #ddd;">
    <tr style="background: #f9f9f9;">
        <td style="border: 1px solid #ddd; padding: 8px; font-weight: bold;">Server</td>
        <td style="border: 1px solid #ddd; padding: 8px;"><code>server.jjosephsalon.com</code></td>
    </tr>
    <tr>
        <td style="border: 1px solid #ddd; padding: 8px; font-weight: bold;">Port</td>
        <td style="border: 1px solid #ddd; padding: 8px;">587</td>
    </tr>
    <tr style="background: #f9f9f9;">
        <td style="border: 1px solid #ddd; padding: 8px; font-weight: bold;">Security</td>
        <td style="border: 1px solid #ddd; padding: 8px;">STARTTLS</td>
    </tr>
    <tr>
        <td style="border: 1px solid #ddd; padding: 8px; font-weight: bold;">Username</td>
        <td style="border: 1px solid #ddd; padding: 8px;">Your email address</td>
    </tr>
    <tr style="background: #f9f9f9;">
        <td style="border: 1px solid #ddd; padding: 8px; font-weight: bold;">Password</td>
        <td style="border: 1px solid #ddd; padding: 8px;">Your password</td>
    </tr>
</table>

<h4>Setup Steps:</h4>
<ol>
    <li>Open Settings → Mail → Accounts</li>
    <li>Tap 'Add Account' → Select 'Other'</li>
    <li>Choose 'Add Mail Account'</li>
    <li>Enter your name, email, and password</li>
    <li>Tap 'Next' and select 'IMAP' when prompted</li>
    <li>Configure servers with settings above</li>
    <li>Tap 'Save' to complete setup</li>
</ol>

<h3>🤖 Android Setup</h3>
<p><strong>Gmail or Email App Configuration</strong></p>

<h4>Incoming Mail - IMAP</h4>
<table style="border-collapse: collapse; width: 100%; border: 1px solid #ddd;">
    <tr style="background: #f9f9f9;">
        <td style="border: 1px solid #ddd; padding: 8px; font-weight: bold;">Server</td>
        <td style="border: 1px solid #ddd; padding: 8px;"><code>server.jjosephsalon.com</code></td>
    </tr>
    <tr>
        <td style="border: 1px solid #ddd; padding: 8px; font-weight: bold;">Port</td>
        <td style="border: 1px solid #ddd; padding: 8px;">993</td>
    </tr>
    <tr style="background: #f9f9f9;">
        <td style="border: 1px solid #ddd; padding: 8px; font-weight: bold;">Security</td>
        <td style="border: 1px solid #ddd; padding: 8px;">SSL/TLS</td>
    </tr>
    <tr>
        <td style="border: 1px solid #ddd; padding: 8px; font-weight: bold;">Username</td>
        <td style="border: 1px solid #ddd; padding: 8px;">Your email address</td>
    </tr>
    <tr style="background: #f9f9f9;">
        <td style="border: 1px solid #ddd; padding: 8px; font-weight: bold;">Password</td>
        <td style="border: 1px solid #ddd; padding: 8px;">Your password</td>
    </tr>
</table>

<h4>Outgoing Mail - SMTP</h4>
<table style="border-collapse: collapse; width: 100%; border: 1px solid #ddd;">
    <tr style="background: #f9f9f9;">
        <td style="border: 1px solid #ddd; padding: 8px; font-weight: bold;">Server</td>
        <td style="border: 1px solid #ddd; padding: 8px;"><code>server.jjosephsalon.com</code></td>
    </tr>
    <tr>
        <td style="border: 1px solid #ddd; padding: 8px; font-weight: bold;">Port</td>
        <td style="border: 1px solid #ddd; padding: 8px;">587</td>
    </tr>
    <tr style="background: #f9f9f9;">
        <td style="border: 1px solid #ddd; padding: 8px; font-weight: bold;">Security</td>
        <td style="border: 1px solid #ddd; padding: 8px;">STARTTLS</td>
    </tr>
    <tr>
        <td style="border: 1px solid #ddd; padding: 8px; font-weight: bold;">Username</td>
        <td style="border: 1px solid #ddd; padding: 8px;">Your email address</td>
    </tr>
    <tr style="background: #f9f9f9;">
        <td style="border: 1px solid #ddd; padding: 8px; font-weight: bold;">Password</td>
        <td style="border: 1px solid #ddd; padding: 8px;">Your password</td>
    </tr>
</table>

<h4>Setup Steps:</h4>
<ol>
    <li>Open Email or Gmail app</li>
    <li>Tap 'Add Account' → Select 'Other'</li>
    <li>Enter your account details and password</li>
    <li>Choose 'IMAP' when prompted</li>
    <li>Configure servers with settings above</li>
    <li>Complete the setup process</li>
    <li>Your email will now sync to your device</li>
</ol>

<div style="background: #E0F2FE; border: 1px solid #0284C7; border-radius: 8px; padding: 16px; margin: 16px 0;">
    <p><strong>Need assistance with setup?</strong> Contact your manager for technical support.</p>
</div>
`;
    
    if (confirm('This will replace the current content with the email setup template. Continue?')) {
        quill.root.innerHTML = template;
        document.getElementById('content-input').value = template;
    }
}

// Form submission handler
document.querySelector('form').addEventListener('submit', function(e) {
    // Make sure content is saved to hidden field
    document.getElementById('content-input').value = quill.root.innerHTML;
});
</script>

<?php require __DIR__.'/includes/footer.php'; ?>