<?php

namespace App\Repositories;

use App\User;
use Illuminate\Support\Facades\Auth;

class NotificationSettingsRepository
{
    /**
     * Get notification settings for the authenticated user
     *
     * @return User|null
     */
    public function getUserWithNotificationSettings(): ?User
    {
        return Auth::user();
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
        
        if (!$user) {
            return false;
        }
        
        return $user->update([
            'accept_email' => $settings['accept_email'] ?? true,
            'accept_whatsapp' => $settings['accept_whatsapp'] ?? false,
            'accept_pusher' => $settings['accept_pusher'] ?? false,
        ]);
    }

    /**
     * Get notification settings for a specific user by ID
     *
     * @param int $userId
     * @return User|null
     */
    public function findUserById(int $userId): ?User
    {
        return User::select('id', 'accept_email', 'accept_whatsapp', 'accept_pusher')
            ->find($userId);
    }

    /**
     * Check if user has specific notification enabled
     *
     * @param string $type
     * @return bool
     */
    public function isNotificationEnabled(string $type): bool
    {
        $user = Auth::user();
        
        if (!$user) {
            return false;
        }
        
        switch ($type) {
            case 'email':
                return (bool) $user->accept_email;
            case 'whatsapp':
                return (bool) $user->accept_whatsapp;
            case 'pusher':
                return (bool) $user->accept_pusher;
            default:
                return false;
        }
    }
}
