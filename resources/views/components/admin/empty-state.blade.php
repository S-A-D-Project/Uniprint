@props([
    'icon' => 'inbox',
    'title' => 'No data found',
    'description' => 'There is no data to display at the moment.',
    'action' => null,
    'actionLabel' => null,
    'actionIcon' => null,
])

<div class="flex flex-col items-center justify-center py-12 px-4 text-center {{ $attributes->get('class') }}" {{ $attributes->except('class') }}>
    <div class="bg-secondary/50 p-6 rounded-full mb-4">
        <i data-lucide="{{ $icon }}" class="h-16 w-16 text-muted-foreground"></i>
    </div>
    
    <h3 class="text-lg font-semibold mb-2">{{ $title }}</h3>
    <p class="text-muted-foreground max-w-md mb-6">{{ $description }}</p>
    
    @if($action)
        <x-admin.button 
            :href="$action" 
            variant="primary" 
            :icon="$actionIcon">
            {{ $actionLabel ?? 'Get Started' }}
        </x-admin.button>
    @endif
    
    @if($slot->isNotEmpty())
        <div class="mt-4">
            {{ $slot }}
        </div>
    @endif
</div>
