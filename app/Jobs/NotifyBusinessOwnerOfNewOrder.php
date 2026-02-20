<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class NotifyBusinessOwnerOfNewOrder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private readonly string $purchaseOrderId
    ) {
    }

    public function handle(): void
    {
        try {
            $order = DB::table('customer_orders')->where('purchase_order_id', $this->purchaseOrderId)->first();
            if (!$order) {
                return;
            }

            if (!schema_has_table('order_notifications')) {
                return;
            }

            $businessUserId = null;
            if (schema_has_column('enterprises', 'owner_user_id')) {
                $businessUserId = DB::table('enterprises')
                    ->where('enterprise_id', $order->enterprise_id)
                    ->value('owner_user_id');
            }
            if (!$businessUserId && schema_has_table('staff')) {
                $businessUserId = DB::table('staff')
                    ->where('enterprise_id', $order->enterprise_id)
                    ->orderByRaw("CASE WHEN position = 'Owner' THEN 0 ELSE 1 END")
                    ->value('user_id');
            }
            if (!$businessUserId) {
                return;
            }

            $orderNo = $order->order_no ?? substr((string) $order->purchase_order_id, 0, 16);

            DB::table('order_notifications')->insert([
                'notification_id' => (string) Str::uuid(),
                'purchase_order_id' => $this->purchaseOrderId,
                'recipient_id' => $businessUserId,
                'notification_type' => 'new_order',
                'title' => 'New order received',
                'message' => 'You received a new order (' . $orderNo . ').',
                'is_read' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Throwable $e) {
            Log::warning('NotifyBusinessOwnerOfNewOrder job failed', [
                'purchase_order_id' => $this->purchaseOrderId,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
