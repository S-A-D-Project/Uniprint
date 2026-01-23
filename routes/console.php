<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Carbon\Carbon;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('orders:auto-complete', function () {
    if (! Schema::hasTable('system_settings')) {
        $this->warn('system_settings table not found. Run migrations first.');
        return 0;
    }

    $rawHours = DB::table('system_settings')->where('key', 'order_auto_complete_hours')->value('value');
    $hours = is_numeric($rawHours) ? (int) $rawHours : 72;
    if ($hours < 1) {
        $hours = 72;
    }

    $completedStatusId = DB::table('statuses')->where('status_name', 'Completed')->value('status_id');
    $deliveredStatusId = DB::table('statuses')->where('status_name', 'Delivered')->value('status_id');

    if (! $completedStatusId || ! $deliveredStatusId) {
        $this->error('Required statuses are missing (Delivered/Completed).');
        return 1;
    }

    $cutoff = Carbon::now()->subHours($hours);

    $deliveredAt = DB::table('order_status_history')
        ->join('statuses', 'order_status_history.status_id', '=', 'statuses.status_id')
        ->where('statuses.status_name', 'Delivered')
        ->where('order_status_history.timestamp', '<=', $cutoff)
        ->select('order_status_history.purchase_order_id', DB::raw('MAX(order_status_history.timestamp) as delivered_time'))
        ->groupBy('order_status_history.purchase_order_id');

    $candidates = DB::table('customer_orders')
        ->joinSub($deliveredAt, 'delivered_at', function ($join) {
            $join->on('customer_orders.purchase_order_id', '=', 'delivered_at.purchase_order_id');
        })
        ->leftJoin('order_status_history as osh_completed', function ($join) use ($completedStatusId) {
            $join->on('customer_orders.purchase_order_id', '=', 'osh_completed.purchase_order_id')
                ->where('osh_completed.status_id', '=', $completedStatusId);
        })
        ->whereNull('osh_completed.approval_id')
        ->select('customer_orders.purchase_order_id', 'customer_orders.customer_id', 'customer_orders.enterprise_id', 'customer_orders.order_no')
        ->get();

    $count = 0;
    foreach ($candidates as $row) {
        DB::transaction(function () use ($row, $completedStatusId, &$count) {
            DB::table('order_status_history')->insert([
                'approval_id' => (string) Str::uuid(),
                'purchase_order_id' => $row->purchase_order_id,
                'user_id' => null,
                'status_id' => $completedStatusId,
                'remarks' => 'Order auto-completed by system due to no customer confirmation',
                'timestamp' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            if (Schema::hasColumn('customer_orders', 'status_id')) {
                DB::table('customer_orders')
                    ->where('purchase_order_id', $row->purchase_order_id)
                    ->update([
                        'status_id' => $completedStatusId,
                        'updated_at' => now(),
                    ]);
            }

            DB::table('order_notifications')->insert([
                'notification_id' => (string) Str::uuid(),
                'purchase_order_id' => $row->purchase_order_id,
                'recipient_id' => $row->customer_id,
                'notification_type' => 'status_change',
                'title' => 'Order Auto-Completed',
                'message' => "Order #{$row->order_no} was automatically marked as completed.",
                'is_read' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $count++;
        });
    }

    $this->info("Auto-completed {$count} order(s).");
    return 0;
})->purpose('Auto-complete delivered orders after configured timeout');

Artisan::command('orders:deadline-warnings', function () {
    if (! Schema::hasTable('order_notifications')) {
        $this->warn('order_notifications table not found. Run migrations first.');
        return 0;
    }

    $today = Carbon::today();
    $dueSoon = Carbon::today()->addDay();

    $dueExpr = 'customer_orders.date_requested';
    if (Schema::hasColumn('customer_orders', 'pickup_date')) {
        $dueExpr = 'customer_orders.pickup_date';
    } elseif (Schema::hasColumn('customer_orders', 'requested_fulfillment_date')) {
        $dueExpr = 'customer_orders.requested_fulfillment_date';
    } elseif (Schema::hasColumn('customer_orders', 'delivery_date')) {
        $dueExpr = 'customer_orders.delivery_date';
    }

    $rows = DB::table('customer_orders')
        ->select('purchase_order_id', 'enterprise_id', 'order_no', DB::raw("DATE({$dueExpr}) as due_date"))
        ->whereNotNull($dueExpr)
        ->whereRaw("DATE({$dueExpr}) <= ?", [$dueSoon->toDateString()])
        ->get();

    $count = 0;

    foreach ($rows as $row) {
        $dueDate = $row->due_date ? Carbon::parse($row->due_date) : null;
        if (! $dueDate) {
            continue;
        }

        $isOverdue = $dueDate->lt($today);
        $isDueSoon = (! $isOverdue) && $dueDate->lte($dueSoon);
        if (! $isOverdue && ! $isDueSoon) {
            continue;
        }

        $recipientId = null;
        if (Schema::hasColumn('enterprises', 'owner_user_id')) {
            $recipientId = DB::table('enterprises')->where('enterprise_id', $row->enterprise_id)->value('owner_user_id');
        }
        if (! $recipientId && Schema::hasTable('staff')) {
            $recipientId = DB::table('staff')
                ->where('enterprise_id', $row->enterprise_id)
                ->whereNotNull('user_id')
                ->value('user_id');
        }

        if (! $recipientId) {
            continue;
        }

        $alreadySentToday = DB::table('order_notifications')
            ->where('purchase_order_id', $row->purchase_order_id)
            ->where('recipient_id', $recipientId)
            ->where('notification_type', 'deadline_warning')
            ->whereDate('created_at', $today->toDateString())
            ->exists();

        if ($alreadySentToday) {
            continue;
        }

        $title = $isOverdue ? 'Order Overdue' : 'Order Due Soon';
        $message = $isOverdue
            ? "Order #{$row->order_no} is overdue (due {$dueDate->format('M d, Y')})."
            : "Order #{$row->order_no} is due soon (due {$dueDate->format('M d, Y')}).";

        DB::table('order_notifications')->insert([
            'notification_id' => (string) Str::uuid(),
            'purchase_order_id' => $row->purchase_order_id,
            'recipient_id' => $recipientId,
            'notification_type' => 'deadline_warning',
            'title' => $title,
            'message' => $message,
            'is_read' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $count++;
    }

    $this->info("Created {$count} deadline warning notification(s). Run daily via cron.");
    return 0;
})->purpose('Create deadline warning notifications for business users');
