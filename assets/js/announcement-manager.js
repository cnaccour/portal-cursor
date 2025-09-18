/**
 * Announcement Management JavaScript
 * Handles CRUD operations for announcements with file attachments
 */

function announcementManager() {
    return {
        // Component state
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
        
        // Modal Management
        openAddModal() {
            this.resetForm();
            this.modalMode = 'add';
            this.attachments = [];
            this.showModal = true;
            this.initializeEditor();
        },
        
        openEditModal(announcement) {
            this.modalMode = 'edit';
            this.selectedAnnouncement = announcement;
            this.populateForm(announcement);
            this.attachments = announcement.attachments || [];
            this.showModal = true;
            this.initializeEditor();
        },
        
        closeModal() {
            this.destroyEditor();
            this.showModal = false;
            this.selectedAnnouncement = null;
            this.resetForm();
        },
        
        // Form Management
        resetForm() {
            this.formData = {
                title: '',
                content: '',
                category: 'general',
                pinned: false,
                expiration_date: ''
            };
        },
        
        populateForm(announcement) {
            this.formData = {
                title: announcement.title,
                content: announcement.content,
                category: announcement.category,
                pinned: announcement.pinned,
                expiration_date: announcement.expiration_date || ''
            };
        },
        
        // Rich Text Editor Management
        initializeEditor() {
            setTimeout(() => {
                this.initQuillEditor();
            }, 150);
        },
        
        destroyEditor() {
            if (this.quillEditor) {
                try {
                    delete this.quillEditor;
                    this.quillEditor = null;
                } catch (e) {
                    console.log('Error destroying editor:', e);
                }
            }
        },
        
        initQuillEditor() {
            this.destroyEditor();
            
            setTimeout(() => {
                const container = document.getElementById('content-editor');
                if (container && !this.quillEditor) {
                    this.prepareEditorContainer(container);
                    this.createQuillInstance();
                    this.setupEditorContent();
                    this.bindEditorEvents();
                }
            }, 100);
        },
        
        prepareEditorContainer(container) {
            container.innerHTML = '';
            const existingQuill = container.querySelector('.ql-container');
            if (existingQuill) {
                container.innerHTML = '';
            }
        },
        
        createQuillInstance() {
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
        },
        
        setupEditorContent() {
            if (this.modalMode === 'edit' && this.formData.content) {
                this.quillEditor.root.innerHTML = this.formData.content;
            } else {
                this.quillEditor.root.innerHTML = '';
                this.formData.content = '';
            }
        },
        
        bindEditorEvents() {
            this.quillEditor.on('text-change', () => {
                this.formData.content = this.quillEditor.root.innerHTML;
            });
        },
        
        // Form Submission
        async submitForm() {
            try {
                this.updateContentFromEditor();
                const formData = this.buildFormData();
                const response = await this.sendFormRequest(formData);
                await this.handleFormResponse(response);
            } catch (error) {
                this.handleFormError(error);
            }
        },
        
        updateContentFromEditor() {
            if (this.quillEditor) {
                this.formData.content = this.quillEditor.root.innerHTML;
            }
        },
        
        buildFormData() {
            const formData = new FormData();
            formData.append('csrf_token', window.csrfToken);
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
            
            return formData;
        },
        
        async sendFormRequest(formData) {
            const response = await fetch('/api/save-announcement.php', {
                method: 'POST',
                body: formData
            });
            
            if (!response.ok) {
                throw new Error('Network response was not ok: ' + response.status);
            }
            
            return response;
        },
        
        async handleFormResponse(response) {
            const result = await response.json();
            
            if (result.success) {
                alert(result.message);
                window.location.reload();
            } else {
                alert('Error: ' + result.error);
            }
        },
        
        handleFormError(error) {
            console.error('Form submission error:', error);
            alert('An error occurred: ' + error.message);
        },
        
        // Announcement Deletion
        async deleteAnnouncement(announcementId) {
            if (!this.confirmDeletion()) return;
            
            try {
                const response = await this.sendDeleteRequest(announcementId);
                await this.handleDeleteResponse(response);
            } catch (error) {
                this.handleDeleteError(error);
            }
        },
        
        confirmDeletion() {
            return confirm('Are you sure you want to delete this announcement?');
        },
        
        async sendDeleteRequest(announcementId) {
            const formData = new FormData();
            formData.append('csrf_token', window.csrfToken);
            formData.append('announcement_id', announcementId);
            
            const response = await fetch('/api/delete-announcement.php', {
                method: 'POST',
                body: formData
            });
            
            if (!response.ok) {
                throw new Error('Network response was not ok: ' + response.status);
            }
            
            return response;
        },
        
        async handleDeleteResponse(response) {
            const result = await response.json();
            
            if (result.success) {
                alert(result.message);
                window.location.reload();
            } else {
                alert('Error: ' + result.error);
            }
        },
        
        handleDeleteError(error) {
            console.error('Delete error:', error);
            alert('An error occurred: ' + error.message);
        },
        
        // File Management
        handleFileSelect(event) {
            this.handleFiles(event.target.files);
        },
        
        handleFileDrop(event) {
            this.handleFiles(event.dataTransfer.files);
        },
        
        async handleFiles(fileList) {
            const files = Array.from(fileList);
            
            if (!this.validateFiles(files)) return;
            
            if (this.shouldUploadImmediately()) {
                await this.uploadFiles(files, this.selectedAnnouncement.id);
            } else {
                this.addToPendingList(files);
            }
        },
        
        validateFiles(files) {
            for (const file of files) {
                if (!this.validateFileSize(file)) return false;
                if (!this.validateFileType(file)) return false;
            }
            return true;
        },
        
        validateFileSize(file) {
            const maxSize = 10 * 1024 * 1024; // 10MB
            if (file.size > maxSize) {
                alert(`File "${file.name}" is too large. Maximum size is 10MB.`);
                return false;
            }
            return true;
        },
        
        validateFileType(file) {
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
                return false;
            }
            return true;
        },
        
        shouldUploadImmediately() {
            return this.modalMode === 'edit' && 
                   this.selectedAnnouncement && 
                   this.selectedAnnouncement.id;
        },
        
        addToPendingList(files) {
            for (const file of files) {
                this.attachments.push({
                    name: file.name,
                    size: file.size,
                    type: file.type,
                    pending: true,
                    file: file
                });
            }
        },
        
        async uploadFiles(files, announcementId) {
            this.uploadingFiles = true;
            
            try {
                for (const file of files) {
                    await this.uploadSingleFile(file, announcementId);
                }
            } catch (error) {
                alert('Upload error: ' + error.message);
            } finally {
                this.uploadingFiles = false;
            }
        },
        
        async uploadSingleFile(file, announcementId) {
            const formData = new FormData();
            formData.append('csrf_token', window.csrfToken);
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
        },
        
        async removeAttachment(index) {
            const attachment = this.attachments[index];
            
            if (attachment.pending) {
                this.attachments.splice(index, 1);
                return;
            }
            
            if (!this.confirmAttachmentDeletion()) return;
            
            try {
                await this.deleteAttachment(attachment);
                this.attachments.splice(index, 1);
            } catch (error) {
                alert('Error: ' + error.message);
            }
        },
        
        confirmAttachmentDeletion() {
            return confirm('Are you sure you want to delete this attachment?');
        },
        
        async deleteAttachment(attachment) {
            const formData = new FormData();
            formData.append('csrf_token', window.csrfToken);
            formData.append('announcement_id', this.selectedAnnouncement.id);
            formData.append('filename', attachment.filename);
            
            const response = await fetch('/api/delete-attachment.php', {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            
            if (!result.success) {
                throw new Error('Error deleting attachment: ' + result.error);
            }
        },
        
        // Utility Functions
        formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }
    }
}