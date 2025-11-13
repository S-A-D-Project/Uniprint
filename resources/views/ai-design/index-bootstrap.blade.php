@extends('layouts.public')

@section('title', 'AI Design Tool')

@section('content')
<div class="container-fluid px-4">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex align-items-center mb-3">
                <i data-lucide="sparkles" class="me-3" style="width: 2rem; height: 2rem; color: #8B5CF6;"></i>
                <div>
                    <h1 class="h2 mb-1 text-dark fw-bold">AI Design Tool</h1>
                    <p class="text-muted mb-0">Create custom designs for your print jobs using AI</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Tab Navigation -->
    <div class="row mb-4">
        <div class="col-12">
            <ul class="nav nav-tabs" id="designTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active d-flex align-items-center" id="ai-generate-tab" data-bs-toggle="tab" data-bs-target="#ai-generate" type="button" role="tab">
                        <i data-lucide="sparkles" class="me-2" style="width: 1rem; height: 1rem;"></i>
                        AI Generate
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link d-flex align-items-center" id="my-designs-tab" data-bs-toggle="tab" data-bs-target="#my-designs" type="button" role="tab">
                        <i data-lucide="folder" class="me-2" style="width: 1rem; height: 1rem;"></i>
                        My Designs
                    </button>
                </li>
            </ul>
        </div>
    </div>

    <!-- Tab Content -->
    <div class="tab-content" id="designTabsContent">
        <!-- AI Generate Tab -->
        <div class="tab-pane fade show active" id="ai-generate" role="tabpanel">
            <div class="row g-4">
                <!-- Input Section -->
                <div class="col-lg-6">
                    <div class="card shadow-sm">
                        <div class="card-header bg-light">
                            <h5 class="card-title mb-0 d-flex align-items-center">
                                <i data-lucide="edit-3" class="me-2" style="width: 1.25rem; height: 1.25rem; color: #8B5CF6;"></i>
                                Design Prompt
                            </h5>
                            <small class="text-muted">Describe what you want to create and let AI generate it for you</small>
                        </div>
                        <div class="card-body">
                            <form id="ai-design-form">
                                <div class="mb-3">
                                    <label for="prompt" class="form-label fw-semibold">Design Description *</label>
                                    <textarea class="form-control" id="prompt" name="prompt" rows="4" 
                                              placeholder="E.g., Modern business card with blue gradient, company logo space, and contact information layout" required></textarea>
                                    <div class="form-text">Be specific about colors, style, elements, and layout</div>
                                </div>

                                <div class="row g-3 mb-3">
                                    <div class="col-md-6">
                                        <label for="design-type" class="form-label fw-semibold">Design Type</label>
                                        <select class="form-select" id="design-type" name="design_type">
                                            <option value="business-card">Business Card</option>
                                            <option value="flyer">Flyer</option>
                                            <option value="poster">Poster</option>
                                            <option value="brochure">Brochure</option>
                                            <option value="logo">Logo</option>
                                            <option value="banner">Banner</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="color-scheme" class="form-label fw-semibold">Color Scheme</label>
                                        <select class="form-select" id="color-scheme" name="color_scheme">
                                            <option value="professional">Professional (Blue/Gray)</option>
                                            <option value="vibrant">Vibrant (Multi-color)</option>
                                            <option value="monochrome">Monochrome (Black/White)</option>
                                            <option value="warm">Warm (Red/Orange/Yellow)</option>
                                            <option value="cool">Cool (Blue/Green/Purple)</option>
                                            <option value="custom">Custom Colors</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="row g-3 mb-4">
                                    <div class="col-md-6">
                                        <label for="style" class="form-label fw-semibold">Style</label>
                                        <select class="form-select" id="style" name="style">
                                            <option value="modern">Modern</option>
                                            <option value="classic">Classic</option>
                                            <option value="minimalist">Minimalist</option>
                                            <option value="vintage">Vintage</option>
                                            <option value="corporate">Corporate</option>
                                            <option value="creative">Creative</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="size" class="form-label fw-semibold">Size</label>
                                        <select class="form-select" id="size" name="size">
                                            <option value="business-card">Business Card (3.5" x 2")</option>
                                            <option value="flyer-letter">Flyer Letter (8.5" x 11")</option>
                                            <option value="poster-small">Small Poster (11" x 17")</option>
                                            <option value="poster-large">Large Poster (18" x 24")</option>
                                            <option value="square">Square (1:1)</option>
                                            <option value="landscape">Landscape (16:9)</option>
                                            <option value="portrait">Portrait (9:16)</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="d-grid gap-2 d-md-flex">
                                    <button type="submit" class="btn btn-primary flex-fill d-flex align-items-center justify-content-center" id="generate-btn" style="background: linear-gradient(135deg, #8B5CF6, #A855F7);">
                                        <i data-lucide="sparkles" class="me-2" style="width: 1rem; height: 1rem;"></i>
                                        <span class="btn-text">Generate Design</span>
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary d-flex align-items-center" id="reset-btn">
                                        <i data-lucide="rotate-ccw" class="me-2" style="width: 1rem; height: 1rem;"></i>
                                        Reset
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Get Design Help from Experts -->
                    <div class="card shadow-sm mt-4">
                        <div class="card-body text-center">
                            <h6 class="card-title text-muted mb-3">Need Professional Help?</h6>
                            <button onclick="openDesignChat()" class="btn btn-outline-primary d-flex align-items-center justify-content-center mx-auto" style="border-color: #8B5CF6; color: #8B5CF6;">
                                <i data-lucide="message-circle" class="me-2" style="width: 1rem; height: 1rem;"></i>
                                Get Design Help from Experts
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Output Section -->
                <div class="col-lg-6">
                    <div class="card shadow-sm">
                        <div class="card-header bg-light">
                            <h5 class="card-title mb-0 d-flex align-items-center">
                                <i data-lucide="image" class="me-2" style="width: 1.25rem; height: 1.25rem; color: #8B5CF6;"></i>
                                Generated Design
                            </h5>
                            <small class="text-muted">Your AI-generated design will appear here</small>
                        </div>
                        <div class="card-body">
                            <div id="design-output" class="text-center p-5">
                                <div class="border border-2 border-dashed rounded p-5" style="border-color: #E5E7EB;">
                                    <i data-lucide="image" class="mb-3" style="width: 3rem; height: 3rem; color: #9CA3AF;"></i>
                                    <h6 class="text-muted mb-2">No design generated yet</h6>
                                    <p class="text-muted small mb-0">Fill out the form and click "Generate Design" to create your custom design</p>
                                </div>
                            </div>
                            
                            <!-- Design Actions (hidden initially) -->
                            <div id="design-actions" class="d-none mt-3">
                                <div class="d-grid gap-2 d-md-flex">
                                    <button class="btn btn-success flex-fill d-flex align-items-center justify-content-center" id="save-design-btn">
                                        <i data-lucide="heart" class="me-2" style="width: 1rem; height: 1rem;"></i>
                                        Save Design
                                    </button>
                                    <button class="btn btn-outline-primary d-flex align-items-center" id="regenerate-btn">
                                        <i data-lucide="refresh-cw" class="me-2" style="width: 1rem; height: 1rem;"></i>
                                        Regenerate
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- My Designs Tab -->
        <div class="tab-pane fade" id="my-designs" role="tabpanel">
            <div class="row">
                <div class="col-12">
                    <div class="card shadow-sm">
                        <div class="card-header bg-light">
                            <h5 class="card-title mb-0 d-flex align-items-center">
                                <i data-lucide="folder" class="me-2" style="width: 1.25rem; height: 1.25rem; color: #8B5CF6;"></i>
                                My Saved Designs
                            </h5>
                        </div>
                        <div class="card-body">
                            <div id="saved-designs-container">
                                <div class="text-center p-5">
                                    <i data-lucide="folder-open" class="mb-3" style="width: 3rem; height: 3rem; color: #9CA3AF;"></i>
                                    <h6 class="text-muted mb-2">No saved designs yet</h6>
                                    <p class="text-muted small mb-3">Generate and save designs to see them here</p>
                                    <button class="btn btn-primary" onclick="document.getElementById('ai-generate-tab').click();" style="background: linear-gradient(135deg, #8B5CF6, #A855F7);">
                                        <i data-lucide="sparkles" class="me-2" style="width: 1rem; height: 1rem;"></i>
                                        Create Your First Design
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Design Consultation Chat Modal -->
<div class="modal fade" id="designChatModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #8B5CF6, #A855F7); color: white;">
                <h5 class="modal-title d-flex align-items-center">
                    <i data-lucide="message-circle" class="me-2" style="width: 1.25rem; height: 1.25rem;"></i>
                    Design Consultation
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="card bg-light">
                            <div class="card-body">
                                <h6 class="card-title">Current Project</h6>
                                <p class="small text-muted mb-1">Type: <span id="chat-design-type">Business Card</span></p>
                                <p class="small text-muted mb-1">Style: <span id="chat-design-style">Modern</span></p>
                                <p class="small text-muted mb-0">Context: <span id="chat-design-context">Modern business card with blue gradient...</span></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="border rounded p-3 mb-3" style="height: 300px; overflow-y: auto;" id="design-chat-messages">
                            <div class="text-center text-muted">
                                <i data-lucide="message-circle" style="width: 2rem; height: 2rem;"></i>
                                <p class="mb-0">Start a conversation with our design experts</p>
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="d-flex flex-wrap gap-2">
                                <button class="btn btn-sm btn-outline-secondary quick-question" data-question="What colors work best for business cards?">
                                    Business card colors?
                                </button>
                                <button class="btn btn-sm btn-outline-secondary quick-question" data-question="How do I choose the right font?">
                                    Font selection?
                                </button>
                                <button class="btn btn-sm btn-outline-secondary quick-question" data-question="What file formats do you recommend?">
                                    File formats?
                                </button>
                            </div>
                        </div>
                        <div class="input-group">
                            <input type="text" class="form-control" id="design-chat-input" placeholder="Ask about design styles, colors, layouts...">
                            <button class="btn btn-primary" type="button" id="send-design-message" style="background: linear-gradient(135deg, #8B5CF6, #A855F7);">
                                <i data-lucide="send" style="width: 1rem; height: 1rem;"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Initialize Lucide icons
