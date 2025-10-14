<?php

use App\Notifications\WelcomeUserNotification;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\GeminiController;
use App\Http\Controllers\LicenseController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ImporterController;
use App\Http\Controllers\ActivityControlller;
use App\Http\Controllers\HackathonController;
use App\Http\Controllers\Mentor\MentorProgramController;
use App\Http\Controllers\Mentor\MentorshipTaskController;
use App\Http\Controllers\Judger\JudgerScreeningController;
/* Disable frontend */ // Route::get('/', 'HomeController@index')->name('admin'); ///

Route::get('test', function () {
    $user = User::find(1); 
$user->notify(new WelcomeUserNotification($user,'12345'));
});

Route::get('default-permissions', function () {
    App\Permission::default_permissions();
});
Route::get('mineRate', 'IndexController@mineRate')->name('mineRate');

Route::get('sendCustomerEmail/{id?}', 'IndexController@sendCustomerEmail')->name('sendCustomerEmail');
Route::get('/reminderMentorShipRequest', 'IndexController@reminderMentorShipRequest')->name('mentors.reminderMentorShipRequest');

// routes/web.php
//Route::get('create-meeting', 'MicrosoftController@createMeeting');
//Route::get('test', function () {
//    event(new App\Events\WebMessage('NewMent','sss'));
//    return "Event has been sent!";
//});
//Route::get('test',function (){
//    function prime_or_not($no1)
//    {
//        //Insert your code here
//        $sum = 0;
//        if ($no1 < 2) {
//            return 0;
//        }
//        for ($i = 2; $i <= sqrt($no1); $i++) {
//            if ($no1 % $i == 0) {
//                return -1;
//            }
//        }
//        for ($i = 1; $i <= $no1; $i++) {
//            $sum += $i;
//        }
//        return $sum;
//    }
//    return view('admin.reports.company');
//});


/// Frontend routes ///
Route::get('lang/{locale}', 'IndexController@lang');
Route::get('js-translations/{locale?}', 'IndexController@getJsTranslations')->name('js.translations');
Route::get('/', 'IndexController@home');
Route::get('home', 'IndexController@home');
Route::get('about', 'IndexController@home');
Route::get('faqs', 'IndexController@home');
Route::get('contact', 'IndexController@home');
Route::get('events', 'IndexController@home');
Route::get('csr-events', 'IndexController@home');
Route::get('blog', 'IndexController@home');
Route::get('blog/{id}', 'IndexController@post');
Route::post('message', 'IndexController@message');
Route::post('sendmsg', 'IndexController@sendmsg');
Route::get('page/{id}', 'IndexController@page');
Route::get('search', 'IndexController@search');
Route::get('event/{id}', 'IndexController@event');
Route::post('eventregister', 'IndexController@eventregister');

// test middleware
Route::get('/testting', function () {
    $stamp = '1746024780';
    $format = 'Y-m-d';
    try {
        $parser = 'carbon';
        $date = Carbon\Carbon::parse($stamp)->format($format);
    } catch (\Exception $e) {
        $parser = 'date';
        $date = date($format, $stamp);
    }
    dd($date, $parser);
});
Route::get('/2otp', function () {
    return redirect('/dashboard');
})->middleware(['auth', '2fa']);

Route::get('/db-migrate', 'IndexController@dbMigrate');
Route::get('/seed-permissions', 'IndexController@seedPermissions');
Auth::routes(['verify' => true]);
Route::get('organizations/set-password/{invitationId}', 'SettingsController@showSetPasswordForm')
    ->name('invitations.set_password_view_form');
Route::post('organizations/settings/accept/{invitationId}', 'SettingsController@acceptInvitation')->name('invitations.accept');

