<x-ui.modal id="rejectDesignFileModal" title="Reject Design File" size="md" centered>
    <form id="rejectDesignFileForm" method="POST">
        @csrf
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium mb-2">Rejection Reason</label>
                <textarea name="rejection_reason" required rows="4"
                          class="w-full px-4 py-2 border border-input rounded-md bg-background focus:outline-none focus:ring-2 focus:ring-ring"></textarea>
            </div>
        </div>
    </form>

    <x-slot name="footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" form="rejectDesignFileForm" class="btn btn-danger">Reject File</button>
    </x-slot>
</x-ui.modal>