if (typeof lucide !== 'undefined') {
    lucide.createIcons();
}

// AI Design Form Handler
document.getElementById('ai-design-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const btn = document.getElementById('generate-btn');
    const originalText = btn.innerHTML;
    
    // Show loading state
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Generating...';
    
    try {
        const formData = new FormData(this);
        
        const response = await fetch('{{ route("ai-design.generate") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            // Display generated design
            const outputDiv = document.getElementById('design-output');
            outputDiv.innerHTML = `
                <div class="text-center">
                    <img src="${result.design_url}" class="img-fluid rounded shadow" alt="Generated Design" style="max-height: 400px;">
                    <div class="mt-3">
                        <h6 class="text-success">Design Generated Successfully!</h6>
                        <p class="text-muted small">Your custom ${result.design_type} design is ready</p>
                    </div>
                </div>
            `;
            
            // Show design actions
            document.getElementById('design-actions').classList.remove('d-none');
            
            // Store design data for saving
            window.currentDesign = result;
            
        } else {
            throw new Error(result.message || 'Failed to generate design');
        }
    } catch (error) {
        console.error('Design generation error:', error);
        
        // Show error in output
        const outputDiv = document.getElementById('design-output');
        outputDiv.innerHTML = `
            <div class="text-center text-danger">
                <i data-lucide="alert-circle" class="mb-3" style="width: 3rem; height: 3rem;"></i>
                <h6>Generation Failed</h6>
                <p class="small">${error.message}</p>
                <button class="btn btn-outline-primary btn-sm" onclick="document.getElementById('generate-btn').click();">
                    Try Again
                </button>
            </div>
        `;
        
        // Re-initialize icons
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    } finally {
        // Reset button
        btn.disabled = false;
        btn.innerHTML = originalText;
        
        // Re-initialize icons
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    }
});

