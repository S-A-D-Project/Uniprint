<?php

namespace App\Utils;

/**
 * Cached schema information to avoid expensive information_schema queries
 * Generated from database migration - do not edit manually
 */
class SchemaCache
{
    private static array $tables = [
        'users' => true,
        'customer_orders' => true,
        'order_items' => true,
        'order_status_history' => true,
        'statuses' => true,
        'payments' => true,
        'services' => true,
        'enterprises' => true,
        'staff' => true,
        'roles' => true,
        'role_types' => true,
        'order_notifications' => true,
        'saved_services' => true,
        'saved_service_customizations' => true,
        'saved_service_design_files' => true,
        'customization_options' => true,
        'order_item_customizations' => true,
        'order_design_files' => true,
        'order_extension_requests' => true,
        'order_refunds' => true,
        'conversations' => true,
        'chat_messages' => true,
        'online_users' => true,
        'typing_indicators' => true,
        'reviews' => true,
        'rating_summaries' => true,
        'ratings' => true,
        'user_designs' => true,
        'discount_codes' => true,
        'pricing_rules' => true,
        'audit_logs' => true,
        'notifications' => true,
        'notification_preferences' => true,
        'social_logins' => true,
        'personal_access_tokens' => true,
        'sessions' => true,
        'transactions' => true,
        'user_payment_accounts' => true,
        'service_custom_fields' => true,
        'service_images' => true,
        'system_settings' => true,
        'ai_generation_daily_usages' => true,
        'user_reports' => true,
        'review_files' => true,
        'system_feedback' => true,
        'login' => true,
        'cache' => true,
        'cache_locks' => true,
        'jobs' => true,
        'job_batches' => true,
        'failed_jobs' => true,
        'migrations' => true,
    ];

