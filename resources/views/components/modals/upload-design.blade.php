@props([
    'id' => 'uploadDesignModal'
])

<x-ui.modal :id="$id" title="Upload Design Asset" size="lg" centered>
    <form id="uploadDesignForm" enctype="multipart/form-data">
        @csrf
        
        <!-- File Upload Area -->
        <div class="mb-4">
            <label class="form-label fw-semibold">Design Files</label>
            <div class="upload-area border-2 border-dashed rounded-3 p-4 text-center position-relative" 
                 id="uploadArea"
                 ondrop="handleDrop(event)" 
                 ondragover="handleDragOver(event)" 
                 ondragleave="handleDragLeave(event)">
                <div class="upload-content">
                    <i class="bi bi-cloud-upload text-primary mb-3" style="font-size: 3rem;"></i>
                    <h5 class="mb-2">Drop files here or click to browse</h5>
                    <p class="text-muted mb-3">
                        Supported formats: JPG, PNG, PDF, AI, PSD, SVG<br>
                        Maximum file size: 10MB per file
                    </p>
                    <input type="file" 
                           id="designFiles" 
                           name="design_files[]" 
                           multiple 
                           accept=".jpg,.jpeg,.png,.pdf,.ai,.psd,.svg"
                           class="d-none">
                    <button type="button" 
                            class="btn btn-outline-primary" 
                            onclick="document.getElementById('designFiles').click()">
                        <i class="bi bi-folder2-open me-2"></i>Browse Files
                    </button>
                </div>
            </div>
        </div>
        
        <!-- File Preview Area -->
        <div id="filePreview" class="mb-4" style="display: none;">
            <label class="form-label fw-semibold">Selected Files</label>
            <div id="fileList" class="border rounded-3 p-3 bg-light">
                <!-- Files will be listed here -->
            </div>
        </div>
        
        <!-- Asset Details -->
        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="assetName" class="form-label fw-semibold">Asset Name</label>
                    <input type="text" 
                           class="form-control" 
                           id="assetName" 
                           name="asset_name" 
                           placeholder="Enter a descriptive name"
                           required>
                    <div class="form-text">This will help you identify the asset later</div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="assetCategory" class="form-label fw-semibold">Category</label>
                    <select class="form-select" id="assetCategory" name="category">
                        <option value="">Select category</option>
                        <option value="logo">Logo</option>
                        <option value="business_card">Business Card</option>
                        <option value="flyer">Flyer</option>
                        <option value="poster">Poster</option>
                        <option value="banner">Banner</option>
                        <option value="other">Other</option>
                    </select>
                </div>
            </div>
        </div>
        
        <div class="mb-3">
            <label for="assetDescription" class="form-label fw-semibold">Description</label>
            <textarea class="form-control" 
                      id="assetDescription" 
                      name="description" 
                      rows="3" 
                      placeholder="Optional description of the design asset"></textarea>
        </div>
        
        <!-- Tags -->
        <div class="mb-4">
            <label for="assetTags" class="form-label fw-semibold">Tags</label>
            <input type="text" 
                   class="form-control" 
                   id="assetTags" 
                   name="tags" 
                   placeholder="Enter tags separated by commas (e.g., red, modern, corporate)">
            <div class="form-text">Tags help you find your assets quickly</div>
        </div>
        
        <!-- Upload Progress -->
        <div id="uploadProgress" class="mb-3" style="display: none;">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="fw-semibold">Uploading...</span>
                <span id="progressText">0%</span>
            </div>
            <div class="progress">
                <div class="progress-bar progress-bar-striped progress-bar-animated" 
                     id="progressBar" 
                     role="progressbar" 
                     style="width: 0%"></div>
            </div>
        </div>
    </form>
    
    <x-slot name="footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
            Cancel
        </button>
        <button type="button" class="btn btn-primary" id="uploadButton" onclick="uploadDesigns()">
            <i class="bi bi-cloud-upload me-2"></i>Upload Assets
        </button>
    </x-slot>
</x-ui.modal>

@push('styles')
<style>
.upload-area {
    border-color: #dee2e6 !important;
    background-color: #f8f9fa;
    transition: all 0.3s ease;
    cursor: pointer;
}

