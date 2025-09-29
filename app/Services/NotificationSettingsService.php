<?php

namespace App\Services;

use App\User;
use Illuminate\Support\Facades\Auth;

class NotificationSettingsService
{
    /**
     * Get notification settings for the authenticated user
     *
     * @return array
     */
    public function getNotificationSettings(): array
    {
        $user = Auth::user();
        
        return [
            'accept_email' => (bool) $user->accept_email,
            'accept_whatsapp' => (bool) $user->accept_whatsapp,
            'accept_pusher' => (bool) $user->accept_pusher,
        ];
    }

    /**
     * Update notification settings for the authenticated user
     *
     * @param array $settings
     * @return bool
     */
    public function updateNotificationSettings(array $settings): bool
    {
        $user = Auth::user();
        
        $user->update([
            'accept_email' => $settings['accept_email'] ?? true,
            'accept_whatsapp' => $settings['accept_whatsapp'] ?? false,
            'accept_pusher' => $settings['accept_pusher'] ?? false,
        ]);
        
        return true;
    }

    /**
     * Get notification settings for a specific user
     *
     * @param int $userId
     * @return array|null
     */
    public function getUserNotificationSettings(int $userId): ?array
    {
        $user = User::find($userId);
        
        if (!$user) {
            return null;
        }
        
        return [
            'accept_email' => (bool) $user->accept_email,
            'accept_whatsapp' => (bool) $user->accept_whatsapp,
            'accept_pusher' => (bool) $user->accept_pusher,
        ];
    }
}
