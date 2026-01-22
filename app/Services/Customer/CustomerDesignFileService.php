<?php

namespace App\Services\Customer;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

/**
 * Customer Design File Service
 * Handles design file upload, management, and versioning for orders
 */
class CustomerDesignFileService
{
    private const ALLOWED_TYPES = ['jpg', 'jpeg', 'png', 'pdf', 'ai', 'psd', 'eps', 'svg'];
    private const MAX_FILE_SIZE = 52428800; // 50MB in bytes

    /**
     * Upload design file for an order
     *
     * @param string $userId
     * @param string $orderId
     * @param array $fileData
     * @return string File ID
     * @throws ValidationException
     */
    public function uploadFile(string $userId, string $orderId, array $fileData): string
    {
        // Validate
        $validator = Validator::make($fileData, [
            'file_name' => 'required|string|max:255',
            'file_path' => 'required|string',
            'file_type' => 'required|string|in:' . implode(',', self::ALLOWED_TYPES),
            'file_size' => 'required|integer|max:' . self::MAX_FILE_SIZE,
            'design_notes' => 'nullable|string|max:2000',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        DB::beginTransaction();
        try {
            // Verify order ownership
            $order = DB::table('customer_orders')
                ->where('purchase_order_id', $orderId)
                ->where('customer_id', $userId)
                ->first();

            if (!$order) {
                throw new \Exception('Order not found or access denied');
            }

            // Check current status - only allow uploads for certain statuses
            $currentStatus = $this->getCurrentOrderStatus($orderId);
            if (in_array($currentStatus->status_name, ['Delivered', 'Cancelled'])) {
                throw new \Exception('Cannot upload files for orders in current status');
            }

            // Get version number
            $version = DB::table('order_design_files')
                ->where('purchase_order_id', $orderId)
                ->count() + 1;

            // Create file record
            $fileId = \Illuminate\Support\Str::uuid()->toString();
            DB::table('order_design_files')->insert([
                'file_id' => $fileId,
                'purchase_order_id' => $orderId,
                'uploaded_by' => $userId,
                'file_name' => $fileData['file_name'],
                'file_path' => $fileData['file_path'],
                'file_type' => $fileData['file_type'],
                'file_size' => $fileData['file_size'],
                'design_notes' => $fileData['design_notes'] ?? null,
                'version' => $version,
                'is_approved' => false,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);

            // Notify business
            $this->notifyBusiness($order->enterprise_id, $orderId, $order->order_no, $fileData['file_name']);

            // Log audit
            $this->logAudit($userId, 'design_file_uploaded', 'order_design_files', $fileId, [
                'order_id' => $orderId,
                'version' => $version,
                'file_name' => $fileData['file_name'],
            ]);

            DB::commit();

            Log::info('Design file uploaded', [
                'user_id' => $userId,
                'order_id' => $orderId,
                'file_id' => $fileId,
                'version' => $version,
            ]);

            return $fileId;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error uploading design file', [
                'user_id' => $userId,
                'order_id' => $orderId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Delete design file
     *
     * @param string $userId
     * @param string $orderId
     * @param string $fileId
     * @return bool
     */
    public function deleteFile(string $userId, string $orderId, string $fileId): bool
    {
        DB::beginTransaction();
        try {
            // Verify ownership and get file
            $file = DB::table('order_design_files')
                ->join('customer_orders', 'order_design_files.purchase_order_id', '=', 'customer_orders.purchase_order_id')
                ->where('order_design_files.file_id', $fileId)
                ->where('order_design_files.purchase_order_id', $orderId)
                ->where('customer_orders.customer_id', $userId)
                ->where('order_design_files.uploaded_by', $userId)
                ->select('order_design_files.*')
                ->first();

            if (!$file) {
                throw new \Exception('File not found or access denied');
            }

            // Check if approved
            if ($file->is_approved) {
                throw new \Exception('Cannot delete approved files');
            }

            // Delete file from storage
            if (Storage::disk('public')->exists($file->file_path)) {
                Storage::disk('public')->delete($file->file_path);
            }

            // Delete from database
            DB::table('order_design_files')->where('file_id', $fileId)->delete();

            // Log audit
            $this->logAudit($userId, 'design_file_deleted', 'order_design_files', $fileId, [
                'order_id' => $orderId,
                'file_name' => $file->file_name,
            ]);

            DB::commit();

            Log::info('Design file deleted', [
                'user_id' => $userId,
                'file_id' => $fileId,
            ]);

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting design file', [
                'user_id' => $userId,
                'file_id' => $fileId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Get all design files for an order
     *
     * @param string $userId
     * @param string $orderId
     * @return \Illuminate\Support\Collection
     */
    public function getOrderFiles(string $userId, string $orderId)
    {
        try {
            // Verify order ownership
            $order = DB::table('customer_orders')
                ->where('purchase_order_id', $orderId)
                ->where('customer_id', $userId)
                ->first();

            if (!$order) {
                throw new \Exception('Order not found or access denied');
            }

            return DB::table('order_design_files')
                ->leftJoin('users', 'order_design_files.uploaded_by', '=', 'users.user_id')
                ->where('order_design_files.purchase_order_id', $orderId)
                ->select(
                    'order_design_files.*',
                    'users.name as uploaded_by_name'
                )
                ->orderBy('order_design_files.version', 'desc')
                ->get();
        } catch (\Exception $e) {
            Log::error('Error getting order files', [
                'user_id' => $userId,
                'order_id' => $orderId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Get file details
     *
     * @param string $userId
     * @param string $fileId
     * @return object|null
     */
    public function getFileDetails(string $userId, string $fileId)
    {
        try {
            return DB::table('order_design_files')
                ->join('customer_orders', 'order_design_files.purchase_order_id', '=', 'customer_orders.purchase_order_id')
                ->leftJoin('users', 'order_design_files.uploaded_by', '=', 'users.user_id')
                ->where('order_design_files.file_id', $fileId)
                ->where('customer_orders.customer_id', $userId)
                ->select(
                    'order_design_files.*',
                    'users.name as uploaded_by_name',
                    'customer_orders.order_no'
                )
                ->first();
        } catch (\Exception $e) {
            Log::error('Error getting file details', [
                'user_id' => $userId,
                'file_id' => $fileId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Get file download URL
     *
     * @param string $userId
     * @param string $fileId
     * @return string|null
     */
    public function getFileDownloadUrl(string $userId, string $fileId): ?string
    {
        try {
            $file = $this->getFileDetails($userId, $fileId);
            
            if (!$file) {
                return null;
            }

            if (!Storage::disk('public')->exists($file->file_path)) {
                Log::warning('File path does not exist', [
                    'file_id' => $fileId,
                    'file_path' => $file->file_path
                ]);
                return null;
            }

            return Storage::disk('public')->url($file->file_path);
        } catch (\Exception $e) {
            Log::error('Error getting file download URL', [
                'user_id' => $userId,
                'file_id' => $fileId,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Get current order status
     *
     * @param string $orderId
     * @return object|null
     */
    private function getCurrentOrderStatus(string $orderId)
    {
        return DB::table('order_status_history')
            ->join('statuses', 'order_status_history.status_id', '=', 'statuses.status_id')
            ->where('order_status_history.purchase_order_id', $orderId)
            ->orderBy('order_status_history.timestamp', 'desc')
            ->select('statuses.status_name', 'order_status_history.timestamp')
            ->first();
    }

    /**
     * Notify business about file upload
     *
     * @param string $enterpriseId
     * @param string $orderId
     * @param string $orderNo
     * @param string $fileName
     * @return void
     */
    private function notifyBusiness(string $enterpriseId, string $orderId, string $orderNo, string $fileName): void
    {
        $businessUserId = null;

        if (\Illuminate\Support\Facades\Schema::hasColumn('enterprises', 'owner_user_id')) {
            $businessUserId = DB::table('enterprises')
                ->where('enterprise_id', $enterpriseId)
                ->value('owner_user_id');
        }

        if (! $businessUserId && \Illuminate\Support\Facades\Schema::hasTable('staff')) {
            $businessUserId = DB::table('staff')
                ->where('enterprise_id', $enterpriseId)
                ->whereNotNull('user_id')
                ->value('user_id');
        }

        if ($businessUserId) {
            DB::table('order_notifications')->insert([
                'notification_id' => \Illuminate\Support\Str::uuid()->toString(),
                'purchase_order_id' => $orderId,
                'recipient_id' => $businessUserId,
                'notification_type' => 'file_upload',
                'title' => 'Design File Uploaded',
                'message' => "Customer uploaded a design file ({$fileName}) for order #{$orderNo}",
                'is_read' => false,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }
    }

    /**
     * Log audit trail
     *
     * @param string $userId
     * @param string $action
     * @param string $tableName
     * @param string $recordId
     * @param array $changes
     * @return void
     */
    private function logAudit(string $userId, string $action, string $tableName, string $recordId, array $changes = []): void
    {
        try {
            DB::table('audit_logs')->insert([
                'log_id' => \Illuminate\Support\Str::uuid()->toString(),
                'user_id' => $userId,
                'action' => $action,
                'entity_type' => $tableName,
                'entity_id' => $recordId,
                'description' => $action,
                'old_values' => null,
                'new_values' => empty($changes) ? null : json_encode($changes),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        } catch (\Exception $e) {
            Log::error('Error logging audit', ['error' => $e->getMessage()]);
        }
    }
}
