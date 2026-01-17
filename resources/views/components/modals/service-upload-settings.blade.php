@php
    $hasRequiresFileUpload = \Illuminate\Support\Facades\Schema::hasColumn('services', 'requires_file_upload');
    $hasUploadEnabled = \Illuminate\Support\Facades\Schema::hasColumn('services', 'file_upload_enabled');
@endphp

@if($hasRequiresFileUpload)
    <x-ui.modal id="serviceUploadSettingsModal" title="File Upload Settings" size="md" centered>
        <form id="serviceUploadSettingsForm" method="POST">
            @csrf
            <div class="space-y-4">
                <div>
                    <div class="text-sm text-muted-foreground">Service</div>
                    <div class="font-semibold" id="serviceUploadSettingsName">—</div>
                </div>

                @if($hasUploadEnabled)
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-sm font-medium">Enable File Upload</div>
                            <div class="text-xs text-muted-foreground">If enabled, customers can upload design files for this service.</div>
                        </div>
                        <x-ui.form.switch name="file_upload_enabled" id="serviceUploadSettingsEnabled" :checked="false" />
                    </div>

                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-sm font-medium">Require File Upload</div>
                            <div class="text-xs text-muted-foreground">If enabled, customers must upload design files before you can proceed.</div>
                        </div>
                        <x-ui.form.switch name="requires_file_upload" id="serviceUploadSettingsRequires" :checked="false" />
                    </div>
                @else
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-sm font-medium">Requires File Upload</div>
                            <div class="text-xs text-muted-foreground">If enabled, customers must upload design files before you can proceed.</div>
                        </div>
                        <x-ui.form.switch name="requires_file_upload" id="serviceUploadSettingsRequires" :checked="false" />
                    </div>
                @endif
            </div>
        </form>

        <x-slot name="footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" form="serviceUploadSettingsForm" class="btn btn-primary">Save</button>
        </x-slot>
    </x-ui.modal>
@endif
