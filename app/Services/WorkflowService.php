<?php

namespace App\Services;

use App\Models\OrderWorkflow;
use App\Models\CustomerOrder;
use App\Models\OrderStatusHistory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Exception;

/**
 * Workflow Service
 * 
 * Manages order workflows and stage progression
 * 
 * @package App\Services
 */
class WorkflowService
{
    /**
     * Assign workflow to order based on context
     *
     * @param CustomerOrder $order
     * @param array $context
     * @return OrderWorkflow|null
     */
    public function assignWorkflow(CustomerOrder $order, array $context = []): ?OrderWorkflow
    {
        $workflows = OrderWorkflow::where('enterprise_id', $order->enterprise_id)
            ->active()
            ->orderBy('is_default', 'desc')
            ->orderBy('workflow_id', 'asc')
            ->get();

        foreach ($workflows as $workflow) {
            if ($workflow->appliesTo($context)) {
                $order->workflow_id = $workflow->workflow_id;
                $order->estimated_completion = $this->calculateEstimatedCompletion($workflow);
                $order->save();
                
                Log::info('Workflow assigned to order', [
                    'order_id' => $order->order_id,
                    'workflow_id' => $workflow->workflow_id,
                    'workflow_name' => $workflow->workflow_name,
                ]);
                
                return $workflow;
            }
        }

        // Fallback to default workflow
        $defaultWorkflow = OrderWorkflow::where('enterprise_id', $order->enterprise_id)
            ->default()
            ->first();

        if ($defaultWorkflow) {
            $order->workflow_id = $defaultWorkflow->workflow_id;
            $order->estimated_completion = $this->calculateEstimatedCompletion($defaultWorkflow);
            $order->save();
        }

        return $defaultWorkflow;
    }

    /**
     * Calculate estimated completion date
     *
     * @param OrderWorkflow $workflow
     * @return Carbon
     */
    private function calculateEstimatedCompletion(OrderWorkflow $workflow): Carbon
    {
        $totalHours = $workflow->calculateTotalDuration();
        $businessDays = ceil($totalHours / 8); // Assuming 8-hour workday
        
        return now()->addBusinessDays($businessDays);
    }

    /**
     * Progress order to next stage
     *
     * @param int $orderId
     * @param int|null $staffId
     * @return array
     * @throws Exception
     */
    public function progressToNextStage(int $orderId, ?int $staffId = null): array
    {
        DB::beginTransaction();
        
        try {
            $order = CustomerOrder::with('workflow')->findOrFail($orderId);
            
            if (!$order->workflow) {
                throw new Exception('Order has no assigned workflow');
            }

            $currentStatus = $order->current_status;
            $nextStage = $order->workflow->getNextStage($currentStatus);

            if (!$nextStage) {
                return [
                    'success' => false,
                    'message' => 'Order is at final stage',
                    'current_stage' => $currentStatus,
                ];
            }

            // Update order status
            $order->current_status = $nextStage['name'];
            $order->save();

            // Create status history
            OrderStatusHistory::create([
                'order_id' => $orderId,
                'status_name' => $nextStage['name'],
                'staff_id' => $staffId,
            ]);

            DB::commit();

            Log::info('Order progressed to next stage', [
                'order_id' => $orderId,
                'from_stage' => $currentStatus,
                'to_stage' => $nextStage['name'],
                'staff_id' => $staffId,
            ]);

            return [
                'success' => true,
                'message' => 'Order progressed successfully',
                'previous_stage' => $currentStatus,
                'current_stage' => $nextStage['name'],
                'next_stage' => $order->workflow->getNextStage($nextStage['name']),
            ];
            
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to progress order', [
                'order_id' => $orderId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Get order progress percentage
     *
     * @param CustomerOrder $order
     * @return int
     */
    public function getProgressPercentage(CustomerOrder $order): int
    {
        if (!$order->workflow) {
            return 0;
        }

        $stages = $order->workflow->workflow_stages;
        $totalStages = count($stages);
        
        if ($totalStages === 0) {
            return 0;
        }

        $currentStageIndex = 0;
        foreach ($stages as $index => $stage) {
            if ($stage['name'] === $order->current_status) {
                $currentStageIndex = $index;
                break;
            }
        }

        return intval((($currentStageIndex + 1) / $totalStages) * 100);
    }

    /**
     * Get workflow timeline for order
     *
     * @param CustomerOrder $order
     * @return array
     */
    public function getWorkflowTimeline(CustomerOrder $order): array
    {
        if (!$order->workflow) {
            return [];
        }

        $stages = $order->workflow->workflow_stages;
        $statusHistory = $order->statusHistory()->orderBy('status_timestamp')->get();
        
        $timeline = [];
        $currentDate = $order->order_creation_date;

        foreach ($stages as $stage) {
            $stageName = $stage['name'];
            $duration = $stage['duration_hours'] ?? 0;
            
            // Check if this stage has been completed
            $historyEntry = $statusHistory->firstWhere('status_name', $stageName);
            
            $timeline[] = [
                'stage_name' => $stageName,
                'estimated_duration' => $duration,
                'estimated_start' => $currentDate,
                'estimated_end' => Carbon::parse($currentDate)->addHours($duration),
                'actual_start' => $historyEntry ? $historyEntry->status_timestamp : null,
                'completed' => $historyEntry !== null,
                'is_current' => $order->current_status === $stageName,
                'staff' => $historyEntry ? $historyEntry->staff : null,
            ];
            
            $currentDate = Carbon::parse($currentDate)->addHours($duration);
        }

        return $timeline;
    }

    /**
     * Create custom workflow
     *
     * @param array $data
     * @return OrderWorkflow
     * @throws Exception
     */
    public function createWorkflow(array $data): OrderWorkflow
    {
        DB::beginTransaction();
        
        try {
            // If setting as default, unset other defaults
            if ($data['is_default'] ?? false) {
                OrderWorkflow::where('enterprise_id', $data['enterprise_id'])
                    ->update(['is_default' => false]);
            }

            $workflow = OrderWorkflow::create($data);
            
            DB::commit();
            
            Log::info('Workflow created', [
                'workflow_id' => $workflow->workflow_id,
                'workflow_name' => $workflow->workflow_name,
                'enterprise_id' => $workflow->enterprise_id,
            ]);
            
            return $workflow;
            
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to create workflow', [
                'error' => $e->getMessage(),
                'data' => $data,
            ]);
            throw $e;
        }
    }

