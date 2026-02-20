<?php

use App\Helpers\SafePropertyHelper;

if (!function_exists('safe_get')) {
    /**
     * Safely get a property from an object
     *
     * @param object|null $object
     * @param string $property
     * @param mixed $default
     * @return mixed
     */
    function safe_get($object, string $property, $default = null)
    {
        return SafePropertyHelper::safeGet($object, $property, $default);
    }
}

if (!function_exists('safe_nested')) {
    /**
     * Safely get a nested property from an object
     *
     * @param object|null $object
     * @param string $path
     * @param mixed $default
     * @return mixed
     */
    function safe_nested($object, string $path, $default = null)
    {
        return SafePropertyHelper::safeNested($object, $path, $default);
    }
}

if (!function_exists('enterprise_name')) {
    /**
     * Safely get enterprise name from service
     *
     * @param object|null $service
     * @return string
     */
    function enterprise_name($service): string
    {
        return SafePropertyHelper::getEnterpriseName($service);
    }
}

if (!function_exists('service_name')) {
    /**
     * Safely get service name
     *
     * @param object|null $service
     * @return string
     */
    function service_name($service): string
    {
        return SafePropertyHelper::getServiceName($service);
    }
}

// Backward compatibility
if (!function_exists('product_name')) {
    /**
     * Safely get service name (alias for service_name)
     *
     * @param object|null $service
     * @return string
     */
    function product_name($service): string
    {
        return SafePropertyHelper::getServiceName($service);
    }
}

if (!function_exists('format_price')) {
    /**
     * Safely format price
     *
     * @param mixed $price
     * @param int $decimals
     * @return string
     */
    function format_price($price, int $decimals = 2): string
    {
        return SafePropertyHelper::formatPrice($price, $decimals);
    }
}

if (!function_exists('user_name')) {
    /**
     * Safely get user name
     *
     * @param object|null $user
     * @return string
     */
    function user_name($user): string
    {
        return SafePropertyHelper::getUserName($user);
    }
}

if (!function_exists('order_status')) {
    /**
     * Safely get order status
     *
     * @param object|null $order
     * @return string
     */
    function order_status($order): string
    {
        return SafePropertyHelper::getOrderStatus($order);
    }
}

if (!function_exists('system_setting')) {
    function system_setting(string $key, $default = null)
    {
        try {
            if (!schema_has_table('system_settings')) {
                return $default;
            }

            $cacheKey = 'system_setting.' . $key;

            return \Illuminate\Support\Facades\Cache::remember($cacheKey, 300, function () use ($key, $default) {
                try {
                    $turso = app('App\Services\TursoHttpService');
                    $data = $turso->select('system_settings', ['key' => $key]);
                    $value = !empty($data) ? $data[0]['value'] : null;
                    return $value !== null ? $value : $default;
                } catch (\Throwable $e) {
                    return $default;
                }
            });
        } catch (\Throwable $e) {
            return $default;
        }
    }
}

if (!function_exists('system_brand_name')) {
    function system_brand_name(): string
    {
        return (string) system_setting('brand_name', config('app.name', 'UniPrint'));
    }
}

if (!function_exists('system_brand_tagline')) {
    function system_brand_tagline(): string
    {
        return (string) system_setting('brand_tagline', 'Smart Printing Services');
    }
}

if (!function_exists('system_brand_logo_url')) {
    function system_brand_logo_url(): ?string
    {
        $url = system_setting('brand_logo_url', null);
        $url = is_string($url) ? trim($url) : null;
        return $url !== '' ? $url : null;
    }
}

// Cached schema helpers - avoid expensive information_schema queries
if (!function_exists('schema_has_table')) {
    /**
     * Check if table exists (cached) - avoids expensive Schema::hasTable calls
     */
    function schema_has_table(string $table): bool
    {
        static $tables = null;
        if ($tables === null) {
            $tables = [
                'users', 'customer_orders', 'order_items', 'order_status_history', 'statuses',
                'payments', 'services', 'enterprises', 'staff', 'roles', 'role_types',
                'order_notifications', 'saved_services', 'saved_service_customizations',
                'saved_service_design_files', 'customization_options', 'order_item_customizations',
                'order_design_files', 'order_extension_requests', 'order_refunds',
                'conversations', 'chat_messages', 'online_users', 'typing_indicators',
                'reviews', 'rating_summaries', 'ratings', 'user_designs', 'discount_codes',
                'pricing_rules', 'audit_logs', 'notifications', 'notification_preferences',
                'social_logins', 'personal_access_tokens', 'sessions', 'transactions',
                'user_payment_accounts', 'service_custom_fields', 'service_images',
                'system_settings', 'user_reports',
                'review_files', 'system_feedback', 'login', 'cache', 'cache_locks',
                'jobs', 'job_batches', 'failed_jobs', 'migrations',
            ];
            $tables = array_flip($tables);
        }
        return isset($tables[$table]);
    }
}

if (!function_exists('schema_has_column')) {
    /**
     * Check if column exists in table (cached) - avoids expensive Schema::hasColumn calls
     */
    function schema_has_column(string $table, string $column): bool
    {
        static $columns = null;
        if ($columns === null) {
            $columns = [
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
                'user_designs' => ['design_id', 'user_id', 'file_name', 'file_path', 'file_type', 'file_size', 'created_at', 'updated_at'],
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
                'user_reports' => ['report_id', 'reporter_id', 'enterprise_id', 'service_id', 'reason', 'description', 'status', 'resolved_by', 'resolved_at', 'created_at', 'updated_at'],
                'system_feedback' => ['feedback_id', 'user_id', 'category', 'message', 'status', 'created_at', 'updated_at'],
                'login' => ['login_id', 'user_id', 'username', 'password', 'created_at', 'updated_at'],
            ];
        }
        return isset($columns[$table]) && in_array($column, $columns[$table]);
    }
}
