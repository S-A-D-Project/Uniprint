@extends('layouts.public')

@section('title', $service->service_name . ' - Printing Service')

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
                    <a href="{{ route('enterprises.show', $service->enterprise_id) }}" class="hover:text-primary">{{ $service->enterprise->name ?? 'Unknown Shop' }}</a>
                    <i data-lucide="chevron-right" class="h-4 w-4"></i>
                    <span class="text-foreground">{{ $service->service_name }}</span>
                </div>
            </div>

            <div class="grid lg:grid-cols-2 gap-8">
                <!-- Service Image and Info -->
                <div>
                    @if(!empty($service->image_path))
                        <div class="h-96 bg-secondary rounded-xl mb-4 overflow-hidden">
                            <img src="{{ asset('storage/' . $service->image_path) }}" alt="{{ $service->service_name }}" class="w-full h-full object-cover" />
                        </div>
                    @else
                        <div class="h-96 gradient-accent rounded-xl mb-4 flex items-center justify-center">
                            <i data-lucide="printer" class="h-32 w-32 text-white"></i>
                        </div>
                    @endif
                    <div class="space-y-4">
                        <div>
                            <a href="{{ route('enterprises.show', $service->enterprise_id) }}" class="inline-block px-3 py-1 text-sm font-medium bg-secondary text-secondary-foreground rounded-md hover:bg-secondary/80 transition-smooth">
                                {{ $service->enterprise->name ?? 'Unknown Shop' }}
                            </a>
                        </div>

                        <div>
                            @if(session('user_id'))
                                <a href="{{ route('chat.enterprise', $service->enterprise_id) }}" class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-primary text-primary-foreground font-medium rounded-md hover:shadow-glow transition-smooth">
                                    <i data-lucide="message-circle" class="h-4 w-4"></i>
                                    Message this shop
                                </a>
                            @else
                                <a href="{{ route('login', ['tab' => 'signup']) }}" class="inline-flex items-center justify-center gap-2 px-4 py-2 border border-input rounded-md hover:bg-secondary transition-smooth">
                                    <i data-lucide="log-in" class="h-4 w-4"></i>
                                    Sign in to message
                                </a>
                            @endif
                        </div>
                        <div>
                            <h1 class="text-3xl font-bold mb-2">{{ $service->service_name }}</h1>
                            <p class="text-lg text-muted-foreground">{{ $service->description ?? 'Professional printing service' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-muted-foreground mb-1">Starting Price</p>
                            <div class="text-4xl font-bold text-primary">
                                ₱{{ number_format($service->base_price, 2) }}
                            </div>
                            <p class="text-sm text-muted-foreground mt-1">Price may vary based on customizations</p>
                        </div>
                    </div>
                </div>

                <!-- Customization Options and Order Form -->
                <div class="space-y-6">
                    <form id="serviceForm" action="{{ route('saved-services.save') }}" method="POST">
                        @csrf
                        <input type="hidden" name="service_id" value="{{ $service->service_id }}">
                        
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

                            <div class="pt-4 border-t border-border">
                                <div class="flex justify-between items-center mb-2">
                                    <span class="text-lg font-medium">Total Price</span>
                                    <span class="text-3xl font-bold text-primary">₱<span id="totalPrice">{{ number_format($service->base_price, 2) }}</span></span>
                                </div>
                                
                                <!-- Rush Fee Display -->
                                <div class="flex justify-between items-center mb-4 text-sm" id="rush-fee-display" style="display: none;">
                                    <span class="text-gray-600">Rush Fee:</span>
                                    <span class="font-medium text-orange-600" id="service-rush-fee">₱0.00</span>
                                </div>

                                @if(session('user_id'))
                                    <button type="submit" id="saveServiceBtn" class="w-full inline-flex items-center justify-center gap-2 px-8 py-4 text-lg font-semibold text-white gradient-primary rounded-lg hover:shadow-glow transition-smooth mb-3">
                                        <i data-lucide="heart" class="h-5 w-5"></i>
                                        <span class="btn-text">Save Service</span>
                                    </button>
                                    
                                    <!-- Order Now Button with Completion Time -->
                                    <button type="button" onclick="orderNow()" id="service-order-now-btn" class="w-full px-4 py-3 bg-gradient-to-r from-primary to-primary/90 text-white font-semibold rounded-lg hover:shadow-lg transition-all flex flex-col items-center justify-center gap-1 mb-3">
                                        <div class="flex items-center gap-2">
                                            <i data-lucide="zap" class="h-5 w-5"></i>
                                            <span>Order Now</span>
                                        </div>
                                        <div class="text-xs opacity-90" id="service-completion-time">
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
                                @endif
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
    const basePrice = {{ $service->base_price }};
    
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
        document.getElementById('totalPrice').textContent = total.toFixed(2);
    }
    
    function orderNow() {
        const form = document.getElementById('serviceForm');
        if (!form) return;
        form.action = "{{ route('checkout.from-service') }}";
        form.submit();
    }
</script>
@endpush
