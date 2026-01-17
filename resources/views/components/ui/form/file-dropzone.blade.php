@props([
    'name' => 'files',
    'id' => null,
    'label' => null,
    'required' => false,
    'multiple' => false,
    'accept' => null,
    'help' => null,
    'buttonText' => 'Choose Files',
])

@php
    $inputId = $id;

    if (!$inputId) {
        $base = $name ?: ('dropzone_' . uniqid());
        $inputId = preg_replace('/[^A-Za-z0-9\-_:.]/', '_', $base);
    }

    $dropId = $inputId . '_drop';
    $listId = $inputId . '_list';
@endphp

<div class="space-y-2">
    @if($label)
        <label class="block text-sm font-medium" for="{{ $inputId }}">{{ $label }}</label>
    @endif

    <div id="{{ $dropId }}" class="border-2 border-dashed border-gray-300 rounded-lg p-4 text-center cursor-pointer hover:border-primary transition-colors">
        <div class="space-y-2">
            <div class="text-sm text-muted-foreground">Drop files here or click to upload</div>
            <button type="button" class="inline-flex items-center justify-center gap-2 px-4 py-2 text-sm font-medium border border-input rounded-md hover:bg-accent hover:text-accent-foreground transition-smooth" onclick="document.getElementById(@json($inputId)).click()">{{ $buttonText }}</button>
        </div>
    </div>

    <input
        type="file"
        id="{{ $inputId }}"
        name="{{ $name }}"
        @if($multiple) multiple @endif
        @if($accept) accept="{{ $accept }}" @endif
        @if($required) required @endif
        class="hidden"
    />

    <div id="{{ $listId }}" class="text-xs text-muted-foreground" style="display:none"></div>

    @if($help)
        <p class="text-xs text-muted-foreground">{{ $help }}</p>
    @endif
</div>

<script>
(function(){
    const drop = document.getElementById(@json($dropId));
    const input = document.getElementById(@json($inputId));
    const list = document.getElementById(@json($listId));
    if (!drop || !input || !list) return;

    drop.addEventListener('click', () => input.click());

    function renderList(files) {
        if (!files || files.length === 0) {
            list.style.display = 'none';
            list.textContent = '';
            return;
        }
        list.style.display = 'block';
        list.textContent = Array.from(files).map(f => f.name).join(', ');
    }

    input.addEventListener('change', () => renderList(input.files));

    drop.addEventListener('dragover', (e) => {
        e.preventDefault();
        drop.classList.add('border-primary');
    });

    drop.addEventListener('dragleave', () => {
        drop.classList.remove('border-primary');
    });

    drop.addEventListener('drop', (e) => {
        e.preventDefault();
        drop.classList.remove('border-primary');
        if (!e.dataTransfer || !e.dataTransfer.files) return;
        input.files = e.dataTransfer.files;
        renderList(input.files);
    });
})();
</script>
