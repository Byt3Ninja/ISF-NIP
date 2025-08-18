<?php

return [
    // Page Titles
    'create_title' => 'Create Notification Template',
    'edit_title' => 'Edit Notification Template',
    'index_title' => 'Notification Templates',
    'show_title' => 'View Notification Template',
    
    // Subtitles and Descriptions
    'create_subtitle' => 'Design and configure a new notification template for your application',
    'edit_subtitle' => 'Modify and update your notification template settings',
    'index_subtitle' => 'Manage all your notification templates in one place',
    
    // Form Sections
    'basic_info' => 'Basic Information',
    'channel_config' => 'Channel Configuration',
    'template_content' => 'Template Content',
    'advanced_settings' => 'Advanced Settings',
    
    // Form Fields
    'template_name' => 'Template Name',
    'template_title' => 'Template Title',
    'description' => 'Description',
    'channel' => 'Notification Channel',
    'subject' => 'Email Subject',
    'sender' => 'Sender Information',
    'content' => 'Template Content',
    'is_active' => 'Active Template',
    
    // Placeholders
    'name_placeholder' => 'e.g., welcome-email, password-reset',
    'title_placeholder' => 'e.g., Welcome to Our Platform',
    'description_placeholder' => 'Brief description of when this template is used...',
    'subject_placeholder' => 'e.g., Welcome to {{app.name}}!',
    'sender_placeholder' => 'e.g., noreply@company.com or Company Name',
    'content_placeholder' => 'Enter your template content here. Use {{variable}} for dynamic content.',
    
    // Help Text
    'name_help' => 'Unique identifier for this template. Use lowercase letters, numbers, and hyphens only.',
    'title_help' => 'Human-readable title that describes this template.',
    'description_help' => 'Optional description to help identify the purpose of this template.',
    'channel_help' => 'Select the communication channel for this notification.',
    'subject_help' => 'Email subject line. You can use variables like {{user.name}}.',
    'sender_help' => 'Sender name or email address. Leave empty to use system default.',
    'content_help' => 'Template content with support for variables and basic HTML formatting.',
    
    // Channels
    'channels' => [
        'email' => 'Email',
        'whatsapp' => 'WhatsApp',
        'pusher' => 'Push Notification',
        'sms' => 'SMS',
        'slack' => 'Slack',
        'discord' => 'Discord',
    ],
    
    // Buttons and Actions
    'save_template' => 'Save Template',
    'update_template' => 'Update Template',
    'test_send' => 'Test Send',
    'back_to_templates' => 'Back to Templates',
    'select_channel' => 'Select Channel',
    'create_template' => 'Create Template',
    'edit_template' => 'Edit Template',
    'delete_template' => 'Delete Template',
    'clone_template' => 'Clone Template',
    'preview_template' => 'Preview Template',
    'export_template' => 'Export Template',
    'import_template' => 'Import Template',
    
    // Editor and Preview
    'editor' => 'Editor',
    'preview' => 'Preview',
    'live_preview' => 'Live Preview',
    'preview_empty' => 'Start typing in the editor to see a preview of your template.',
    'editor_placeholder' => 'Enter your notification content here...',
    'content_aria_label' => 'Template content editor',
    
    // Variables
    'available_variables' => 'Available Variables',
    'loading_variables' => 'Loading variables...',
    'no_variables' => 'No variables available',
    'variables_load_error' => 'Failed to load variables',
    'variables_load_failed' => 'Unable to load variables',
    'click_to_insert' => 'Click to insert',
    'insert_variable' => 'Insert variable',
    'retry' => 'Retry',
    
    // Validation Messages
    'validation_errors' => 'Please correct the following errors:',
    'validation_failed' => 'Please fill in all required fields.',
    'test_validation_failed' => 'Please complete the form before testing.',
    'is_required' => 'is required',
    'subject_required_email' => 'Subject is required for email templates',
    'content_too_short' => 'Content must be at least 10 characters long',
    'name_invalid_format' => 'Template name can only contain lowercase letters, numbers, and hyphens',
    'name_already_exists' => 'A template with this name already exists',
    
    // Status Messages
    'template_created' => 'Notification template created successfully!',
    'template_updated' => 'Notification template updated successfully!',
    'template_deleted' => 'Notification template deleted successfully!',
    'template_cloned' => 'Notification template cloned successfully!',
    'test_sent' => 'Test notification sent successfully!',
    'test_failed' => 'Failed to send test notification. Please try again.',
    'save_timeout' => 'Save operation timed out. Please try again.',
    
    // Loading States
    'processing' => 'Processing...',
    'saving' => 'Saving template...',
    'updating' => 'Updating template...',
    'testing' => 'Sending test...',
    'loading' => 'Loading...',
    'deleting' => 'Deleting template...',
    
    // System Messages
    'initializing' => 'Initializing notification template editor...',
    'initialization_complete' => 'Template editor initialized successfully',
    'editor_initialized' => 'Code editor initialized successfully',
    'editor_fallback' => 'Using fallback text editor',
    
    // Test Modal
    'test_modal_title' => 'Test Notification Template',
    'test_email_label' => 'Test Email Address',
    'test_email_placeholder' => 'Enter email address to receive test notification',
    'test_phone_label' => 'Test Phone Number',
    'test_phone_placeholder' => 'Enter phone number (with country code)',
    'test_preview_title' => 'Template Preview',
    'send_test' => 'Send Test',
    'test_sent_success' => 'Test notification sent successfully!',
    'test_email_required' => 'Please enter a test email address',
    'test_phone_required' => 'Please enter a test phone number',
    
    // Table Headers (for index view)
    'table_name' => 'Name',
    'table_title' => 'Title',
    'table_channel' => 'Channel',
    'table_status' => 'Status',
    'table_created_at' => 'Created',
    'table_updated_at' => 'Updated',
    'table_actions' => 'Actions',
    
    // Status Labels
    'active' => 'Active',
    'inactive' => 'Inactive',
    'draft' => 'Draft',
    'published' => 'Published',
    
    // Search and Filters
    'search_templates' => 'Search templates...',
    'filter_by_channel' => 'Filter by channel',
    'filter_by_status' => 'Filter by status',
    'all_channels' => 'All Channels',
    'all_statuses' => 'All Statuses',
    'no_templates_found' => 'No templates found matching your criteria.',
    'create_first_template' => 'Create your first notification template to get started.',
    
    // Pagination
    'showing_results' => 'Showing :from to :to of :total results',
    'previous' => 'Previous',
    'next' => 'Next',
    
    // Confirmation Messages
    'confirm_delete' => 'Are you sure you want to delete this template?',
    'confirm_delete_message' => 'This action cannot be undone. All data associated with this template will be permanently deleted.',
    'yes_delete' => 'Yes, Delete',
    'cancel' => 'Cancel',
    
    // Import/Export
    'export_success' => 'Template exported successfully!',
    'import_success' => 'Template imported successfully!',
    'import_failed' => 'Failed to import template. Please check the file format.',
    'select_file' => 'Select file to import',
    'supported_formats' => 'Supported formats: JSON, XML',
    
    // Accessibility
    'skip_to_content' => 'Skip to main content',
    'close_modal' => 'Close modal',
    'toggle_menu' => 'Toggle navigation menu',
    'sort_by' => 'Sort by',
    'ascending' => 'Ascending',
    'descending' => 'Descending',
    
    // Keyboard Shortcuts
    'keyboard_shortcuts' => 'Keyboard Shortcuts',
    'save_shortcut' => 'Ctrl+S: Save template',
    'test_shortcut' => 'Ctrl+T: Test template',
    'preview_shortcut' => 'Ctrl+P: Toggle preview',
    'help_shortcut' => 'F1: Show help',
    
    // Error Messages
    'error_occurred' => 'An error occurred',
    'network_error' => 'Network error. Please check your connection.',
    'server_error' => 'Server error. Please try again later.',
    'permission_denied' => 'You do not have permission to perform this action.',
    'template_not_found' => 'Template not found.',
    'invalid_template_data' => 'Invalid template data provided.',
    
    // Tips and Hints
    'tip_variables' => 'Tip: Click on any variable to insert it into your template.',
    'tip_preview' => 'Tip: Use the preview tab to see how your template will look.',
    'tip_test' => 'Tip: Always test your templates before using them in production.',
    'tip_save_often' => 'Tip: Save your work frequently to avoid losing changes.',
    
    // Advanced Features
    'conditional_content' => 'Conditional Content',
    'loops' => 'Loops',
    'custom_variables' => 'Custom Variables',
    'template_inheritance' => 'Template Inheritance',
    'version_history' => 'Version History',
    'analytics' => 'Analytics',
    'a_b_testing' => 'A/B Testing',
    
    // Integration
    'webhook_url' => 'Webhook URL',
    'api_endpoint' => 'API Endpoint',
    'external_service' => 'External Service',
    'integration_settings' => 'Integration Settings',
    
    // Scheduling
    'schedule_template' => 'Schedule Template',
    'send_immediately' => 'Send Immediately',
    'schedule_for_later' => 'Schedule for Later',
    'recurring_schedule' => 'Recurring Schedule',
    
    // Analytics and Reporting
    'delivery_rate' => 'Delivery Rate',
    'open_rate' => 'Open Rate',
    'click_rate' => 'Click Rate',
    'bounce_rate' => 'Bounce Rate',
    'unsubscribe_rate' => 'Unsubscribe Rate',
    'performance_metrics' => 'Performance Metrics',
];
