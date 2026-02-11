@php
    $isVerified = isset($enterprise->is_verified) ? (bool) $enterprise->is_verified : true;
@endphp

@if(! $isVerified)
    <form method="POST" action="{{ route('admin.enterprises.verify', $enterprise->enterprise_id) }}" data-up-no-loader data-up-no-button-loader>
        @csrf
        <x-admin.button type="submit" variant="success" icon="check-circle" class="w-full">Verify Enterprise</x-admin.button>
    </form>
@endif