.upload-area:hover {
    border-color: var(--bs-primary) !important;
    background-color: rgba(var(--bs-primary-rgb), 0.05);
}

.upload-area.drag-over {
    border-color: var(--bs-primary) !important;
    background-color: rgba(var(--bs-primary-rgb), 0.1);
    transform: scale(1.02);
}

.file-item {
    display: flex;
    align-items: center;
    padding: 0.75rem;
    border: 1px solid #dee2e6;
    border-radius: 0.5rem;
    margin-bottom: 0.5rem;
    background-color: white;
}

.file-item:last-child {
    margin-bottom: 0;
}

.file-icon {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 0.375rem;
    margin-right: 0.75rem;
}

.file-info {
    flex: 1;
}

.file-name {
    font-weight: 500;
    margin-bottom: 0.25rem;
}

.file-size {
    font-size: 0.875rem;
    color: #6c757d;
}

.file-remove {
    color: #dc3545;
    cursor: pointer;
    padding: 0.25rem;
    border-radius: 0.25rem;
    transition: background-color 0.2s;
}

.file-remove:hover {
    background-color: rgba(220, 53, 69, 0.1);
}
</style>
@endpush

@push('scripts')
<script>
let selectedFiles = [];

// File upload area click handler
document.getElementById('uploadArea').addEventListener('click', function() {
    document.getElementById('designFiles').click();
});

// File input change handler
document.getElementById('designFiles').addEventListener('change', function(e) {
    handleFiles(e.target.files);
});

// Drag and drop handlers
function handleDragOver(e) {
    e.preventDefault();
    e.stopPropagation();
    document.getElementById('uploadArea').classList.add('drag-over');
}

function handleDragLeave(e) {
    e.preventDefault();
    e.stopPropagation();
    document.getElementById('uploadArea').classList.remove('drag-over');
}

function handleDrop(e) {
    e.preventDefault();
    e.stopPropagation();
    document.getElementById('uploadArea').classList.remove('drag-over');
    
    const files = e.dataTransfer.files;
    handleFiles(files);
}

function handleFiles(files) {
    const validFiles = [];
    const maxSize = 10 * 1024 * 1024; // 10MB
    const allowedTypes = ['image/jpeg', 'image/png', 'application/pdf', 'image/svg+xml'];
    
    Array.from(files).forEach(file => {
        if (file.size > maxSize) {
            showToast(`File "${file.name}" is too large. Maximum size is 10MB.`, 'error');
            return;
        }
        
        if (!allowedTypes.includes(file.type) && !file.name.match(/\.(ai|psd)$/i)) {
            showToast(`File "${file.name}" is not a supported format.`, 'error');
            return;
        }
        
        validFiles.push(file);
    });
    
    if (validFiles.length > 0) {
        selectedFiles = [...selectedFiles, ...validFiles];
        displayFiles();
    }
}

function displayFiles() {
    const fileList = document.getElementById('fileList');
    const filePreview = document.getElementById('filePreview');
    
    if (selectedFiles.length === 0) {
        filePreview.style.display = 'none';
        return;
    }
    
    filePreview.style.display = 'block';
    fileList.innerHTML = '';
    
    selectedFiles.forEach((file, index) => {
        const fileItem = document.createElement('div');
        fileItem.className = 'file-item';
        
        const iconClass = getFileIcon(file.type, file.name);
        const iconColor = getFileIconColor(file.type, file.name);
        
        fileItem.innerHTML = `
            <div class="file-icon" style="background-color: ${iconColor}20;">
                <i class="${iconClass}" style="color: ${iconColor}; font-size: 1.25rem;"></i>
            </div>
            <div class="file-info">
                <div class="file-name">${file.name}</div>
                <div class="file-size">${formatFileSize(file.size)}</div>
            </div>
            <div class="file-remove" onclick="removeFile(${index})">
                <i class="bi bi-x-lg"></i>
            </div>
        `;
        
        fileList.appendChild(fileItem);
    });
}

function removeFile(index) {
    selectedFiles.splice(index, 1);
    displayFiles();
}