    private static array $columns = [
        'users' => ['user_id', 'name', 'email', 'phone', 'avatar', 'is_active', 'email_verified_at', 'two_factor_secret', 'two_factor_enabled_at', 'remember_token', 'created_at', 'updated_at', 'two_factor_email_enabled', 'two_factor_sms_enabled', 'two_factor_totp_enabled', 'two_factor_code', 'two_factor_expires_at'],
        'customer_orders' => ['purchase_order_id', 'customer_id', 'enterprise_id', 'order_no', 'purpose', 'date_requested', 'delivery_date', 'pickup_date', 'requested_fulfillment_date', 'shipping_fee', 'discount', 'subtotal', 'total', 'payment_method', 'payment_status', 'fulfillment_method', 'rush_option', 'notes', 'created_at', 'updated_at'],
        'order_items' => ['item_id', 'purchase_order_id', 'service_id', 'quantity', 'price_snapshot', 'item_subtotal', 'notes_to_enterprise', 'created_at', 'updated_at'],
        'order_status_history' => ['approval_id', 'purchase_order_id', 'status_id', 'timestamp', 'user_id', 'remarks', 'created_at', 'updated_at'],
        'statuses' => ['status_id', 'status_name', 'description', 'created_at', 'updated_at'],
        'payments' => ['payment_id', 'purchase_order_id', 'amount_due', 'amount_paid', 'payment_method', 'is_verified', 'verified_at', 'verified_by', 'created_at', 'updated_at'],
        'services' => ['service_id', 'enterprise_id', 'service_name', 'description', 'image_path', 'fulfillment_type', 'allowed_payment_methods', 'file_upload_enabled', 'requires_file_upload', 'supports_rush', 'base_price', 'is_active', 'requires_downpayment', 'downpayment_percent', 'created_at', 'updated_at'],
        'enterprises' => ['enterprise_id', 'owner_user_id', 'name', 'category', 'description', 'address', 'contact_number', 'email', 'logo_path', 'is_active', 'is_verified', 'verification_status', 'verification_notes', 'checkout_rush_options', 'created_at', 'updated_at'],
        'staff' => ['staff_id', 'enterprise_id', 'user_id', 'position', 'is_active', 'created_at', 'updated_at'],
        'roles' => ['role_id', 'user_id', 'role_type_id', 'created_at', 'updated_at'],
        'role_types' => ['role_type_id', 'user_role_type', 'description', 'created_at', 'updated_at'],
        'order_notifications' => ['notification_id', 'purchase_order_id', 'recipient_id', 'notification_type', 'title', 'message', 'is_read', 'created_at', 'updated_at'],
        'saved_services' => ['saved_service_id', 'user_id', 'service_id', 'customizations', 'quantity', 'saved_at', 'created_at', 'updated_at'],
        'customization_options' => ['option_id', 'service_id', 'option_name', 'option_type', 'price_modifier', 'is_active', 'created_at', 'updated_at'],
        'order_item_customizations' => ['order_item_customization_id', 'order_item_id', 'option_id', 'price_snapshot', 'created_at', 'updated_at'],
        'order_design_files' => ['file_id', 'purchase_order_id', 'file_name', 'file_path', 'file_type', 'file_size', 'uploaded_by', 'approved_by', 'approved_at', 'created_at', 'updated_at'],
        'order_extension_requests' => ['request_id', 'purchase_order_id', 'requested_by', 'new_delivery_date', 'reason', 'status', 'reviewed_by', 'reviewed_at', 'created_at', 'updated_at'],
        'conversations' => ['conversation_id', 'customer_id', 'business_id', 'status', 'last_message_at', 'created_at', 'updated_at'],
        'chat_messages' => ['message_id', 'conversation_id', 'sender_id', 'content', 'is_read', 'created_at', 'updated_at'],
        'online_users' => ['user_id', 'status', 'last_seen_at', 'created_at', 'updated_at'],
        'typing_indicators' => ['conversation_id', 'user_id', 'is_typing', 'updated_at'],
        'reviews' => ['review_id', 'user_id', 'enterprise_id', 'service_id', 'rating', 'comment', 'is_visible', 'created_at', 'updated_at'],
        'user_designs' => ['design_id', 'user_id', 'file_name', 'file_path', 'file_type', 'file_size', 'is_ai_generated', 'ai_prompt', 'created_at', 'updated_at'],
        'discount_codes' => ['discount_id', 'enterprise_id', 'code', 'discount_type', 'discount_value', 'min_order_amount', 'max_uses', 'current_uses', 'starts_at', 'expires_at', 'is_active', 'created_at', 'updated_at'],
        'pricing_rules' => ['rule_id', 'enterprise_id', 'service_id', 'rule_type', 'discount_percent', 'is_active', 'created_at', 'updated_at'],
        'audit_logs' => ['log_id', 'user_id', 'action', 'entity_type', 'entity_id', 'old_values', 'new_values', 'ip_address', 'user_agent', 'created_at'],
        'notifications' => ['id', 'type', 'notifiable_type', 'notifiable_id', 'data', 'read_at', 'created_at', 'updated_at'],
        'notification_preferences' => ['preference_id', 'user_id', 'notification_type', 'is_enabled', 'created_at', 'updated_at'],
        'social_logins' => ['social_login_id', 'user_id', 'provider', 'provider_id', 'provider_token', 'provider_refresh_token', 'created_at', 'updated_at'],
        'personal_access_tokens' => ['id', 'tokenable_type', 'tokenable_id', 'name', 'token', 'abilities', 'last_used_at', 'expires_at', 'created_at', 'updated_at'],
        'sessions' => ['id', 'user_id', 'ip_address', 'user_agent', 'payload', 'last_activity'],
        'transactions' => ['transaction_id', 'purchase_order_id', 'amount', 'transaction_type', 'payment_method', 'reference_no', 'status', 'transaction_date', 'created_at', 'updated_at'],
        'user_payment_accounts' => ['account_id', 'user_id', 'provider', 'provider_account_id', 'access_token', 'refresh_token', 'is_active', 'created_at', 'updated_at'],
        'service_custom_fields' => ['field_id', 'service_id', 'field_label', 'field_type', 'field_options', 'is_required', 'sort_order', 'created_at', 'updated_at'],
        'service_images' => ['image_id', 'service_id', 'image_path', 'sort_order', 'created_at', 'updated_at'],
        'system_settings' => ['setting_id', 'key', 'value', 'created_at', 'updated_at'],
        'ai_generation_daily_usages' => ['usage_id', 'user_id', 'usage_date', 'generation_count', 'created_at', 'updated_at'],
        'user_reports' => ['report_id', 'reporter_id', 'enterprise_id', 'service_id', 'reason', 'description', 'status', 'resolved_by', 'resolved_at', 'created_at', 'updated_at'],
        'system_feedback' => ['feedback_id', 'user_id', 'category', 'message', 'status', 'created_at', 'updated_at'],
        'login' => ['login_id', 'user_id', 'username', 'password', 'created_at', 'updated_at'],
    ];

    /**
     * Check if a table exists (cached)
     */
    public static function hasTable(string $table): bool
    {
        return isset(self::$tables[$table]);
    }

    /**
     * Check if a column exists in a table (cached)
     */
    public static function hasColumn(string $table, string $column): bool
    {
        if (!isset(self::$columns[$table])) {
            return false;
        }
        return in_array($column, self::$columns[$table]);
    }

    /**
     * Get all columns for a table
     */
    public static function getColumns(string $table): array
    {
        return self::$columns[$table] ?? [];
    }

    /**
     * Get expression for date field (with fallback)
     */
    public static function getDateField(string $table, string $preferred, string $fallback = 'created_at'): string
    {
        if (self::hasColumn($table, $preferred)) {
            return "{$table}.{$preferred}";
        }
        return "{$table}.{$fallback}";
    }
}
