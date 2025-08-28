<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\RoleController;
use App\Http\Controllers\API\StepController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\StageController;
use App\Http\Controllers\API\SectorController;
use App\Http\Controllers\API\CompanyController;
use App\Http\Controllers\API\CountryController;
use App\Http\Controllers\API\MessageController;
use App\Http\Controllers\API\ProfileController;
use App\Http\Controllers\API\ProgramController;
use App\Http\Controllers\API\CurrencyController;
use App\Http\Controllers\API\DashboardController;
use App\Http\Controllers\API\ExpertiseController;
use App\Http\Controllers\API\PermissionController;
use App\Http\Controllers\API\OrganizationController;
use App\Http\Controllers\API\RegistrationController;
use App\Http\Controllers\API\Mentors\MentorController;
use App\Http\Controllers\API\PermissionGroupController;
use App\Http\Controllers\API\Mentors\ScheduleController;
use App\Http\Controllers\API\Startup\MentorshipSessionController;
use App\Http\Controllers\NotificationSettingsController;
use App\Http\Controllers\TestNotificationController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
Route::match(['post', 'get'], 'register-company', [RegistrationController::class, 'register']);
Route::get('uploads/companies/{path}', [RegistrationController::class, 'showImage'])->where('path', '.*');
Route::get('company', [RegistrationController::class, 'getCompany']);

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::get('/expertises',[ExpertiseController::class, 'index']);

