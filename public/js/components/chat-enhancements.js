/**
 * UniPrint Chat Enhancements
 * Enhanced chat features including file upload, message reactions, and threading
 */

class ChatEnhancements {
    constructor(chatApp) {
        this.chatApp = chatApp;
        this.fileUploadConfig = {
            maxSize: 10 * 1024 * 1024, // 10MB
            allowedTypes: ['image/jpeg', 'image/png', 'image/gif', 'application/pdf', 'text/plain'],
            uploadUrl: '/api/chat/upload'
        };
        
        this.messageReactions = new Map();
        this.messageThreads = new Map();
        this.emojiPicker = null;
        
        this.init();
    }
    
    init() {
        this.setupFileUpload();
        this.setupMessageReactions();
        this.setupMessageThreading();
        this.setupEmojiPicker();
        this.setupMessageSearch();
        this.setupKeyboardShortcuts();
    }
    
    /**
     * File Upload Enhancement
     */
    setupFileUpload() {
        // Add file upload button to chat input
        const chatInput = document.querySelector('#messageInput');
        if (!chatInput) return;
        
        const inputContainer = chatInput.parentNode;
        
        // Create file upload button
        const fileButton = document.createElement('button');
        fileButton.type = 'button';
        fileButton.className = 'btn btn-outline-secondary btn-sm';
        fileButton.innerHTML = '<i class="bi bi-paperclip"></i>';
        fileButton.title = 'Attach file';
        fileButton.onclick = () => this.openFileDialog();
        
        // Create hidden file input
        const fileInput = document.createElement('input');
        fileInput.type = 'file';
        fileInput.id = 'chatFileInput';
        fileInput.style.display = 'none';
        fileInput.multiple = true;
        fileInput.accept = this.fileUploadConfig.allowedTypes.join(',');
        fileInput.onchange = (e) => this.handleFileSelection(e);
        
        // Add to DOM
        inputContainer.appendChild(fileButton);
        inputContainer.appendChild(fileInput);
        
        // Setup drag and drop
        this.setupDragAndDrop();
    }
    
    openFileDialog() {
        document.getElementById('chatFileInput').click();
    }
    
    handleFileSelection(event) {
        const files = Array.from(event.target.files);
        files.forEach(file => this.uploadFile(file));
    }
    
    setupDragAndDrop() {
        const chatMessages = document.querySelector('#chatMessages');
        if (!chatMessages) return;
        
        chatMessages.addEventListener('dragover', (e) => {
            e.preventDefault();
            chatMessages.classList.add('drag-over');
        });
        
        chatMessages.addEventListener('dragleave', (e) => {
            e.preventDefault();
            chatMessages.classList.remove('drag-over');
        });
        
        chatMessages.addEventListener('drop', (e) => {
            e.preventDefault();
            chatMessages.classList.remove('drag-over');
            
            const files = Array.from(e.dataTransfer.files);
            files.forEach(file => this.uploadFile(file));
        });
    }
    
