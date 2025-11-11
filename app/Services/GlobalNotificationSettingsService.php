<?php

namespace App\Services;

use App\Settings;

class GlobalNotificationSettingsService
{
    /**
     * Get global notification settings from settings table (id=1)
     *
     * @return array
     */
    public static function getSettings(): array
    {
        $settings = Settings::find(1);
        
        if (!$settings) {
            return [
                'enable_email' => true,
                'enable_whatsapp' => true,
                'enable_pusher' => true,
            ];
        }
        
        return [
            'enable_email' => (bool) $settings->enable_email,
            'enable_whatsapp' => (bool) $settings->enable_whatsapp,
            'enable_pusher' => (bool) $settings->enable_pusher,
        ];
    }

    /**
     * Check if email notifications are enabled globally
     *
     * @return bool
     */
    public static function isEmailEnabled(): bool
    {
        $settings = self::getSettings();
        return $settings['enable_email'];
    }

    /**
     * Check if WhatsApp notifications are enabled globally
     *
     * @return bool
     */
    public static function isWhatsAppEnabled(): bool
    {
        $settings = self::getSettings();
        return $settings['enable_whatsapp'];
    }

    /**
     * Check if Pusher notifications are enabled globally
     *
     * @return bool
     */
    public static function isPusherEnabled(): bool
    {
        $settings = self::getSettings();
        return $settings['enable_pusher'];
    }

    /**
     * Update global notification settings in settings table (id=1)
     *
     * @param array $data
     * @return bool
     */
    public static function updateSettings(array $data): bool
    {
        $settings = Settings::find(1);
        
        if (!$settings) {
            $settings = new Settings();
            $settings->id = 1;
        }
        
        $settings->enable_email = $data['enable_email'] ?? true;
        $settings->enable_whatsapp = $data['enable_whatsapp'] ?? true;
        $settings->enable_pusher = $data['enable_pusher'] ?? true;
        
        return $settings->save();
    }

    /**
     * Get enabled channels array
     *
     * @return array
     */
    public static function getEnabledChannels(): array
    {
        $settings = self::getSettings();
        $channels = ['database'];
        
        if ($settings['enable_email']) {
            $channels[] = 'mail';
        }
        
        if ($settings['enable_whatsapp']) {
            $channels[] = 'whatsapp';
        }
        
        if ($settings['enable_pusher']) {
            $channels[] = 'broadcast';
        }
        
        return $channels;
    }
}
