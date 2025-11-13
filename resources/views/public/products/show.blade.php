@extends('layouts.public')

@section('title', $product->product_name . ' - Printing Service')

@section('content')
    <div class="min-h-screen bg-background">
        <main class="container mx-auto px-4 py-8">
            <!-- Breadcrumb -->
            <div class="mb-6">
                <div class="flex items-center gap-2 text-sm text-muted-foreground">
                    <a href="{{ route('home') }}" class="hover:text-primary">Home</a>
                    <i data-lucide="chevron-right" class="h-4 w-4"></i>
                    <a href="{{ route('enterprises.index') }}" class="hover:text-primary">Shops</a>
                    <i data-lucide="chevron-right" class="h-4 w-4"></i>
                    <a href="{{ route('enterprises.show', $product->enterprise_id) }}" class="hover:text-primary">{{ $product->enterprise->name ?? 'Unknown Shop' }}</a>
                    <i data-lucide="chevron-right" class="h-4 w-4"></i>
                    <span class="text-foreground">{{ $product->product_name }}</span>
                </div>
            </div>

            <div class="grid lg:grid-cols-2 gap-8">
                <!-- Product Image and Info -->
                <div>
                    <div class="h-96 gradient-accent rounded-xl mb-4 flex items-center justify-center">
                        <i data-lucide="printer" class="h-32 w-32 text-white"></i>
                    </div>
                    <div class="space-y-4">
                        <div>
                            <a href="{{ route('enterprises.show', $product->enterprise_id) }}" class="inline-block px-3 py-1 text-sm font-medium bg-secondary text-secondary-foreground rounded-md hover:bg-secondary/80 transition-smooth">
                                {{ $product->enterprise->name ?? 'Unknown Shop' }}
                            </a>
                        </div>
                        <div>
                            <h1 class="text-3xl font-bold mb-2">{{ $product->product_name }}</h1>
                            <p class="text-lg text-muted-foreground">{{ $product->description ?? 'Professional printing service' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-muted-foreground mb-1">Starting Price</p>
                            <div class="text-4xl font-bold text-primary">
                                ₱{{ number_format($product->base_price, 2) }}
                            </div>
                            <p class="text-sm text-muted-foreground mt-1">Price may vary based on customizations</p>
                        </div>
                    </div>
                </div>

                <!-- Customization Options and Order Form -->
                <div class="space-y-6">
                    <form id="productForm" action="{{ route('saved-services.save') }}" method="POST">
                        @csrf
                        <input type="hidden" name="product_id" value="{{ $product->product_id }}">
                        
                        @if($customizationGroups->isNotEmpty())
                            <div class="space-y-4">
                                <h3 class="text-xl font-bold">Service Options</h3>
                                
                                @foreach($customizationGroups as $type => $options)
                                    <div class="bg-card border border-border rounded-xl p-6">
                                        <div class="flex items-center justify-between mb-4">
                                            <h4 class="text-lg font-semibold">{{ $type }}</h4>
                                        </div>

                                        <div class="space-y-2">
                                            @foreach($options as $option)
                                                <label class="flex items-center justify-between p-3 rounded-lg hover:bg-secondary transition-smooth cursor-pointer border border-transparent hover:border-primary">
                                                    <div class="flex items-center space-x-3">
                                                        <input type="checkbox" name="customizations[]" value="{{ $option->option_id }}" class="h-4 w-4 text-primary rounded" onchange="updatePrice()">
                                                        <div>
                                                            <span class="font-medium">{{ $option->option_name }}</span>
                                                        </div>
                                                    </div>
                                                    @if($option->price_modifier != 0)
                                                        <span class="text-sm font-medium {{ $option->price_modifier > 0 ? 'text-primary' : 'text-success' }}" data-price="{{ $option->price_modifier }}">
                                                            {{ $option->price_modifier > 0 ? '+' : '' }}₱{{ number_format(abs($option->price_modifier), 2) }}
                                                        </span>
                                                    @endif
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="bg-muted/30 border border-border rounded-lg p-6 text-center">
                                <p class="text-muted-foreground">No customization options available for this service</p>
                                <p class="text-sm text-muted-foreground mt-2">Contact the shop directly for custom requirements</p>
                            </div>
                        @endif


                        <!-- Special Instructions -->
                        <div class="bg-card border border-border rounded-xl p-6">
                            <h4 class="text-lg font-semibold mb-3 flex items-center gap-2">
                                <i data-lucide="message-square" class="h-5 w-5 text-primary"></i>
                                Special Instructions
                            </h4>
                            <textarea 
                                name="notes" 
                                placeholder="Special instructions for your print job (e.g., design preferences, pickup notes, custom requirements)"
                                rows="3"
                                class="w-full px-3 py-2 border border-input rounded-md bg-background focus:outline-none focus:ring-2 focus:ring-ring resize-none"
                            ></textarea>
                        </div>

                        <!-- Quantity and Add to Cart -->
                        <div class="bg-card border border-border rounded-xl p-6 space-y-4">
                            <div>
                                <label class="block text-sm font-medium mb-2">Quantity</label>
                                <div class="flex items-center gap-3">
                                    <button type="button" onclick="changeQuantity(-1)" class="inline-flex items-center justify-center h-10 w-10 border border-input rounded-md hover:bg-accent hover:text-accent-foreground transition-smooth">
                                        <i data-lucide="minus" class="h-4 w-4"></i>
                                    </button>
                                    <input type="number" name="quantity" id="quantity" value="1" min="1" max="100" class="w-20 text-center text-xl font-bold border border-input rounded-md py-2" onchange="updatePrice()">
                                    <button type="button" onclick="changeQuantity(1)" class="inline-flex items-center justify-center h-10 w-10 border border-input rounded-md hover:bg-accent hover:text-accent-foreground transition-smooth">
                                        <i data-lucide="plus" class="h-4 w-4"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- Design Upload Section -->
                            <div class="border-t border-border pt-4 mb-4">
                                <h4 class="text-lg font-semibold mb-3 flex items-center gap-2">
                                    <i data-lucide="upload" class="h-5 w-5 text-primary"></i>
                                    Upload Your Design Files
                                </h4>
                                <p class="text-sm text-muted-foreground mb-4">Upload your design files for printing (optional)</p>
                                
                                <!-- File Upload Area -->
                                <div class="border-2 border-dashed border-gray-300 rounded-lg p-4 text-center hover:border-primary transition-colors mb-4" id="design-upload-area">
                                    <input type="file" id="design-files" name="design_files[]" accept=".jpg,.jpeg,.png,.pdf,.ai,.psd,.eps,.svg" class="hidden" multiple>
                                    <div id="upload-prompt">
                                        <i data-lucide="upload-cloud" class="h-8 w-8 text-gray-400 mx-auto mb-2"></i>
                                        <h5 class="font-medium text-gray-900 mb-1">Drop files here or click to upload</h5>
                                        <p class="text-xs text-gray-600 mb-3">JPG, PNG, PDF, AI, PSD, EPS, SVG (Max 10MB each)</p>
                                        <button type="button" class="bg-primary text-white py-2 px-4 rounded-lg font-medium hover:bg-primary/90 transition-colors text-sm" onclick="document.getElementById('design-files').click()">
                                            <i data-lucide="upload" class="h-4 w-4 inline mr-1"></i>
                                            Choose Files
                                        </button>
                                    </div>
                                </div>

                                <!-- Uploaded Files List -->
                                <div id="uploaded-files-list" class="space-y-2 hidden">
                                    <h5 class="font-medium text-gray-900 text-sm">Uploaded Files:</h5>
                                    <div id="files-container"></div>
                                </div>
                            </div>

                            <!-- Rush Pickup Options -->
                            <div class="pt-4 border-t border-border mb-4">
                                <h4 class="text-lg font-semibold mb-3 flex items-center gap-2">
                                    <i data-lucide="clock" class="h-5 w-5 text-primary"></i>
                                    Pickup Timeline
                                </h4>
                                
                                <div class="space-y-3" id="product-rush-options">
                                    <!-- Standard Pickup -->
                                    <label class="relative cursor-pointer block">
                                        <input type="radio" name="product_rush_option" value="standard" checked class="sr-only peer" data-fee="0">
                                        <div class="border border-gray-200 rounded-lg p-3 transition-all peer-checked:border-primary peer-checked:bg-primary/5 hover:border-gray-300">
                                            <div class="flex items-center justify-between">
                                                <div class="flex items-center gap-2">
                                                    <i data-lucide="calendar" class="h-4 w-4 text-blue-600"></i>
                                                    <div>
                                                        <h5 class="font-medium text-gray-900 text-sm">Standard (2-3 days)</h5>
                                                        <p class="text-xs text-gray-600">Ready for pickup</p>
                                                    </div>
                                                </div>
                                                <span class="text-sm font-bold text-green-600">FREE</span>
                                            </div>
                                        </div>
                                    </label>

                                    <!-- Express Pickup -->
                                    <label class="relative cursor-pointer block">
                                        <input type="radio" name="product_rush_option" value="express" class="sr-only peer" data-fee="50">
                                        <div class="border border-gray-200 rounded-lg p-3 transition-all peer-checked:border-primary peer-checked:bg-primary/5 hover:border-gray-300">
                                            <div class="flex items-center justify-between">
                                                <div class="flex items-center gap-2">
                                                    <i data-lucide="zap" class="h-4 w-4 text-orange-600"></i>
                                                    <div>
                                                        <h5 class="font-medium text-gray-900 text-sm">Express (1 day)</h5>
                                                        <p class="text-xs text-gray-600">Next business day</p>
                                                    </div>
                                                </div>
                                                <span class="text-sm font-bold text-orange-600">+₱50</span>
                                            </div>
                                        </div>
                                    </label>

                                    <!-- Rush Pickup -->
                                    <label class="relative cursor-pointer block">
                                        <input type="radio" name="product_rush_option" value="rush" class="sr-only peer" data-fee="100">
                                        <div class="border border-gray-200 rounded-lg p-3 transition-all peer-checked:border-primary peer-checked:bg-primary/5 hover:border-gray-300">
                                            <div class="flex items-center justify-between">
                                                <div class="flex items-center gap-2">
                                                    <i data-lucide="flame" class="h-4 w-4 text-red-600"></i>
                                                    <div>
                                                        <h5 class="font-medium text-gray-900 text-sm flex items-center gap-1">
                                                            Rush (4-6 hrs)
                                                            <span class="bg-red-100 text-red-800 text-xs px-1 py-0.5 rounded">URGENT</span>
                                                        </h5>
                                                        <p class="text-xs text-gray-600">Same day pickup</p>
                                                    </div>
                                                </div>
                                                <span class="text-sm font-bold text-red-600">+₱100</span>
                                            </div>
                                        </div>
                                    </label>

                                    <!-- Same Day Pickup -->
                                    <label class="relative cursor-pointer block">
                                        <input type="radio" name="product_rush_option" value="same_day" class="sr-only peer" data-fee="200">
                                        <div class="border border-gray-200 rounded-lg p-3 transition-all peer-checked:border-primary peer-checked:bg-primary/5 hover:border-gray-300">
                                            <div class="flex items-center justify-between">
                                                <div class="flex items-center gap-2">
                                                    <i data-lucide="rocket" class="h-4 w-4 text-purple-600"></i>
                                                    <div>
                                                        <h5 class="font-medium text-gray-900 text-sm flex items-center gap-1">
                                                            Same Day (2-3 hrs)
                                                            <span class="bg-purple-100 text-purple-800 text-xs px-1 py-0.5 rounded">PREMIUM</span>
                                                        </h5>
                                                        <p class="text-xs text-gray-600">Ultra-fast pickup</p>
                                                    </div>
                                                </div>
                                                <span class="text-sm font-bold text-purple-600">+₱200</span>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                            </div>

                            <div class="pt-4 border-t border-border">
                                <div class="flex justify-between items-center mb-2">
                                    <span class="text-lg font-medium">Total Price</span>
                                    <span class="text-3xl font-bold text-primary">₱<span id="totalPrice">{{ number_format($product->base_price, 2) }}</span></span>
                                </div>
                                
                                <!-- Rush Fee Display -->
                                <div class="flex justify-between items-center mb-4 text-sm" id="rush-fee-display" style="display: none;">
                                    <span class="text-gray-600">Rush Fee:</span>
                                    <span class="font-medium text-orange-600" id="product-rush-fee">₱0.00</span>
                                </div>

                                @auth
                                    <button type="submit" id="saveServiceBtn" class="w-full inline-flex items-center justify-center gap-2 px-8 py-4 text-lg font-semibold text-white gradient-primary rounded-lg hover:shadow-glow transition-smooth mb-3">
                                        <i data-lucide="heart" class="h-5 w-5"></i>
                                        <span class="btn-text">Save Service</span>
                                    </button>
                                    
                                    <!-- Order Now Button with Completion Time -->
                                    <button type="button" onclick="buyNow()" id="product-order-now-btn" class="w-full px-4 py-3 bg-gradient-to-r from-primary to-primary/90 text-white font-semibold rounded-lg hover:shadow-lg transition-all flex flex-col items-center justify-center gap-1 mb-3">
                                        <div class="flex items-center gap-2">
                                            <i data-lucide="zap" class="h-5 w-5"></i>
                                            <span>Order Now</span>
                                        </div>
                                        <div class="text-xs opacity-90" id="product-completion-time">
                                            Ready by Friday, Nov 15, 2024 at 5:00 PM
                                        </div>
                                    </button>
                                    
                                    <a href="{{ route('saved-services.index') }}" class="block text-center px-4 py-2 border border-input rounded-md hover:bg-accent hover:text-accent-foreground transition-smooth">
                                        <i data-lucide="heart" class="h-4 w-4 inline mr-2"></i>
                                        View Saved Services
                                    </a>
                                @else
                                    <a href="{{ route('login') }}" class="block w-full text-center px-8 py-4 text-lg font-semibold text-white gradient-primary rounded-lg hover:shadow-glow transition-smooth">
                                        Login to Order Service
                                    </a>
                                @endauth
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
@endsection

@push('scripts')
<script>
    const basePrice = {{ $product->base_price }};
    
    function changeQuantity(change) {
        const input = document.getElementById('quantity');
        const newValue = Math.max(1, parseInt(input.value) + change);
        input.value = newValue;
        updatePrice();
    }
    
    function updatePrice() {
        const quantity = parseInt(document.getElementById('quantity').value);
        let total = basePrice;
        
        // Calculate from selected options
        document.querySelectorAll('input[type="radio"]:checked, input[type="checkbox"]:checked').forEach(input => {
            const priceSpan = input.closest('label').querySelector('[data-price]');
            if (priceSpan) {
                total += parseFloat(priceSpan.dataset.price);
            }
        });
        
        total *= quantity;
        document.getElementById('totalPrice').textContent = total.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    }
    
    // Form validation and cart functionality
    document.getElementById('productForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const requiredGroups = document.querySelectorAll('[data-required="true"]');
        let isValid = true;
        
        requiredGroups.forEach(group => {
            const groupId = group.dataset.groupId;
            const selected = document.querySelector(`input[name="customization[${groupId}]"]:checked`);
            if (!selected) {
                isValid = false;
                alert('Please select all required options');
            }
        });
        
        if (!isValid) {
            return;
        }

        // Save service via AJAX
        await saveService();
    });

    // Save service function
    async function saveService() {
        const btn = document.getElementById('saveServiceBtn');
        const originalText = btn.innerHTML;
        
        // Show loading state
        btn.disabled = true;
        btn.innerHTML = '<i data-lucide="loader-2" class="h-5 w-5 animate-spin"></i><span class="btn-text">Saving...</span>';
        
        try {
            const formData = new FormData(document.getElementById('productForm'));
            
            const response = await fetch('{{ route("saved-services.save") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                // Show success state
                btn.innerHTML = '<i data-lucide="check" class="h-5 w-5"></i><span class="btn-text">Saved!</span>';
                
                // Update saved services count in header if exists
                updateSavedServicesCount();
                
                // Show success message
                showToast(result.message, 'success');
                
                // Reset button after 2 seconds
                setTimeout(() => {
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                    if (typeof lucide !== 'undefined') {
                        lucide.createIcons();
                    }
                }, 2000);
            } else {
                throw new Error(result.message || 'Failed to save service');
            }
        } catch (error) {
            console.error('Error saving service:', error);
            showToast(error.message || 'Failed to save service', 'error');
            
            // Reset button
            btn.disabled = false;
            btn.innerHTML = originalText;
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        }
    }

    // Buy now function
    async function buyNow() {
        await saveService();
        // Redirect to checkout after successful save
        setTimeout(() => {
            window.location.href = '{{ route("checkout.index") }}';
        }, 1000);
    }

    // Update saved services count
    async function updateSavedServicesCount() {
        try {
            const response = await fetch('{{ route("saved-services.count") }}');
            const result = await response.json();
            
            // Update saved services count in header if element exists
            const savedServicesCountElement = document.querySelector('.saved-services-count');
            if (savedServicesCountElement) {
                savedServicesCountElement.textContent = result.count;
            }
        } catch (error) {
            console.error('Error updating saved services count:', error);
        }
    }

    // Rush pickup functionality
    let currentProductRushFee = 0;
    const basePrice = {{ $product->base_price }};
    
    // Rush pickup options configuration
    const productRushOptions = {
        'standard': { fee: 0, label: 'Standard Pickup', description: 'Ready in 2-3 business days' },
        'express': { fee: 50, label: 'Express Pickup', description: 'Ready in 1 business day' },
        'rush': { fee: 100, label: 'Rush Pickup', description: 'Ready in 4-6 hours' },
        'same_day': { fee: 200, label: 'Same Day Pickup', description: 'Ready in 2-3 hours' }
    };
    
    // Calculate completion time for product page
    function calculateProductCompletionTime(rushType) {
        const now = new Date();
        let completionTime = new Date(now);
        
        if (rushType === 'same_day') {
            completionTime.setHours(now.getHours() + 3);
        } else if (rushType === 'rush') {
            completionTime.setHours(now.getHours() + 6);
        } else if (rushType === 'express') {
            completionTime.setDate(now.getDate() + 1);
            completionTime.setHours(17, 0, 0, 0);
            while (completionTime.getDay() === 0 || completionTime.getDay() === 6) {
                completionTime.setDate(completionTime.getDate() + 1);
            }
        } else {
            let businessDays = 0;
            while (businessDays < 2) {
                completionTime.setDate(completionTime.getDate() + 1);
                if (completionTime.getDay() !== 0 && completionTime.getDay() !== 6) {
                    businessDays++;
                }
            }
            completionTime.setHours(17, 0, 0, 0);
        }
        
        return completionTime;
    }
    
    // Format completion time
    function formatProductCompletionTime(date) {
        const options = {
            weekday: 'long',
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: 'numeric',
            minute: '2-digit',
            hour12: true
        };
        return date.toLocaleDateString('en-US', options);
    }
    
    // Update product pricing and completion time
    function updateProductRushDisplay(rushType) {
        const option = productRushOptions[rushType];
        if (!option) return;
        
        // Update rush fee
        currentProductRushFee = option.fee;
        const rushFeeDisplay = document.getElementById('rush-fee-display');
        const rushFeeAmount = document.getElementById('product-rush-fee');
        
        if (currentProductRushFee > 0) {
            rushFeeDisplay.style.display = 'flex';
            rushFeeAmount.textContent = `₱${currentProductRushFee.toFixed(2)}`;
            
            // Update rush fee color based on urgency
            rushFeeAmount.className = 'font-medium';
            if (rushType === 'express') {
                rushFeeAmount.classList.add('text-orange-600');
            } else if (rushType === 'rush') {
                rushFeeAmount.classList.add('text-red-600');
            } else if (rushType === 'same_day') {
                rushFeeAmount.classList.add('text-purple-600');
            }
        } else {
            rushFeeDisplay.style.display = 'none';
        }
        
        // Update total price
        const newTotal = basePrice + currentProductRushFee;
        document.getElementById('totalPrice').textContent = newTotal.toFixed(2);
        
        // Update completion time
        const completionTime = calculateProductCompletionTime(rushType);
        const formattedTime = formatProductCompletionTime(completionTime);
        document.getElementById('product-completion-time').textContent = `Ready by ${formattedTime}`;
        
        // Update order button styling based on urgency
        const orderBtn = document.getElementById('product-order-now-btn');
        orderBtn.className = 'w-full px-4 py-3 text-white font-semibold rounded-lg hover:shadow-lg transition-all flex flex-col items-center justify-center gap-1 mb-3';
        
        if (rushType === 'rush' || rushType === 'same_day') {
            orderBtn.classList.add('bg-gradient-to-r', 'from-red-500', 'to-red-600', 'hover:from-red-600', 'hover:to-red-700');
            if (rushType === 'same_day') {
                orderBtn.classList.remove('from-red-500', 'to-red-600', 'hover:from-red-600', 'hover:to-red-700');
                orderBtn.classList.add('from-purple-500', 'to-purple-600', 'hover:from-purple-600', 'hover:to-purple-700');
            }
        } else if (rushType === 'express') {
            orderBtn.classList.add('bg-gradient-to-r', 'from-orange-500', 'to-orange-600', 'hover:from-orange-600', 'hover:to-orange-700');
        } else {
            orderBtn.classList.add('bg-gradient-to-r', 'from-primary', 'to-primary/90');
        }
    }
    
    // Initialize product rush option listeners
    document.addEventListener('DOMContentLoaded', function() {
        const productRushRadios = document.querySelectorAll('input[name="product_rush_option"]');
        
        productRushRadios.forEach(radio => {
            radio.addEventListener('change', function() {
                if (this.checked) {
                    updateProductRushDisplay(this.value);
                }
            });
        });
        
        // Initialize with default selection
        const defaultProductOption = document.querySelector('input[name="product_rush_option"]:checked');
        if (defaultProductOption) {
            updateProductRushDisplay(defaultProductOption.value);
        }
    });

    // Toast notification helper
    function showToast(message, type = 'info') {
        const toastHtml = `
            <div class="fixed top-4 right-4 z-50 bg-${type === 'error' ? 'red' : 'green'}-500 text-white px-6 py-3 rounded-lg shadow-lg toast-notification">
                <div class="flex items-center gap-2">
                    <i data-lucide="${type === 'error' ? 'x-circle' : 'check-circle'}" class="h-5 w-5"></i>
                    <span>${message}</span>
                </div>
            </div>
        `;
        
        const toastContainer = document.createElement('div');
        toastContainer.innerHTML = toastHtml;
        document.body.appendChild(toastContainer);
        
        // Initialize icons for toast
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
        
        // Auto remove after 3 seconds
        setTimeout(() => {
            if (document.body.contains(toastContainer)) {
                document.body.removeChild(toastContainer);
            }
        }, 3000);
    }

    // File Upload Functionality
    const uploadArea = document.getElementById('design-upload-area');
    const fileInput = document.getElementById('design-files');
    const filesList = document.getElementById('uploaded-files-list');
    const filesContainer = document.getElementById('files-container');

    // Drag and drop functionality
    uploadArea.addEventListener('dragover', (e) => {
        e.preventDefault();
        uploadArea.classList.add('border-primary', 'bg-primary/5');
    });

    uploadArea.addEventListener('dragleave', (e) => {
        e.preventDefault();
        uploadArea.classList.remove('border-primary', 'bg-primary/5');
    });

    uploadArea.addEventListener('drop', (e) => {
        e.preventDefault();
        uploadArea.classList.remove('border-primary', 'bg-primary/5');
        
        const files = Array.from(e.dataTransfer.files);
        handleFileUpload(files);
    });

    uploadArea.addEventListener('click', () => {
        fileInput.click();
    });

    fileInput.addEventListener('change', (e) => {
        const files = Array.from(e.target.files);
        handleFileUpload(files);
    });

    function handleFileUpload(files) {
        if (files.length === 0) return;
        
        // Validate file types and sizes
        const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'application/pdf', 'application/postscript', 'image/svg+xml'];
        const maxSize = 10 * 1024 * 1024; // 10MB
        
        const validFiles = files.filter(file => {
            if (!allowedTypes.includes(file.type) && !file.name.match(/\.(ai|psd|eps)$/i)) {
                alert(`File "${file.name}" is not a supported format.`);
                return false;
            }
            if (file.size > maxSize) {
                alert(`File "${file.name}" is too large. Maximum size is 10MB.`);
                return false;
            }
            return true;
        });
        
        if (validFiles.length === 0) return;
        
        filesList.classList.remove('hidden');
        filesContainer.innerHTML = '';
        
        validFiles.forEach((file, index) => {
            const fileItem = document.createElement('div');
            fileItem.className = 'flex items-center justify-between p-3 bg-gray-50 rounded-lg border';
            
            const fileIcon = getFileIcon(file.type, file.name);
            
            fileItem.innerHTML = `
                <div class="flex items-center gap-3">
                    <i data-lucide="${fileIcon}" class="h-5 w-5 text-gray-500"></i>
                    <div>
                        <p class="font-medium text-gray-900">${file.name}</p>
                        <p class="text-sm text-gray-600">${(file.size / 1024 / 1024).toFixed(2)} MB</p>
                    </div>
                </div>
                <button type="button" class="text-red-500 hover:text-red-700 p-1" onclick="removeUploadedFile(${index})">
                    <i data-lucide="x" class="h-4 w-4"></i>
                </button>
            `;
            
            filesContainer.appendChild(fileItem);
        });
        
        // Re-initialize Lucide icons
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    }

    function getFileIcon(fileType, fileName) {
        if (fileType.startsWith('image/')) return 'file-image';
        if (fileType === 'application/pdf') return 'file-text';
        if (fileName.match(/\.(ai|psd)$/i)) return 'file';
        if (fileName.match(/\.(eps|svg)$/i)) return 'file-code';
        return 'file';
    }

    function removeUploadedFile(index) {
        const dt = new DataTransfer();
        const files = Array.from(fileInput.files);
        
        files.forEach((file, i) => {
            if (i !== index) {
                dt.items.add(file);
            }
        });
        
        fileInput.files = dt.files;
        handleFileUpload(Array.from(dt.files));
        
        if (dt.files.length === 0) {
            filesList.classList.add('hidden');
        }
    }

    // Initialize
    document.addEventListener('DOMContentLoaded', function() {
        updatePrice();
    });
</script>
@endpush
