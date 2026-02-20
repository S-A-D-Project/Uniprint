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
    if (! Schema::hasTable('order_status_history') || ! Schema::hasTable('customer_orders')) {
        $this->warn('Required order tables not found. Run migrations first.');
        return 0;
    }

    if (! Schema::hasTable('order_notifications')) {
        $this->warn('order_notifications table not found. Run migrations first.');
        return 0;
    }

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

    // Only auto-complete orders whose LATEST status is Delivered and that Delivered timestamp is older than cutoff.
    $latest = DB::table('order_status_history')
        ->select('purchase_order_id', DB::raw('MAX(timestamp) as last_ts'))
        ->groupBy('purchase_order_id');

    $latestDelivered = DB::table('order_status_history as osh')
        ->joinSub($latest, 'latest', function ($join) {
            $join->on('osh.purchase_order_id', '=', 'latest.purchase_order_id')
                ->on('osh.timestamp', '=', 'latest.last_ts');
        })
        ->where('osh.status_id', $deliveredStatusId)
        ->where('osh.timestamp', '<=', $cutoff)
        ->select('osh.purchase_order_id', 'osh.timestamp as delivered_time');

    $candidates = DB::table('customer_orders')
        ->joinSub($latestDelivered, 'delivered_at', function ($join) {
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
})->purpose('Create deadline warning notifications for business owners');

