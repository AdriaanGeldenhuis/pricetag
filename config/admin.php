<?php
/**
 * Admin Configuration
 * Pricetag.co.za - Enterprise E-commerce Platform
 *
 * Configuration for admin panel features, security, and permissions.
 */

return [
    // =========================================================================
    // Role Configuration
    // =========================================================================
    'roles' => [
        'super_admin' => [
            'display_name' => 'Super Administrator',
            'description' => 'Full system access with all permissions',
            'max_users' => (int) env('MAX_SUPER_ADMINS', 10), // Maximum super admins (0 = unlimited)
            'require_mfa' => (bool) env('SUPER_ADMIN_REQUIRE_MFA', false),
            'ip_whitelist' => env('SUPER_ADMIN_IP_WHITELIST', ''),
            'session_timeout' => 1800, // 30 minutes
            'require_password_change_days' => 30, // 0 = never
        ],
        'admin' => [
            'display_name' => 'Administrator',
            'description' => 'Administrative access with limited permissions',
            'max_users' => 0, // Unlimited
            'require_mfa' => (bool) env('ADMIN_REQUIRE_MFA', false),
            'ip_whitelist' => env('ADMIN_IP_WHITELIST', ''),
            'session_timeout' => 7200, // 2 hours
            'require_password_change_days' => 90,
        ],
    ],

    // =========================================================================
    // Super Admin Exclusive Permissions
    // =========================================================================
    // These actions can ONLY be performed by super admins
    'super_admin_only' => [
        'create_admin_users',
        'delete_admin_users',
        'change_user_roles',
        'change_admin_status',
        'toggle_user_mfa',
        'view_audit_logs',
        'impersonate_users',
        'force_password_reset',
        'access_system_settings',
        'manage_ip_whitelist',
        'delete_products',
        'delete_orders',
        'bulk_operations',
        'view_security_logs',
        'export_user_data',
    ],

    // =========================================================================
    // Security Requirements
    // =========================================================================
    'security' => [
        // Require MFA for all admin users (overrides individual role settings)
        'require_admin_mfa' => (bool) env('REQUIRE_ADMIN_MFA', false),

        // Require super admin approval for new admin users
        'require_admin_approval' => false,

        // Notify super admins of certain security events
        'notify_events' => [
            'admin_user_created',
            'admin_user_deleted',
            'super_admin_role_changed',
            'multiple_failed_logins',
            'suspicious_activity',
            'mfa_disabled',
            'impersonation_start',
        ],

        // Session security
        'single_session' => false, // Only allow one active session per admin
        'terminate_on_password_change' => true,
        'terminate_on_role_change' => true,
        'terminate_on_status_change' => true,

        // Login protection
        'max_failed_attempts' => 5,
        'lockout_duration' => 900, // 15 minutes
        'log_all_attempts' => true,
    ],

    // =========================================================================
    // Audit Configuration
    // =========================================================================
    'audit' => [
        'enabled' => true,
        'log_all_admin_actions' => true,
        'retain_days' => 365, // How long to keep audit logs
        'export_format' => 'csv',

        // Actions that require explicit logging (always logged even if audit is disabled)
        'critical_actions' => [
            'user_role_change',
            'user_delete',
            'user_create',
            'impersonation_start',
            'impersonation_end',
            'mfa_disable',
            'bulk_operations',
            'password_reset',
            'sessions_terminated',
        ],
    ],

    // =========================================================================
    // Impersonation Settings
    // =========================================================================
    'impersonation' => [
        'enabled' => (bool) env('ALLOW_IMPERSONATION', true),
        'max_duration' => 3600, // Auto-terminate after 1 hour
        'exclude_roles' => ['super_admin'], // Cannot impersonate these roles
        'show_banner' => true, // Show warning banner during impersonation
        'log_actions' => true, // Log all actions during impersonation
        'allowed_for' => ['super_admin'], // Only these roles can impersonate
    ],

    // =========================================================================
    // UI Settings
    // =========================================================================
    'ui' => [
        // Items per page in admin lists
        'items_per_page' => 20,

        // Show advanced features
        'show_security_features' => true,
        'show_audit_logs' => true,
        'show_impersonation' => true,

        // Quick actions available
        'quick_actions' => [
            'terminate_sessions',
            'force_password_reset',
            'disable_mfa',
            'impersonate',
        ],
    ],

    // =========================================================================
    // Notification Settings
    // =========================================================================
    'notifications' => [
        'email' => [
            'enabled' => (bool) env('ADMIN_EMAIL_NOTIFICATIONS', true),
            'from_address' => env('MAIL_FROM_ADDRESS', 'noreply@pricetag.co.za'),
            'from_name' => env('MAIL_FROM_NAME', 'Pricetag Admin'),
        ],

        'in_app' => [
            'enabled' => true,
            'auto_dismiss' => false,
            'max_displayed' => 5,
        ],
    ],

    // =========================================================================
    // Data Export Settings
    // =========================================================================
    'export' => [
        'enabled' => true,
        'formats' => ['csv', 'xlsx', 'json'],
        'max_records' => 10000,
        'include_sensitive' => false, // Include email, phone in exports
    ],
];
