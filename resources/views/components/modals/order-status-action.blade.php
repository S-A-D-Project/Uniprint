<x-ui.modal id="orderStatusActionModal" title="Update Order Status" size="md" centered>
    <form id="orderStatusActionForm" method="POST" data-up-global-loader>
        @csrf
        <input type="hidden" name="status_id" id="orderStatusActionStatusId" value="">
        <div class="space-y-4">
            <div>
                <div class="text-sm text-muted-foreground">Next status</div>
                <div class="font-semibold" id="orderStatusActionStatusName">—</div>
            </div>

            <div>
                <label class="block text-sm font-medium mb-2">Remarks (optional)</label>
                <textarea name="remarks" id="orderStatusActionRemarks" rows="3"
                          class="w-full px-4 py-2 border border-input rounded-md bg-background focus:outline-none focus:ring-2 focus:ring-ring"
                          placeholder="Add a short note"></textarea>
            </div>
        </div>
    </form>

    <x-slot name="footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" form="orderStatusActionForm" class="btn btn-primary" id="orderStatusActionSubmit" data-up-button-loader>Update</button>
    </x-slot>
</x-ui.modal>
