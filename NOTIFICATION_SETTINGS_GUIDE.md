# Global Notification Settings Implementation

## Overview

This implementation adds global notification channel settings to the admin/organization manager settings. Settings are stored in the `settings` table with `id=1` only.

## Components Created

### 1. Database Migration
**File:** `database/migrations/2024_11_11_000001_add_notification_channels_to_settings_table.php`

Adds three boolean columns to the `settings` table:
- `enable_email` (default: true)
- `enable_whatsapp` (default: true)
- `enable_pusher` (default: true)

### 2. GlobalNotificationSettingsService
**File:** `app/Services/GlobalNotificationSettingsService.php`

Static service for managing global notification settings:

```php
// Get all settings
GlobalNotificationSettingsService::getSettings();

// Check if channels are enabled
GlobalNotificationSettingsService::isEmailEnabled();
GlobalNotificationSettingsService::isWhatsAppEnabled();
GlobalNotificationSettingsService::isPusherEnabled();

// Get enabled channels array
GlobalNotificationSettingsService::getEnabledChannels();

// Update settings
GlobalNotificationSettingsService::updateSettings([
    'enable_email' => true,
    'enable_whatsapp' => false,
    'enable_pusher' => true,
]);
```

### 3. SettingsController Methods
**File:** `app/Http/Controllers/SettingsController.php`

Added two methods:

- `notificationSettings(Request $request)` - Display notification settings page
- `updateNotificationSettings(Request $request)` - Update notification settings

### 4. Routes
**File:** `routes/web.php`

Added routes under dashboard:
```
GET    /dashboard/notification-settings              -> notificationSettings
PUT    /dashboard/notification-settings              -> updateNotificationSettings
```

### 5. Admin View
**File:** `resources/views/admin/settings/notifications/index.blade.php`

Beautiful admin interface with:
- Toggle switches for each channel
- Visual indicators (icons and colors)
- Informative descriptions
- Success/error messages
- Responsive design

## How to Use

### 1. Run Migration
```bash
php artisan migrate
```

### 2. Access Admin Settings
Navigate to: `/dashboard/notification-settings`

### 3. Toggle Notification Channels
- Email Notifications
- WhatsApp Notifications
- Push Notifications (Pusher)

### 4. In Code - Check if Channels are Enabled

```php
use App\Services\GlobalNotificationSettingsService;

// Check individual channels
if (GlobalNotificationSettingsService::isEmailEnabled()) {
    // Send email
}

if (GlobalNotificationSettingsService::isWhatsAppEnabled()) {
    // Send WhatsApp
}

if (GlobalNotificationSettingsService::isPusherEnabled()) {
    // Send push notification
}

// Get all enabled channels
$channels = GlobalNotificationSettingsService::getEnabledChannels();
// Returns: ['database', 'mail', 'whatsapp', 'broadcast']
```

### 5. Update Notification Classes

To use these settings in notification classes, update the `via()` method:

```php
<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use App\Services\GlobalNotificationSettingsService;

class MyNotification extends Notification
{
    public function via(object $notifiable): array
    {
        $channels = ['database'];

        // Check if email is enabled globally
        if (GlobalNotificationSettingsService::isEmailEnabled() && $notifiable->accept_email) {
            $channels[] = 'mail';
        }

        // Check if WhatsApp is enabled globally
        if (GlobalNotificationSettingsService::isWhatsAppEnabled() && $notifiable->accept_whatsapp) {
            $channels[] = 'whatsapp';
        }

        // Check if Pusher is enabled globally
        if (GlobalNotificationSettingsService::isPusherEnabled() && $notifiable->accept_pusher) {
            $channels[] = 'broadcast';
        }

        return $channels;
    }
}
```

## Database Schema

```sql
ALTER TABLE settings ADD COLUMN enable_email BOOLEAN DEFAULT TRUE;
ALTER TABLE settings ADD COLUMN enable_whatsapp BOOLEAN DEFAULT TRUE;
ALTER TABLE settings ADD COLUMN enable_pusher BOOLEAN DEFAULT TRUE;
```

## Important Notes

1. **Settings stored in row id=1 only** - All global notification settings are stored in the settings table with id=1
2. **User preferences still respected** - Users can disable channels in their personal settings
3. **Global override** - If a channel is disabled globally, users cannot enable it
4. **Default values** - All channels default to true (enabled)

## Access Control

The notification settings page is accessible to:
- Admin users
- Organization managers

Route: `/dashboard/notification-settings`

## API Usage

### Get Current Settings
```php
$settings = \App\Services\GlobalNotificationSettingsService::getSettings();
// Returns:
// [
//     'enable_email' => true,
//     'enable_whatsapp' => true,
//     'enable_pusher' => true,
// ]
```

### Update Settings
```php
\App\Services\GlobalNotificationSettingsService::updateSettings([
    'enable_email' => true,
    'enable_whatsapp' => false,
    'enable_pusher' => true,
]);
```

## Testing

To test the implementation:

1. Run migration: `php artisan migrate`
2. Navigate to `/dashboard/notification-settings`
3. Toggle channels on/off
4. Click "Save Changes"
5. Verify settings are saved in database:
   ```bash
   php artisan tinker
   App\Settings::find(1)->only(['enable_email', 'enable_whatsapp', 'enable_pusher']);
   ```

## Troubleshooting

### Settings not saving
- Verify migration ran: `php artisan migrate:status`
- Check Settings model has fillable attributes
- Verify form is posting to correct route

### Settings not loading
- Ensure Settings table exists
- Check id=1 record exists in settings table
- Verify columns exist: `enable_email`, `enable_whatsapp`, `enable_pusher`

### Page not accessible
- Check user has admin/organization manager role
- Verify route is registered: `php artisan route:list | grep notification`
- Check user is authenticated