Route::group(['prefix' => 'dashboard', 'middleware' => ['auth', 'verified', '2fa']], function () {
    ####################### Overall System Admin Routes ###############
    // All Logged users
    Route::get('/', 'HomeController@index')->name('core.admin_access');
    Route::get('/dashboard', 'HomeController@index')->name('core.dashboard_view');
    Route::get('/dark', 'HomeController@dark');
    Route::get('/import-users', 'UserController@showImportForm')->name('import.view_form');
    Route::post('/import-users', 'UserController@import')->name('import.execute_users');

    //Roles
    Route::get('documentation/view/{id?}', 'HomeController@help');
    Route::resource('documentation', 'DocumentationController');

    // Judger
    Route::get('judger/screening', 'Judger\JudgerScreeningController@index');
    Route::post('judger/screening/evaluation/save_draft', 'Judger\JudgerScreeningController@save_evaluation_draft')->name('screening.evaluate_save_draft');
    Route::post('judger/screening/evaluation/publish', 'Judger\JudgerScreeningController@publish_evaluation')->name('screening.evaluate_publish');

    // Chat
    Route::get('chat', 'ChatController@index');
    
    // Chat API routes (using web session authentication)
    Route::get('chat/messages', 'ChatController@messages')->name('chat.messages');
    Route::get('chat/conversations/{conversationId}/messages', 'ChatController@getConversationMessages')->name('chat.conversation_messages');
    Route::post('chat/conversations', 'ChatController@createOrGetConversation')->name('chat.create_conversation');
    Route::get('chat/messages/{messageId}', 'ChatController@getMessage')->name('chat.get_message'); // For real-time broadcasting
    Route::get('chat/groups', 'ChatController@myGroups')->name('chat.groups');
    Route::post('chat/groups', 'ChatController@createGroup')->name('chat.create_group');
    Route::post('chat/groups/{groupId}/add-user', 'ChatController@addUser')->name('chat.add_user');
    Route::post('chat/groups/{groupId}/remove-user', 'ChatController@removeUser')->name('chat.remove_user');
    Route::post('chat/groups/{groupId}/exit', 'ChatController@exitGroup')->name('chat.exit_group');
    Route::delete('chat/groups/{groupId}', 'ChatController@deleteGroup')->name('chat.delete_group');
    Route::post('chat/messages', 'ChatController@sendMessage')->name('chat.send_message');
    Route::post('chat/groups/{groupId}/messages', 'ChatController@sendMessage')->name('chat.send_group_message');
    Route::delete('chat/messages/{messageId}', 'ChatController@deleteMessage')->name('chat.delete_message');
    Route::get('chat/users/search', 'ChatController@searchUser')->name('chat.search_users');

    // Hackathon Routes
    Route::get('hackathon', 'HackathonController@index')->name('hackathon.view');
    Route::get('hackathon/{id}/manage', 'HackathonController@manage')->name('hackathon.manageTeam');
    Route::get('apply/{id}/hackathon', 'HackathonController@applyHackathon')->name('hackathon.apply');
    Route::get('submission/{id}/hackathon', 'HackathonController@hackathonSubmissions')->name('hackathon.hackathonSubmissions');
    Route::post('hackathon/createTeam', 'HackathonController@createTeam')->name('hackathon.createTeam');
    Route::post('hackathon/deleteTeamInfo', 'HackathonController@deleteTeamInfo')->name('hackathon.deleteTeamInfo');
    Route::post('hackathon/invitation', 'HackathonController@invitation')->name('hackathon.invitation');
    Route::post('hackathon/cancelinvitation', 'HackathonController@cancelInvitation')->name('hackathon.Cancelinvitation');
    Route::patch('hackathon/acceptInvitation/{id}', 'HackathonController@acceptInvitation')->name('hackathon.acceptInvitation');
    Route::patch('hackathon/rejectInvitation/{id}', 'HackathonController@rejectInvitation')->name('hackathon.rejectInvitation');
    Route::post('hackathon/getOutTeam', 'HackathonController@getOutTeam')->name('hackathon.getOutTeam');
    Route::get('hackathon/my-team', 'HackathonController@myTeam')->name('hackathon.myTeam');
    Route::post('hackathon/EditProjectIdea', 'HackathonController@EditProjectIdea')->name('hackathon.EditProjectIdea');
    Route::post('hackathon/EditTeamInfo', 'HackathonController@EditTeamInfo')->name('hackathon.EditTeamInfo');
    Route::post('hackathon/RequestJoin', 'HackathonController@RequestJoin')->name('hackathon.RequestJoinTeam');
    Route::post('hackathon/ApprovedRequestJoin', 'HackathonController@ApprovedRequestJoin')->name('hackathon.ApprovedRequestJoinTeam');
    Route::post('hackathon/RejectedRequestJoin', 'HackathonController@RejectedRequestJoin')->name('hackathon.RejectedRequestJoin');
    Route::get('hackathon/viewSubmission/{step_id}/{user_id}', 'HackathonController@ViewSubmission')->name('hackathon.ViewSubmission');
    Route::get('hackathon/dashboard/{id}', 'HackathonController@viewAdminHackaton')->name('hackathon.viewAdminHackaton');
    // Route::get('hackathon/dashboard/winners/{id}', 'HackathonController@viewHackathonWinners')->name('hackathon.viewHackathonWinners');
    Route::get('hackathon/dashboard/winners/{id}', 'HackathonController@viewHackathonWinners');
    Route::get('hackathon/dashboard/winners/{id}/export', 'HackathonController@exportHackathonWinners');
    Route::post('hackathon/dashboard/winners/{id}', 'HackathonController@publishHackathonWinners');
    Route::get('hackathon/team/{id}', 'HackathonController@viewHackathonTeam')->name('hackathon.viewTeam');

    Route::post('hackathon/addSharedFile', 'SharedFileController@store')->name('hackathon.addSharedFile');
    Route::delete('hackathon/deleteSharedFile/{id}', 'SharedFileController@destroy')->name('hackathon.deleteSharedFile');
    Route::post('hackathon/AddtodoList', 'TodoListController@store')->name('hackathon.AddtodoList');
    Route::post('hackathon/EdittodoList', 'TodoListController@update')->name('hackathon.EdittodoList');
    Route::post('hackathon/EdittodoListStatus', 'TodoListController@changeTodoStatus')->name('hackathon.EdittodoListStatus');
    Route::delete('hackathon/deleteTodoList/{id}', 'TodoListController@destroy')->name('hackathon.deleteTodoList');

    Route::get('viewHackathonSummary/{id}', 'HackathonController@hackathon_Summary')->name('hackathon.hackathon_Summary');

    //waiting list
    Route::post('hackathon/leaveTeam', 'HackathonController@leaveTeam')->name('hackathon.leaveTeam');
    // Route::get('hackathon/manage', [HackathonController::class, 'manage']);
    
    Route::get('hackathon/details/{id}', [HackathonController::class, 'show']);
    Route::post('hackathon/likeProject', [HackathonController::class, 'likeProject']);


    Route::get('calendar/{id}/edit', 'SettingsController@editcal')->name('calendar.events_edit');
    Route::post('calendar/{id}/update', 'SettingsController@updatecal')->name('calendar.events_update');
    Route::get('calendar/{id}/delete', 'SettingsController@delcal')->name('calendar.events_delete');
    Route::post('calendar/view', 'SettingsController@viewcal')->name('calendar.events_view');
    // Sliders Routes
    Route::get('add_slide', 'SlidersController@add_slide')->name('slides.create');
    Route::post('store_slide', 'SlidersController@store_slide')->name('slides.store');
    Route::any('slides', 'SlidersController@slides')->name('slides.list');
    Route::get('edit_slide/{id}', 'SlidersController@editslide')->name('slides.edit');
    Route::post('update_slide/{id}', 'SlidersController@updateslide')->name('slides.update');
    Route::get('delete_slide/{id}', 'SlidersController@deleteslide')->name('slides.delete');
    // Menus Routes
    Route::get('add_menu', 'MenusController@add_menu')->name('menus.create');
    Route::post('store_menu', 'MenusController@store_menu')->name('menus.store');
    Route::any('menus', 'MenusController@myMenus')->name('menus.list');
    Route::get('edit_menu/{id}', 'MenusController@editMenu')->name('menus.edit');
    Route::post('update_menu/{id}', 'MenusController@updatemenu')->name('menus.update');
    Route::get('delete_menu/{id}', 'MenusController@deleteMenu')->name('menus.delete');
    // Posts Routes
    Route::get('addpost', 'PostsController@add_post')->name('posts.create');
    Route::post('storepost', 'PostsController@store_post')->name('posts.store');
    Route::any('posts', 'PostsController@myposts')->name('posts.list');
    Route::get('editpost/{id}', 'PostsController@editpost')->name('posts.edit');
    Route::post('updatepost/{id}', 'PostsController@updatepost')->name('posts.update');
    Route::get('deletepost/{id}', 'PostsController@deletepost')->name('posts.delete');
    // Pages Routes
    Route::get('addpage', 'PagesController@add_page')->name('pages.create');
    Route::post('storepage', 'PagesController@store_page')->name('pages.store');
    Route::any('pages', 'PagesController@mypages')->name('pages.list');
    Route::get('editpage/{id}', 'PagesController@editpage')->name('pages.edit');
    Route::post('updatepage/{id}', 'PagesController@updatepage')->name('pages.update');
    Route::get('deletepage/{id}', 'PagesController@deletepage')->name('pages.delete');
    Route::get('editmypage', 'PagesController@editmypage')->name('pages.update_mine');
    // Pages Events
    Route::get('addevent', 'EventsController@add_event')->name('events.create');
    Route::post('storeevent', 'EventsController@store_event')->name('events.store');
    Route::any('allevents', 'EventsController@myevent')->name('events.list');
    Route::get('editevent/{id}', 'EventsController@editevent')->name('events.edit');
    Route::post('updateevent/{id}', 'EventsController@updateevent')->name('events.update');
    Route::get('deleteevent/{id}', 'EventsController@deleteevent')->name('events.delete');
    // Settings Routes
    Route::any('settings', 'SettingsController@get_settings')->name('settings.system_general_view');
    Route::get('industry-settings', 'SettingsController@industrySettings')->name('settings.industry_view');
    Route::post('update_settings', 'SettingsController@update_settings')->name('settings.system_general_update');
    Route::any('setaboutus', 'SettingsController@get_aboutus');
    Route::post('updateaboutus', 'SettingsController@update_aboutus');
    Route::any('setcontactus', 'SettingsController@get_contactus');
    Route::post('updatecontactus', 'SettingsController@update_contactus');
    Route::put('settings/Updateone', 'SettingsController@update_settingsone')->name('settings.system_section_one_update');
    Route::put('settings/Updatelogo', 'SettingsController@update_settinglogo')->name('settings.system_logo_update');
    Route::put('settings/Updatetwo', 'SettingsController@update_settingstwo')->name('settings.system_section_two_update');
    Route::put('settings/UpdateMail', 'SettingsController@update_settingsmail')->name('settings.system_mail_update');
    Route::put('settings/UpdatePush', 'SettingsController@update_settingspush')->name('settings.system_push_notifications_update');
    Route::put('settings/UpdateMeeting', 'SettingsController@update_settingsMeeting')->name('settings.system_meeting_integration_update');
    Route::put('settings/UpdateLogin', 'SettingsController@update_settingsLogin')->name('settings.system_social_login_update');
    Route::put('settings/UpdateimgHome', 'SettingsController@update_settingsImgHome')->name('settings.system_social_home_image_update');
    Route::put('settings/UpdateimgAchiev', 'SettingsController@update_settingsAchievements')->name('settings.system_social_achievements_update');
    Route::put('settings/UpdateWhatsApp', 'SettingsController@update_settingsWhatsapp')->name('settings.system_social_whatsapp_update');

    Route::post('whatsapp/session/{sessionId}/start', 'WhatsAppSessionController@start')->where('sessionId', '[0-9]+')->name('whatsapp.session.start');
    Route::post('whatsapp/session/{sessionId}/status', 'WhatsAppSessionController@status')->where('sessionId', '[0-9]+')->name('whatsapp.session.status');
    Route::post('whatsapp/session/{sessionId}/qr-data', 'WhatsAppSessionController@getQrData')->where('sessionId', '[0-9]+')->name('whatsapp.session.qr.data');
    Route::post('whatsapp/session/{sessionId}/qr-image', 'WhatsAppSessionController@getQrImage')->where('sessionId', '[0-9]+')->name('whatsapp.session.qr.image');
    Route::delete('whatsapp/session/{sessionId}/terminate', 'WhatsAppSessionController@terminate')->where('sessionId', '[0-9]+')->name('whatsapp.session.terminate');
    Route::post('whatsapp/session/{sessionId}/send', 'IndexController@sendMessage')->where('sessionId', '[0-9]+')->name('whatsapp.session.send');
    Route::post('whatsapp/getWASession', 'WhatsAppSessionController@getWASessionID')->name('whatsapp.session.getsessionid');




    Route::get('organizations/settings', 'SettingsController@showSettings')->name('settings.organization_manage');
    Route::put('organizations/settings/updateMeeting', 'SettingsController@meetingSetting')->name('settings.organization_meeting_manage');
    Route::put('organizations/settings/socialLogin', 'SettingsController@socialSetting')->name('settings.organization_social_manage');
    Route::put('organizations/settings/general', 'SettingsController@generalSetting')->name('settings.organization.general.manage');
    Route::post('organizations/settings/invite', 'SettingsController@inviteUser')->name('organization.users_invite');
    Route::post('organizations/settings/resend-invitation/{invitationId}', 'SettingsController@resendInvitation')->name('organization.users_invitations_resend');
    Route::delete('organizations/settings/cancel-invitation/{invitationId}', 'SettingsController@cancelInvitation')->name('organization.users_invitations_cancel');
    Route::get('organizations/settings/reminder-logs', 'SettingsController@ReminderLogs')->name('organization.ReminderLogs');
    Route::post('organizations/reminder-logs/destroy', 'ReminderLogController@destroy')->name('organization.destroyReminderLogs');

    //services categories Manager routes
    Route::resource('serviceCategories', 'ServiceCategoryController');
    //Services Manager routes
    Route::resource('services', 'ServiceController');
    //Services requests
    Route::get('requests/all', 'ServiceController@requests')->name('service_requests.list_all');
    Route::get('requests/old', 'ServiceController@oldrequests')->name('service_requests.list_archived');
    Route::post('requests/approve', 'ServiceController@approve');
    Route::get('requests/mine', 'ServiceController@myrequests')->name('service_requests.list_mine');
    //Services Directory
    Route::get('directory', 'HomeController@directory')->name('users.directory_view');
    Route::post('directory/{id}', 'HomeController@showone');
    Route::post('directory/{id}/rate', 'HomeController@rateservice');
    Route::post('servicerequest', 'HomeController@servicereq');
    //Types Manager routes
    Route::resource('types', 'TypeController');
    //Partners Manager routes
    Route::resource('partners', 'PartnersController');
    Route::resource('stages', 'StageController');
    //Press Manger routes
    Route::resource('press', 'PressController');
    //Client's Profile
    Route::get('/myProfile', 'ClientProfileController@edit')->name('user.profile_Edit');
    Route::get('/show/myProfile', 'ClientProfileController@show')->name('user.profile_view');
    Route::post('/myProfile', 'ClientProfileController@update')->name('user.profile_update_basic');
    Route::post('/myProfile/bio', 'ClientProfileController@Updatebio')->name('user.profile_update_bio');
    Route::post('/myProfile/password', 'ClientProfileController@updatePassword')->name('user.account_update_password');
Route::post('/myProfile/notification-settings', 'ClientProfileController@updateNotificationSettings')->name('user.notification_settings_update');
    Route::get('/show/UserProfile/{id}', 'ClientProfileController@showMintorprofile')->name('mentors.profile_view');
    Route::get('/show/Mentors', 'ClientProfileController@showMintors')->name('mentors.list');
    // lowercase alias for backward-compatibility / ease of testing
    Route::get('/show/mentors', 'ClientProfileController@showMintors')->name('mentors.list.lowercase');
    Route::get('/confirm-email/{token}', 'ClientProfileController@confirmEmailUpdate')->name('user.profile_email_confirm');

    Route::group(['prefix' => '2fa'], function () {
        Route::get('/', 'ClientProfileController@show2faForm');
        Route::post('/generateSecret', 'ClientProfileController@generate2faSecret')->name('security.2fa_generate_secret');
        Route::post('/enable2fa', 'ClientProfileController@enable2fa')->name('security.2fa_enable');
        Route::post('/disable2fa', 'ClientProfileController@disable2fa')->name('security.2fa_disable');

        // 2fa middleware
        Route::post('/2faVerify', function () {
            return redirect('/dashboard');
        })->name('security.2fa_verify_login')->middleware('2fa');
    });


    Route::post('/save-phone-session', 'ClientProfileController@savePhoneSession')->name('security.2fa.phone_save_session');
    Route::post('/send-code', 'ClientProfileController@sendCode')->name('security.2fa_code_send');
    Route::post('/verify-code', 'ClientProfileController@verifyCode')->name('security.2fa_code_verify');

    Route::resource('Tags', 'TagsController');
    Route::resource('Labels', 'LabelController');
    Route::resource('TimeSheet', 'TimeSheetController');
    Route::resource('Category', 'CatigoriesController'); // category in TimeSheet Folder
    Route::resource('subcategories', 'SubCategoryController');

    Route::get('/show/FinanceTimeSheet', 'TimeSheetController@Finance')->name('time_sheets.reports.finance.view');
    Route::get('/export-FinanceTS', 'TimeSheetController@exportFTS')->name('time_sheets.reports.finance.export');
    Route::get('/UserTimeSheet', 'TimeSheetController@ClockUsers')->name('time_sheets.view.mine');
    Route::post('/UserTimeSheet/{id}', 'TimeSheetController@updateClockUser')->name('time_sheets.clock.update.mine');


    //Assessments
    Route::get('/question-assessment', 'AssessmentController@assessment')->name('assessments.take');
    Route::get('/assessment-create', 'AssessmentController@MakeAssessment')->name('assessments.assign');
    Route::get('/analysis-assessment', 'AssessmentController@analysisAssessment')->name('assessments.results_analysis_view');
    Route::get('/history-assessment', 'AssessmentController@historyAssessment')->name('assessments.results_history_view');
    Route::get('/category-assessment', 'CatigoriesController@index')->name('assessments.results_by_category_view');
    Route::post('/answer-assessment', 'AssessmentController@AnswerAssessment')->name('assessments.results_answers_view');
    Route::get('/exportAssessment-pdf', 'AssessmentController@generatePDF')->name('assessments.results_export_pdf');


    Route::get('/assessment-admin/{id}', 'AssessmentController@assessmentAdmin')->name('assessments.questions_manage');

    // LFM File Manager Imges
    Route::get('/LFMGet/{idUser?}/{files?}', 'organizationsController@GetLFM')->name('LFMGet');

    //Client's company
    Route::resource('organizations', 'organizationsController');

    //Assessment
    Route::resource('questions', 'QuestionController');
    Route::resource('weights', 'WeightController');
    Route::resource('assessments', 'AssessmentController');

    Route::get('get_subcategory', 'QuestionController@category')->name('subcategories.list_by_category');
    // Route::resource('answerOption', 'AnswerOptionController');

    //Company profile
    Route::get('/companyProfile/{id}', 'ClientCompanyController@show')->name('company.profile_view');
    Route::get('/myCompany/{id?}', 'ClientCompanyController@editOrCreate')->name('company.profile_view_mine');
    Route::post('/myCompany/{id?}', 'ClientCompanyController@updateOrStore')->name('company.profile_update_mine');
    Route::post('/AddCompanyTeams/{id?}', 'ClientCompanyController@AddTeams')->name('company.profile_teams_add');
    Route::post('/AddCompanyEdit/{id?}', 'ClientCompanyController@EditTeams')->name('company.profile_teams_edit');
    Route::post('/AddCompanyDelete/{id?}', 'ClientCompanyController@DeleteTeams')->name('company.profile_teams_delete');
    Route::post('/AddCompanyMileStone/{id?}', 'ClientCompanyController@AddMileStone')->name('company.profile_milestones_add');
    Route::post('/EditCompanyMileStone/{id?}', 'ClientCompanyController@EditMileStone')->name('company.profile_milestones_edit');
    Route::post('/DeleteCompanyMileStone/{id?}', 'ClientCompanyController@DeleteMileStone')->name('company.profile_milestones_delete');
    Route::post('/delete-document', 'ClientCompanyController@deleteDocument')->name('company.profile_documents_delete');

    //Industry
    Route::post('/industries/technologies', 'TechnologyController@create_technologies')->name('settings.create_technologies');
    Route::put('/industries/tech/{id}', 'TechnologyController@update_technologies')->name('settings.update_technologies');
    Route::delete('/industries/technologies/{id}', 'TechnologyController@delete_technologies')->name('settings.delete_technologies');
    
    Route::post('/industries', 'IndustryController@store')->name('settings.create_industries');
    Route::put('/industries/industry/{id}', 'IndustryController@update')->name('settings.update_industries');
    Route::delete('/industries/{id}', 'IndustryController@destroy')->name('settings.delete_industries');

    Route::post('/industries/sectors', 'SectorController@store')->name('settings.create_sectors');
    Route::put('/industries/sector/{id}', 'SectorController@update')->name('settings.update_sectors');
    Route::delete('/industries/sectors/{id}', 'SectorController@destroy')->name('settings.delete_sectors');




    //like a service
    Route::post('/likeService', 'ClientCompanyController@likeService')->name('services.like');
    //Users  Manager routes
    Route::resource('users', 'UserController');
    Route::get('/judger', 'UserController@judger')->name('users.judger');
    Route::post('/users/password', 'UserController@updatePassword')->name('user.profile_update_password');
    Route::get('/companies', 'ClientCompanyController@companies')->name('users.view_companies');
    // Export startups (CSV / Excel) - preserves current filters passed as query params
    Route::get('/companies/export', 'CompanyExportController@export')->name('users.companies_export');
    //Cities
    Route::resource('cities', 'CityController');
    Route::get('get_states', 'CityController@states')->name('utils.locations_list_states_by_country');

    //FileManager
    Route::get('/myFiles', 'FilemanagerController@index')->name('my_files.list');
    Route::get('/uploads/files/{id}/{path}', 'FilemanagerController@showfile')->where('path', '.*')->name('my_files.view_file');
    Route::get('/uploads/photos/{id}/{path}', 'FilemanagerController@showimage')->where('path', '.*')->name('my_files.view_image');
    Route::get('/uploads/tasks/{id}/cards/{card_id}/{path}', [MentorshipTaskController::class, 'attachments'])->where('path', '.*');
    //Explore private files
    Route::get('/private/{id}/{path}', 'FilemanagerController@showprivate')->where('path', '.*');
    //FileManager for training
    Route::get('/training/allfiles', 'FilemanagerController@allfiles');
    Route::get('/training/files/{path}', 'FilemanagerController@trainingfile')->where('path', '.*');
    Route::get('/training/photos/{path}', 'FilemanagerController@trainingimage')->where('path', '.*');
    //SendMessage
    Route::post('/send_message', 'MessengerController@sendmsg');
    Route::get('/sendmessage', 'MessengerController@sendmessage')->name('messages.send');
    Route::post('/sendmessage', 'MessengerController@savemessage')->name('messages.save');
    Route::post('/sendteammessage','HackathonController@sendTeamMessage')->name('messages.send_team_message');
    Route::post('/sendReplymessage', 'MessengerController@sendReplyMessage')->name('messages.reply');
    Route::get('/get-users-by-cycle', 'MessengerController@getUsersByProgram')->name('messages.recipients_list_by_program');

    //ShowMessages
    Route::get('/messages', 'MessengerController@index')->name('messages.list');
    // Route::get('/messages/{id}', 'MessengerController@show')->name('messages.view');
    Route::post('/draftMessage', 'MessengerController@draftmessage')->name('messages.drafts_save');
    Route::post('/deleteDraft', 'MessengerController@deleteDraft')->name('messages.drafts_delete');
    Route::get('/getAllDraft', 'MessengerController@getAllDrafts')->name('messages.drafts_list');
    Route::get('/outbox', 'MessengerController@outbox')->name('messages.folders_outbox');
    Route::get('/openmsg', 'MessengerController@openmsg');
    Route::post('/mread', 'MessengerController@makeread');
    Route::post('/messages/delete', 'MessengerController@destroy')->name('messages.delete');
    Route::get('/messages/trash', 'MessengerController@MessengerTrash')->name('messages.folders_trash');
    Route::post('/messages/toggleStarredStatus', 'MessengerController@toggleStarredStatus')->name('messages.star_toggle');
    Route::get('/messages/getAllStarred', 'MessengerController@getAllStarred')->name('messages.folders_starred');
    Route::post('/messages/toggleMarkReadStatus', 'MessengerController@toggleMarkReadStatus')->name('messages.read_status_toggle');
    //Contact US Messages
    Route::get('mymsg', 'SettingsController@mymsg')->name('messages.view');
    Route::post('msgreply', 'SettingsController@msgreply')->name('messages.reply');
    Route::get('deletemsg/{id}', 'SettingsController@deletemsg')->name('messages.delete');
    Route::post('makeread/{id}', 'SettingsController@makeread')->name('messages.read_status_mark_read');
    Route::post('makeunread/{id}', 'SettingsController@makeunread')->name('messages.read_status_mark_unread');
    Route::get('openonemsg/{id}', 'SettingsController@openonemsg')->name('messages.view');
    //products categories Manager routes
    Route::resource('productsCategories', 'ProductCategoryController');

    //products Manager routes
    Route::resource('products', 'ProductController');
    //publish
    Route::any('products/publish/{id}', 'ProductController@publish');
    //Interests
    Route::any('interests', 'HomeController@myinterests')->name('user.profile.interests_manage');
    //Audit Logs
    Route::resource('audit-logs', 'AuditLogsController', ['except' => ['create', 'store', 'edit', 'update']]);
    //Permissions
    Route::resource('permissions', 'PermissionsController');
    // permissions groups
    Route::resource('permission-groups', 'PermissionGroupsController');
    //Roles
    Route::resource('roles', 'RolesController');
    //Faqs
    Route::resource('faqs', 'FaqsController');

    // Forms
    Route::get('forms-list', 'FormController@index')->name('forms.index');

    Route::get('forms-submissions', 'FormController@submissions')->name('forms_submissions.index');
    Route::get('forms-submissions/export/{type}', 'FormController@export');
    Route::get('submission-details/{id}', 'FormController@submissionDetails')->name('form_submission_details.index');

    //BuilderForm Builder
    Route::get('show-builder/{id?}', 'FormBuilderController@showBuilder')->name('forms.builder_view');
    Route::get('show-form/{id}', 'FormBuilderController@showForm')->name('forms.render_view');
    Route::post('form/{id}/save-form', 'FormBuilderController@saveForm')->name('forms.design_save');
    Route::post('builder/{set}', 'FormBuilderController@modifyForm');
    Route::post('submit-form', 'FormBuilderController@handleFormRequest')->name('forms.submissions_create');
    Route::get('builder/organization/{id?}', 'FormBuilderController@builderByOrganization');
    //Cycles
    Route::get('cycles/{id?}', 'CyclesController@index')->name('cycles.list');
    Route::get('cycles/{id}/registered', 'CyclesController@users')->name('cycles.users_manage');
    
    Route::post('cycles/{id}/add_page', 'CycleCustomPagesController@add');
    Route::post('cycles/{id}/show_page', 'CycleCustomPagesController@view');
    Route::post('cycles/{id}/update_page', 'CycleCustomPagesController@update');
    Route::post('cycles/{id}/pages_resort', 'CycleCustomPagesController@reorder');
    Route::post('cycles/{id}/toggle_status', 'CycleCustomPagesController@toggle_status');
    Route::post('cycles/{id}/toggle_visibility', 'CycleCustomPagesController@toggle_visibility');
    Route::post('cycles/{id}/delete_page', 'CycleCustomPagesController@delete');
    
    Route::post('cycles/modify', 'CyclesController@modify')->name('cycles.modify');
    Route::post('cycles/validate-url', 'CyclesController@validateURL');
    Route::get('cycles/view/{c?}/{s?}', 'CyclesController@view')->name('cycles.view');
    Route::post('cycles/save', 'CyclesController@saveForm');
    Route::get('cycles/my/{id}', 'CyclesController@my')->name('cycles.list_mine');
    Route::post('change/cyc', 'CyclesController@changAll');
    Route::get('/export-cyclesComp', 'CyclesController@exportcyclesComp');

    Route::get('/queue-jobs', 'QueueJobController@index')->name('queue.jobs');
    Route::get('/run-queue-once', 'QueueJobController@runOnce')->name('queue.run.once');


    //MentorShip Schedule
    Route::get('mentorshipschedule ', 'MentorshipScheduleController@index')->name('mentorship.schedule');
    Route::post('mentorshipschedule/store', 'MentorshipScheduleController@store')->name('mentorship.schedule_store');
    Route::post('mentorshipschedule/update', 'MentorshipScheduleController@update')->name('mentorship.schedule_update');
    Route::post('mentorshipschedule/destroy', 'MentorshipScheduleController@destroy')->name('mentorship.schedule_destroy');

    //Screening
    Route::get('screening', 'ScreeningController@index');
    Route::get('/dashboard/screening/get/{formId}/{stepId}/{panel}', 'ScreeningController@get')->name('screening.get');
    Route::post('screening/update', 'ScreeningController@update');
    Route::post('screening/save', 'ScreeningController@save');
    Route::post('screening/add', 'ScreeningController@add');
    Route::get('screening/results/{id?}', 'ScreeningController@results');
    Route::get('screening/resultsReport', 'ScreeningController@resultsReport')->name('screening.results_report');
    Route::get('{id?}/screening', 'ScreeningController@index');
    Route::get('screening/{fid}/{cid}', 'ScreeningController@view')->name('screening.view');
    Route::get('screening/get/{fid}/{sid}/{pid}', 'ScreeningController@getusers');
    Route::post('screening/changestep', 'ScreeningController@changestep')->name('screening.change_step');
    Route::get('/export-Result', 'ScreeningController@exportresultScreen');
    Route::post('screening/evaluation/save_draft', 'ScreeningController@save_evaluation_draft')->name('screening.evaluation_save_draft');
    Route::post('screening/evaluation/publish', 'ScreeningController@publish_evaluation')->name('screening.evaluation_publish');
    Route::post('screening/evaluation/results', 'ScreeningController@get_results')->name('screening.evaluation_results');

    //ZOOM
    Route::get('zoom/all', 'MeetingController@showall');
    Route::get('zoom/{id}/get', 'MeetingController@show');
    Route::post('zoom/store', 'MeetingController@store');
    Route::post('zoom/{id}/update', 'MeetingController@update');
    Route::post('zoom/{id}/delete', 'MeetingController@destroy');
    Route::get('zoom/{id}/registrants', 'MeetingController@registrants');
    //Mentors
    Route::post('mentors/modify', 'MentorsController@modify')->name('mentors.settings_modify');

    Route::get('program/show', 'Mentor\MentorProgramController@show');

    Route::post('mentors/modify', 'MentorsController@modify')->name('mentors.settings_modify');
    Route::post('mentorship/add', 'MentorsController@add')->name('mentors.add');
    Route::get('mentorship/assign/{id?}', 'MentorsController@list')->name('mentorships.requests_list');
    Route::post('mentorship/assign/{id}', 'MentorsController@assign')->name('mentorships.requests_assign');
    Route::get('mentorship/view/{id?}', 'MentorsController@index')->name('mentorships.requests_view');
    Route::get('mentorship/{id?}/surveys', 'MentorsController@list_surveys')->name('mentorships.surveys_list');
    Route::get('mentorship/{id?}/surveys/create', 'MentorsController@create_survey')->name('mentorships.surveys_create');
    Route::post('mentorship/{id?}/surveys/store', 'MentorsController@store_survey')->name('mentorships.surveys_store');
    Route::get('mentorship/{id?}/surveys/{survey?}/edit', 'MentorsController@edit_survey')->name('mentorships.surveys_edit');
    Route::patch('mentorship/{id?}/surveys/{survey?}/update', 'MentorsController@update_survey')->name('mentorships.surveys_update');
    Route::delete('mentorship/{id?}/surveys/{survey?}/destroy', 'MentorsController@destroy_survey')->name('mentorships.surveys_delete');
    Route::get('mentorship/sessions', 'MentorsController@sessions')->name('mentorships.sessions_list');
    Route::post('mentorship/session', 'MentorsController@getSession')->name('mentorships.sessions_view_details');
    Route::post('mentorship/modify', 'MentorsController@modifySession')->name('mentorships.sessions_modify');
    Route::post('mentorship/addwait', 'MentorsController@addWait')->name('mentorships.waitlist_add');
    Route::get('mentorship/mine', 'MentorsController@mine')->name('mentorships.list_mine');
    Route::post('mentorship/rate', 'MentorsController@rate')->name('mentorships.sessions_rate');
    Route::post('mentorship/delsession/{id}', 'MentorsController@delSession')->name('mentorships.sessions_delete');
    Route::post('mentorship/session-reports-data', 'MentorsController@getSessionReportsData')->name('mentorships.sessions_reports_data');
    Route::get('/export-mentorship', 'MentorsController@exportMentorship')->name('mentorships.export');
    Route::post('/mentorship/getDates', 'MentorsController@MentorDates')->name('mentorships.dates_view');



    //Training
    Route::get('training/all/{id?}', 'TrainingController@all')->name('trainings.list');
    Route::get('training/edit/{id}', 'TrainingController@edit')->name('trainings.edit');
    Route::post('training/store', 'TrainingController@store')->name('trainings.store');
    Route::post('training/delete/{id}', 'TrainingController@delete')->name('trainings.delete');
    Route::post('training/delcontent/{id}', 'TrainingController@delcontent')->name('trainings.content_delete');
    Route::get('training/addcontent/{id}', 'TrainingController@addcontent')->name('trainings.content_create');
    Route::get('training/editcontent/{id}', 'TrainingController@editcontent')->name('trainings.content_edit');
    Route::get('training/viewcontent/{id}', 'TrainingController@viewcontent')->name('trainings.content_view');
    Route::post('training/upload', 'TrainingController@upload')->name('trainings.files_upload');
    Route::post('training/storecontent', 'TrainingController@storecontent')->name('trainings.content_store');
    Route::get('training/my', 'TrainingController@mysessions')->name('trainings.my_sessions_mine');
    Route::get('training/my/{id}', 'TrainingController@my')->name('trainings.list_mine');
    Route::get('training/my/{id}/quiz/{quiz_id}', 'quizzesController@myQuiz')->name('quizzes.view_overview_mine');
    Route::get('training/my/{id}/quiz/{quiz_id}/responses/{response_id}', 'quizzesController@myQuizResponses')->name('quizzes.view_response_mine');
    Route::post('training/my/{id}/quiz/{quiz_id}', 'quizzesController@doQuiz')->name('quizzes.take_start');
    Route::get('training/EditTR/{id}', 'TrainingController@editTR')->name('trainings.records_edit');
    Route::get('training/viewCourse/{id}', 'TrainingController@showCourse')->name('trainings.course_view');
    Route::get('quizResults', 'quizzesController@quizResults')->name('quizzes.quiz_Results_view');
    Route::get('quizResults/{id}', 'quizzesController@quizResultsdetails')->name('quizzes.quiz_results_view_details');


    Route::get('training/results/{id?}', 'TrainingController@results')->name('trainings.results_view');
    Route::get('training/sessions/{id?}', 'TrainingController@sessions')->name('trainings.sessions_list');
    Route::get('training/sessionsReport', 'TrainingController@sessionsReport')->name('trainings.sessions_report');

    Route::post('training/addsession', 'TrainingController@addsession')->name('trainings.sessions_create');
    Route::post('training/delsession/{id}', 'TrainingController@delsession')->name('trainings.sessions_delete');
    Route::post('training/session', 'TrainingController@session')->name('trainings.sessions_view');
    Route::put('training/editsession/{id}', 'TrainingController@editsession')->name('trainings.sessions_edit');
    Route::post('trainings/getdsc/{id}', 'TrainingController@getdsc')->name('trainings.descriptions_view');
    Route::get('/export-Tresult', 'TrainingController@exportTresult')->name('trainings.results_export');
    Route::post('trainings/change/Tresult', 'TrainingController@changAllTL')->name('trainings.tl_update_all');
    Route::get('training/create', 'TrainingController@create')->name('trainings.create');
    Route::get('training/{id?}/surveys', 'surveysController@list_surveys')->name('trainings.surveys_list');
    Route::get('training/{id?}/surveys/create', 'surveysController@create_survey')->name('trainings.surveys_create');
    Route::post('training/{id?}/surveys/store', 'surveysController@store_survey')->name('trainings.surveys_store');
    Route::get('training/{id?}/surveys/{survey?}/edit', 'surveysController@edit_survey')->name('trainings.surveys_edit');
    Route::patch('training/{id?}/surveys/{survey?}/update', 'surveysController@update_survey')->name('trainings.surveys_update');
    Route::delete('training/{id?}/surveys/{survey?}/destroy', 'surveysController@destroy_survey')->name('trainings.surveys_delete');
    Route::get('training/session-report/{id}', 'TrainingController@sessionReport')->name('trainings.reports_session');
    Route::get('training/session-record/{id}', 'TrainingController@sessionRecord')->name('trainings.reports_recording');
    Route::get('training/session-attendance-report/{id}', 'TrainingController@attendanceReport')->name('trainings.reports_attendance');
    Route::get('training/session-attendance-offline/{id}', 'TrainingController@attendanceOffline')->name('trainings.attendance_offline_manage');
    Route::post('training/QrCode-offline', 'TrainingController@QrCodeTraining')->name('trainings.attendance_qr_code_create');
    Route::get('training/Attendance-offline/{id}', 'TrainingController@getattendanceOffline')->name('trainings.attendance_offline_view');
    Route::post('training/session-reports-data', 'TrainingController@getSessionReportsData')->name('trainings.session_reports_data');


    Route::get('/Totalsurveys', 'MentorsController@total_surveys')->name('surveys.dashboard_count_view');
    // questions bank
    Route::resource('question-categories', 'QuestionCategoriesController');
    Route::resource('questions-bank', 'QuestionsBankController');
    Route::resource('quizzes', 'quizzesController')->except(['show']);
    Route::get('quizzes/{quiz}/questions', 'quizzesController@get_questions')->name('quizzes.questions_manage');
    Route::post('quizzes/{quiz}/questions', 'quizzesController@save_questions')->name('quizzes.questions_update');
    Route::post('quizzes/{quiz}/questions/delete', 'quizzesController@delete_questions')->name('quizzes.questions_delete');
    Route::get('quizzes/my', 'quizzesController@my')->name('quizzes.dashboard_view');

    Route::resource('surveys', 'surveysController')->except(['show']);
    Route::get('surveys/{survey}/questions', 'surveysController@get_questions')->name('surveys.questions_manage');
    Route::get('surveys/{survey}/results', 'surveysController@get_results')->name('surveys.results_view');
    Route::post('surveys/{survey}/questions', 'surveysController@save_questions')->name('surveys.questions_update');
    Route::get('surveys/start/{id?}', 'surveysController@survey_taking')->name('surveys.take');
    Route::post('surveys/start/{id?}', 'surveysController@survey_submit')->name('surveys.submit');
    Route::post('surveys/{survey}/questions/delete', 'surveysController@delete_questions')->name('surveys.questions_delete');

    //Rooms routes
    Route::resource('rooms', 'RoomsController');
    Route::get('/roomImages/{id}', 'RoomsController@roomImages')->name('rooms.images_manage');
    Route::get('/deleteImages/{id}', 'RoomsController@deleteImages')->name('rooms.images_delete');
    Route::resource('booking', 'BookingController');
    Route::get('/bookingreview', 'BookingController@review')->name('bookings.review_pending');
    Route::get('/bookingApprove/{id}/{action}', 'BookingController@approve')->name('bookings.approve');
    Route::get('/upcomingApprovedReview', 'BookingController@upcomingApprovedReview')->name('bookings.review_upcoming_approved');
    Route::get('/upcomingBusyReview', 'BookingController@upcomingBusyReview')->name('bookings.review_upcoming_busy');
    Route::get('/oldReview', 'BookingController@oldReview')->name('bookings.review_past');
    //Final Screening
    Route::get('final', 'FinalscreeningController@index')->name('screening.index');
    Route::get('{id?}/final', 'FinalscreeningController@index')->name('screening.show');
    Route::post('final/update', 'FinalscreeningController@update')->name('screening.update');
    Route::post('final/save', 'FinalscreeningController@save')->name('screening.store');
    Route::post('final/addzoom', 'FinalscreeningController@addzoom')->name('screening.zoom_add');
    Route::get('final/results/{id?}', 'FinalscreeningController@results')->name('screening.results_view');
    Route::get('final/{step_id}/{company_id}', 'FinalscreeningController@view')->name('screening.form_view');
    Route::get('final/get/{sid}/{pid}', 'FinalscreeningController@getusers')->name('screening.users_view_details');
    Route::post('final/delsession/{id}', 'FinalscreeningController@delsession')->name('screening.sessions_delete');
    Route::get('/send-whatsapp', 'CyclesController@testSend')->name('whatsapp.send');

    //Program List
    Route::resource('programlist', 'ProgramController');
    Route::post('programlist/create', 'ProgramController@edit')->name('programs.create');
    Route::get('programlist/edit/{id}', 'ProgramController@edit')->name('programs.edit');
    Route::get('programlist/show/{id}', 'ProgramController@show')->name('programs.view');
    Route::post('programlist/Email', 'ProgramController@SentEmail')->name('programs.send_email');
    Route::get('/export-Prog', 'ProgramController@exportProg')->name('programs.export');
    Route::get('programlist/report/{id}', 'ProgramController@programReport')->name('programs.report');

    /* Start of Currencies Routes */
    Route::patch('currencies/UpdateCurrency', 'CurrencyController@UpdateAllCurrency')->name('currencies.rates_update_all');
    Route::resource('currencies', 'CurrencyController')->except(['show']);
    /* End of Currencies Routes */

    // ===============> Mentor Dashboard Routes <===============
    Route::group(['prefix' => 'mentor', 'namespace' => 'Mentor'], function () {
        Route::get('mentorshipSessions', 'MentorshipController@index')->name('mentorships.sessions_list_detailed');
        Route::get('meetingRedirect/{mentorship_request_id}', 'MentorshipController@meetingRedirect')->name('integrations.meeting_redirect');
        Route::resource('mentorshipTasks', 'MentorshipTaskController');
        Route::post('mentorshipTasks/add-task', 'MentorshipTaskController@addTask')->name('mentorshipTasks.add_task');
        Route::post('mentorshipTasks/move-task', 'MentorshipTaskController@moveTask')->name('mentorshipTasks.move_task');
        Route::post('mentorshipTasks/edit-task', 'MentorshipTaskController@updateTask')->name('mentorshipTasks.edit_task');
        Route::post('mentorshipTasks/remove-task', 'MentorshipTaskController@removeTask')->name('mentorshipTasks.remove_task');
        Route::post('mentorshipTasks/post-comment', 'MentorshipTaskController@addComment')->name('mentorshipTasks.post_comment');
        Route::get('mentorshipTasks/display/{id}', 'MentorshipTaskController@display')->name('mentorshipTasks.display');
        Route::post('mentorshipTasks/upload-attachments', 'MentorshipTaskController@attach')->name('mentorshipTasks.upload_attachments');
        Route::post('mentorshipTasks/delete-attachments', 'MentorshipTaskController@deAttach')->name('mentorshipTasks.delete_attachments');

        Route::post('mentorshipTasks/send/comment', 'MentorshipTaskController@sendComment')->name('mentorship_tasks.comment');
        Route::post('mentorshipTasks/updateW', 'MentorshipTaskController@updateWith')->name('mentorship_tasks.update_special');

    });



    // =====================> Startup Routes <===========

    Route::group(['prefix' => 'startup', 'as' => 'startup.', 'namespace' => 'Startup'], function () {
        Route::get('mentorshipTasks', 'MentorshipTaskController@home')->name('mentorship_tasks.dashboard.view');
        Route::get('mentorshipTasks/{mentorship_id}', 'MentorshipTaskController@index')->name('startup.mentorshipTasks.index');
        Route::get('mentorshipTasks/show/{mentorshipTask}', 'MentorshipTaskController@show')->name('startup.mentorshipTasks.show');
        Route::post('mentorshipTasks/complete/{mentorshipTask}', 'MentorshipTaskController@complete')->name('mentorship_tasks.complete_mine');
        Route::post('mentorshipTasks/send/comment', 'MentorshipTaskController@sendComment')->name('mentorship_tasks.comment_mine');

        Route::resource('fundingRounds', 'FundingRoundController')->except(['index']);
    });

    // =================> Calender Routes <==================
    Route::group(['prefix' => 'calendar', 'namespace' => 'Calender'], function () {
        Route::get('/', 'CalenderController@index')->name('calendar.events_list');
        Route::post('calendarViewCall', 'CalenderController@ViewCall')->name('calendar.ajax');
        // ===>  google sync <=====
        Route::get('login/google', 'GoogleCalendarController@redirectToGoogle')->name('integrations.calendar_google_login');
        Route::get('callback/google', 'GoogleCalendarController@handleGoogleCallback')->name('integrations.calendar_google_callback');
        Route::get('sync-events', 'GoogleCalendarController@syncEvents')->name('integrations.calendar_google_sync');

        // ====> Microsoft sync <=====
        Route::get('login/microsoft', 'MicrosoftCalendarController@redirectToMicrosoft')->name('integrations.calendar_microsoft_login');
        Route::get('callback/microsoft', 'MicrosoftCalendarController@handleMicrosoftCallback')->name('integrations.calendar_microsoft_callback');
        Route::get('microsoft-sync-events', 'MicrosoftCalendarController@syncEvents')->name('integrations.calendar_microsoft_sync');
    });

    /* Start of Gemini SWOT Analysis Routes */
    Route::get('/swot-analysis', [GeminiController::class, 'showSwotAnalysis'])->name('swot.view');
    Route::post('/swot-analysis/generate', [GeminiController::class, 'generateSwotAnalysis'])->name('swot.generate');
    Route::post('/swot-analysis/save', [GeminiController::class, 'saveSwotAnalysis'])->name('swot.save');
    Route::get('/swot-analysis/export', [GeminiController::class, 'exportToPdf'])->name('swot.export');
    /* End of Gemini SWOT Analysis Routes */


    /* Start of Projects Routes */
    Route::post('projects/{project}/link-programs', 'ProjectController@linkPrograms')->name('projects.programs_link');
    Route::delete('projects/{project}/unlink-program/{cycle}', 'ProjectController@unlinkProgram')->name('projects.programs_unlink');
    Route::post('/projects/{project}/add-team-members', 'ProjectController@addTeamMembers')->name('projects.team_add_member');
    Route::delete('/projects/{project}/remove-team-member/{teamMember}', 'ProjectController@removeTeamMember')->name('projects.team_remove_member');
    Route::resource('projects', 'ProjectController');
    /* End of Projects Routes */

    Route::get('/license/activate', [LicenseController::class, 'showActivateForm'])->name('license.activate_view_form');
    Route::post('/license/activate', [LicenseController::class, 'activate'])->name('license.activate_execute');

    /* 
     Templates Routes */
    Route::prefix('admin')->group(function () {
        // Define specific routes BEFORE resource routes to avoid conflicts
        Route::get('notification-templates/variables', 'NotificationTemplateController@getVariables')->name('notification-templates.variables');
        Route::get('notification-templates/{notificationTemplate}/preview', 'NotificationTemplateController@preview')->name('notification-templates.preview');
        Route::post('notification-templates/{notificationTemplate}/clone', 'NotificationTemplateController@clone')->name('notification-templates.clone');
        Route::post('notification-templates/{notificationTemplate}/test-send', 'NotificationTemplateController@testSend')->name('notification-templates.test-send');
        
        // Resource routes come last
        Route::resource('notification-templates', 'NotificationTemplateController');
        
        /* Notification Triggers Routes - MOVED INSIDE DASHBOARD GROUP */
        // Define specific routes BEFORE resource routes to avoid conflicts
        Route::get('notification-triggers/events', 'NotificationTriggerController@getEvents')->name('notification-triggers.events');
        Route::get('notification-triggers/recipient-types', 'NotificationTriggerController@getRecipientRuleTypes')->name('notification-triggers.recipient-types');
        Route::get('notification-triggers/sample-conditions', 'NotificationTriggerController@getSampleConditions')->name('notification-triggers.sample-conditions');
        Route::post('notification-triggers/{notificationTrigger}/toggle-active', 'NotificationTriggerController@toggleActive')->name('notification-triggers.toggle-active');
        Route::post('notification-triggers/{notificationTrigger}/clone', 'NotificationTriggerController@clone')->name('notification-triggers.clone');
        Route::post('notification-triggers/{notificationTrigger}/test', 'NotificationTriggerController@test')->name('notification-triggers.test');
        
        // Resource routes come last
        Route::resource('notification-triggers', 'NotificationTriggerController');
    });



     /* Start of Activities Routes */
    Route::get('/activities', [ActivityControlller::class, 'index'])->name('activities.index');
    Route::post('/log-activity', [ActivityControlller::class, 'logActivity'])->name('activity.log');
    Route::delete('/activities/{id}', [ActivityControlller::class, 'destroy'])->name('activities.destroy');
    
    // Security actions for suspicious logins
    Route::post('/activities/force-logout', [ActivityControlller::class, 'forceLogout'])->name('activities.force-logout');
    Route::post('/activities/reset-password', [ActivityControlller::class, 'resetPassword'])->name('activities.reset-password');
    Route::post('/activities/block-ip', [ActivityControlller::class, 'blockIp'])->name('activities.block-ip');
    Route::get('/activities/load-more', [ActivityControlller::class, 'loadMoreActivities'])->name('activities.load-more');
    /* End of Activities Routes */


    // ===============> Reports <============
    Route::group(['prefix' => 'reports'], function () {
        Route::get('/', 'ReportController@index')->name('reports.list');
        // ======> Ajax Route
        Route::get('view/{type?}', 'ReportController@View')->name('reports.view.ajax');
        // ======> Export Routes
        Route::post('export/final-screening', 'ReportController@exportFinalScreening')->name('reports.export.finalScreening');
        Route::post('export/screening', 'ReportController@exportScreening')->name('reports.export.screening');
    });
    Route::get('reports/show-survey/{id}', 'ReportController@surveyReport');
    //================> End Reports <============

    Route::get('/importer', [ImporterController::class, 'index'])->name('importer.index');
    Route::post('/importer/upload', [ImporterController::class, 'upload'])->name('importer.upload');
    Route::post('/importer/import', [ImporterController::class, 'import'])->name('importer.import');
    Route::get('/importer/organizations', [ImporterController::class, 'getOrganizations'])->name('importer.organizations');


    Route::post('/notifications/mark-all-read', function () {
        $user = Auth::user();
        if ($user) {
            $user->unreadNotifications()->get()->each->markAsRead();
            return response()->json(['success' => true, 'message' => 'All notifications marked as read']);
        }
        return response()->json(['success' => false, 'message' => 'User not authenticated'], 401);
    });

    Route::post('/notifications/mark-read/{id}', function ($id) {
        $user = Auth::user();
        if ($user) {
            $notification = $user->notifications()->findOrFail($id);
            $notification->markAsRead();
            return response()->json(['success' => true, 'message' => 'Notification marked as read']);
        }
    });
});



