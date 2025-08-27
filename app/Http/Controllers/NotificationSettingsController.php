<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Services\NotificationSettingsService;
use App\Repositories\NotificationSettingsRepository;
use Illuminate\Support\Facades\Validator;

class NotificationSettingsController extends Controller
{
    protected $notificationSettingsService;
    protected $notificationSettingsRepository;

    public function __construct(
        NotificationSettingsService $notificationSettingsService,
        NotificationSettingsRepository $notificationSettingsRepository
    ) {
        $this->middleware('auth');
        $this->notificationSettingsService = $notificationSettingsService;
        $this->notificationSettingsRepository = $notificationSettingsRepository;
    }

    /**
     * Get notification settings for the authenticated user
     *
     * @return JsonResponse
     */
    public function getNotificationSettings(): JsonResponse
    {
        try {
            $settings = $this->notificationSettingsService->getNotificationSettings();
            
            return response()->json([
                'success' => true,
                'data' => $settings,
                'message' => 'Notification settings retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve notification settings',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update notification settings for the authenticated user
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function updateNotificationSettings(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'accept_email' => 'boolean',
                'accept_whatsapp' => 'boolean',
                'accept_pusher' => 'boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $settings = $request->only(['accept_email', 'accept_whatsapp', 'accept_pusher']);
            
            // Set defaults if not provided
            $settings['accept_email'] = $settings['accept_email'] ?? true;
            $settings['accept_whatsapp'] = $settings['accept_whatsapp'] ?? false;
            $settings['accept_pusher'] = $settings['accept_pusher'] ?? false;

            $updated = $this->notificationSettingsService->updateNotificationSettings($settings);
            
            if ($updated) {
                return response()->json([
                    'success' => true,
                    'message' => 'Notification settings updated successfully',
                    'data' => $this->notificationSettingsService->getNotificationSettings()
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to update notification settings'
            ], 500);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update notification settings',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get notification settings for a specific user (admin only)
     *
     * @param int $userId
     * @return JsonResponse
     */
    public function getUserNotificationSettings(int $userId): JsonResponse
    {
        try {
            // Check if user has permission to view other users' settings
            if (!auth()->user()->hasRole('Admin')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access'
                ], 403);
            }

            $settings = $this->notificationSettingsService->getUserNotificationSettings($userId);
            
            if (!$settings) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $settings,
                'message' => 'User notification settings retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve user notification settings',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
