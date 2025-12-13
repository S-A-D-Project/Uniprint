<div class="chat-input">
    <div class="typing-indicator" id="typingIndicator" style="display: none;">
        <i class="bi bi-three-dots"></i> Someone is typing...
    </div>
    
    <div class="input-wrapper">
        <div class="input-actions">
            <button class="attachment-button" id="attachmentButton" title="Attach file">
                <i class="bi bi-paperclip"></i>
            </button>
            <input type="file" id="fileInput" style="display: none;" accept="image/*,.pdf,.doc,.docx">
        </div>
        
        <textarea 
            class="message-input" 
            id="messageInput" 
            placeholder="Type a message..." 
            rows="1"
        ></textarea>
        
        <button class="send-button" id="sendButton">
            <i class="bi bi-send-fill"></i>
        </button>
    </div>
    
    <div class="file-preview" id="filePreview" style="display: none;">
        <div class="file-info">
            <i class="bi bi-file-earmark"></i>
            <span class="file-name"></span>
            <span class="file-size"></span>
        </div>
        <button class="remove-file" id="removeFile">
            <i class="bi bi-x"></i>
        </button>
    </div>
</div>
