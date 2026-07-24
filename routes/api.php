<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\FormController;
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
use App\Http\Controllers\TestNotificationController;
use App\Http\Controllers\API\Mentors\MentorController;
use App\Http\Controllers\API\TrainingReportController;
use App\Http\Controllers\API\PermissionGroupController;
use App\Http\Controllers\API\Mentors\ScheduleController;
use App\Http\Controllers\API\MentorshipReportController;
use App\Http\Controllers\NotificationSettingsController;
use App\Http\Controllers\API\Startup\MentorshipSessionController;

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
Route::get('/expertise',[ExpertiseController::class, 'index']);
Route::get('/specializations',[ExpertiseController::class, 'children']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

Route::middleware('sanctum.custom')->group(function () {
    // Expertise management (protected)
    Route::post('/expertise',[ExpertiseController::class, 'store']);
    Route::put('/expertise/{id}',[ExpertiseController::class, 'update']);
    Route::post('/expertise/import',[ExpertiseController::class, 'import']);
    Route::delete('/expertise/{id}',[ExpertiseController::class, 'destroy']);
    Route::post('/specializations',[ExpertiseController::class, 'store']);
    Route::put('/specializations/{id}',[ExpertiseController::class, 'update']);
    Route::delete('/specializations/{id}',[ExpertiseController::class, 'destroy']);
    Route::post('/specializations/import',[ExpertiseController::class, 'importSpecialization']);
    Route::get('/countries', [CountryController::class, 'index']);
    Route::get('/dashboard',[DashboardController::class, 'index']);
    Route::get('/me', [ProfileController::class, 'me']);
    Route::patch('/users/{user}/deactivate', [UserController::class, 'deactivate']);
    Route::apiResource('users', UserController::class);
    Route::get('users/companies', [UserController::class, 'companies']);
    Route::put('users/{user}/password', [UserController::class,'updatePassword']);
    Route::apiResource('roles',RoleController::class);
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
    Route::put('/v1/{roleId}/permissions', [RoleController::class, 'updatePermissions']);
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
    Route::post('/company/founder', [CompanyController::class, 'storeFounder']);
    Route::put('/company/founder/{id}', [CompanyController::class, 'updateFounder']);
    Route::delete('/company/founder/{id}', [CompanyController::class, 'deleteFounder']);
    Route::post('/company/shareholder', [CompanyController::class, 'storeShareholder']);
    Route::put('/company/shareholder/{id}', [CompanyController::class, 'updateShareholder']);
    Route::delete('/company/shareholder/{id}', [CompanyController::class, 'deleteShareholder']);
    Route::post('/company/investor', [CompanyController::class, 'storeInvestor']);
    Route::put('/company/investor/{id}', [CompanyController::class, 'updateInvestor']);
    Route::delete('/company/investor/{id}', [CompanyController::class, 'deleteInvestor']);
    Route::post('/company/milestones', [CompanyController::class, 'storeMilestone']);
    Route::put('/company/milestones/{id}', [CompanyController::class, 'updateMilestone']);
    Route::delete('/company/milestones/{id}', [CompanyController::class, 'deleteMilestone']);
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
    Route::get('/v1/programs/{programId}/mentors', [ProgramController::class, 'mentors']);
    Route::get('v1/programs/{programId}/startups', [ProgramController::class, 'startups']);
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
    Route::post('/v1/programs/{programId}/steps', [StepController::class, 'createStep']);
    Route::put('/v1/programs/{programId}/steps/{stepId}', [StepController::class, 'updateStep']);
    Route::delete('/v1/programs/{programId}/steps/{stepId}', [StepController::class, 'deleteStep']);
    Route::patch('v1/programs/{programId}/steps/reorder', [StepController::class, 'reorderSteps']);
    Route::patch('v1/programs/{programId}/steps/{stepId}/change-order', [StepController::class, 'changeStepOrder']);
    Route::get('/v1/step-types', [StepController::class, 'getStepTypes']);
    Route::apiResource('steps', StepController::class);
    Route::get('/v1/forms', [FormController::class, 'index']);
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
Route::prefix('dashboard')->group(function () {
    Route::get('/summary', [MentorshipReportController::class, 'summary']);
    Route::get('/weekly-trends', [MentorshipReportController::class, 'weeklyTrends']);
    Route::get('/mentor-hours', [MentorshipReportController::class, 'mentorHours']);
    Route::get('/mentor-performance', [MentorshipReportController::class, 'mentorPerformance']);
    Route::get('/startup-engagement', [MentorshipReportController::class, 'startupEngagement']);
    Route::get('/trends/by-program', [MentorshipReportController::class, 'trendsByProgram']);
    Route::get('/trends/peak-activity', [MentorshipReportController::class, 'peakActivity']);
});

Route::prefix('training')->group(function () {
    Route::get('summary', [TrainingReportController::class, 'summary']);
    Route::get('session-types', [TrainingReportController::class, 'sessionTypes']);
    Route::get('monthly-trends', [TrainingReportController::class, 'monthlyTrends']);
    Route::get('type-breakdown', [TrainingReportController::class, 'typeBreakdown']);
    Route::get('hours-trend', [TrainingReportController::class, 'hoursTrend']);
    Route::get('trainers', [TrainingReportController::class, 'trainers']);
    Route::get('topics', [TrainingReportController::class, 'topics']);
    Route::get('sessions/recent', [TrainingReportController::class, 'recentSessions']);
    Route::post('session-reports', [TrainingReportController::class, 'getSessionReportsData']);
});

Route::prefix('mentorship')->group(function () {
    Route::post('session-reports', [MentorshipReportController::class, 'sessionReportsData']);
});

Route::prefix('chat')->group(function () {
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

    // Get Users for Chat
    Route::get('/users', [ChatController::class, 'getUsers']);

    // Conversations
    Route::get('/conversations', [ChatController::class, 'messages']);
    Route::get('/conversations/{conversationId}/messages', [ChatController::class, 'getConversationMessages']);
    Route::post('/conversations', [ChatController::class, 'createOrGetConversation']);
});

Route::prefix('training')->group(function () {
    Route::get('summary', [TrainingReportController::class, 'summary']);
    Route::get('session-types', [TrainingReportController::class, 'sessionTypes']);
    Route::get('monthly-trends', [TrainingReportController::class, 'monthlyTrends']);
    Route::get('type-breakdown', [TrainingReportController::class, 'typeBreakdown']);
    Route::get('hours-trend', [TrainingReportController::class, 'hoursTrend']);
    Route::get('trainers', [TrainingReportController::class, 'trainers']);
    Route::get('topics', [TrainingReportController::class, 'topics']);
    Route::get('sessions/recent', [TrainingReportController::class, 'recentSessions']);
    Route::post('session-reports', [TrainingReportController::class, 'getSessionReportsData']);
});

Route::prefix('mentorship')->group(function () {
    Route::post('session-reports', [MentorshipReportController::class, 'sessionReportsData']);
});

});

Route::middleware(['auth:sanctum'])->prefix('v1/importer')->name('api.v1.importer.')->group(function () {
    Route::get('/template',                               [\App\Http\Controllers\Importer\ImporterController::class, 'downloadTemplate']);
    Route::get('/schema',                                 [\App\Http\Controllers\Importer\ImporterController::class, 'schema']);
    Route::get('/organizations',                          [\App\Http\Controllers\Importer\ImporterController::class, 'organizations']);

    Route::middleware('throttle:importer-general')->group(function () {
        Route::post('/sessions',                          [\App\Http\Controllers\Importer\ImporterController::class, 'createSession']);
        Route::get('/sessions/{uuid}',                    [\App\Http\Controllers\Importer\ImporterController::class, 'showSession']);
        Route::delete('/sessions/{uuid}',                 [\App\Http\Controllers\Importer\ImporterController::class, 'discardSession']);
        Route::delete('/sessions/{uuid}/file',            [\App\Http\Controllers\Importer\ImporterController::class, 'deleteFile']);
        Route::post('/sessions/{uuid}/automap',           [\App\Http\Controllers\Importer\ImporterController::class, 'autoMap']);
        Route::post('/sessions/{uuid}/validate',          [\App\Http\Controllers\Importer\ImporterController::class, 'validateRows']);
        Route::post('/sessions/{uuid}/conflicts',         [\App\Http\Controllers\Importer\ImporterController::class, 'detectConflicts']);
        Route::get('/sessions/{uuid}/preview',            [\App\Http\Controllers\Importer\ImporterController::class, 'preview']);
        Route::post('/sessions/{uuid}/execute',           [\App\Http\Controllers\Importer\ImporterController::class, 'execute']);
    });

    Route::middleware('throttle:importer-upload')->group(function () {
        Route::post('/sessions/{uuid}/file',              [\App\Http\Controllers\Importer\ImporterController::class, 'uploadFile']);
    });
});