    /**
     * Update workflow
     *
     * @param int $workflowId
     * @param array $data
     * @return OrderWorkflow
     * @throws Exception
     */
    public function updateWorkflow(int $workflowId, array $data): OrderWorkflow
    {
        DB::beginTransaction();
        
        try {
            $workflow = OrderWorkflow::findOrFail($workflowId);
            
            // If setting as default, unset other defaults
            if (($data['is_default'] ?? false) && !$workflow->is_default) {
                OrderWorkflow::where('enterprise_id', $workflow->enterprise_id)
                    ->where('workflow_id', '!=', $workflowId)
                    ->update(['is_default' => false]);
            }

            $workflow->update($data);
            
            DB::commit();
            
            Log::info('Workflow updated', ['workflow_id' => $workflowId]);
            
            return $workflow->fresh();
            
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to update workflow', [
                'workflow_id' => $workflowId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Get workflow statistics
     *
     * @param int $enterpriseId
     * @return array
     */
    public function getWorkflowStatistics(int $enterpriseId): array
    {
        $workflows = OrderWorkflow::where('enterprise_id', $enterpriseId)->get();
        
        $stats = [];
        
        foreach ($workflows as $workflow) {
            $orders = CustomerOrder::where('workflow_id', $workflow->workflow_id)->get();
            
            $completedOrders = $orders->where('current_status', 'Complete');
            $avgCompletionTime = $this->calculateAverageCompletionTime($completedOrders);
            
            $stats[] = [
                'workflow_id' => $workflow->workflow_id,
                'workflow_name' => $workflow->workflow_name,
                'workflow_type' => $workflow->workflow_type,
                'total_orders' => $orders->count(),
                'completed_orders' => $completedOrders->count(),
                'in_progress_orders' => $orders->whereNotIn('current_status', ['Complete', 'Cancelled'])->count(),
                'average_completion_hours' => $avgCompletionTime,
                'estimated_duration' => $workflow->calculateTotalDuration(),
            ];
        }

        return $stats;
    }

    /**
     * Calculate average completion time for orders
     *
     * @param $orders
     * @return float
     */
    private function calculateAverageCompletionTime($orders): float
    {
        if ($orders->isEmpty()) {
            return 0;
        }

        $totalHours = 0;
        $count = 0;

        foreach ($orders as $order) {
            $completionHistory = $order->statusHistory()
                ->where('status_name', 'Complete')
                ->first();
                
            if ($completionHistory) {
                $hours = Carbon::parse($order->order_creation_date)
                    ->diffInHours($completionHistory->status_timestamp);
                $totalHours += $hours;
                $count++;
            }
        }

        return $count > 0 ? $totalHours / $count : 0;
    }
}
