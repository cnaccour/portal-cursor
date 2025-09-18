<?php
require __DIR__.'/includes/auth.php';
require_login();
require_role('admin'); // Only admins can access this page
require __DIR__.'/includes/header.php';

// Generate CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Load all announcements (static + dynamic)
$allAnnouncements = include __DIR__.'/includes/all-announcements.php';

// Sort announcements: pinned first, then by date (most recent first)
usort($allAnnouncements, function($a, $b) {
    if ($a['pinned'] !== $b['pinned']) {
        return $b['pinned'] <=> $a['pinned'];
    }
    return strtotime($b['date_created']) <=> strtotime($a['date_created']);
});

// Get unique categories for the dropdown
$categories = array_unique(array_column($allAnnouncements, 'category'));
sort($categories);

$message = '';
?>

<div x-data="announcementManager()">

    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-semibold">Announcement Management</h1>
        <button @click="openAddModal()" 
                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
            <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Add New Announcement
        </button>
    </div>

    <?php if ($message): ?>
        <?= $message ?>
        <div class="mb-6"></div>
    <?php endif; ?>

    <!-- Announcements Table -->
    <div class="bg-white rounded-xl border overflow-hidden">
        <div class="p-6 border-b">
            <h2 class="text-lg font-semibold">All Announcements</h2>
            <p class="text-sm text-gray-600 mt-1">Manage your salon announcements and notifications.</p>
        </div>
        
        <?php if (empty($allAnnouncements)): ?>
            <div class="p-12 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No announcements</h3>
                <p class="mt-1 text-sm text-gray-500">Get started by creating your first announcement.</p>
                <div class="mt-6">
                    <button @click="openAddModal()" 
                            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                        Add Announcement
                    </button>
                </div>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Announcement
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Category
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Date
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($allAnnouncements as $index => $announcement): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <div class="flex items-start">
                                    <?php if ($announcement['pinned']): ?>
                                        <svg class="w-4 h-4 text-red-500 mr-2 mt-1 flex-shrink-0" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/>
                                        </svg>
                                    <?php endif; ?>
                                    <div>
                                        <div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($announcement['title']) ?></div>
                                        <div class="text-sm text-gray-500 mt-1">
                                            <?php 
                                            // Strip HTML tags for preview and show plain text
                                            $plainContent = strip_tags($announcement['content']);
                                            $preview = strlen($plainContent) > 80 ? substr($plainContent, 0, 80) . '...' : $plainContent;
                                            echo htmlspecialchars($preview);
                                            ?>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800 capitalize">
                                    <?= htmlspecialchars($announcement['category']) ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex flex-col space-y-1">
                                    <?php if ($announcement['pinned']): ?>
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                            Pinned
                                        </span>
                                    <?php endif; ?>
                                    <?php 
                                    $isExpired = !empty($announcement['expiration_date']) && strtotime($announcement['expiration_date']) < time();
                                    $isExpiring = !empty($announcement['expiration_date']) && strtotime($announcement['expiration_date']) < strtotime('+7 days');
                                    ?>
                                    <?php if ($isExpired): ?>
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                            Expired
                                        </span>
                                    <?php elseif ($isExpiring): ?>
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                            Expiring Soon
                                        </span>
                                    <?php else: ?>
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                            Active
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <div><?= date('M j, Y', strtotime($announcement['date_created'])) ?></div>
                                <?php if (!empty($announcement['expiration_date'])): ?>
                                    <div class="text-xs text-gray-400">Expires: <?= date('M j, Y', strtotime($announcement['expiration_date'])) ?></div>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <?php 
                                // For now, all announcements are editable (we'll add truly static ones later)
                                $isStatic = false;
                                ?>
                                <?php if ($isStatic): ?>
                                    <span class="px-2 py-1 text-xs bg-gray-100 text-gray-500 rounded">
                                        <svg class="w-3 h-3 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                        </svg>
                                        Locked
                                    </span>
                                <?php else: ?>
                                    <div class="flex space-x-2">
                                        <button @click="openEditModal(<?= htmlspecialchars(json_encode($announcement), ENT_QUOTES) ?>)"
                                                class="text-blue-600 hover:text-blue-900">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                            </svg>
                                        </button>
                                        <button @click="deleteAnnouncement('<?= htmlspecialchars($announcement['id']) ?>')"
                                                class="text-red-600 hover:text-red-900">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                        </button>
                                    </div>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <!-- Add/Edit Modal -->
    <div x-show="showModal" 
         class="fixed inset-0 z-50 overflow-y-auto"
         style="display: none;">
        
        <!-- Background overlay -->
        <div class="fixed inset-0 bg-black bg-opacity-50" @click="closeModal()"></div>
        
        <!-- Modal panel -->
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto relative z-10">
                
                <!-- Modal Header -->
                <div class="flex items-center justify-between p-6 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900" x-text="modalMode === 'add' ? 'Add New Announcement' : 'Edit Announcement'"></h3>
                    <button @click="closeModal()" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                
                <!-- Modal Body -->
                <form @submit.prevent="submitForm" class="p-6">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                    <input type="hidden" name="mode" x-model="modalMode">
                    <input type="hidden" name="announcement_id" x-model="selectedAnnouncement?.id">
                    
                    <div class="space-y-6">
                        <!-- Title -->
                        <div>
                            <label for="title" class="block text-sm font-medium text-gray-700 mb-2">Title</label>
                            <input type="text" id="title" name="title" required
                                   x-model="formData.title"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                        
                        <!-- Content -->
                        <div>
                            <label for="content" class="block text-sm font-medium text-gray-700 mb-2">Content</label>
                            <div id="content-editor" class="bg-white border border-gray-300 rounded-lg" style="height: 200px;"></div>
                            <textarea id="content" name="content" x-model="formData.content" class="hidden" required></textarea>
                        </div>
                        
                        <!-- Category and Pin Status -->
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label for="category" class="block text-sm font-medium text-gray-700 mb-2">Category</label>
                                <select id="category" name="category" required
                                        x-model="formData.category"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    <option value="general">General</option>
                                    <option value="system">System</option>
                                    <option value="training">Training</option>
                                    <option value="schedule">Schedule</option>
                                    <option value="policy">Policy</option>
                                    <option value="events">Events</option>
                                    <option value="safety">Safety</option>
                                </select>
                            </div>
                            
                            <div class="flex items-center pt-8">
                                <input type="checkbox" id="pinned" name="pinned" value="1"
                                       x-model="formData.pinned"
                                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                <label for="pinned" class="ml-2 block text-sm text-gray-700">Pin announcement</label>
                            </div>
                        </div>
                        
                        <!-- Expiration Date -->
                        <div>
                            <label for="expiration_date" class="block text-sm font-medium text-gray-700 mb-2">
                                Expiration Date (Optional)
                            </label>
                            <input type="date" id="expiration_date" name="expiration_date"
                                   x-model="formData.expiration_date"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
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
                                <p class="text-sm text-gray-600">Drop files here or <span class="text-blue-600 font-medium">click to upload</span></p>
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
                                            <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                                                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                                <div class="bg-blue-50 border border-blue-200 rounded-lg p-3">
                                    <div class="flex items-center">
                                        <svg class="animate-spin w-4 h-4 text-blue-600 mr-2" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        <span class="text-sm text-blue-700">Uploading files...</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Modal Footer -->
                    <div class="flex justify-end space-x-3 mt-6 pt-6 border-t border-gray-200">
                        <button type="button" @click="closeModal()" 
                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg">
                            Cancel
                        </button>
                        <button type="submit" 
                                class="px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-lg">
                            <span x-text="modalMode === 'add' ? 'Create Announcement' : 'Update Announcement'"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>