function getFileIcon(type, name) {
    if (type.startsWith('image/')) return 'bi bi-file-earmark-image';
    if (type === 'application/pdf') return 'bi bi-file-earmark-pdf';
    if (name.match(/\.ai$/i)) return 'bi bi-file-earmark-code';
    if (name.match(/\.psd$/i)) return 'bi bi-file-earmark-code';
    return 'bi bi-file-earmark';
}

function getFileIconColor(type, name) {
    if (type.startsWith('image/')) return '#28a745';
    if (type === 'application/pdf') return '#dc3545';
    if (name.match(/\.(ai|psd)$/i)) return '#6f42c1';
    return '#6c757d';
}

function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

async function uploadDesigns() {
    if (selectedFiles.length === 0) {
        showToast('Please select at least one file to upload.', 'error');
        return;
    }
    
    const assetName = document.getElementById('assetName').value.trim();
    if (!assetName) {
        showToast('Please enter an asset name.', 'error');
        document.getElementById('assetName').focus();
        return;
    }
    
    const uploadButton = document.getElementById('uploadButton');
    const uploadProgress = document.getElementById('uploadProgress');
    const progressBar = document.getElementById('progressBar');
    const progressText = document.getElementById('progressText');
    
    // Show progress and disable button
    uploadProgress.style.display = 'block';
    const originalText = uploadButton.innerHTML;
    if (window.UniPrintUI && typeof UniPrintUI.setButtonLoading === 'function') {
        UniPrintUI.setButtonLoading(uploadButton, true, { text: 'Uploading...' });
    } else {
        uploadButton.disabled = true;
        uploadButton.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Uploading...';
    }
    
    try {
        const formData = new FormData();
        
        // Add files
        selectedFiles.forEach((file, index) => {
            formData.append(`design_files[${index}]`, file);
        });
        
        // Add form data
        formData.append('asset_name', assetName);
        formData.append('category', document.getElementById('assetCategory').value);
        formData.append('description', document.getElementById('assetDescription').value);
        formData.append('tags', document.getElementById('assetTags').value);
        formData.append('_token', document.querySelector('input[name="_token"]').value);
        
        const response = await fetch('/api/design-assets/upload', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const result = await response.json();
        
        // Update progress to 100%
        progressBar.style.width = '100%';
        progressText.textContent = '100%';
        
        setTimeout(() => {
            showToast('Design assets uploaded successfully!', 'success');
            
            // Close modal and refresh page
            bootstrap.Modal.getInstance(document.getElementById('{{ $id }}')).hide();
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        }, 500);
        
    } catch (error) {
        console.error('Upload error:', error);
        showToast('Failed to upload design assets. Please try again.', 'error');
    } finally {
        // Reset UI
        uploadProgress.style.display = 'none';
        if (window.UniPrintUI && typeof UniPrintUI.setButtonLoading === 'function') {
            UniPrintUI.setButtonLoading(uploadButton, false);
        } else {
            uploadButton.disabled = false;
            uploadButton.innerHTML = originalText;
        }
        progressBar.style.width = '0%';
        progressText.textContent = '0%';
    }
}

// Reset form when modal is hidden
document.getElementById('{{ $id }}').addEventListener('hidden.bs.modal', function() {
    document.getElementById('uploadDesignForm').reset();
    selectedFiles = [];
    displayFiles();
    document.getElementById('uploadProgress').style.display = 'none';
});

// Toast notification function
function showToast(message, type = 'info') {
    // Create toast element
    const toast = document.createElement('div');
    toast.className = `toast align-items-center text-white bg-${type === 'error' ? 'danger' : type === 'success' ? 'success' : 'primary'} border-0`;
    toast.setAttribute('role', 'alert');
    toast.setAttribute('aria-live', 'assertive');
    toast.setAttribute('aria-atomic', 'true');
    
    toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">${message}</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    `;
    
    // Add to toast container or create one
    let toastContainer = document.querySelector('.toast-container');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
        toastContainer.style.zIndex = '9999';
        document.body.appendChild(toastContainer);
    }
    
    toastContainer.appendChild(toast);
    
    // Show toast
    const bsToast = new bootstrap.Toast(toast);
    bsToast.show();
    
    // Remove from DOM after hiding
    toast.addEventListener('hidden.bs.toast', function() {
        toast.remove();
    });
}
</script>
@endpush