    async uploadFile(file) {
        // Validate file
        if (!this.validateFile(file)) return;
        
        // Show upload progress
        const progressId = this.showUploadProgress(file);
        
        try {
            const formData = new FormData();
            formData.append('file', file);
            formData.append('conversation_id', this.chatApp.currentConversation?.id);
            
            const response = await fetch(this.fileUploadConfig.uploadUrl, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });
            
            if (!response.ok) throw new Error('Upload failed');
            
            const result = await response.json();
            
            // Send file message
            this.sendFileMessage(result.file);
            
            // Remove progress indicator
            this.removeUploadProgress(progressId);
            
        } catch (error) {
            console.error('File upload error:', error);
            this.showUploadError(progressId, error.message);
        }
    }
    
    validateFile(file) {
        if (file.size > this.fileUploadConfig.maxSize) {
            this.showError(`File too large. Maximum size is ${this.formatFileSize(this.fileUploadConfig.maxSize)}`);
            return false;
        }
        
        if (!this.fileUploadConfig.allowedTypes.includes(file.type)) {
            this.showError('File type not allowed');
            return false;
        }
        
        return true;
    }
    
    showUploadProgress(file) {
        const progressId = 'upload_' + Date.now();
        const messagesContainer = document.querySelector('#chatMessages');
        
        const progressElement = document.createElement('div');
        progressElement.id = progressId;
        progressElement.className = 'upload-progress mb-3';
        progressElement.innerHTML = `
            <div class="d-flex align-items-center">
                <div class="flex-grow-1">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-sm">${file.name}</span>
                        <span class="text-sm text-muted">Uploading...</span>
                    </div>
                    <div class="progress" style="height: 4px;">
                        <div class="progress-bar progress-bar-animated" style="width: 0%"></div>
                    </div>
                </div>
                <button class="btn btn-sm btn-outline-danger ms-2" onclick="this.parentElement.parentElement.remove()">
                    <i class="bi bi-x"></i>
                </button>
            </div>
        `;
        
        messagesContainer.appendChild(progressElement);
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
        
        return progressId;
    }
    
    removeUploadProgress(progressId) {
        const element = document.getElementById(progressId);
        if (element) element.remove();
    }
    
    showUploadError(progressId, message) {
        const element = document.getElementById(progressId);
        if (element) {
            element.innerHTML = `
                <div class="alert alert-danger alert-sm mb-0">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    Upload failed: ${message}
                </div>
            `;
            setTimeout(() => element.remove(), 5000);
        }
    }
    
    sendFileMessage(fileData) {
        const messageData = {
            type: 'file',
            file: fileData,
            conversation_id: this.chatApp.currentConversation?.id
        };
        
        this.chatApp.sendMessage(JSON.stringify(messageData));
    }
    
    /**
     * Message Reactions
     */
    setupMessageReactions() {
        // Add reaction buttons to existing messages
        document.addEventListener('click', (e) => {
            if (e.target.closest('.message-react-btn')) {
                const messageId = e.target.closest('.message-item').dataset.messageId;
                this.showReactionPicker(messageId, e.target);
            }
            
            if (e.target.closest('.reaction-item')) {
                const messageId = e.target.closest('.message-item').dataset.messageId;
                const emoji = e.target.closest('.reaction-item').dataset.emoji;
                this.toggleReaction(messageId, emoji);
            }
        });
    }
    
    showReactionPicker(messageId, button) {
        const reactions = ['👍', '❤️', '😂', '😮', '😢', '😡'];
        
        // Remove existing picker
        document.querySelector('.reaction-picker')?.remove();
        
        const picker = document.createElement('div');
        picker.className = 'reaction-picker';
        picker.innerHTML = reactions.map(emoji => 
            `<button class="reaction-btn" data-emoji="${emoji}">${emoji}</button>`
        ).join('');
        
        // Position picker
        const rect = button.getBoundingClientRect();
        picker.style.cssText = `
            position: fixed;
            top: ${rect.top - 50}px;
            left: ${rect.left}px;
            background: white;
            border: 1px solid #ddd;
            border-radius: 20px;
            padding: 5px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            z-index: 1000;
            display: flex;
            gap: 5px;
        `;
        
        // Add event listeners
        picker.addEventListener('click', (e) => {
            if (e.target.dataset.emoji) {
                this.addReaction(messageId, e.target.dataset.emoji);
                picker.remove();
            }
        });
        
        document.body.appendChild(picker);
        
        // Remove picker when clicking outside
        setTimeout(() => {
            document.addEventListener('click', function removePickerHandler(e) {
                if (!picker.contains(e.target)) {
                    picker.remove();
                    document.removeEventListener('click', removePickerHandler);
                }
            });
        }, 100);
    }
    
    addReaction(messageId, emoji) {
        // Send reaction to server
        fetch('/api/chat/reactions', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                message_id: messageId,
                emoji: emoji
            })
        });
        
        // Update UI immediately
        this.updateReactionUI(messageId, emoji);
    }
    
    updateReactionUI(messageId, emoji) {
        const messageElement = document.querySelector(`[data-message-id="${messageId}"]`);
        if (!messageElement) return;
        
        let reactionsContainer = messageElement.querySelector('.message-reactions');
        if (!reactionsContainer) {
            reactionsContainer = document.createElement('div');
            reactionsContainer.className = 'message-reactions';
            messageElement.appendChild(reactionsContainer);
        }
        
        // Find or create reaction item
        let reactionItem = reactionsContainer.querySelector(`[data-emoji="${emoji}"]`);
        if (!reactionItem) {
            reactionItem = document.createElement('span');
            reactionItem.className = 'reaction-item';
            reactionItem.dataset.emoji = emoji;
            reactionItem.innerHTML = `${emoji} <span class="count">1</span>`;
            reactionsContainer.appendChild(reactionItem);
        } else {
            const countElement = reactionItem.querySelector('.count');
            const currentCount = parseInt(countElement.textContent);
            countElement.textContent = currentCount + 1;
        }
    }
    
    /**
     * Message Threading
     */
    setupMessageThreading() {
        document.addEventListener('click', (e) => {
            if (e.target.closest('.message-reply-btn')) {
                const messageId = e.target.closest('.message-item').dataset.messageId;
                this.startReply(messageId);
            }
        });
    }
    
    startReply(messageId) {
        const messageElement = document.querySelector(`[data-message-id="${messageId}"]`);
        const messageText = messageElement.querySelector('.message-text').textContent;
        const senderName = messageElement.querySelector('.sender-name').textContent;
        
        // Show reply preview
        this.showReplyPreview(messageId, senderName, messageText);
        
        // Focus input
        document.querySelector('#messageInput').focus();
    }
    
    showReplyPreview(messageId, senderName, messageText) {
        // Remove existing preview
        document.querySelector('.reply-preview')?.remove();
        
        const preview = document.createElement('div');
        preview.className = 'reply-preview';
        preview.innerHTML = `
            <div class="d-flex align-items-center justify-content-between bg-light p-2 rounded mb-2">
                <div class="flex-grow-1">
                    <small class="text-muted">Replying to ${senderName}</small>
                    <div class="text-truncate">${messageText}</div>
                </div>
                <button class="btn btn-sm btn-outline-secondary" onclick="this.parentElement.parentElement.remove()">
                    <i class="bi bi-x"></i>
                </button>
            </div>
        `;
        
        const inputContainer = document.querySelector('#messageInput').parentNode;
        inputContainer.insertBefore(preview, inputContainer.firstChild);
        
        // Store reply context
        this.replyContext = { messageId, senderName, messageText };
    }
    
    /**
     * Emoji Picker
     */
    setupEmojiPicker() {
        const inputContainer = document.querySelector('#messageInput')?.parentNode;
        if (!inputContainer) return;
        
        const emojiButton = document.createElement('button');
        emojiButton.type = 'button';
        emojiButton.className = 'btn btn-outline-secondary btn-sm';
        emojiButton.innerHTML = '<i class="bi bi-emoji-smile"></i>';
        emojiButton.title = 'Add emoji';
        emojiButton.onclick = () => this.toggleEmojiPicker();
        
        inputContainer.appendChild(emojiButton);
    }
    
    toggleEmojiPicker() {
        const existing = document.querySelector('.emoji-picker');
        if (existing) {
            existing.remove();
            return;
        }
        
        const emojis = [
            '😀', '😃', '😄', '😁', '😆', '😅', '😂', '🤣', '😊', '😇',
            '🙂', '🙃', '😉', '😌', '😍', '🥰', '😘', '😗', '😙', '😚',
            '😋', '😛', '😝', '😜', '🤪', '🤨', '🧐', '🤓', '😎', '🤩',
            '🥳', '😏', '😒', '😞', '😔', '😟', '😕', '🙁', '☹️', '😣',
            '👍', '👎', '👌', '✌️', '🤞', '🤟', '🤘', '🤙', '👈', '👉',
            '❤️', '🧡', '💛', '💚', '💙', '💜', '🖤', '🤍', '🤎', '💔'
        ];
        
        const picker = document.createElement('div');
        picker.className = 'emoji-picker';
        picker.innerHTML = `
            <div class="emoji-grid">
                ${emojis.map(emoji => `<button class="emoji-btn" data-emoji="${emoji}">${emoji}</button>`).join('')}
            </div>
        `;
        
        picker.style.cssText = `
            position: absolute;
            bottom: 100%;
            right: 0;
            background: white;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            z-index: 1000;
            max-height: 200px;
            overflow-y: auto;
        `;
        
        picker.addEventListener('click', (e) => {
            if (e.target.dataset.emoji) {
                this.insertEmoji(e.target.dataset.emoji);
                picker.remove();
            }
        });
        
        document.querySelector('#messageInput').parentNode.appendChild(picker);
    }
    
    insertEmoji(emoji) {
        const input = document.querySelector('#messageInput');
        const start = input.selectionStart;
        const end = input.selectionEnd;
        const text = input.value;
        
        input.value = text.substring(0, start) + emoji + text.substring(end);
        input.selectionStart = input.selectionEnd = start + emoji.length;
        input.focus();
    }
    
    /**
     * Message Search
     */
    setupMessageSearch() {
        // Add search input to chat header
        const chatHeader = document.querySelector('.chat-header');
        if (!chatHeader) return;
        
        const searchContainer = document.createElement('div');
        searchContainer.className = 'chat-search';
        searchContainer.innerHTML = `
            <div class="input-group input-group-sm">
                <input type="text" class="form-control" id="messageSearch" placeholder="Search messages...">
                <button class="btn btn-outline-secondary" type="button" id="searchBtn">
                    <i class="bi bi-search"></i>
                </button>
            </div>
        `;
        
        chatHeader.appendChild(searchContainer);
        
        // Search functionality
        let searchTimeout;
        document.getElementById('messageSearch').addEventListener('input', (e) => {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                this.searchMessages(e.target.value);
            }, 300);
        });
    }
    
    searchMessages(query) {
        const messages = document.querySelectorAll('.message-item');
        
        if (!query.trim()) {
            messages.forEach(msg => {
                msg.style.display = '';
                msg.classList.remove('search-highlight');
            });
            return;
        }
        
        messages.forEach(msg => {
            const text = msg.querySelector('.message-text').textContent.toLowerCase();
            if (text.includes(query.toLowerCase())) {
                msg.style.display = '';
                msg.classList.add('search-highlight');
            } else {
                msg.style.display = 'none';
                msg.classList.remove('search-highlight');
            }
        });
    }
    
    /**
     * Keyboard Shortcuts
     */
    setupKeyboardShortcuts() {
        document.addEventListener('keydown', (e) => {
            // Ctrl/Cmd + Enter to send message
            if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
                const sendButton = document.querySelector('#sendMessageBtn');
                if (sendButton) sendButton.click();
            }
            
            // Escape to close emoji picker or reply preview
            if (e.key === 'Escape') {
                document.querySelector('.emoji-picker')?.remove();
                document.querySelector('.reply-preview')?.remove();
            }
        });
    }
    
    /**
     * Utility Methods
     */
    formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }
    
    showError(message) {
        // Use existing notification system or create toast
        if (window.stateManager) {
            window.stateManager.setState('ui.notifications', [
                ...window.stateManager.getState('ui.notifications'),
                { id: Date.now(), message, type: 'error', timestamp: new Date() }
            ]);
        } else {
            alert(message);
        }
    }
}

// Auto-initialize when chat app is available
document.addEventListener('DOMContentLoaded', function() {
    if (window.chatApp) {
        window.chatEnhancements = new ChatEnhancements(window.chatApp);
    }
});

// Export for module usage
if (typeof module !== 'undefined' && module.exports) {
    module.exports = ChatEnhancements;
}
