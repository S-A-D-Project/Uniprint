@extends('layouts.public')

@section('title', 'AI Design Tool')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-background to-muted/20">
    <div class="container mx-auto px-4 py-8">
        <!-- Page Header -->
        <div class="mb-8">
            <div class="flex items-center gap-4 mb-6">
                <div class="w-12 h-12 bg-gradient-to-r from-primary to-primary-glow rounded-xl flex items-center justify-center shadow-lg">
                    <i data-lucide="sparkles" class="h-6 w-6 text-white"></i>
                </div>
                <div>
                    <h1 class="text-3xl font-bold text-foreground mb-2">AI Design Tool</h1>
                    <p class="text-muted-foreground text-lg">Create stunning designs for your print projects using AI technology</p>
                </div>
            </div>
        </div>

        <!-- Tab Navigation -->
        <div class="mb-8">
            <div class="border-b border-border">
                <nav class="flex space-x-8" aria-label="Tabs">
                    <button class="tab-button active" data-tab="ai-generate" id="ai-generate-tab">
                        <i data-lucide="sparkles" class="h-5 w-5"></i>
                        <span>AI Generate</span>
                    </button>
                    <button class="tab-button" data-tab="my-designs" id="my-designs-tab">
                        <i data-lucide="folder" class="h-5 w-5"></i>
                        <span>My Designs</span>
                    </button>
                </nav>
            </div>
        </div>

        <!-- Tab Content -->
        <div class="tab-content">
            <!-- AI Generate Tab -->
            <div class="tab-panel active" id="ai-generate-panel">
                <div class="mb-6">
                    <div class="bg-card rounded-xl shadow-sm border border-border overflow-hidden">
                        <div class="p-4 flex items-center justify-between gap-4">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 bg-primary/10 rounded-lg flex items-center justify-center">
                                    <i data-lucide="sparkles" class="h-5 w-5 text-primary"></i>
                                </div>
                                <div>
                                    <div class="text-sm font-semibold text-foreground">Daily AI generations</div>
                                    <div class="text-xs text-muted-foreground">
                                        <span id="ai-usage-text">Loading usage...</span>
                                    </div>
                                </div>
                            </div>
                            <button type="button" id="ai-usage-refresh" class="text-xs text-muted-foreground hover:text-foreground transition-smooth">Refresh</button>
                        </div>
                    </div>
                </div>
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <!-- Input Section -->
                    <div class="lg:col-span-1 space-y-6">
                        <div class="bg-card rounded-xl shadow-sm border border-border overflow-hidden">
                            <div class="bg-gradient-to-r from-primary/5 to-primary-glow/5 px-6 py-4 border-b border-border">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 bg-primary/10 rounded-lg flex items-center justify-center">
                                        <i data-lucide="edit-3" class="h-4 w-4 text-primary"></i>
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-semibold text-foreground">Design Prompt</h3>
                                        <p class="text-sm text-muted-foreground">Describe what you want to create and let AI generate it for you</p>
                                    </div>
                                </div>
                            </div>
                            <div class="p-6">
                                <form id="ai-design-form" class="space-y-6">
                                    <div class="space-y-2">
                                        <div class="flex items-center justify-between">
                                            <label for="prompt" class="block text-sm font-medium text-foreground">Design Description *</label>
                                            <button 
                                                type="button" 
                                                id="enhance-prompt-btn"
                                                class="text-xs bg-primary/10 text-primary px-3 py-1 rounded-full hover:bg-primary/20 transition-colors flex items-center gap-1">
                                                <i data-lucide="wand-2" class="h-3 w-3"></i>
                                                Enhance
                                            </button>
                                        </div>
                                        <textarea 
                                            class="w-full px-4 py-3 border border-border rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent transition-all duration-200 resize-none" 
                                            id="prompt" 
                                            name="prompt" 
                                            rows="4" 
                                            placeholder="E.g., Modern business card with purple gradient, company logo space, and contact information layout" 
                                            required></textarea>
                                        <p class="text-xs text-muted-foreground">Be specific about colors, style, elements, and layout for best results</p>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div class="space-y-2">
                                            <label for="design-type" class="block text-sm font-medium text-foreground">Design Type</label>
                                            <select class="w-full px-4 py-3 border border-border rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent transition-all duration-200 bg-background" id="design-type" name="design_type">
                                                <option value="business-card">Business Card</option>
                                                <option value="flyer">Flyer</option>
                                                <option value="poster">Poster</option>
                                                <option value="brochure">Brochure</option>
                                                <option value="logo">Logo</option>
                                                <option value="banner">Banner</option>
                                            </select>
                                        </div>
                                        <div class="space-y-2">
                                            <label for="color-scheme" class="block text-sm font-medium text-foreground">Color Scheme</label>
                                            <select class="w-full px-4 py-3 border border-border rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent transition-all duration-200 bg-background" id="color-scheme" name="color_scheme">
                                                <option value="professional">Professional (Blue/Gray)</option>
                                                <option value="vibrant">Vibrant (Multi-color)</option>
                                                <option value="monochrome">Monochrome (Black/White)</option>
                                                <option value="warm">Warm (Red/Orange/Yellow)</option>
                                                <option value="cool">Cool (Blue/Green/Purple)</option>
                                                <option value="custom">Custom Colors</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div class="space-y-2">
                                            <label for="style" class="block text-sm font-medium text-foreground">Style</label>
                                            <select class="w-full px-4 py-3 border border-border rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent transition-all duration-200 bg-background" id="style" name="style">
                                                <option value="modern">Modern</option>
                                                <option value="classic">Classic</option>
                                                <option value="minimalist">Minimalist</option>
                                                <option value="vintage">Vintage</option>
                                                <option value="corporate">Corporate</option>
                                                <option value="creative">Creative</option>
                                            </select>
                                        </div>
                                        <div class="space-y-2">
                                            <label for="size" class="block text-sm font-medium text-foreground">Size</label>
                                            <select class="w-full px-4 py-3 border border-border rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent transition-all duration-200 bg-background" id="size" name="size">
                                                <option value="square">Square (1:1)</option>
                                                <option value="landscape">Landscape (16:9)</option>
                                                <option value="portrait">Portrait (9:16)</option>
                                                <option value="business-card">Business Card (3.5" x 2")</option>
                                                <option value="flyer">Flyer (A4)</option>
                                                <option value="poster">Poster (2:3)</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="flex flex-col sm:flex-row gap-3">
                                        <button 
                                            type="submit" 
                                            class="flex-1 bg-gradient-to-r from-primary to-primary-glow text-white px-6 py-3 rounded-lg font-medium hover:shadow-lg hover:shadow-primary/25 transition-all duration-200 flex items-center justify-center gap-2 group" 
                                            id="generate-btn">
                                            <i data-lucide="sparkles" class="h-4 w-4 group-hover:scale-110 transition-transform duration-200"></i>
                                            <span class="btn-text">Generate Design</span>
                                        </button>
                                        <button 
                                            type="button" 
                                            class="px-6 py-3 border border-border text-muted-foreground rounded-lg font-medium hover:bg-muted hover:text-foreground transition-all duration-200 flex items-center justify-center gap-2" 
                                            id="reset-btn">
                                            <i data-lucide="rotate-ccw" class="h-4 w-4"></i>
                                            Reset
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>

                    </div>

                    <!-- Output Section -->
                    <div class="lg:col-span-2 space-y-6">
                        <div class="bg-card rounded-xl shadow-sm border border-border overflow-hidden h-full">
                            <div class="bg-gradient-to-r from-primary/5 to-primary-glow/5 px-6 py-4 border-b border-border">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 bg-primary/10 rounded-lg flex items-center justify-center">
                                        <i data-lucide="image" class="h-4 w-4 text-primary"></i>
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-semibold text-foreground">Generated Design</h3>
                                        <p class="text-sm text-muted-foreground">Your AI-generated design will appear here</p>
                                    </div>
                                </div>
                            </div>
                            <div class="p-6 flex flex-col justify-center items-center min-h-96">
                                <div id="design-output" class="text-center w-full">
                                    <div class="border-2 border-dashed border-border rounded-xl p-12 bg-muted/20">
                                        <div class="w-16 h-16 bg-muted/50 rounded-full flex items-center justify-center mx-auto mb-4">
                                            <i data-lucide="image" class="h-8 w-8 text-muted-foreground"></i>
                                        </div>
                                        <h4 class="text-lg font-semibold text-foreground mb-2">No design generated yet</h4>
                                        <p class="text-muted-foreground">Fill out the form and click "Generate Design" to create your custom design</p>
                                    </div>
                                </div>
                                
                                <!-- Design Actions (hidden initially) -->
                                <div id="design-actions" class="hidden mt-6">
                                    <div class="flex flex-col sm:flex-row gap-3">
                                        <button class="flex-1 bg-success text-white px-6 py-3 rounded-lg font-medium hover:bg-success/90 transition-all duration-200 flex items-center justify-center gap-2" id="save-design-btn">
                                            <i data-lucide="heart" class="h-4 w-4"></i>
                                            Save Design
                                        </button>
                                        <button class="px-6 py-3 border border-primary text-primary rounded-lg font-medium hover:bg-primary hover:text-white transition-all duration-200 flex items-center justify-center gap-2" id="regenerate-btn">
                                            <i data-lucide="refresh-cw" class="h-4 w-4"></i>
                                            Regenerate
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- My Designs Tab -->
        <div class="tab-panel hidden" id="my-designs-panel">
            <div class="bg-card rounded-xl shadow-sm border border-border overflow-hidden">
                <div class="bg-gradient-to-r from-primary/5 to-primary-glow/5 px-6 py-4 border-b border-border">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-primary/10 rounded-lg flex items-center justify-center">
                            <i data-lucide="folder" class="h-4 w-4 text-primary"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-foreground">My Saved Designs</h3>
                            <p class="text-sm text-muted-foreground">View and manage your saved AI-generated designs</p>
                        </div>
                    </div>
                </div>
                <div class="p-6">
                    <div id="saved-designs-container">
                        <div class="text-center py-12">
                            <div class="w-16 h-16 bg-muted/50 rounded-full flex items-center justify-center mx-auto mb-4">
                                <i data-lucide="folder-open" class="h-8 w-8 text-muted-foreground"></i>
                            </div>
                            <h4 class="text-lg font-semibold text-foreground mb-2">No saved designs yet</h4>
                            <p class="text-muted-foreground mb-6">Generate and save designs to see them here</p>
                            <button 
                                class="bg-gradient-to-r from-primary to-primary-glow text-white px-6 py-3 rounded-lg font-medium hover:shadow-lg hover:shadow-primary/25 transition-all duration-200 flex items-center justify-center gap-2 mx-auto" 
                                onclick="document.getElementById('ai-generate-tab').click();">
                                <i data-lucide="sparkles" class="h-4 w-4"></i>
                                Create Your First Design
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
/* Custom Tab Styles */
.tab-button {
    @apply flex items-center gap-2 px-4 py-3 text-sm font-medium text-muted-foreground border-b-2 border-transparent hover:text-foreground hover:border-primary/50 transition-all duration-200;
}