// Reset Form
document.getElementById('reset-btn').addEventListener('click', function() {
    document.getElementById('ai-design-form').reset();
    
    // Reset output
    const outputDiv = document.getElementById('design-output');
    outputDiv.innerHTML = `
        <div class="border border-2 border-dashed rounded p-5" style="border-color: #E5E7EB;">
            <i data-lucide="image" class="mb-3" style="width: 3rem; height: 3rem; color: #9CA3AF;"></i>
            <h6 class="text-muted mb-2">No design generated yet</h6>
            <p class="text-muted small mb-0">Fill out the form and click "Generate Design" to create your custom design</p>
        </div>
    `;
    
    // Hide design actions
    document.getElementById('design-actions').classList.add('d-none');
    
    // Re-initialize icons
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
});

// Design Chat Functionality
function openDesignChat() {
    // Update chat context with current form values
    const designType = document.getElementById('design-type').value;
    const style = document.getElementById('style').value;
    const prompt = document.getElementById('prompt').value;
    
    document.getElementById('chat-design-type').textContent = designType.replace('-', ' ').replace(/\b\w/g, l => l.toUpperCase());
    document.getElementById('chat-design-style').textContent = style.charAt(0).toUpperCase() + style.slice(1);
    document.getElementById('chat-design-context').textContent = prompt.substring(0, 50) + (prompt.length > 50 ? '...' : '');
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('designChatModal'));
    modal.show();
}

