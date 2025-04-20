<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GeminiController;
use App\Http\Controllers\LicenseController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\Mentor\MentorshipTaskController;
use App\Http\Controllers\Judger\JudgerScreeningController;
/* Disable frontend */ // Route::get('/', 'HomeController@index')->name('admin'); ///

Route::get('test', function () {});
Route::get('default-permissions', function() { App\Permission::default_permissions(); });
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
 Route::get('/2otp', function () {
    return redirect('/dashboard');
})->middleware(['auth', '2fa']);

Auth::routes(['verify' => true]);
Route::get('organizations/set-password/{invitationId}', 'SettingsController@showSetPasswordForm')
    ->name('set-password-form');
Route::post('organizations/settings/accept/{invitationId}', 'SettingsController@acceptInvitation')->name('accept-invitation');

Route::group(['prefix' => 'dashboard', 'middleware' => ['auth', 'verified', '2fa']], function () {
    ####################### Overall System Admin Routes ###############
    // All Logged users
    Route::get('/', 'HomeController@index')->name('admin');
    Route::get('/dashboard', 'HomeController@index')->name('dashboard');
    Route::get('/dark', 'HomeController@dark');
    Route::get('/import-users', 'UserController@showImportForm')->name('import.form');
    Route::post('/import-users', 'UserController@import')->name('import.users');

    //Roles
    Route::get('documentation/view/{id?}', 'HomeController@help');
    Route::resource('documentation', 'DocumentationController');

    // Judger
    Route::get('judger/screening','Judger\JudgerScreeningController@index');
   
    Route::get('calendar/{id}/edit', 'SettingsController@editcal')->name('calendar.edit');
    Route::post('calendar/{id}/update', 'SettingsController@updatecal')->name('calendar.update');
    Route::get('calendar/{id}/delete', 'SettingsController@delcal')->name('calendar.destroy');
    Route::post('calendar/view', 'SettingsController@viewcal')->name('calendar.show');
    // Sliders Routes
    Route::get('add_slide', 'SlidersController@add_slide')->name('slides.create');
    Route::post('store_slide', 'SlidersController@store_slide')->name('slides.store');
    Route::any('slides', 'SlidersController@slides')->name('slides.index');
    Route::get('edit_slide/{id}', 'SlidersController@editslide')->name('slides.edit');
    Route::post('update_slide/{id}', 'SlidersController@updateslide')->name('slides.update');
    Route::get('delete_slide/{id}', 'SlidersController@deleteslide')->name('slides.destroy');
    // Menus Routes
    Route::get('add_menu', 'MenusController@add_menu')->name('menus.create');
    Route::post('store_menu', 'MenusController@store_menu')->name('menus.store');
    Route::any('menus', 'MenusController@myMenus')->name('menus.index');
    Route::get('edit_menu/{id}', 'MenusController@editMenu')->name('menus.edit');
    Route::post('update_menu/{id}', 'MenusController@updatemenu')->name('menus.update');
    Route::get('delete_menu/{id}', 'MenusController@deleteMenu')->name('menus.destroy');
    // Posts Routes
    Route::get('addpost', 'PostsController@add_post')->name('posts.create');
    Route::post('storepost', 'PostsController@store_post')->name('posts.store');
    Route::any('posts', 'PostsController@myposts')->name('posts.index');
    Route::get('editpost/{id}', 'PostsController@editpost')->name('posts.edit');
    Route::post('updatepost/{id}', 'PostsController@updatepost')->name('posts.update');
    Route::get('deletepost/{id}', 'PostsController@deletepost')->name('posts.destroy');
    // Pages Routes
    Route::get('addpage', 'PagesController@add_page')->name('pages.create');
    Route::post('storepage', 'PagesController@store_page')->name('pages.store');
    Route::any('pages', 'PagesController@mypages')->name('pages.index');
    Route::get('editpage/{id}', 'PagesController@editpage')->name('pages.edit');
    Route::post('updatepage/{id}', 'PagesController@updatepage')->name('pages.update');
    Route::get('deletepage/{id}', 'PagesController@deletepage')->name('pages.destroy');
    Route::get('editmypage', 'PagesController@editmypage')->name('pages.editMyPage');
    // Pages Events
    Route::get('addevent', 'EventsController@add_event')->name('events.create');
    Route::post('storeevent', 'EventsController@store_event')->name('events.store');
    Route::any('allevents', 'EventsController@myevent')->name('events.index');
    Route::get('editevent/{id}', 'EventsController@editevent')->name('events.edit');
    Route::post('updateevent/{id}', 'EventsController@updateevent')->name('events.update');
    Route::get('deleteevent/{id}', 'EventsController@deleteevent')->name('events.destroy');
    // Settings Routes
    Route::any('settings', 'SettingsController@get_settings')->name('settings.list');
    Route::post('update_settings', 'SettingsController@update_settings')->name('settings.save');
    Route::any('setaboutus', 'SettingsController@get_aboutus');
    Route::post('updateaboutus', 'SettingsController@update_aboutus');
    Route::any('setcontactus', 'SettingsController@get_contactus');
    Route::post('updatecontactus', 'SettingsController@update_contactus');
    Route::put('settings/Updateone', 'SettingsController@update_settingsone')->name('updateone');
    Route::put('settings/Updatelogo', 'SettingsController@update_settinglogo')->name('updatelogo');
    Route::put('settings/Updatetwo', 'SettingsController@update_settingstwo')->name('updatetwo');
    Route::put('settings/UpdateMail', 'SettingsController@update_settingsmail')->name('updateMail');
    Route::put('settings/UpdatePush', 'SettingsController@update_settingspush')->name('updatePush');
    Route::put('settings/UpdateMeeting', 'SettingsController@update_settingsMeeting')->name('updateMeeting');
    Route::put('settings/UpdateLogin', 'SettingsController@update_settingsLogin')->name('updateSMLogin');
    Route::put('settings/UpdateimgHome', 'SettingsController@update_settingsImgHome')->name('updateSMImgHome');
    Route::put('settings/UpdateimgAchiev', 'SettingsController@update_settingsAchievements')->name('updateSMAchiev');
    Route::put('settings/UpdateWhatsApp', 'SettingsController@update_settingsWhatsapp')->name('updateSMWhatsApp');


    Route::get('organizations/settings', 'SettingsController@showSettings')->name('organization-settings');
    Route::put('organizations/settings/updateMeeting', 'SettingsController@meetingSetting')->name('meeting-settings');
    Route::put('organizations/settings/socialLogin', 'SettingsController@socialSetting')->name('social-settings');
    Route::put('organizations/settings/general', 'SettingsController@generalSetting')->name('general-settings');
    Route::post('organizations/settings/invite', 'SettingsController@inviteUser')->name('invite-user');
    Route::post('organizations/settings/resend-invitation/{invitationId}', 'SettingsController@resendInvitation')->name('resend-invitation');
    Route::delete('organizations/settings/cancel-invitation/{invitationId}', 'SettingsController@cancelInvitation')->name('cancel-invitation');

    //services categories Manager routes
    Route::resource('serviceCategories', 'ServiceCategoryController');
    //Services Manager routes
    Route::resource('services', 'ServiceController');
    //Services requests
    Route::get('requests/all', 'ServiceController@requests')->name('requests');
    Route::get('requests/old', 'ServiceController@oldrequests')->name('oldrequests');
    Route::post('requests/approve', 'ServiceController@approve');
    Route::get('requests/mine', 'ServiceController@myrequests')->name('myrequests');
    //Services Directory
    Route::get('directory', 'HomeController@directory')->name('directory');
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
    Route::get('/myProfile', 'ClientProfileController@edit')->name('my_profile');
    Route::get('/show/myProfile', 'ClientProfileController@show')->name('show_my_profile');
    Route::post('/myProfile', 'ClientProfileController@update')->name('update_my_profile');
    Route::post('/myProfile/bio', 'ClientProfileController@Updatebio')->name('updatebio_my_profile');
    Route::post('/myProfile/password', 'ClientProfileController@updatePassword')->name('update_my_profile_password');
    Route::get('/show/UserProfile/{id}', 'ClientProfileController@showMintorprofile')->name('show_mentor_profile');
    Route::get('/show/Mentors', 'ClientProfileController@showMintors')->name('mentors.list');
    Route::get('/confirm-email/{token}', 'ClientProfileController@confirmEmailUpdate')->name('email.confirm');
    
    Route::group(['prefix'=>'2fa'], function(){
        Route::get('/','ClientProfileController@show2faForm');
        Route::post('/generateSecret','ClientProfileController@generate2faSecret')->name('2fa.generate2faSecret');
        Route::post('/enable2fa','ClientProfileController@enable2fa')->name('2fa.enable2fa');
        Route::post('/disable2fa','ClientProfileController@disable2fa')->name('2fa.disable2fa');
    
        // 2fa middleware
        Route::post('/2faVerify', function () {
            return redirect('/dashboard');
        })->name('2fa.2faVerify')->middleware('2fa');
    });
    
    

    Route::post('/save-phone-session', 'ClientProfileController@savePhoneSession')->name('save.phone.session');
    Route::post('/send-code', 'ClientProfileController@sendCode')->name('send.code');
    Route::post('/verify-code', 'ClientProfileController@verifyCode')->name('verify.code');

    Route::resource('Tags', 'TagsController');
    Route::resource('Labels', 'LabelController');
    Route::resource('TimeSheet', 'TimeSheetController');
    Route::resource('Category', 'CatigoriesController'); // category in TimeSheet Folder
    Route::resource('subcategories', 'SubCategoryController');

    Route::get('/show/FinanceTimeSheet', 'TimeSheetController@Finance')->name('show_finance_time_sheet');
    Route::get('/export-FinanceTS', 'TimeSheetController@exportFTS')->name('exportFTS');
    Route::get('/UserTimeSheet', 'TimeSheetController@ClockUsers')->name('UserTimeSheet');
    Route::post('/UserTimeSheet/{id}', 'TimeSheetController@updateClockUser')->name('UserClockUpdate');


    //Assessments
    Route::get('/question-assessment', 'AssessmentController@assessment')->name('question-assessment');
    Route::get('/assessment-create', 'AssessmentController@MakeAssessment')->name('make_new_assessment_to_user');
    Route::get('/analysis-assessment', 'AssessmentController@analysisAssessment')->name('analysis-assessment');;
    Route::get('/history-assessment', 'AssessmentController@historyAssessment')->name('history-assessment');;
    Route::get('/category-assessment', 'CatigoriesController@index')->name('category-assessment');
    Route::post('/answer-assessment', 'AssessmentController@AnswerAssessment')->name('answer-assessment');
    Route::get('/exportAssessment-pdf', 'AssessmentController@generatePDF')->name('assessment.pdf');
    Route::get('/exportAssessment-pdf-all', 'AssessmentController@exportPDF')->name('assessment.pdf-all');


    Route::get('/assessment-admin/{id}', 'AssessmentController@assessmentAdmin')->name('question-assessment_admin');

    // LFM File Manager Imges
    Route::get('/LFMGet/{idUser?}/{files?}', 'organizationsController@GetLFM')->name('LFMGet');

    //Client's company
    Route::resource('organizations', 'organizationsController');

    //Assessment
    Route::resource('questions', 'QuestionController');
    Route::resource('weights', 'WeightController');
    Route::resource('assessments', 'AssessmentController');

    Route::get('get_subcategory', 'QuestionController@category')->name('get_subcategory_by_category');
    // Route::resource('answerOption', 'AnswerOptionController');

    //Company profile
    Route::get('/companyProfile/{id}', 'ClientCompanyController@show')->name('company_profile');
    Route::get('/myCompany/{id?}', 'ClientCompanyController@editOrCreate')->name('my_company');
    Route::post('/myCompany/{id?}', 'ClientCompanyController@updateOrStore')->name('update_my_company');
    Route::post('/AddCompanyTeams/{id?}', 'ClientCompanyController@AddTeams')->name('AddTeams_Comp');
    Route::post('/AddCompanyEdit/{id?}', 'ClientCompanyController@EditTeams')->name('EditTeams_Comp');
    Route::post('/AddCompanyDelete/{id?}', 'ClientCompanyController@DeleteTeams')->name('DeleteTeams_Comp');
    Route::post('/AddCompanyMileStone/{id?}', 'ClientCompanyController@AddMileStone')->name('AddMileStone_Comp');
    Route::post('/EditCompanyMileStone/{id?}', 'ClientCompanyController@EditMileStone')->name('EditMileStone_Comp');
    Route::post('/DeleteCompanyMileStone/{id?}', 'ClientCompanyController@DeleteMileStone')->name('DeleteMileStone_Comp');
    Route::post('/delete-document', 'ClientCompanyController@deleteDocument')->name('delete.document');
    


    //like a service
    Route::post('/likeService', 'ClientCompanyController@likeService')->name('like_service');
    //Users  Manager routes
    Route::resource('users', 'UserController');
    Route::post('/users/password', 'UserController@updatePassword')->name('update_account_password');
    Route::get('/companies', 'UserController@companies')->name('users.companies');
    //Cities
    Route::resource('cities', 'CityController');
    Route::get('get_states', 'CityController@states')->name('get_states_by_country');

    //FileManager
    Route::get('/myFiles', 'FilemanagerController@index')->name('myFiles.index');
    Route::get('/uploads/files/{id}/{path}', 'FilemanagerController@showfile')->where('path', '.*')->name('myFiles.showFile');
    Route::get('/uploads/photos/{id}/{path}', 'FilemanagerController@showimage')->where('path', '.*')->name('myFiles.showImage');
    Route::get('/uploads/tasks/{id}/cards/{card_id}/{path}', [MentorshipTaskController::class, 'attachments'])->where('path', '.*');
    //Explore private files
    Route::get('/private/{id}/{path}', 'FilemanagerController@showprivate')->where('path', '.*');
    //FileManager for training
    Route::get('/training/allfiles', 'FilemanagerController@allfiles');
    Route::get('/training/files/{path}', 'FilemanagerController@trainingfile')->where('path', '.*');
    Route::get('/training/photos/{path}', 'FilemanagerController@trainingimage')->where('path', '.*');
    //SendMessage
    Route::post('/send_message', 'MessengerController@sendmsg');
    Route::get('/sendmessage', 'MessengerController@sendmessage')->name('sendmessage');
    Route::post('/sendmessage', 'MessengerController@savemessage');
    Route::post('/sendReplymessage', 'MessengerController@sendReplyMessage')->name('messages.sendReplymessage');
    Route::get('/get-users-by-cycle', 'MessengerController@getUsersByProgram')->name('messages.recipients_list_by_program');

    //ShowMessages
    Route::get('/messages', 'MessengerController@index')->name('messages.index');
    Route::get('/messages/{id}', 'MessengerController@show')->name('messages.show');
    Route::post('/draftMessage', 'MessengerController@draftmessage')->name('messages.draftmessage');
    Route::post('/deleteDraft', 'MessengerController@deleteDraft')->name('messages.deleteDraft');
    Route::get('/getAllDraft', 'MessengerController@getAllDrafts')->name('messages.getAllDrafts');
    Route::get('/outbox', 'MessengerController@outbox')->name('outbox');
    Route::get('/openmsg', 'MessengerController@openmsg');
    Route::post('/mread', 'MessengerController@makeread');
    Route::post('/messages/delete', 'MessengerController@destroy')->name('messages.delete');
    Route::get('/messages/trash', 'MessengerController@MessengerTrash')->name('messages.trash');
    Route::post('/messages/toggleStarredStatus', 'MessengerController@toggleStarredStatus')->name('messages.ToggleStarred');
    Route::get('/messages/getAllStarred', 'MessengerController@getAllStarred')->name('messages.Starred');
    Route::post('/messages/toggleMarkReadStatus', 'MessengerController@toggleMarkReadStatus')->name('messages.ToggleMarkRead');
    //Contact US Messages
    Route::get('mymsg', 'SettingsController@mymsg')->name('messages.view');
    Route::post('msgreply', 'SettingsController@msgreply')->name('messages.reply');
    Route::get('deletemsg/{id}', 'SettingsController@deletemsg')->name('messages.delete');
    Route::post('makeread/{id}', 'SettingsController@makeread')->name('messages.mark-read');
    Route::post('makeunread/{id}', 'SettingsController@makeunread')->name('messages.mark-unread');
    Route::get('openonemsg/{id}', 'SettingsController@openonemsg')->name('messages.show');
    //products categories Manager routes
    Route::resource('productsCategories', 'ProductCategoryController');

    //products Manager routes
    Route::resource('products', 'ProductController');
    //publish
    Route::any('products/publish/{id}', 'ProductController@publish');
    //Interests
    Route::any('interests', 'HomeController@myinterests')->name('myinterests');
    //Audit Logs
    Route::resource('audit-logs', 'AuditLogsController', ['except' => ['create', 'store', 'edit', 'update']]);
    //Permissions
    Route::resource('permissions', 'PermissionsController');
    //Roles
    Route::resource('roles', 'RolesController');
    //Faqs
    Route::resource('faqs', 'FaqsController');
    //BuilderForm Builder
    Route::get('show-builder/{id?}', 'FormBuilderController@showBuilder')->name('show.builder');
    Route::get('show-form/{id}', 'FormBuilderController@showForm')->name('show.form');
    Route::post('form/{id}/save-form', 'FormBuilderController@saveForm')->name('save.form');
    Route::post('builder/{set}', 'FormBuilderController@modifyForm');
    Route::post('submit-form', 'FormBuilderController@handleFormRequest')->name('submit.form');
    Route::get('builder/organization/{id?}', 'FormBuilderController@builderByOrganization');
    //Cycles
    Route::get('cycles/{id?}', 'CyclesController@index')->name('index.cycle');
    Route::get('cycles/{id}/registered', 'CyclesController@users')->name('cycles.users');
    Route::post('cycles/modify', 'CyclesController@modify')->name('cycles.modify');
    Route::get('cycles/view/{c?}/{s?}', 'CyclesController@view');
    Route::post('cycles/save', 'CyclesController@saveForm');
    Route::get('cycles/my/{id}', 'CyclesController@my')->name('cycles.my');
    Route::post('change/cyc', 'CyclesController@changAll');
    Route::get('/export-cyclesComp', 'CyclesController@exportcyclesComp');

    //Screening
    Route::get('screening', 'ScreeningController@index');
    Route::post('screening/update', 'ScreeningController@update');
    Route::post('screening/save', 'ScreeningController@save');
    Route::post('screening/add', 'ScreeningController@add');
    Route::get('screening/results/{id?}', 'ScreeningController@results');
    Route::get('{id?}/screening', 'ScreeningController@index');
    Route::get('screening/{fid}/{cid}', 'ScreeningController@view')->name('screening.view');
    Route::get('screening/get/{fid}/{sid}/{pid}', 'ScreeningController@getusers');
    Route::post('screening/changestep', 'ScreeningController@changestep');
    Route::get('/export-Result', 'ScreeningController@exportresultScreen');

    //ZOOM
    Route::get('zoom/all', 'MeetingController@showall');
    Route::get('zoom/{id}/get', 'MeetingController@show');
    Route::post('zoom/store', 'MeetingController@store');
    Route::post('zoom/{id}/update', 'MeetingController@update');
    Route::post('zoom/{id}/delete', 'MeetingController@destroy');
    Route::get('zoom/{id}/registrants', 'MeetingController@registrants');
    //Mentors
    Route::post('mentors/modify', 'MentorsController@modify')->name('mentors.modify');
    Route::post('mentorship/add', 'MentorsController@add')->name('mentors.add');
    Route::get('mentorship/assign/{id?}', 'MentorsController@list')->name('mentorship.list');
    Route::post('mentorship/assign/{id}', 'MentorsController@assign')->name('mentorship.assign');
    Route::get('mentorship/view/{id?}', 'MentorsController@index')->name('mentorship.view');
    Route::get('mentorship/{id?}/surveys', 'MentorsController@list_surveys')->name('mentorship.surveys');
    Route::get('mentorship/{id?}/surveys/create', 'MentorsController@create_survey')->name('mentorship.create_survey');
    Route::post('mentorship/{id?}/surveys/store', 'MentorsController@store_survey')->name('mentorship.store_survey');
    Route::get('mentorship/{id?}/surveys/{survey?}/edit', 'MentorsController@edit_survey')->name('mentorship.edit_survey');
    Route::patch('mentorship/{id?}/surveys/{survey?}/update', 'MentorsController@update_survey')->name('mentorship.update_survey');
    Route::delete('mentorship/{id?}/surveys/{survey?}/destroy', 'MentorsController@destroy_survey')->name('mentorship.destroy_survey');
    Route::get('mentorship/sessions', 'MentorsController@sessions')->name('mentorship.sessions');
    Route::post('mentorship/session', 'MentorsController@getSession')->name('mentorship.getSession');
    Route::post('mentorship/modify', 'MentorsController@modifySession')->name('mentorship.modifySession');
    Route::post('mentorship/addwait', 'MentorsController@addWait')->name('mentorship.addWait');
    Route::get('mentorship/mine', 'MentorsController@mine')->name('my.mentorships');
    Route::post('mentorship/rate', 'MentorsController@rate')->name('mentorship.rate');
    Route::post('mentorship/delsession/{id}', 'MentorsController@delSession')->name('mentorship.delSession');
    Route::get('/export-mentorship', 'MentorsController@exportMentorship')->name('mentorship.exportMentorship');
    Route::post('/mentorship/getDates', 'MentorsController@MentorDates')->name('mentorship.getDates');



    //Training
    Route::get('training/all/{id?}', 'TrainingController@all')->name('training.all');
    Route::get('training/edit/{id}', 'TrainingController@edit')->name('training.edit');
    Route::post('training/store', 'TrainingController@store')->name('training.store');
    Route::post('training/delete/{id}', 'TrainingController@delete')->name('training.delete');
    Route::post('training/delcontent/{id}', 'TrainingController@delcontent')->name('training.delcontent');
    Route::get('training/addcontent/{id}', 'TrainingController@addcontent')->name('training.addcontent');
    Route::get('training/editcontent/{id}', 'TrainingController@editcontent')->name('training.editcontent');
    Route::get('training/viewcontent/{id}', 'TrainingController@viewcontent')->name('training.viewcontent');
    Route::post('training/upload', 'TrainingController@upload')->name('training.upload');
    Route::post('training/storecontent', 'TrainingController@storecontent')->name('training.storecontent');
    Route::get('training/my', 'TrainingController@mysessions')->name('training.my');
    Route::get('training/my/{id}', 'TrainingController@my')->name('training.my');
    Route::get('training/my/{id}/quiz/{quiz_id}', 'TrainingController@myQuiz')->name('quiz.overview');
    Route::get('training/my/{id}/quiz/{quiz_id}/responses/{response_id}', 'TrainingController@myQuizResponses')->name('quiz.response');
    Route::post('training/my/{id}/quiz/{quiz_id}', 'TrainingController@doQuiz')->name('quiz.start');
    Route::get('training/EditTR/{id}', 'TrainingController@editTR')->name('training.editTR');
    Route::get('training/viewCourse/{id}', 'TrainingController@showCourse')->name('training.viewCourse');


    Route::get('training/results/{id?}', 'TrainingController@results')->name('training.results');
    Route::get('training/sessions/{id?}', 'TrainingController@sessions')->name('training.sessions');
    Route::post('training/addsession', 'TrainingController@addsession')->name('training.addsession');
    Route::post('training/delsession/{id}', 'TrainingController@delsession')->name('training.delsession');
    Route::post('training/session', 'TrainingController@session')->name('training.session');
    Route::post('training/editsession', 'TrainingController@editsession')->name('training.editsession');
    Route::post('trainings/getdsc/{id}', 'TrainingController@getdsc')->name('trainings.getdsc');
    Route::get('/export-Tresult', 'TrainingController@exportTresult')->name('training.exportTresult');
    Route::post('trainings/change/Tresult', 'TrainingController@changAllTL')->name('training.changAllTL');
    Route::get('training/create', 'TrainingController@create')->name('training.create');
    Route::get('training/{id?}/surveys', 'TrainingController@list_surveys')->name('training.surveys');
    Route::get('training/{id?}/surveys/create', 'TrainingController@create_survey')->name('training.create_survey');
    Route::post('training/{id?}/surveys/store', 'TrainingController@store_survey')->name('training.store_survey');
    Route::get('training/{id?}/surveys/{survey?}/edit', 'TrainingController@edit_survey')->name('training.edit_survey');
    Route::patch('training/{id?}/surveys/{survey?}/update', 'TrainingController@update_survey')->name('training.update_survey');
    Route::delete('training/{id?}/surveys/{survey?}/destroy', 'TrainingController@destroy_survey')->name('training.destroy_survey');
    Route::get('training/session-report/{id}', 'TrainingController@sessionReport')->name('training.session-report');
    Route::get('training/session-record/{id}', 'TrainingController@sessionRecord')->name('training.session-record');
    Route::get('training/session-attendance-report/{id}', 'TrainingController@attendanceReport')->name('training.attendance-report');
    Route::get('training/session-attendance-offline/{id}', 'TrainingController@attendanceOffline')->name('training.attendanceOffline');
    Route::post('training/QrCode-offline', 'TrainingController@QrCodeTraining')->name('training.createQrCode');
    Route::get('training/Attendance-offline/{id}', 'TrainingController@getattendanceOffline')->name('training.getattendanceOffline');


    Route::get('/Totalsurveys', 'MentorsController@total_surveys')->name('total.surveys');
    // questions bank
    Route::resource('question-categories', 'QuestionCategoriesController');
    Route::resource('questions-bank', 'QuestionsBankController');
    Route::resource('quizzes', 'quizzesController')->except(['show']);
    Route::get('quizzes/{quiz}/questions', 'quizzesController@get_questions')->name('quizzes.questions');
    Route::post('quizzes/{quiz}/questions', 'quizzesController@save_questions')->name('quizzes.saveQuestions');
    Route::get('quizzes/my', 'quizzesController@my')->name('quizzes.dashboard');

    Route::resource('surveys', 'surveysController')->except(['show']);
    Route::get('surveys/{survey}/questions', 'surveysController@get_questions')->name('surveys.questions');
    Route::get('surveys/{survey}/results', 'surveysController@get_results')->name('surveys.results');
    Route::post('surveys/{survey}/questions', 'surveysController@save_questions')->name('surveys.saveQuestions');
    Route::get('surveys/start/{id?}', 'surveysController@survey_taking')->name('surveys.taking');
    Route::post('surveys/start/{id?}', 'surveysController@survey_submit')->name('surveys.submit');

    //Rooms routes
    Route::resource('rooms', 'RoomsController');
    Route::get('/roomImages/{id}', 'RoomsController@roomImages')->name('roomImages');
    Route::get('/deleteImages/{id}', 'RoomsController@deleteImages')->name('deleteImages');
    Route::resource('booking', 'BookingController');
    Route::get('/bookingreview', 'BookingController@review')->name('bookingreview');
    Route::get('/bookingApprove/{id}/{action}', 'BookingController@approve')->name('bookingApprove');
    Route::get('/upcomingApprovedReview', 'BookingController@upcomingApprovedReview')->name('upcomingApprovedReview');
    Route::get('/upcomingBusyReview', 'BookingController@upcomingBusyReview')->name('upcomingBusyReview');
    Route::get('/oldReview', 'BookingController@oldReview')->name('oldReview');
    //Final Screening
    Route::get('final', 'FinalscreeningController@index')->name('screening.index');
    Route::get('{id?}/final', 'FinalscreeningController@index')->name('screening.show');
    Route::post('final/update', 'FinalscreeningController@update')->name('screening.update');
    Route::post('final/save', 'FinalscreeningController@save')->name('screening.store');
    Route::post('final/addzoom', 'FinalscreeningController@addzoom')->name('screening.addzoom');
    Route::get('final/results/{id?}', 'FinalscreeningController@results')->name('screening.results');
    Route::get('final/{step_id}/{company_id}', 'FinalscreeningController@view')->name('screening.openForm');
    Route::get('final/get/{sid}/{pid}', 'FinalscreeningController@getusers')->name('screening.getuser');
    Route::post('final/delsession/{id}', 'FinalscreeningController@delsession')->name('screening.delsession');


    //Program List
    Route::resource('programlist', 'ProgramController');
    Route::get('programlist/show/{id}', 'ProgramController@show')->name('programlist.show');
    Route::post('programlist/Email', 'ProgramController@SentEmail')->name('programlist.SentEmail');
    Route::get('/export-Prog', 'ProgramController@exportProg')->name('exportProg');

    /* Start of Currencies Routes */
    Route::patch('currencies/UpdateCurrency', 'CurrencyController@UpdateAllCurrency')->name('currencies.UpdateAllCurrency');
    Route::resource('currencies', 'CurrencyController')->except(['show']);
    /* End of Currencies Routes */

    // ===============> Mentor Dashboard Routes <===============
    Route::group(['prefix' => 'mentor', 'namespace' => 'Mentor'], function () {
        Route::get('mentorshipSessions', 'MentorshipController@index')->name('mentorshipSessions.index');
        Route::get('meetingRedirect/{mentorship_request_id}', 'MentorshipController@meetingRedirect')->name('meetingRedirect');
        Route::resource('mentorshipTasks', 'MentorshipTaskController');
        Route::post('mentorshipTasks/add-task', 'MentorshipTaskController@addTask');
        Route::post('mentorshipTasks/move-task', 'MentorshipTaskController@moveTask');
        Route::post('mentorshipTasks/edit-task', 'MentorshipTaskController@updateTask');
        Route::post('mentorshipTasks/remove-task', 'MentorshipTaskController@removeTask');
        Route::post('mentorshipTasks/post-comment', 'MentorshipTaskController@addComment');
        Route::get('mentorshipTasks/display/{id}', 'MentorshipTaskController@display');
        Route::post('mentorshipTasks/upload-attachments', 'MentorshipTaskController@attach');
        Route::post('mentorshipTasks/delete-attachments', 'MentorshipTaskController@deAttach');

        Route::post('mentorshipTasks/send/comment', 'MentorshipTaskController@sendComment')->name('mentorshipTasks.sendComment');
        Route::post('mentorshipTasks/updateW', 'MentorshipTaskController@updateWith')->name('mentorshipTasks.updateWith');
    });



    // =====================> Startup Routes <===========

    Route::group(['prefix' => 'startup', 'as' => 'startup.', 'namespace' => 'Startup'], function () {
        Route::get('mentorshipTasks', 'MentorshipTaskController@home')->name('mentorshipTasks.home');
        Route::get('mentorshipTasks/{mentorship_id}', 'MentorshipTaskController@index')->name('mentorshipTasks.index');
        Route::get('mentorshipTasks/show/{mentorshipTask}', 'MentorshipTaskController@show')->name('mentorshipTasks.show');
        Route::post('mentorshipTasks/complete/{mentorshipTask}', 'MentorshipTaskController@complete')->name('mentorshipTasks.complete');
        Route::post('mentorshipTasks/send/comment', 'MentorshipTaskController@sendComment')->name('mentorshipTasks.sendComment');

        Route::resource('fundingRounds', 'FundingRoundController')->except(['index']);
    });

    // =================> Calender Routes <==================
    Route::group(['prefix' => 'calendar', 'namespace' => 'Calender'], function () {
        Route::get('/', 'CalenderController@index')->name('calendar');
        Route::post('calendarViewCall', 'CalenderController@ViewCall')->name('calendarAjax');
        // ===>  google sync <=====
        Route::get('login/google', 'GoogleCalendarController@redirectToGoogle')->name('calender.login.google');
        Route::get('callback/google', 'GoogleCalendarController@handleGoogleCallback')->name('calender.callback.google');
        Route::get('sync-events', 'GoogleCalendarController@syncEvents')->name('calender.syncEvents');

        // ====> Microsoft sync <=====
        Route::get('login/microsoft', 'MicrosoftCalendarController@redirectToMicrosoft')->name('calender.login.microsoft');
        Route::get('callback/microsoft', 'MicrosoftCalendarController@handleMicrosoftCallback')->name('calender.callback.microsoft');
        Route::get('microsoft-sync-events', 'MicrosoftCalendarController@syncEvents')->name('calender.MicrosoftsyncEvents');
    });

    /* Start of Gemini SWOT Analysis Routes */
    Route::get('/swot-analysis', [GeminiController::class, 'showSwotAnalysis'])->name('swot.view');
    Route::post('/swot-analysis/generate', [GeminiController::class, 'generateSwotAnalysis'])->name('swot.generate');
    Route::post('/swot-analysis/save', [GeminiController::class, 'saveSwotAnalysis'])->name('swot.save');
    Route::get('/swot-analysis/export', [GeminiController::class, 'exportToPdf'])->name('swot.export');
    /* End of Gemini SWOT Analysis Routes */


    /* Start of Projects Routes */
    Route::post('projects/{project}/link-programs', 'ProjectController@linkPrograms')->name('projects.linkPrograms');
    Route::delete('projects/{project}/unlink-program/{cycle}', 'ProjectController@unlinkProgram')->name('projects.unlinkProgram');
    Route::post('/projects/{project}/add-team-members', 'ProjectController@addTeamMembers')->name('projects.addTeamMembers');
    Route::delete('/projects/{project}/remove-team-member/{teamMember}', 'ProjectController@removeTeamMember')->name('projects.removeTeamMember');
    Route::resource('projects', 'ProjectController');    
    /* End of Projectss Routes */

    Route::get('/license/activate', [LicenseController::class, 'showActivateForm'])->name('license.activate-form');
    Route::post('/license/activate', [LicenseController::class, 'activate'])->name('license.activate');

    

    // ===============> Reports <============
    Route::group(['prefix' => 'reports'], function () {
        Route::get('/', 'ReportController@index')->name('reports.index');
        // ======> Ajax Route
        Route::get('view/{type?}', 'ReportController@View')->name('reports.view.ajax');
    });
    //================> End Reports <============
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

Route::get('/auth/microsoft/redirect/{mentorship_id}',  'MicrosoftController@redirect')->name('auth.microsoft.redirect');
Route::get('/auth/microsoft/callback',  'MicrosoftController@callback')->name('auth.microsoft.callback');

// --------------------> Linked In <-------------------
Route::get('/auth/linkedin', 'SocialController@redirectToLinkedIn')->name('linkedin.login');
Route::get('/auth/linkedin-openid/callback', 'SocialController@handleLinkedInCallback')->name('handleLinkedInCallback');

if (request()->secure()) {
    URL::forceScheme("https");
}