.tab-button.active {
    @apply text-primary border-primary;
}

.tab-panel {
    @apply hidden;
}

.tab-panel.active {
    @apply block;
}

</style>
@endpush

@push('scripts')
<script>
// Initialize Lucide icons
if (typeof lucide !== 'undefined') {
    lucide.createIcons();
}

function base64ToUint8Array(base64) {
    const binaryString = atob(base64);
    const len = binaryString.length;
    const bytes = new Uint8Array(len);
    for (let i = 0; i < len; i++) {
        bytes[i] = binaryString.charCodeAt(i);
    }
    return bytes;
}


// Tab Management
function switchTab(tabName) {
    // Update tab buttons
    document.querySelectorAll('.tab-button').forEach(btn => {
        btn.classList.remove('active');
    });
    document.getElementById(tabName + '-tab').classList.add('active');
    
    // Update tab panels
    document.querySelectorAll('.tab-panel').forEach(panel => {
        panel.classList.remove('active');
        panel.classList.add('hidden');
    });
    document.getElementById(tabName + '-panel').classList.remove('hidden');
    document.getElementById(tabName + '-panel').classList.add('active');
    
    // Re-initialize icons
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
}

// Tab button event listeners
document.querySelectorAll('.tab-button').forEach(btn => {
    btn.addEventListener('click', () => {
        const tabName = btn.getAttribute('data-tab');
        switchTab(tabName);
    });
});