<!-- Information Note -->
<div class="mt-6 bg-blue-50 border border-blue-200 rounded-xl p-4">
    <h3 class="font-semibold text-blue-800 mb-2">Announcement Management</h3>
    <p class="text-sm text-blue-700">
        All current announcements are editable. You can create, edit, and delete announcements through this interface. 
        The rich text editor supports formatting, links, lists, and basic styling.
    </p>
</div>

<script>
function announcementManager() {
    return {
        showModal: false,
        modalMode: 'add',
        selectedAnnouncement: null,
        formData: {
            title: '',
            content: '',
            category: 'general',
            pinned: false,
            expiration_date: ''
        },
        attachments: [],
        uploadingFiles: false,
        quillEditor: null,
        
        openAddModal() {
            this.modalMode = 'add';
            this.selectedAnnouncement = null;
            this.formData = {
                title: '',
                content: '',
                category: 'general',
                pinned: false,
                expiration_date: ''
            };
            this.attachments = [];
            this.showModal = true;
            
            // Initialize Quill editor after modal opens
            setTimeout(() => {
                this.initQuillEditor();
            }, 150);
        },
        
        openEditModal(announcement) {
            this.modalMode = 'edit';
            this.selectedAnnouncement = announcement;
            this.formData = {
                title: announcement.title,
                content: announcement.content,
                category: announcement.category,
                pinned: announcement.pinned,
                expiration_date: announcement.expiration_date || ''
            };
            this.attachments = announcement.attachments || [];
            this.showModal = true;
            
            // Initialize Quill editor after modal opens
            setTimeout(() => {
                this.initQuillEditor();
            }, 150);
        },
        
        closeModal() {
            // Properly destroy Quill instance before closing
            if (this.quillEditor) {
                try {
                    // Clear the editor container
                    const container = document.getElementById('content-editor');
                    if (container) {
                        container.innerHTML = '';
                    }
                    this.quillEditor = null;
                } catch (e) {
                    console.log('Error destroying editor:', e);
                }
            }
            this.showModal = false;
            this.selectedAnnouncement = null;
        },
        
        initQuillEditor() {
            // Destroy any existing editor first
            if (this.quillEditor) {
                try {
                    const container = document.getElementById('content-editor');
                    if (container) {
                        container.innerHTML = '';
                    }
                    this.quillEditor = null;
                } catch (e) {
                    console.log('Error cleaning up previous editor:', e);
                }
            }
            
            // Wait for DOM to be ready
            const container = document.getElementById('content-editor');
            if (container) {
                // Clear any existing content
                container.innerHTML = '';
                
                // Create new Quill instance
                this.quillEditor = new Quill('#content-editor', {
                    theme: 'snow',
                    modules: {
                        toolbar: [
                            [{ 'header': [1, 2, 3, false] }],
                            ['bold', 'italic', 'underline'],
                            [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                            ['link'],
                            ['clean']
                        ]
                    }
                });
                
                // Set initial content (only for edit mode)
                if (this.modalMode === 'edit' && this.formData.content) {
                    this.quillEditor.root.innerHTML = this.formData.content;
                } else {
                    // For add mode, ensure content is empty
                    this.quillEditor.root.innerHTML = '';
                    this.formData.content = '';
                }
                
                // Update formData when editor content changes
                this.quillEditor.on('text-change', () => {
                    this.formData.content = this.quillEditor.root.innerHTML;
                });
            }
        },
        
        async submitForm() {
            try {
                // Get content from Quill editor
                if (this.quillEditor) {
                    this.formData.content = this.quillEditor.root.innerHTML;
                }
                
                const formData = new FormData();
                formData.append('csrf_token', '<?= $_SESSION['csrf_token'] ?>');
                formData.append('mode', this.modalMode);
                formData.append('title', this.formData.title);
                formData.append('content', this.formData.content);
                formData.append('category', this.formData.category);
                formData.append('expiration_date', this.formData.expiration_date);
                
                if (this.formData.pinned) {
                    formData.append('pinned', '1');
                }
                
                if (this.modalMode === 'edit' && this.selectedAnnouncement) {
                    formData.append('announcement_id', this.selectedAnnouncement.id);
                }
                
                const response = await fetch('/api/save-announcement.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    // Show success message and reload page
                    alert(result.message);
                    window.location.reload();
                } else {
                    alert('Error: ' + result.error);
                }
            } catch (error) {
                alert('An error occurred: ' + error.message);
            }
        },
        
        async deleteAnnouncement(announcementId) {
            if (!confirm('Are you sure you want to delete this announcement?')) {
                return;
            }
            
            try {
                const formData = new FormData();
                formData.append('csrf_token', '<?= $_SESSION['csrf_token'] ?>');
                formData.append('announcement_id', announcementId);
                
                const response = await fetch('/api/delete-announcement.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    alert(result.message);
                    window.location.reload();
                } else {
                    alert('Error: ' + result.error);
                }
            } catch (error) {
                alert('An error occurred: ' + error.message);
            }
        },
        
        handleFileSelect(event) {
            this.handleFiles(event.target.files);
        },
        
        handleFileDrop(event) {
            this.handleFiles(event.dataTransfer.files);
        },
        
        async handleFiles(fileList) {
            const files = Array.from(fileList);
            
            // Validate files
            for (const file of files) {
                if (file.size > 10 * 1024 * 1024) { // 10MB
                    alert(`File "${file.name}" is too large. Maximum size is 10MB.`);
                    return;
                }
                
                const allowedTypes = [
                    'application/pdf',
                    'application/msword',
                    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    'application/vnd.ms-excel',
                    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    'image/jpeg',
                    'image/png',
                    'image/gif',
                    'image/webp'
                ];
                
                if (!allowedTypes.includes(file.type)) {
                    alert(`File "${file.name}" is not a supported type.`);
                    return;
                }
            }
            
            // If we're editing, upload files immediately
            if (this.modalMode === 'edit' && this.selectedAnnouncement?.id) {
                await this.uploadFiles(files, this.selectedAnnouncement.id);
            } else {
                // For new announcements, just add to pending list
                for (const file of files) {
                    this.attachments.push({
                        name: file.name,
                        size: file.size,
                        type: file.type,
                        pending: true,
                        file: file
                    });
                }
            }
        },
        
        async uploadFiles(files, announcementId) {
            this.uploadingFiles = true;
            
            try {
                for (const file of files) {
                    const formData = new FormData();
                    formData.append('csrf_token', '<?= $_SESSION['csrf_token'] ?>');
                    formData.append('announcement_id', announcementId);
                    formData.append('file', file);
                    
                    const response = await fetch('/api/upload-attachment.php', {
                        method: 'POST',
                        body: formData
                    });
                    
                    const result = await response.json();
                    
                    if (result.success) {
                        this.attachments.push(result.file);
                    } else {
                        alert(`Error uploading ${file.name}: ${result.error}`);
                    }
                }
            } catch (error) {
                alert('Upload error: ' + error.message);
            } finally {
                this.uploadingFiles = false;
            }
        },
        
        async removeAttachment(index) {
            const attachment = this.attachments[index];
            
            if (attachment.pending) {
                // Just remove from pending list
                this.attachments.splice(index, 1);
                return;
            }
            
            if (!confirm('Are you sure you want to delete this attachment?')) {
                return;
            }
            
            try {
                const formData = new FormData();
                formData.append('csrf_token', '<?= $_SESSION['csrf_token'] ?>');
                formData.append('announcement_id', this.selectedAnnouncement.id);
                formData.append('filename', attachment.filename);
                
                const response = await fetch('/api/delete-attachment.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    this.attachments.splice(index, 1);
                } else {
                    alert('Error deleting attachment: ' + result.error);
                }
            } catch (error) {
                alert('Error: ' + error.message);
            }
        },
        
        formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }
    }
}
</script>

<?php require __DIR__.'/includes/footer.php'; ?>