Artisan::command('orders:auto-cancel-overdue', function () {
    if (! Schema::hasTable('order_status_history') || ! Schema::hasTable('customer_orders')) {
        $this->warn('Required order tables not found. Run migrations first.');
        return 0;
    }

    if (! Schema::hasTable('statuses')) {
        $this->warn('statuses table not found. Run migrations first.');
        return 0;
    }

    if (! Schema::hasTable('system_settings')) {
        $this->warn('system_settings table not found. Run migrations first.');
        return 0;
    }

    $rawDays = DB::table('system_settings')->where('key', 'order_overdue_cancel_days')->value('value');
    $days = is_numeric($rawDays) ? (int) $rawDays : 14;
    if ($days < 1) {
        $days = 14;
    }

    $cancelledStatusId = DB::table('statuses')->where('status_name', 'Cancelled')->value('status_id');
    if (! $cancelledStatusId) {
        $this->error('Cancelled status is missing.');
        return 1;
    }

    $today = Carbon::today();
    $cutoff = $today->copy()->subDays($days);

    $dueExpr = 'customer_orders.date_requested';
    if (Schema::hasColumn('customer_orders', 'pickup_date')) {
        $dueExpr = 'customer_orders.pickup_date';
    } elseif (Schema::hasColumn('customer_orders', 'requested_fulfillment_date')) {
        $dueExpr = 'customer_orders.requested_fulfillment_date';
    } elseif (Schema::hasColumn('customer_orders', 'delivery_date')) {
        $dueExpr = 'customer_orders.delivery_date';
    }

    // Latest status per order
    $latest = DB::table('order_status_history')
        ->select('purchase_order_id', DB::raw('MAX(timestamp) as last_ts'))
        ->groupBy('purchase_order_id');

    $latestStatus = DB::table('order_status_history as osh')
        ->joinSub($latest, 'latest', function ($join) {
            $join->on('osh.purchase_order_id', '=', 'latest.purchase_order_id')
                ->on('osh.timestamp', '=', 'latest.last_ts');
        })
        ->join('statuses', 'osh.status_id', '=', 'statuses.status_id')
        ->select('osh.purchase_order_id', 'statuses.status_name');

    $candidates = DB::table('customer_orders')
        ->joinSub($latestStatus, 'ls', function ($join) {
            $join->on('customer_orders.purchase_order_id', '=', 'ls.purchase_order_id');
        })
        ->whereNotNull($dueExpr)
        ->whereRaw("DATE({$dueExpr}) <= ?", [$cutoff->toDateString()])
        ->whereNotIn('ls.status_name', ['Cancelled', 'Completed'])
        ->select('customer_orders.purchase_order_id', 'customer_orders.customer_id', 'customer_orders.enterprise_id', 'customer_orders.order_no', 'customer_orders.total')
        ->get();

    $count = 0;
    foreach ($candidates as $row) {
        DB::transaction(function () use ($row, $cancelledStatusId, &$count) {
            // Idempotency: skip if already cancelled
            $alreadyCancelled = DB::table('order_status_history')
                ->where('purchase_order_id', $row->purchase_order_id)
                ->where('status_id', $cancelledStatusId)
                ->exists();
            if ($alreadyCancelled) {
                return;
            }

            DB::table('order_status_history')->insert([
                'approval_id' => (string) Str::uuid(),
                'purchase_order_id' => $row->purchase_order_id,
                'user_id' => null,
                'status_id' => $cancelledStatusId,
                'remarks' => 'Order auto-cancelled by system due to being overdue for too long',
                'timestamp' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            if (Schema::hasColumn('customer_orders', 'status_id')) {
                DB::table('customer_orders')
                    ->where('purchase_order_id', $row->purchase_order_id)
                    ->update([
                        'status_id' => $cancelledStatusId,
                        'updated_at' => now(),
                    ]);
            }

            if (Schema::hasColumn('customer_orders', 'payment_status')) {
                DB::table('customer_orders')
                    ->where('purchase_order_id', $row->purchase_order_id)
                    ->update([
                        'payment_status' => 'failed',
                        'updated_at' => now(),
                    ]);
            }

            // Record refund (best-effort)
            if (Schema::hasTable('order_refunds')) {
                $existsRefund = DB::table('order_refunds')->where('purchase_order_id', $row->purchase_order_id)->exists();
                if (! $existsRefund) {
                    $refundAmount = 0.0;
                    if (Schema::hasTable('payments')) {
                        $p = DB::table('payments')->where('purchase_order_id', $row->purchase_order_id)->first();
                        if ($p && !empty($p->is_verified)) {
                            $refundAmount = (float) ($p->amount_paid ?? 0);
                        }
                    }

                    DB::table('order_refunds')->insert([
                        'refund_id' => (string) Str::uuid(),
                        'purchase_order_id' => $row->purchase_order_id,
                        'status' => 'refunded',
                        'amount' => $refundAmount,
                        'reason' => 'Auto-cancelled overdue order',
                        'refunded_at' => now(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            // Notify customer
            if (Schema::hasTable('order_notifications')) {
                DB::table('order_notifications')->insert([
                    'notification_id' => (string) Str::uuid(),
                    'purchase_order_id' => $row->purchase_order_id,
                    'recipient_id' => $row->customer_id,
                    'notification_type' => 'status_change',
                    'title' => 'Order Cancelled (Overdue)',
                    'message' => "Order #{$row->order_no} was cancelled and refunded because it was overdue for too long.",
                    'is_read' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Notify business owner (best-effort)
            if (Schema::hasTable('order_notifications')) {
                $recipientId = null;
                if (Schema::hasColumn('enterprises', 'owner_user_id')) {
                    $recipientId = DB::table('enterprises')->where('enterprise_id', $row->enterprise_id)->value('owner_user_id');
                }
                if (! $recipientId && Schema::hasTable('staff')) {
                    $recipientId = DB::table('staff')->where('enterprise_id', $row->enterprise_id)->whereNotNull('user_id')->value('user_id');
                }
                if ($recipientId) {
                    DB::table('order_notifications')->insert([
                        'notification_id' => (string) Str::uuid(),
                        'purchase_order_id' => $row->purchase_order_id,
                        'recipient_id' => $recipientId,
                        'notification_type' => 'status_change',
                        'title' => 'Order Auto-Cancelled (Overdue)',
                        'message' => "Order #{$row->order_no} was auto-cancelled by the system due to being overdue for too long.",
                        'is_read' => false,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            $count++;
        });
    }

    $this->info("Auto-cancelled {$count} overdue order(s).");
    return 0;
})->purpose('Auto-cancel orders overdue beyond configured days and record refunds');

Artisan::command('orders:auto-cancel-downpayments', function () {
    if (! Schema::hasTable('customer_orders') || ! Schema::hasTable('order_status_history')) {
        $this->warn('Required order tables not found. Run migrations first.');
        return 0;
    }

    if (! Schema::hasTable('order_notifications')) {
        $this->warn('order_notifications table not found. Run migrations first.');
        return 0;
    }

    if (! Schema::hasColumn('customer_orders', 'downpayment_required_percent') || ! Schema::hasColumn('customer_orders', 'downpayment_due_at')) {
        $this->warn('Downpayment deadline columns not found on customer_orders. Run migrations first.');
        return 0;
    }

    $cancelledStatusId = DB::table('statuses')->where('status_name', 'Cancelled')->value('status_id');
    if (! $cancelledStatusId) {
        $this->error('Status "Cancelled" is not configured.');
        return 1;
    }

    $now = Carbon::now();

    $latest = DB::table('order_status_history')
        ->select('purchase_order_id', DB::raw('MAX(timestamp) as last_ts'))
        ->groupBy('purchase_order_id');

    $latestWithStatus = DB::table('order_status_history as osh')
        ->joinSub($latest, 'latest', function ($join) {
            $join->on('osh.purchase_order_id', '=', 'latest.purchase_order_id')
                ->on('osh.timestamp', '=', 'latest.last_ts');
        })
        ->leftJoin('statuses', 'osh.status_id', '=', 'statuses.status_id')
        ->select('osh.purchase_order_id', 'osh.status_id', 'statuses.status_name', 'osh.timestamp as last_status_time');

    $candidatesQuery = DB::table('customer_orders')
        ->leftJoinSub($latestWithStatus, 'last_status', function ($join) {
            $join->on('customer_orders.purchase_order_id', '=', 'last_status.purchase_order_id');
        })
        ->where('customer_orders.downpayment_required_percent', '>', 0)
        ->whereNotNull('customer_orders.downpayment_due_at')
        ->where('customer_orders.downpayment_due_at', '<=', $now)
        ->where(function ($q) {
            // Default: cancel only orders that are still early in the flow
            $q->whereIn('last_status.status_name', ['Pending', 'Confirmed'])
              ->orWhereNull('last_status.status_name');
        })
        ->select(
            'customer_orders.purchase_order_id',
            'customer_orders.customer_id',
            'customer_orders.order_no',
            'customer_orders.downpayment_required_amount',
            'customer_orders.downpayment_due_at',
            'last_status.status_name'
        );

    if (Schema::hasTable('payments') && Schema::hasColumn('payments', 'purchase_order_id')) {
        $candidatesQuery->leftJoin('payments', 'customer_orders.purchase_order_id', '=', 'payments.purchase_order_id');
        $candidatesQuery->where(function ($q) {
            $q->whereNull('payments.payment_id')
              ->orWhere('payments.is_verified', false)
              ->orWhereRaw('COALESCE(payments.amount_paid, 0) + 0.00001 < COALESCE(customer_orders.downpayment_required_amount, 0)');
        });
    }

    $candidates = $candidatesQuery->get();
    $count = 0;

    foreach ($candidates as $row) {
        DB::transaction(function () use ($row, $cancelledStatusId, &$count) {
            DB::table('order_status_history')->insert([
                'approval_id' => (string) Str::uuid(),
                'purchase_order_id' => $row->purchase_order_id,
                'user_id' => null,
                'status_id' => $cancelledStatusId,
                'remarks' => 'Order auto-cancelled due to overdue downpayment',
                'timestamp' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $updates = [
                'status_id' => $cancelledStatusId,
                'updated_at' => now(),
            ];

            if (Schema::hasColumn('customer_orders', 'payment_status')) {
                $updates['payment_status'] = 'failed';
            }

            DB::table('customer_orders')
                ->where('purchase_order_id', $row->purchase_order_id)
                ->update($updates);

            $dueAt = $row->downpayment_due_at ? Carbon::parse($row->downpayment_due_at)->format('M d, Y g:i A') : null;
            $message = $dueAt
                ? "Order #{$row->order_no} was cancelled because the downpayment was not received by {$dueAt}."
                : "Order #{$row->order_no} was cancelled because the downpayment was not received on time.";

            DB::table('order_notifications')->insert([
                'notification_id' => (string) Str::uuid(),
                'purchase_order_id' => $row->purchase_order_id,
                'recipient_id' => $row->customer_id,
                'notification_type' => 'status_change',
                'title' => 'Order Cancelled',
                'message' => $message,
                'is_read' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $count++;
        });
    }

    $this->info("Auto-cancelled {$count} overdue downpayment order(s). ");
    return 0;
})->purpose('Auto-cancel overdue downpayment-required orders');

Artisan::command('turso:migrate', function () {
    $this->info('Creating tables in Turso database...');
    
    $baseUrl = str_replace('libsql://', 'https://', env('TURSO_URL', 'libsql://uniprint-bragas002.aws-us-east-1.turso.io'));
    $authToken = env('TURSO_AUTH_TOKEN', '');
    
    $tables = [
        'users' => "CREATE TABLE IF NOT EXISTS users (user_id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT NOT NULL, email TEXT UNIQUE NOT NULL, phone TEXT, avatar TEXT, is_active INTEGER DEFAULT 1, created_at DATETIME DEFAULT CURRENT_TIMESTAMP, updated_at DATETIME DEFAULT CURRENT_TIMESTAMP)",
        'enterprises' => "CREATE TABLE IF NOT EXISTS enterprises (enterprise_id INTEGER PRIMARY KEY AUTOINCREMENT, owner_user_id INTEGER, name TEXT NOT NULL, category TEXT, address TEXT, is_active INTEGER DEFAULT 1, is_verified INTEGER DEFAULT 0, created_at DATETIME DEFAULT CURRENT_TIMESTAMP, updated_at DATETIME DEFAULT CURRENT_TIMESTAMP)",
        'services' => "CREATE TABLE IF NOT EXISTS services (service_id INTEGER PRIMARY KEY AUTOINCREMENT, enterprise_id INTEGER NOT NULL, service_name TEXT NOT NULL, description TEXT, base_price REAL DEFAULT 0, is_active INTEGER DEFAULT 1, created_at DATETIME DEFAULT CURRENT_TIMESTAMP, updated_at DATETIME DEFAULT CURRENT_TIMESTAMP)",
        'role_types' => "CREATE TABLE IF NOT EXISTS role_types (role_type_id INTEGER PRIMARY KEY AUTOINCREMENT, user_role_type TEXT UNIQUE NOT NULL, description TEXT, created_at DATETIME DEFAULT CURRENT_TIMESTAMP)",
        'roles' => "CREATE TABLE IF NOT EXISTS roles (role_id INTEGER PRIMARY KEY AUTOINCREMENT, user_id INTEGER NOT NULL, role_type_id INTEGER NOT NULL, created_at DATETIME DEFAULT CURRENT_TIMESTAMP)",
        'staff' => "CREATE TABLE IF NOT EXISTS staff (staff_id INTEGER PRIMARY KEY AUTOINCREMENT, enterprise_id INTEGER NOT NULL, user_id INTEGER NOT NULL, position TEXT, is_active INTEGER DEFAULT 1, created_at DATETIME DEFAULT CURRENT_TIMESTAMP)",
        'customer_orders' => "CREATE TABLE IF NOT EXISTS customer_orders (purchase_order_id INTEGER PRIMARY KEY AUTOINCREMENT, customer_id INTEGER NOT NULL, enterprise_id INTEGER NOT NULL, order_no TEXT UNIQUE NOT NULL, total REAL DEFAULT 0, payment_status TEXT, created_at DATETIME DEFAULT CURRENT_TIMESTAMP)",
        'order_items' => "CREATE TABLE IF NOT EXISTS order_items (item_id INTEGER PRIMARY KEY AUTOINCREMENT, purchase_order_id INTEGER NOT NULL, service_id INTEGER NOT NULL, quantity INTEGER DEFAULT 1, item_subtotal REAL DEFAULT 0)",
        'statuses' => "CREATE TABLE IF NOT EXISTS statuses (status_id INTEGER PRIMARY KEY AUTOINCREMENT, status_name TEXT UNIQUE NOT NULL, description TEXT)",
        'notifications' => "CREATE TABLE IF NOT EXISTS notifications (id INTEGER PRIMARY KEY AUTOINCREMENT, type TEXT, notifiable_type TEXT, notifiable_id INTEGER, data TEXT, read_at DATETIME, created_at DATETIME DEFAULT CURRENT_TIMESTAMP)",
        'order_notifications' => "CREATE TABLE IF NOT EXISTS order_notifications (notification_id INTEGER PRIMARY KEY AUTOINCREMENT, purchase_order_id INTEGER NOT NULL, recipient_id INTEGER NOT NULL, title TEXT, message TEXT, is_read INTEGER DEFAULT 0, created_at DATETIME DEFAULT CURRENT_TIMESTAMP)",
        'reviews' => "CREATE TABLE IF NOT EXISTS reviews (review_id INTEGER PRIMARY KEY AUTOINCREMENT, user_id INTEGER NOT NULL, enterprise_id INTEGER, service_id INTEGER, rating INTEGER, comment TEXT, created_at DATETIME DEFAULT CURRENT_TIMESTAMP)",
        'conversations' => "CREATE TABLE IF NOT EXISTS conversations (conversation_id INTEGER PRIMARY KEY AUTOINCREMENT, customer_id INTEGER NOT NULL, business_id INTEGER NOT NULL, is_read INTEGER DEFAULT 0, created_at DATETIME DEFAULT CURRENT_TIMESTAMP)",
        'chat_messages' => "CREATE TABLE IF NOT EXISTS chat_messages (message_id INTEGER PRIMARY KEY AUTOINCREMENT, conversation_id INTEGER NOT NULL, sender_id INTEGER NOT NULL, content TEXT, is_read INTEGER DEFAULT 0, created_at DATETIME DEFAULT CURRENT_TIMESTAMP)",
        'saved_services' => "CREATE TABLE IF NOT EXISTS saved_services (saved_service_id INTEGER PRIMARY KEY AUTOINCREMENT, user_id INTEGER NOT NULL, service_id INTEGER NOT NULL, quantity INTEGER DEFAULT 1)",
        'system_settings' => "CREATE TABLE IF NOT EXISTS system_settings (setting_id INTEGER PRIMARY KEY AUTOINCREMENT, key TEXT UNIQUE NOT NULL, value TEXT, created_at DATETIME DEFAULT CURRENT_TIMESTAMP)",
        'migrations' => "CREATE TABLE IF NOT EXISTS migrations (id INTEGER PRIMARY KEY AUTOINCREMENT, migration TEXT NOT NULL, batch INTEGER NOT NULL)",
    ];
    
    $http = new \Illuminate\Support\Facades\Http();
    
    foreach ($tables as $name => $sql) {
        try {
            $response = \Illuminate\Support\Facades\Http::withHeaders([
                'Authorization' => 'Bearer ' . $authToken,
                'Content-Type' => 'application/json',
            ])->post($baseUrl . '/v2/pipeline', [
                'requests' => [
                    [
                        'type' => 'execute',
                        'stmt' => [
                            'sql' => $sql,
                            'args' => []
                        ]
                    ]
                ]
            ]);
            
            if ($response->successful()) {
                $this->info("Created table: {$name}");
            } else {
                $this->error("Failed to create table {$name}: " . $response->body());
            }
        } catch (\Exception $e) {
            $this->error("Error creating table {$name}: " . $e->getMessage());
        }
    }
    
    // Insert default role types
    $this->info('Inserting default role types...');
    foreach (['admin', 'business_user', 'customer'] as $roleType) {
        try {
            \Illuminate\Support\Facades\Http::withHeaders([
                'Authorization' => 'Bearer ' . $authToken,
                'Content-Type' => 'application/json',
            ])->post($baseUrl . '/v2/pipeline', [
                'requests' => [
                    [
                        'type' => 'execute',
                        'stmt' => [
                            'sql' => 'INSERT OR IGNORE INTO role_types (user_role_type, description) VALUES (?, ?)',
                            'args' => [$roleType, ucfirst(str_replace('_', ' ', $roleType))]
                        ]
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            $this->warn("Could not insert role type {$roleType}");
        }
    }
    
    // Insert default statuses
    $this->info('Inserting default statuses...');
    foreach (['Pending', 'Confirmed', 'In Production', 'Ready for Pickup', 'Completed', 'Cancelled'] as $status) {
        try {
            \Illuminate\Support\Facades\Http::withHeaders([
                'Authorization' => 'Bearer ' . $authToken,
                'Content-Type' => 'application/json',
            ])->post($baseUrl . '/v2/pipeline', [
                'requests' => [
                    [
                        'type' => 'execute',
                        'stmt' => [
                            'sql' => 'INSERT OR IGNORE INTO statuses (status_name, description) VALUES (?, ?)',
                            'args' => [$status, $status]
                        ]
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            $this->warn("Could not insert status {$status}");
        }
    }
    
    $this->info('Migration completed!');
    return 0;
})->purpose('Create tables in Turso database');
