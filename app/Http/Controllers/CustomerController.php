<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

use App\Models\DesignAsset;
use App\Models\Enterprise;
use App\Models\Service;
use App\Models\CustomerOrder;
use App\Models\OrderItem;
use App\Models\OrderItemCustomization;
use App\Models\OrderStatusHistory;

class CustomerController extends Controller
{
    public function dashboard()
    {
        $userId = session('user_id');
        $userName = session('user_name');

        // Calculate total design assets for this user (saved AI designs + uploaded order design files)
        $userDesignsCount = DB::table('user_designs')->where('user_id', $userId)->count();
        $uploadedDesignFilesCount = DB::table('order_design_files')->where('uploaded_by', $userId)->count();
        $totalAssets = $userDesignsCount + $uploadedDesignFilesCount;

        $stats = [
            'total_orders' => DB::table('customer_orders')->where('customer_id', $userId)->count(),
            'pending_orders' => DB::table('customer_orders')
                ->join('order_status_history', 'customer_orders.purchase_order_id', '=', 'order_status_history.purchase_order_id')
                ->join('statuses', 'order_status_history.status_id', '=', 'statuses.status_id')
                ->where('customer_orders.customer_id', $userId)
                ->where('statuses.status_name', 'Pending')
                ->distinct()
                ->count('customer_orders.purchase_order_id'),
            'completed_orders' => DB::table('customer_orders')
                ->join('order_status_history', 'customer_orders.purchase_order_id', '=', 'order_status_history.purchase_order_id')
                ->join('statuses', 'order_status_history.status_id', '=', 'statuses.status_id')
                ->where('customer_orders.customer_id', $userId)
                ->where('statuses.status_name', 'Delivered')
                ->distinct()
                ->count('customer_orders.purchase_order_id'),
            'total_assets' => $totalAssets,
        ];

        $recent_orders = DB::table('customer_orders')
            ->join('enterprises', 'customer_orders.enterprise_id', '=', 'enterprises.enterprise_id')
            ->where('customer_orders.customer_id', $userId)
            ->select('customer_orders.*', 'enterprises.name as enterprise_name')
            ->orderBy('customer_orders.created_at', 'desc')
            ->limit(5)
            ->get();

        return view('customer.dashboard', compact('stats', 'recent_orders', 'userName'));
    }

    public function orders()
    {
        $userId = session('user_id');
        $userName = session('user_name');

        $orders = DB::table('customer_orders')
            ->join('enterprises', 'customer_orders.enterprise_id', '=', 'enterprises.enterprise_id')
            ->leftJoin(DB::raw('(SELECT purchase_order_id, status_id, MAX(timestamp) as latest_time FROM order_status_history GROUP BY purchase_order_id, status_id ORDER BY latest_time DESC LIMIT 1) as latest_status'), 'customer_orders.purchase_order_id', '=', 'latest_status.purchase_order_id')
            ->leftJoin('statuses', 'latest_status.status_id', '=', 'statuses.status_id')
            ->where('customer_orders.customer_id', $userId)
            ->select('customer_orders.*', 'enterprises.name as enterprise_name', 'statuses.status_name')
            ->orderBy('customer_orders.created_at', 'desc')
            ->paginate(10);

        return view('customer.orders', compact('orders', 'userName'));
    }

    public function orderDetails($id)
    {
        $userId = session('user_id');
        $userName = session('user_name');

        $order = DB::table('customer_orders')
            ->join('enterprises', 'customer_orders.enterprise_id', '=', 'enterprises.enterprise_id')
            ->where('customer_orders.purchase_order_id', $id)
            ->where('customer_orders.customer_id', $userId)
            ->select('customer_orders.*', 'enterprises.name as enterprise_name', 'enterprises.contact_number', 'enterprises.address')
            ->first();

        if (!$order) {
            abort(404);
        }

        // Get order items with service info
        $orderItems = DB::table('order_items')
            ->join('services', 'order_items.service_id', '=', 'services.service_id')
            ->where('order_items.purchase_order_id', $id)
            ->select('order_items.*', 'services.service_name')
            ->get();

        // Get customizations for each item
        foreach ($orderItems as $item) {
            $item->customizations = DB::table('order_item_customizations')
                ->join('customization_options', 'order_item_customizations.option_id', '=', 'customization_options.option_id')
                ->where('order_item_customizations.order_item_id', $item->item_id)
                ->select('customization_options.*', 'order_item_customizations.price_snapshot')
                ->get();
        }

        // Get status history
        $statusHistory = DB::table('order_status_history')
            ->join('statuses', 'order_status_history.status_id', '=', 'statuses.status_id')
            ->join('users', 'order_status_history.user_id', '=', 'users.user_id')
            ->where('order_status_history.purchase_order_id', $id)
            ->select('order_status_history.*', 'statuses.status_name', 'statuses.description', 'users.name as user_name')
            ->orderBy('order_status_history.timestamp', 'desc')
            ->get();

        // Get transaction if exists
        $transaction = DB::table('transactions')
            ->where('purchase_order_id', $id)
            ->first();

        // Get design files
        $designFiles = DB::table('order_design_files')
            ->where('purchase_order_id', $id)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('customer.order-details', compact('order', 'orderItems', 'statusHistory', 'transaction', 'designFiles', 'userName'));
    }

