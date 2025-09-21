<?php
require __DIR__.'/includes/auth.php';
require_login();
require_role('admin'); // Only admins can access this page
require __DIR__.'/includes/header.php';

// Generate CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Load all announcements using helper functions
require_once __DIR__.'/includes/announcement-helpers.php';
$allAnnouncements = getDisplayAnnouncements(true); // Include expired for admin view
$categories = getAnnouncementCategories($allAnnouncements);

$message = '';
?>

<div x-data="announcementManager()">

    <!-- Mobile-First Header -->
    <div class="mb-6">
        <h1 class="text-2xl font-semibold mb-4">Announcement Management</h1>
        <button @click="openAddModal()" 
                class="w-full sm:w-auto px-4 py-3 text-white bg-black hover:bg-gray-800 rounded-md transition-colors flex items-center justify-center gap-2 font-medium">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Add New Announcement
        </button>
    </div>

    <?php if ($message): ?>
        <?= $message ?>
        <div class="mb-6"></div>
    <?php endif; ?>

    <!-- Mobile-First Announcements Cards -->
    <div class="space-y-4">
        <?php if (empty($allAnnouncements)): ?>
            <div class="bg-white rounded-md border p-8 text-center">
                <div class="max-w-sm mx-auto">
                    <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"></path>
                    </svg>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No announcements</h3>
                    <p class="text-sm text-gray-500 mb-4">Get started by creating your first announcement to communicate with your team.</p>
                    <button @click="openAddModal()" 
                            class="inline-flex items-center px-4 py-2 text-white bg-black hover:bg-gray-800 rounded-md transition-colors font-medium">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Add Your First Announcement
                    </button>
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($allAnnouncements as $index => $announcement): ?>
                <div class="bg-white rounded-md border hover:border-gray-300 transition-all duration-200 overflow-hidden">
                    <!-- Header Section -->
                    <div class="p-4 border-b bg-gray-50">
                        <div class="flex items-start justify-between gap-3">
                            <!-- Left: Title and Pin -->
                            <div class="flex items-start gap-2 flex-1 min-w-0">
                                <?php if ($announcement['pinned']): ?>
                                    <svg class="w-4 h-4 text-red-500 mt-1 flex-shrink-0" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/>
                                    </svg>
                                <?php endif; ?>
                                <div class="min-w-0 flex-1">
                                    <h3 class="text-lg font-semibold text-gray-900 truncate"><?= htmlspecialchars($announcement['title']) ?></h3>
                                    <p class="text-sm text-gray-600 mt-1 line-clamp-2">
                                        <?php 
                                        $plainContent = strip_tags($announcement['content']);
                                        $preview = strlen($plainContent) > 100 ? substr($plainContent, 0, 100) . '...' : $plainContent;
                                        echo htmlspecialchars($preview);
                                        ?>
                                    </p>
                                </div>
                            </div>
                            
                            <!-- Right: Attachment Indicator Only -->
                            <div class="flex items-center gap-2 shrink-0">
                                <?php if (!empty($announcement['attachments'])): ?>
                                    <span class="px-2 py-1 text-xs font-medium rounded-full bg-black text-white flex items-center gap-1">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path>
                                        </svg>
                                        <?= count($announcement['attachments']) ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Content Section -->
                    <div class="p-4">
                        <!-- Actions -->
                        <div class="flex flex-col sm:flex-row gap-3">
                            <?php $isStatic = isset($announcement['static']) && $announcement['static']; ?>
                            
                            <?php if (!$isStatic): ?>
                                <button @click="openEditModal(<?= htmlspecialchars(json_encode($announcement), ENT_QUOTES) ?>)"
                                        class="w-full sm:flex-1 inline-flex items-center justify-center px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition-colors">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                    Edit Announcement
                                </button>
                            <?php else: ?>
                                <div class="w-full sm:flex-1 inline-flex items-center justify-center px-4 py-2 border border-amber-300 rounded-md text-sm font-medium text-amber-700 bg-amber-50">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                    </svg>
                                    Static Content (Not Editable)
                                </div>
                            <?php endif; ?>
                            
                            <?php $isAdminDeletable = !isset($announcement['admin_deletable']) || $announcement['admin_deletable']; ?>
                            <?php if ($isAdminDeletable): ?>
                                <button @click="deleteAnnouncement('<?= htmlspecialchars($announcement['id']) ?>')"
                                        class="w-full sm:flex-1 inline-flex items-center justify-center px-4 py-2 border border-red-300 rounded-md text-sm font-medium text-red-700 bg-red-50 hover:bg-red-100 transition-colors">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                    Delete Announcement
                                </button>
                            <?php else: ?>
                                <div class="w-full sm:flex-1 inline-flex items-center justify-center px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-500 bg-gray-100">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728L5.636 5.636m12.728 12.728L18 12M6 6l12 12"></path>
                                    </svg>
                                    Cannot Delete
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Status, Category and Date Info - Bottom -->
                        <div class="mt-4 pt-3 border-t border-gray-100">
                            <div class="flex flex-wrap items-center justify-between gap-2 text-xs text-gray-500">
                                <div class="flex items-center gap-2">
                                    <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800 capitalize">
                                        <?= htmlspecialchars($announcement['category']) ?>
                                    </span>
                                    
                                    <?php if ($announcement['pinned']): ?>
                                        <span class="text-red-600 font-medium">📌 Pinned</span>
                                        <span class="text-gray-300">•</span>
                                    <?php endif; ?>
                                    
                                    <?php 
                                    $isExpired = !empty($announcement['expiration_date']) && strtotime($announcement['expiration_date']) < time();
                                    $isExpiring = !empty($announcement['expiration_date']) && strtotime($announcement['expiration_date']) < strtotime('+7 days');
                                    ?>
                                    <?php if ($isExpired): ?>
                                        <span class="text-gray-500 font-medium">⏰ Expired</span>
                                    <?php elseif ($isExpiring): ?>
                                        <span class="text-orange-600 font-medium">⚠️ Expiring Soon</span>
                                    <?php else: ?>
                                        <span class="text-black font-medium">Active</span>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="flex items-center gap-2">
                                    <span><?= date('M j, Y', strtotime($announcement['date_created'])) ?></span>
                                    <?php if (!empty($announcement['expiration_date'])): ?>
                                        <span class="text-gray-300">•</span>
                                        <span>Expires <?= date('M j, Y', strtotime($announcement['expiration_date'])) ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Add/Edit Modal - Mobile Optimized -->
    <div x-show="showModal" 
         class="fixed inset-0 z-50 overflow-y-auto"
         style="display: none;">
        
        <!-- Background overlay -->
        <div class="fixed inset-0 bg-black bg-opacity-50" @click="closeModal()"></div>
        
        <!-- Modal panel -->
        <div class="flex items-start justify-center min-h-screen p-2 sm:p-4 sm:pt-6">
            <div class="bg-white rounded-lg shadow-xl w-full max-w-2xl max-h-[95vh] overflow-y-auto relative z-10">
                
                <!-- Modal Header -->
                <div class="sticky top-0 bg-white flex items-center justify-between p-4 sm:p-6 border-b border-gray-200 rounded-t-lg">
                    <h3 class="text-lg font-semibold text-gray-900" x-text="modalMode === 'add' ? 'Add New Announcement' : 'Edit Announcement'"></h3>
                    <button @click="closeModal()" class="text-gray-400 hover:text-gray-600 p-1 rounded-md hover:bg-gray-100">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                
                <!-- Modal Body -->
                <form @submit.prevent="submitForm" class="p-4 sm:p-6">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                    <input type="hidden" name="mode" x-model="modalMode">
                    <input type="hidden" name="announcement_id" :value="selectedAnnouncement ? selectedAnnouncement.id : ''">
                    
                    <div class="space-y-6">
                        <!-- Title -->
                        <div>
                            <label for="title" class="block text-sm font-medium text-gray-700 mb-2">Title</label>
                            <input type="text" id="title" name="title" required
                                   x-model="formData.title"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2" style="--tw-ring-color: #AF831A;" focus:border-transparent">
                        </div>
                        
                        <!-- Content -->
                        <div>
                            <label for="content" class="block text-sm font-medium text-gray-700 mb-2">Content</label>
                            <div id="content-editor" class="bg-white border border-gray-300 rounded-lg" style="height: 200px;"></div>
                            <textarea id="content" name="content" x-model="formData.content" class="hidden" required></textarea>
                        </div>
                        
                        <!-- Category -->
                        <div>
                            <label for="category" class="block text-sm font-medium text-gray-700 mb-2">Category</label>
                            <select id="category" name="category" required
                                    x-model="formData.category"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2" style="--tw-ring-color: #AF831A;">
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?= htmlspecialchars($category) ?>" class="capitalize">
                                        <?= htmlspecialchars(ucfirst($category)) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <!-- Pin Status -->
                        <div class="flex items-center">
                            <input type="checkbox" id="pinned" name="pinned" value="1"
                                   x-model="formData.pinned"
                                   class="h-4 w-4 border-gray-300 rounded" style="color: #AF831A; --tw-ring-color: #AF831A;">
                            <label for="pinned" class="ml-2 block text-sm text-gray-700">Pin this announcement to the top</label>
                        </div>
                        
                        <!-- Expiration Date -->
                        <div>
                            <label for="expiration_date" class="block text-sm font-medium text-gray-700 mb-2">
                                Expiration Date (Optional)
                            </label>
                            <input type="date" id="expiration_date" name="expiration_date"
                                   x-model="formData.expiration_date"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2" style="--tw-ring-color: #AF831A;" focus:border-transparent">
                            <p class="text-xs text-gray-500 mt-1">Leave empty for announcements that don't expire</p>
                        </div>
                        
                        <!-- Attachments -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Attachments</label>
                            
                            <!-- File Upload Area -->
                            <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-gray-400 transition-colors"
                                 @dragover.prevent
                                 @drop.prevent="handleFileDrop($event)"
                                 @click="$refs.fileInput.click()">
                                <svg class="w-8 h-8 text-gray-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                </svg>
                                <p class="text-sm text-gray-600">Drop files here or <span class="font-medium" style="color: #AF831A;">click to upload</span></p>
                                <p class="text-xs text-gray-500 mt-1">PDF, Word, Excel, Images (max 10MB each)</p>
                            </div>
                            
                            <input type="file" x-ref="fileInput" multiple 
                                   accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png,.gif,.webp"
                                   @change="handleFileSelect($event)"
                                   class="hidden">
                            
                            <!-- Uploaded Files List -->
                            <div x-show="attachments.length > 0" class="mt-4 space-y-2">
                                <template x-for="(file, index) in attachments" :key="index">
                                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                        <div class="flex items-center space-x-3">
                                            <div class="w-8 h-8 rounded-md flex items-center justify-center" style="background-color: #FDF7E7;">
                                                <svg class="w-4 h-4" style="color: #AF831A;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                </svg>
                                            </div>
                                            <div>
                                                <p class="text-sm font-medium text-gray-900" x-text="file.original_name || file.name"></p>
                                                <p class="text-xs text-gray-500" x-text="formatFileSize(file.file_size || file.size)"></p>
                                            </div>
                                        </div>
                                        <button type="button" @click="removeAttachment(index)"
                                                class="text-red-600 hover:text-red-800">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                        </button>
                                    </div>
                                </template>
                            </div>
                            
                            <!-- Upload Progress -->
                            <div x-show="uploadingFiles" class="mt-2">
                                <div class="rounded-md p-3" style="background-color: #FDF7E7; border: 1px solid #E6D5A1;">
                                    <div class="flex items-center">
                                        <svg class="animate-spin w-4 h-4 mr-2" style="color: #AF831A;" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        <span class="text-sm" style="color: #8B6914;">Uploading files...</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Modal Footer - Mobile Friendly -->
                    <div class="sticky bottom-0 bg-white border-t border-gray-200 p-4 sm:p-6 rounded-b-lg">
                        <div class="flex flex-col sm:flex-row gap-3 sm:justify-end">
                            <button type="button" @click="closeModal()" 
                                    class="w-full sm:w-auto px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-md transition-colors">
                                Cancel
                            </button>
                            <button type="submit" 
                                    class="w-full sm:w-auto px-4 py-2 text-sm font-medium text-white rounded-md transition-colors" 
                                    style="background-color: #AF831A;" onmouseover="this.style.backgroundColor='#8B6914'" onmouseout="this.style.backgroundColor='#AF831A'">
                                <span x-text="modalMode === 'add' ? 'Create Announcement' : 'Update Announcement'"></span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>

<!-- Information Note -->
<div class="mt-6 rounded-xl p-4" style="background-color: #FDF7E7; border: 1px solid #E6D5A1;">
    <h3 class="font-semibold mb-2" style="color: #8B6914;">Announcement Management</h3>
    <p class="text-sm" style="color: #8B6914;">
        All current announcements are editable. You can create, edit, and delete announcements through this interface. 
        The rich text editor supports formatting, links, lists, and basic styling.
    </p>
</div>

<script>
    // Make CSRF token available to external JavaScript
    window.csrfToken = '<?= $_SESSION['csrf_token'] ?>';
</script>
<script src="assets/js/announcement-manager.js"></script>

<?php require __DIR__.'/includes/footer.php'; ?>