// Prompt Enhancer Function
function enhancePrompt() {
    const promptInput = document.getElementById('prompt');
    const currentPrompt = promptInput.value.trim();
    
    if (!currentPrompt) {
        alert('Please enter a design description first');
        return;
    }
    
    // Enhancement suggestions based on current prompt
    const enhancements = [
        {
            keyword: 'color',
            suggestion: 'Add specific color names (e.g., "deep blue", "rose gold", "emerald green")'
        },
        {
            keyword: 'style',
            suggestion: 'Specify the art style (e.g., "minimalist", "vintage", "modern", "abstract")'
        },
        {
            keyword: 'layout',
            suggestion: 'Describe the layout (e.g., "centered", "asymmetric", "grid-based", "diagonal")'
        },
        {
            keyword: 'texture',
            suggestion: 'Add texture details (e.g., "glossy", "matte", "metallic", "textured")'
        },
        {
            keyword: 'font',
            suggestion: 'Specify typography (e.g., "bold sans-serif", "elegant serif", "modern geometric")'
        }
    ];
    
    // Check what's missing and suggest enhancements
    let suggestions = [];
    const lowerPrompt = currentPrompt.toLowerCase();
    
    // Check for missing elements
    if (!lowerPrompt.includes('color') && !lowerPrompt.includes('blue') && !lowerPrompt.includes('red') && !lowerPrompt.includes('green')) {
        suggestions.push('💡 Add specific colors (e.g., "navy blue", "gold", "white")');
    }
    if (!lowerPrompt.includes('style') && !lowerPrompt.includes('modern') && !lowerPrompt.includes('vintage') && !lowerPrompt.includes('classic')) {
        suggestions.push('💡 Specify a style (e.g., "modern", "minimalist", "vintage")');
    }
    if (!lowerPrompt.includes('layout') && !lowerPrompt.includes('centered') && !lowerPrompt.includes('grid')) {
        suggestions.push('💡 Describe the layout (e.g., "centered", "balanced", "asymmetric")');
    }
    if (!lowerPrompt.includes('quality') && !lowerPrompt.includes('professional') && !lowerPrompt.includes('high')) {
        suggestions.push('💡 Add quality descriptors (e.g., "high quality", "professional", "4k")');
    }
    if (!lowerPrompt.includes('element') && !lowerPrompt.includes('icon') && !lowerPrompt.includes('image')) {
        suggestions.push('💡 Mention key design elements (e.g., "with icons", "geometric shapes", "illustrations")');
    }
    
    // Show enhancement modal
    showEnhancementModal(currentPrompt, suggestions);
}