    public function uploadDesignFile(Request $request, $orderId)
    {
        $request->validate([
            'design_file' => 'required|file|mimes:jpg,jpeg,png,pdf,ai,psd,eps|max:51200', // 50MB max
            'design_notes' => 'nullable|string|max:2000',
        ]);

        $userId = session('user_id');

        // Verify order belongs to customer
        $order = DB::table('customer_orders')
            ->where('purchase_order_id', $orderId)
            ->where('customer_id', $userId)
            ->first();

        if (!$order) {
            abort(404);
        }

        // Handle file upload
        $file = $request->file('design_file');
        $fileName = time() . '_' . $file->getClientOriginalName();
        $filePath = $file->storeAs('design_files/' . $orderId, $fileName, 'public');

        // Get file version (count existing files + 1)
        $version = DB::table('order_design_files')
            ->where('purchase_order_id', $orderId)
            ->count() + 1;

        // Store file info in database
        $fileId = \Illuminate\Support\Str::uuid();
        DB::table('order_design_files')->insert([
            'file_id' => $fileId,
            'purchase_order_id' => $orderId,
            'uploaded_by' => $userId,
            'file_name' => $fileName,
            'file_path' => $filePath,
            'file_type' => $file->getClientOriginalExtension(),
            'file_size' => $file->getSize(),
            'design_notes' => $request->design_notes,
            'version' => $version,
            'is_approved' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Notify business
        DB::table('order_notifications')->insert([
            'notification_id' => \Illuminate\Support\Str::uuid(),
            'purchase_order_id' => $orderId,
            'recipient_id' => $order->enterprise_id, // Note: This should be business user ID
            'notification_type' => 'file_upload',
            'title' => 'New Design File Uploaded',
            'message' => "Customer uploaded a new design file for order #{$order->order_no}",
            'is_read' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Design file uploaded successfully! Version ' . $version);
    }

    public function deleteDesignFile($orderId, $fileId)
    {
        $userId = session('user_id');

        // Verify order belongs to customer
        $order = DB::table('customer_orders')
            ->where('purchase_order_id', $orderId)
            ->where('customer_id', $userId)
            ->first();

        if (!$order) {
            abort(404);
        }

        // Get file info
        $file = DB::table('order_design_files')
            ->where('file_id', $fileId)
            ->where('purchase_order_id', $orderId)
            ->where('uploaded_by', $userId)
            ->first();

        if (!$file) {
            abort(404);
        }

        // Don't allow deletion if already approved
        if ($file->is_approved) {
            return redirect()->back()->with('error', 'Cannot delete an approved file');
        }

        // Delete file from storage
        \Illuminate\Support\Facades\Storage::disk('public')->delete($file->file_path);

        // Delete from database
        DB::table('order_design_files')->where('file_id', $fileId)->delete();

        return redirect()->back()->with('success', 'Design file deleted successfully');
    }

    public function notifications()
    {
        $userId = session('user_id');
        $userName = session('user_name');

        $notifications = DB::table('order_notifications')
            ->where('recipient_id', $userId)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('customer.notifications', compact('notifications', 'userName'));
    }

    public function markNotificationRead($id)
    {
        $userId = session('user_id');

        DB::table('order_notifications')
            ->where('notification_id', $id)
            ->where('recipient_id', $userId)
            ->update([
                'is_read' => true,
                'read_at' => now(),
                'updated_at' => now(),
            ]);

        return redirect()->back()->with('success', 'Notification marked as read');
    }

    public function enterpriseServices($id)
    {
        $enterprise = Enterprise::where('enterprise_id', $id)
            ->where('is_active', true)
            ->firstOrFail();

        $services = Service::where('enterprise_id', $id)
            ->where('is_available', true)
            ->with('customizationGroups.customizationOptions')
            ->paginate(12);

        return view('customer.services', compact('enterprise', 'services'));
    }

    public function serviceDetails($id)
    {
        $service = Service::where('service_id', $id)
            ->where('is_available', true)
            ->with(['enterprise', 'customizationGroups.customizationOptions'])
            ->firstOrFail();

        return view('customer.service-details', compact('service'));
    }


    public function placeOrder(Request $request)
    {
        $request->validate([
            'service_id' => 'required|exists:services,service_id',
            'quantity' => 'required|integer|min:1',
            'customizations' => 'nullable|array',
            'notes' => 'nullable|string',
        ]);

        $user = Auth::user();
        $service = Service::findOrFail($request->service_id);

        // Calculate total
        $subtotal = $service->base_price * $request->quantity;

        if ($request->customizations) {
            foreach ($request->customizations as $optionId) {
                $option = \App\Models\CustomizationOption::find($optionId);
                if ($option) {
                    $subtotal += $option->price_modifier * $request->quantity;
                }
            }
        }

        // Create order
        $order = CustomerOrder::create([
            'customer_account_id' => $user->user_id,
            'enterprise_id' => $service->enterprise_id,
            'total_order_amount' => $subtotal,
            'current_status' => 'Pending',
        ]);

        // Create order item
        $orderItem = OrderItem::create([
            'order_id' => $order->order_id,
            'service_id' => $service->service_id,
            'quantity' => $request->quantity,
            'item_subtotal' => $subtotal,
            'notes_to_enterprise' => $request->notes,
        ]);

        // Add customizations
        if ($request->customizations) {
            foreach ($request->customizations as $optionId) {
                $option = \App\Models\CustomizationOption::find($optionId);
                if ($option) {
                    OrderItemCustomization::create([
                        'order_item_id' => $orderItem->item_id,
                        'option_id' => $option->option_id,
                        'option_price_snapshot' => $option->price_modifier,
                    ]);
                }
            }
        }

        // Create status history
        OrderStatusHistory::create([
            'order_id' => $order->order_id,
            'status_name' => 'Pending',
        ]);

        return redirect()->route('customer.orders')->with('success', 'Order placed successfully!');
    }


    public function designAssets()
    {
        $user = Auth::user();

        $assets = DesignAsset::where('user_id', $user->user_id)
            ->orderBy('created_at', 'desc')
            ->paginate(12);

        return view('customer.design-assets', compact('assets'));
    }
}
