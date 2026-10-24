<?php

namespace App\Http\Controllers;

use App\User;
use App\Notifications\TaskNotification;
use App\Notifications\WelcomeUserNotification;
use App\Notifications\NewTrainingNotification;
use App\Notifications\MentorshipRequestNotification;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Auth;

class TestNotificationController extends Controller
{
    /**
     * Test sending a task notification
     */
    public function testTaskNotification(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            $data = [
                'action' => 'test_task',
                'message' => 'This is a test task notification',
                'task' => [
                    'id' => 999,
                    'title' => 'Test Task',
                    'description' => 'This is a test task for notification testing'
                ]
            ];

            $user->notify(new TaskNotification($data));

            return response()->json([
                'success' => true,
                'message' => 'Task notification sent successfully',
                'data' => [
                    'user_id' => $user->id,
                    'notification_type' => 'TaskNotification',
                    'channels' => $this->getUserChannels($user)
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send task notification',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Test sending a welcome notification
     */
    public function testWelcomeNotification(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            $user->notify(new WelcomeUserNotification($user));

            return response()->json([
                'success' => true,
                'message' => 'Welcome notification sent successfully',
                'data' => [
                    'user_id' => $user->id,
                    'notification_type' => 'WelcomeUserNotification',
                    'channels' => $this->getUserChannels($user)
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send welcome notification',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Test sending a training notification
     */
    public function testTrainingNotification(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            $data = [
                'title' => 'Test Training Course',
                'description' => 'This is a test training course for notification testing',
                'instructor' => 'Test Instructor',
                'duration' => '2 hours'
            ];

            $user->notify(new NewTrainingNotification($data));

            return response()->json([
                'success' => true,
                'message' => 'Training notification sent successfully',
                'data' => [
                    'user_id' => $user->id,
                    'notification_type' => 'NewTrainingNotification',
                    'channels' => $this->getUserChannels($user)
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send training notification',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Test sending a mentorship request notification
     */
    public function testMentorshipRequestNotification(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            $data = [
                'company_name' => 'Test Startup',
                'mentor_name' => $user->first_name_en . ' ' . $user->last_name_en,
                'request_date' => now()->format('Y-m-d'),
                'message' => 'This is a test mentorship request'
            ];

            $user->notify(new MentorshipRequestNotification($data));

            return response()->json([
                'success' => true,
                'message' => 'Mentorship request notification sent successfully',
                'data' => [
                    'user_id' => $user->id,
                    'notification_type' => 'MentorshipRequestNotification',
                    'channels' => $this->getUserChannels($user)
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send mentorship request notification',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Test sending notification to multiple users
     */
    public function testBulkNotification(Request $request): JsonResponse
    {
        try {
            $users = User::where('status', 'active')->limit(5)->get();
            
            if ($users->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No active users found'
                ], 404);
            }

            $data = [
                'action' => 'bulk_test',
                'message' => 'This is a bulk test notification',
                'task' => [
                    'id' => 888,
                    'title' => 'Bulk Test Task',
                    'description' => 'This is a bulk test task for notification testing'
                ]
            ];

            Notification::send($users, new TaskNotification($data));

            return response()->json([
                'success' => true,
                'message' => 'Bulk notification sent successfully',
                'data' => [
                    'users_count' => $users->count(),
                    'notification_type' => 'TaskNotification',
                    'user_ids' => $users->pluck('id')->toArray()
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send bulk notification',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user's notification channels
     */
    private function getUserChannels(User $user): array
    {
        return [
            'email' => (bool) $user->accept_email,
            'whatsapp' => (bool) $user->accept_whatsapp,
            'pusher' => (bool) $user->accept_pusher,
            'database' => true // Always enabled
        ];
    }

    /**
     * Get user's notification settings
     */
    public function getUserNotificationSettings(): JsonResponse
    {
        try {
            $user = Auth::user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'notification_settings' => $this->getUserChannels($user),
                    'total_notifications' => $user->notifications()->count(),
                    'unread_notifications' => $user->unreadNotifications()->count()
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get user notification settings',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Test notification with specific channels
     */
    public function testSpecificChannels(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            $channels = $request->input('channels', ['database']);
            
            // Create a custom notification for testing specific channels
            $notification = new class($channels) extends \Illuminate\Notifications\Notification {
                private $channels;
                
                public function __construct($channels) {
                    $this->channels = $channels;
                }
                
                public function via($notifiable) {
                    return $this->channels;
                }
                
                public function toArray($notifiable) {
                    return [
                        'type' => 'test_specific_channels',
                        'message' => 'Test notification with specific channels',
                        'channels' => $this->channels,
                        'timestamp' => now()->toISOString()
                    ];
                }
            };

            $user->notify($notification);

            return response()->json([
                'success' => true,
                'message' => 'Specific channels notification sent successfully',
                'data' => [
                    'user_id' => $user->id,
                    'requested_channels' => $channels,
                    'user_preferences' => $this->getUserChannels($user)
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send specific channels notification',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