Route::middleware('sanctum.custom')->group(function () {
    Route::get('/countries', [CountryController::class, 'index']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/dashboard',[DashboardController::class, 'index']);
    Route::get('/me', [ProfileController::class, 'me']);
    Route::patch('/users/{user}/deactivate', [UserController::class, 'deactivate']);
    Route::apiResource('users', UserController::class);
    Route::get('users/companies', 'UserController@companies');
    Route::put('users/{user}/password', [UserController::class,'updatePassword']);
    Route::apiResource('/roles',RoleController::class);
    Route::get('/countries/{id}/states', [CountryController::class, 'getStates']);
    Route::post('/profile', [ProfileController::class, 'updateProfile']);
    Route::put('/profile/password', [ProfileController::class, 'updatePassword']);
    Route::put('/profile/bio', [ProfileController::class, 'updateBio']);
    Route::get('/currencies', [CurrencyController::class, 'index']);
    Route::get('/currencies-all', [CurrencyController::class, 'currencies']);
    Route::post('/currencies', [CurrencyController::class, 'store']);
    Route::post('/currencies/{id}', [CurrencyController::class, 'update']);
    Route::delete('/currencies/{id}', [CurrencyController::class, 'destroy']);
    Route::patch('currencies/exchange-rates', [CurrencyController::class, 'updateAllCurrency']);
    Route::get('/v1/mentors/{mentor}', [MentorController::class, 'show']);
    Route::get('/v1/users/me/permissions', [PermissionController::class, 'myPermissions']);
    Route::get('/v1/{role}/permissions', [PermissionController::class, 'userPermissions']);
    Route::post('/v1/availability', [ScheduleController::class, 'store']);
    Route::put('/v1/availability/{user_id}', [ScheduleController::class, 'update']);
    Route::delete('/v1/availability/{id}', [ScheduleController::class, 'destroy']);
    Route::get('/mentors/{mentor_id}/weekly-slots', [ScheduleController::class, 'index']);
    Route::get('/slots/{id}', [ScheduleController::class, 'show']);
    Route::get('/v1/mentors', [MentorController::class, 'index']);
    Route::get('/organizations', [OrganizationController::class, 'index']);
    Route::post('/organizations', [OrganizationController::class, 'store']);
    Route::post('/organizations/{organization}', [OrganizationController::class, 'update']);
    Route::delete('organizations/{id}', [OrganizationController::class, 'destroy']);
    Route::get('/startups', [CompanyController::class, 'index']);
    Route::get('/v1/startups/{id}', [CompanyController::class, 'show']);
    Route::post('/company/profile', [CompanyController::class, 'update']);
    Route::get('/sectors', [SectorController::class, 'index']);
    Route::get('/states', [SectorController::class, 'getStates']);
    Route::post('/programs', [ProgramController::class, 'store']);
    Route::post('programs/{id}', [ProgramController::class, 'update']);
    Route::get('/v1/programs', [ProgramController::class, 'index']);
    Route::get('/v1/programs/{id}', [ProgramController::class, 'show']);
    Route::get('/v1/programs/{programId}/reports/summary', [ProgramController::class, 'summary']);
    Route::get('/v1/programs/{programId}/reports/performance', [ProgramController::class, 'performance']);
    Route::get('/v1/programs/{programId}/reports/mentorship', [ProgramController::class, 'mentorship']);
    Route::get('/v1/programs/{programId}/reports/workshops', [ProgramController::class, 'workshops']);
    Route::get('/v1/programs/{programId}/reports/final-evaluation', [ProgramController::class, 'finalEvaluation']);
    Route::post('/mentorship-sessions', [MentorshipSessionController::class, 'addMentorshipSession']);
    
    // Notification Settings Routes
    Route::get('/notification-settings', [NotificationSettingsController::class, 'getNotificationSettings']);
    Route::put('/notification-settings', [NotificationSettingsController::class, 'updateNotificationSettings']);
    Route::get('/users/{userId}/notification-settings', [NotificationSettingsController::class, 'getUserNotificationSettings']);
    
    // Test Notification Routes
    Route::get('/test/notifications/settings', [TestNotificationController::class, 'getUserNotificationSettings']);
    Route::post('/test/notifications/task', [TestNotificationController::class, 'testTaskNotification']);
    Route::post('/test/notifications/welcome', [TestNotificationController::class, 'testWelcomeNotification']);
    Route::post('/test/notifications/training', [TestNotificationController::class, 'testTrainingNotification']);
    Route::post('/test/notifications/mentorship', [TestNotificationController::class, 'testMentorshipRequestNotification']);
    Route::post('/test/notifications/bulk', [TestNotificationController::class, 'testBulkNotification']);
    Route::post('/test/notifications/channels', [TestNotificationController::class, 'testSpecificChannels']);
    
    Route::post('/mentorship-sessions/submit-feedback', [MentorshipSessionController::class, 'submitFeedback']);
    Route::get('/mentorship-sessions/completed', [MentorshipSessionController::class, 'myCompletedSessions']);
    Route::get('/profile/{id}', [ProfileController::class, 'showProfile']);
    Route::apiResource('steps', StepController::class);
    Route::prefix('messages')->group(function () {
        Route::post('/', [MessageController::class, 'store']);
        Route::post('/reply', [MessageController::class, 'reply']); 
        Route::patch('/toggle-status', [MessageController::class, 'toggleStatus']);
        Route::patch('/star', [MessageController::class, 'toggleStar']);
        Route::get('/', [MessageController::class, 'index']);
        Route::get('/{id}', [MessageController::class, 'show']);
        Route::patch('/trash', [MessageController::class, 'moveToTrash']);
        Route::delete('/', [MessageController::class, 'forceDelete']);
    });
    Route::get('/stages', [StageController::class, 'index']);
    Route::prefix('v1')->group(function () {
    Route::apiResource('permissions', PermissionController::class);
    Route::apiResource('permission-groups', PermissionGroupController::class);
});


    // جروبات
    Route::post('/groups', [ChatController::class, 'createGroup']);
    Route::post('/groups/{groupId}/add-user', [ChatController::class, 'addUser']);
    Route::post('/groups/{groupId}/remove-user', [ChatController::class, 'removeUser']);
    Route::post('/groups/{groupId}/exit', [ChatController::class, 'exitGroup']);
    Route::delete('/groups/{groupId}/delete', [ChatController::class, 'deleteGroup']);
    Route::get('/my-groups', [ChatController::class, 'myGroups']);
    



    // wirechat messages
    Route::get('/wirechat/messages', [ChatController::class, 'messages']);
    Route::post('/groups/{groupId}/messages', [ChatController::class, 'sendMessage']);
    Route::delete('/messages/{messageId}', [ChatController::class, 'deleteMessage']);

    // Search Users
    Route::get('/users/search', [ChatController::class, 'searchUser']);
    
});