// Quick question buttons
document.querySelectorAll('.quick-question').forEach(button => {
    button.addEventListener('click', function() {
        const question = this.dataset.question;
        document.getElementById('design-chat-input').value = question;
        document.getElementById('send-design-message').click();
    });
});

// Send design message
document.getElementById('send-design-message').addEventListener('click', function() {
    const input = document.getElementById('design-chat-input');
    const message = input.value.trim();
    
    if (message) {
        const messagesDiv = document.getElementById('design-chat-messages');
        
        // Add user message
        messagesDiv.innerHTML += `
            <div class="d-flex justify-content-end mb-2">
                <div class="bg-primary text-white rounded px-3 py-2 small" style="max-width: 70%;">
                    ${message}
                </div>
            </div>
        `;
        
        // Add expert response (simulated)
        setTimeout(() => {
            messagesDiv.innerHTML += `
                <div class="d-flex justify-content-start mb-2">
                    <div class="bg-light rounded px-3 py-2 small" style="max-width: 70%;">
                        <strong>Design Expert:</strong> Thank you for your question about "${message}". Our design experts will provide detailed guidance based on your specific needs.
                    </div>
                </div>
            `;
            messagesDiv.scrollTop = messagesDiv.scrollHeight;
        }, 1000);
        
        input.value = '';
        messagesDiv.scrollTop = messagesDiv.scrollHeight;
    }
});

// Enter key to send message
document.getElementById('design-chat-input').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        document.getElementById('send-design-message').click();
    }
});

// Initialize Bootstrap tabs
document.addEventListener('DOMContentLoaded', function() {
    // Re-initialize icons when tabs are shown
    document.querySelectorAll('button[data-bs-toggle="tab"]').forEach(tab => {
        tab.addEventListener('shown.bs.tab', function() {
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        });
    });
});
</script>
@endpush