///// Reports routes
//Route::group(['prefix' => 'reports',  'middleware' => ['auth', 'verified']], function(){
//Route::post('users','ExcelController@ExUsers');
//Route::post('companies','ExcelController@ExCompanies');
//route::get('/','ExcelController@index');
//route::get('events/{id}','ExcelController@events');
//route::get('audit-log','ExcelController@auditlog');
//});

//// Facbook & Google Login
Route::get('/auth/redirect/{provider}', 'SocialController@redirect');
Route::get('/callback/{provider}', 'SocialController@callback');
Route::get('/auth/google', 'SocialController@redirectToGoogle');
Route::get('/auth/google/callback', 'SocialController@handleGoogleCallback');

Route::get('/auth/microsoft/redirect/{mentorship_id}',  'MicrosoftController@redirect')->name('integrations.auth_microsoft_redirect');
Route::get('/auth/microsoft/callback',  'MicrosoftController@callback')->name('integrations.auth_microsoft_callback');

// --------------------> Linked In <-------------------
Route::get('/auth/linkedin/{data?}', 'SocialController@redirectToLinkedIn')->name('integrations.auth_linkedin_login');
Route::get('/auth/linkedin-openid/callback', 'SocialController@handleLinkedInCallback')->name('integrations.auth_linkedin_callback');

Route::get('/{org_slug}/join/{step_slug}', 'ExternalCyclesController@publicJoin')->name('public.join');
Route::get('/{org_slug}/program/{program_slug}', 'ExternalCyclesController@publicProgramJoin')->name('public.program_join');
Route::get('/{org_slug}/program/{program_slug}/apply', 'ExternalCyclesController@publicProgramApply')->name('public.program_apply');
Route::post('/{org_slug}/program/{program_slug}/validate_email', 'ExternalCyclesController@validateEmail');
Route::post('/{org_slug}/program/{program_slug}/authenticate', 'ExternalCyclesController@authenticateUser');
Route::post('/{org_slug}/program/{program_slug}/register', 'ExternalCyclesController@registerUser');

if (request()->secure()) {
    URL::forceScheme("https");
}