function showEnhancementModal(currentPrompt, suggestions) {
    const modal = document.createElement('div');
    modal.className = 'fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4';
    modal.id = 'enhancement-modal';
    
    const suggestionsHTML = suggestions.length > 0 
        ? suggestions.map(s => `<li class="text-sm text-foreground">${s}</li>`).join('')
        : '<li class="text-sm text-success">✓ Your prompt looks great!</li>';
    
    modal.innerHTML = `
        <div class="bg-card rounded-2xl shadow-2xl border border-border max-w-md w-full p-6">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 bg-primary/10 rounded-lg flex items-center justify-center">
                    <i data-lucide="wand-2" class="h-5 w-5 text-primary"></i>
                </div>
                <h3 class="text-lg font-semibold text-foreground">Prompt Enhancement Suggestions</h3>
            </div>
            
            <div class="mb-6 p-4 bg-muted/20 rounded-lg border border-border">
                <p class="text-xs text-muted-foreground mb-2">Current Prompt:</p>
                <p class="text-sm text-foreground">"${currentPrompt}"</p>
            </div>
            
            <div class="mb-6">
                <p class="text-sm font-medium text-foreground mb-3">Suggestions to improve your prompt:</p>
                <ul class="space-y-2">
                    ${suggestionsHTML}
                </ul>
            </div>
            
            <div class="space-y-3">
                <button onclick="applyEnhancement()" class="w-full bg-primary text-white px-4 py-2 rounded-lg font-medium hover:bg-primary/90 transition-colors">
                    Apply Enhancement
                </button>
                <button onclick="closeEnhancementModal()" class="w-full bg-muted text-foreground px-4 py-2 rounded-lg font-medium hover:bg-muted/80 transition-colors">
                    Close
                </button>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
    
    // Re-initialize Lucide icons
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
}

function applyEnhancement() {
    const promptInput = document.getElementById('prompt');
    const currentPrompt = promptInput.value.trim();
    
    // Add enhancement suggestions to the prompt
    let enhancedPrompt = currentPrompt;
    
    // Add quality if missing
    if (!currentPrompt.toLowerCase().includes('quality') && !currentPrompt.toLowerCase().includes('professional')) {
        enhancedPrompt += ', high quality, professional';
    }
    
    // Add detail level if missing
    if (!currentPrompt.toLowerCase().includes('detail') && !currentPrompt.toLowerCase().includes('detailed')) {
        enhancedPrompt += ', detailed';
    }
    
    // Add rendering quality
    if (!currentPrompt.toLowerCase().includes('4k') && !currentPrompt.toLowerCase().includes('hd')) {
        enhancedPrompt += ', 4k';
    }
    
    promptInput.value = enhancedPrompt;
    closeEnhancementModal();
    
    // Show success message
    showToast('Prompt enhanced! Ready to generate.', 'success');
}

function closeEnhancementModal() {
    const modal = document.getElementById('enhancement-modal');
    if (modal) {
        modal.remove();
    }
}

function openGeneratedDesignPreview() {
    const base64 = window.currentDesign?.image_base64;
    const mt = window.currentDesign?.mime_type || 'image/png';
    const imageUrl = base64 ? `data:${mt};base64,${base64}` : window.currentDesign?.image_url;
    if (!imageUrl) return;

    const modal = document.createElement('div');
    modal.className = 'fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4';
    modal.id = 'generated-design-preview-modal';

    modal.innerHTML = `
        <div class="bg-card rounded-2xl shadow-2xl border border-border max-w-4xl w-full max-h-[90vh] overflow-hidden">
            <div class="bg-gradient-to-r from-primary to-primary-glow px-6 py-4 flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-white">Generated Design Preview</h3>
                    <p class="text-sm text-white/80">This design is only shown in this modal</p>
                </div>
                <button onclick="closeGeneratedDesignPreview()" class="w-8 h-8 bg-white/20 rounded-lg flex items-center justify-center hover:bg-white/30 transition-colors">
                    <i data-lucide="x" class="h-4 w-4 text-white"></i>
                </button>
            </div>
            <div class="p-6 bg-background">
                <div class="flex justify-center">
                    <img src="${imageUrl}" class="max-w-full h-auto rounded-xl border border-border" alt="Generated Design" style="max-height: 70vh;" onerror="this.outerHTML='<div class=\"text-center py-12\"><p class=\"text-destructive\">Failed to load preview.</p></div>'">
                </div>
            </div>
            <div class="px-6 py-4 border-t border-border bg-muted/20 flex justify-end">
                <button onclick="closeGeneratedDesignPreview()" class="bg-muted text-foreground px-4 py-2 rounded-lg font-medium hover:bg-muted/80 transition-colors">Close</button>
            </div>
        </div>
    `;

    document.body.appendChild(modal);
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
}

function closeGeneratedDesignPreview() {
    const modal = document.getElementById('generated-design-preview-modal');
    if (modal) {
        modal.remove();
    }
}

function showToast(message, type = 'info') {
    const toastContainer = document.createElement('div');
    toastContainer.className = 'fixed top-4 right-4 z-50';
    
    const bgColor = type === 'success' ? 'bg-success' : type === 'error' ? 'bg-destructive' : 'bg-primary';
    
    toastContainer.innerHTML = `
        <div class="flex items-center gap-3 ${bgColor} text-white px-4 py-3 rounded-lg shadow-lg">
            <i data-lucide="${type === 'success' ? 'check' : type === 'error' ? 'alert-circle' : 'info'}" class="h-4 w-4"></i>
            <span class="text-sm font-medium">${message}</span>
        </div>
    `;
    
    document.body.appendChild(toastContainer);
    
    // Re-initialize Lucide icons
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
    
    // Auto-remove after 3 seconds
    setTimeout(() => {
        toastContainer.remove();
    }, 3000);
}

// Enhance button event listener
document.getElementById('enhance-prompt-btn').addEventListener('click', function(e) {
    e.preventDefault();
    enhancePrompt();
});

async function refreshAiUsage() {
    const el = document.getElementById('ai-usage-text');
    if (!el) return;

    try {
        const res = await fetch('{{ route("ai-design.usage") }}', {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin'
        });
        const data = await res.json();
        if (data && data.success) {
            el.textContent = `${data.remaining_today} remaining today (limit ${data.daily_limit})`;
        } else {
            el.textContent = 'Unable to load usage.';
        }
    } catch (e) {
        el.textContent = 'Unable to load usage.';
    }
}

document.getElementById('ai-usage-refresh')?.addEventListener('click', function() {
    refreshAiUsage();
});

// AI Design Form Handler
document.getElementById('ai-design-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const btn = document.getElementById('generate-btn');
    const originalText = btn.innerHTML;
    
    // Show loading state
    btn.disabled = true;
    btn.innerHTML = '<div class="animate-spin w-4 h-4 border-2 border-white border-t-transparent rounded-full mr-2 inline-block"></div>Generating Design (this may take 30-60 seconds)...';
    
    try {
        // Get form values
        const prompt = document.getElementById('prompt').value;
        const style = document.getElementById('style').value;
        const size = document.getElementById('size').value;
        const designType = document.getElementById('design-type')?.value;
        const colorScheme = document.getElementById('color-scheme')?.value;
        
        console.log('Sending request with:', { prompt, style, size });
        
        const response = await fetch('{{ route("ai-design.generate") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin',
            body: JSON.stringify({
                prompt: prompt,
                style: style,
                size: size,
                design_type: designType,
                color_scheme: colorScheme
            })
        });
        
        console.log('Response status:', response.status);
        
        if (!response.ok) {
            let errorPayload = null;
            const raw = await response.text();
            try { errorPayload = raw ? JSON.parse(raw) : null; } catch (e) { errorPayload = null; }
            console.error('HTTP Error:', response.status, raw);

            if (response.status === 429) {
                throw new Error(errorPayload?.message || 'Daily AI generation limit reached. Please try again tomorrow.');
            }

            throw new Error(errorPayload?.message || `HTTP Error: ${response.status} - ${raw}`);
        }
        
        const result = await response.json();
        console.log('API Response:', result);
        
        if (result.success && result.image_base64 && result.filename) {
            if (window.currentDesign?.image_url && String(window.currentDesign.image_url).startsWith('blob:')) {
                try { URL.revokeObjectURL(window.currentDesign.image_url); } catch (e) {}
            }

            const bytes = base64ToUint8Array(result.image_base64);
            const blob = new Blob([bytes], { type: result.mime_type || 'image/png' });
            const objectUrl = URL.createObjectURL(blob);

            // Display generated design
            const outputDiv = document.getElementById('design-output');
            outputDiv.innerHTML = `
                <div class="text-center">
                    <div class="w-20 h-20 bg-success/10 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i data-lucide="check-circle" class="h-10 w-10 text-success"></i>
                    </div>
                    <div class="mt-6">
                        <h4 class="text-lg font-semibold text-success mb-2">Design Generated Successfully!</h4>
                        <p class="text-muted-foreground">Your custom design is ready for use</p>
                    </div>
                    <div class="mt-5 flex flex-col sm:flex-row gap-3 justify-center">
                        <button type="button" onclick="openGeneratedDesignPreview()" class="bg-primary text-white px-6 py-3 rounded-lg font-medium hover:bg-primary/90 transition-all duration-200 flex items-center justify-center gap-2">
                            <i data-lucide="eye" class="h-4 w-4"></i>
                            Preview Design
                        </button>
                    </div>
                </div>
            `;
            
            // Show design actions
            document.getElementById('design-actions').classList.remove('hidden');
            
            // Store design data for saving
            window.currentDesign = {
                ...result,
                image_url: objectUrl,
                image_base64: result.image_base64
            };

            // Re-initialize icons
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }

            refreshAiUsage();
            
        } else {
            const errorMsg = result.message || 'Failed to generate design. Please check your API key and try again.';
            throw new Error(errorMsg);
        }
    } catch (error) {
        console.error('Design generation error:', error);
        
        // Show error in output
        const outputDiv = document.getElementById('design-output');
        let errorMessage = error.message;
        
        // Provide helpful error messages
        if (errorMessage.includes('Daily AI generation limit')) {
            errorMessage = 'Daily AI generation limit reached. Please try again tomorrow.';
        } else if (errorMessage.includes('API key')) {
            errorMessage = 'API key not configured. Please add your Gemini API key to the environment.';
        } else if (errorMessage.includes('HTTP')) {
            errorMessage = 'Server error. Please check the logs and try again.';
        } else if (errorMessage.includes('timeout')) {
            errorMessage = 'Request timed out. The API may be slow. Please try again.';
        }
        
        outputDiv.innerHTML = `
            <div class="text-center py-8">
                <div class="w-16 h-16 bg-destructive/10 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i data-lucide="alert-circle" class="h-8 w-8 text-destructive"></i>
                </div>
                <h4 class="text-lg font-semibold text-destructive mb-2">Generation Failed</h4>
                <p class="text-muted-foreground mb-4">${errorMessage}</p>
                <div class="flex gap-2 justify-center">
                    <button class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-primary/90 transition-colors" onclick="document.getElementById('generate-btn').click();">
                        Try Again
                    </button>
                    <button class="bg-muted text-foreground px-4 py-2 rounded-lg hover:bg-muted/80 transition-colors" onclick="console.log(window.lastError);">
                        View Details
                    </button>
                </div>
            </div>
        `;
        
        // Store error for debugging
        window.lastError = error;
        
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

refreshAiUsage();

// Reset Form
document.getElementById('reset-btn').addEventListener('click', function() {
    document.getElementById('ai-design-form').reset();
    
    // Reset output
    const outputDiv = document.getElementById('design-output');
    outputDiv.innerHTML = `
        <div class="border-2 border-dashed border-border rounded-xl p-12 bg-muted/20">
            <div class="w-16 h-16 bg-muted/50 rounded-full flex items-center justify-center mx-auto mb-4">
                <i data-lucide="image" class="h-8 w-8 text-muted-foreground"></i>
            </div>
            <h4 class="text-lg font-semibold text-foreground mb-2">No design generated yet</h4>
            <p class="text-muted-foreground">Fill out the form and click "Generate Design" to create your custom design</p>
        </div>
    `;
    
    // Hide design actions
    document.getElementById('design-actions').classList.add('hidden');
    
    // Re-initialize icons
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
});

// Save Design
document.getElementById('save-design-btn').addEventListener('click', async function() {
    try {
        if (!window.currentDesign?.image_url || !window.currentDesign?.filename) {
            throw new Error('No generated design to save yet.');
        }

        const title = window.prompt('Enter a title for this design:', 'My AI Design');
        if (!title) return;

        const response = await fetch('{{ route("ai-design.save") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                image_base64: window.currentDesign.image_base64,
                filename: window.currentDesign.filename,
                title: title,
                description: window.currentDesign.prompt || null
            })
        });

        const result = await response.json();
        if (!response.ok || !result.success) {
            throw new Error(result.message || 'Failed to save design');
        }

        showToast('Design saved successfully!', 'success');
    } catch (error) {
        console.error('Save design error:', error);
        showToast(error.message || 'Failed to save design', 'error');
    }
});

// Regenerate
document.getElementById('regenerate-btn').addEventListener('click', function() {
    document.getElementById('ai-design-form').dispatchEvent(new Event('submit'));
});

// Initialize page
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Lucide icons
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
    
    // Set up tab functionality
    document.querySelectorAll('.tab-button').forEach(btn => {
        btn.addEventListener('click', () => {
            const tabName = btn.getAttribute('data-tab');
            switchTab(tabName);
        });
    });
    
});
</script>
@endpush
