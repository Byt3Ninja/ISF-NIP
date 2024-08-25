<?php

/* Disable frontend */// Route::get('/', 'HomeController@index')->name('admin'); ///

Route::get('test',function(){
    
});
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
Route::get('lang/{locale}','IndexController@lang');
Route::get('/','IndexController@home');
Route::get('home','IndexController@home');
Route::get('about','IndexController@home');
Route::get('faqs','IndexController@home');
Route::get('contact','IndexController@home');
Route::get('events','IndexController@home');
Route::get('csr-events','IndexController@home');
Route::get('blog','IndexController@home');
Route::get('blog/{id}','IndexController@post');
Route::post('message','IndexController@message');
Route::post('sendmsg','IndexController@sendmsg');
Route::get('page/{id}','IndexController@page');
Route::get('search','IndexController@search');
Route::get('event/{id}','IndexController@event');
Route::post('eventregister','IndexController@eventregister');

Auth::routes(['verify' => true]);
Route::group(['prefix' => 'dashboard','middleware'=>['auth', 'verified']],function(){
####################### Overall System Admin Routes ###############
	// All Logged users
	Route::get('/', 'HomeController@index')->name('admin');
	Route::get('/dashboard', 'HomeController@index')->name('dashboard');
	Route::get('/dark','HomeController@dark');
    //Roles
    Route::get('documentation/view/{id?}','HomeController@help');
    Route::resource('documentation', 'DocumentationController');


    Route::get('calendar/{id}/edit','SettingsController@editcal');
    Route::post('calendar/{id}/update','SettingsController@updatecal');
    Route::get('calendar/{id}/delete','SettingsController@delcal');
    Route::post('calendar/view','SettingsController@viewcal');
	// Sliders Routes
    Route::get('add_slide', 'SlidersController@add_slide');
    Route::post('store_slide', 'SlidersController@store_slide');
	Route::any('slides', 'SlidersController@slides');
	Route::get('edit_slide/{id}', 'SlidersController@editslide');
	Route::post('update_slide/{id}', 'SlidersController@updateslide');
	Route::get('delete_slide/{id}', 'SlidersController@deleteslide');
    // Menus Routes
    Route::get('add_menu', 'MenusController@add_menu');
    Route::post('store_menu', 'MenusController@store_menu');
	Route::any('menus', 'MenusController@myMenus');
	Route::get('edit_menu/{id}', 'MenusController@editMenu');
	Route::post('update_menu/{id}', 'MenusController@updatemenu');
	Route::get('delete_menu/{id}', 'MenusController@deleteMenu');
	// Posts Routes
    Route::get('addpost', 'PostsController@add_post');
    Route::post('storepost', 'PostsController@store_post');
	Route::any('posts', 'PostsController@myposts');
	Route::get('editpost/{id}', 'PostsController@editpost');
	Route::post('updatepost/{id}', 'PostsController@updatepost');
	Route::get('deletepost/{id}', 'PostsController@deletepost');
	// Pages Routes
    Route::get('addpage', 'PagesController@add_page');
    Route::post('storepage', 'PagesController@store_page');
	Route::any('pages', 'PagesController@mypages');
	Route::get('editpage/{id}', 'PagesController@editpage');
	Route::post('updatepage/{id}', 'PagesController@updatepage');
	Route::get('deletepage/{id}', 'PagesController@deletepage');
    Route::get('editmypage', 'PagesController@editmypage');
    // Pages Events
    Route::get('addevent', 'EventsController@add_event');
    Route::post('storeevent', 'EventsController@store_event');
    Route::any('allevents', 'EventsController@myevent');
    Route::get('editevent/{id}', 'EventsController@editevent');
    Route::post('updateevent/{id}', 'EventsController@updateevent');
    Route::get('deleteevent/{id}', 'EventsController@deleteevent');
	// Settings Routes
	Route::any('settings', 'SettingsController@get_settings');
	Route::post('update_settings', 'SettingsController@update_settings');
	Route::any('setaboutus', 'SettingsController@get_aboutus');
	Route::post('updateaboutus', 'SettingsController@update_aboutus');
	Route::any('setcontactus', 'SettingsController@get_contactus');
	Route::post('updatecontactus', 'SettingsController@update_contactus');
	//services categories Manager routes
	Route::resource('serviceCategories', 'ServiceCategoryController');
	//Services Manager routes
	Route::resource('services', 'ServiceController');
	//Services requests
    Route::get('requests/all', 'ServiceController@requests')->name('requests');
    Route::get('requests/old', 'ServiceController@oldrequests')->name('oldrequests');
    Route::post('requests/approve', 'ServiceController@approve');
    Route::get('requests/mine','ServiceController@myrequests')->name('myrequests');
	//Services Directory
    Route::get('directory','HomeController@directory')->name('directory');
    Route::post('directory/{id}','HomeController@showone');
    Route::post('directory/{id}/rate','HomeController@rateservice');
    Route::post('servicerequest','HomeController@servicereq');
	//Types Manager routes
	Route::resource('types', 'TypeController');
  	//Partners Manager routes
    Route::resource('partners', 'PartnersController');
    //Press Manger routes
    Route::resource('press', 'PressController');
	//Client's Profile
	Route::get('/myProfile', 'ClientProfileController@edit')->name('my_profile');
	Route::post('/myProfile', 'ClientProfileController@update')->name('update_my_profile');
	Route::post('/myProfile/password', 'ClientProfileController@updatePassword')->name('update_my_profile_password');
	//Client's company
    Route::resource('organizations', 'organizationsController');
	Route::get('/myCompany', 'ClientCompanyController@editOrCreate')->name('my_company');
	Route::post('/myCompany', 'ClientCompanyController@updateOrStore')->name('update_my_company');
	//like a service
	Route::post('/likeService', 'ClientCompanyController@likeService')->name('like_service');
	//Users  Manager routes
	Route::resource('users', 'UserController');
	Route::post('/users/password', 'UserController@updatePassword')->name('update_account_password');
	Route::get('/companies','UserController@companies')->name('users.companies');
	//Cities
	Route::resource('cities', 'CityController');
    Route::get('get_states','CityController@states')->name('get_states_by_country');
	//Company profile
	Route::get('/companyProfile/{id}', 'ClientCompanyController@show')->name('company_profile');
	//FileManager
    Route::get('/myFiles','FilemanagerController@index');
    Route::get('/uploads/files/{id}/{path}','FilemanagerController@showfile')->where('path', '.*');
    Route::get('/uploads/photos/{id}/{path}','FilemanagerController@showimage')->where('path', '.*');
    //Explore private files
    Route::get('/private/{id}/{path}','FilemanagerController@showprivate')->where('path', '.*');
    //FileManager for training
    Route::get('/training/allfiles','FilemanagerController@allfiles');
    Route::get('/training/files/{path}','FilemanagerController@trainingfile')->where('path', '.*');
    Route::get('/training/photos/{path}','FilemanagerController@trainingimage')->where('path', '.*');
    //SendMessage
    Route::post('/send_message','MessengerController@sendmsg');
    Route::get('/sendmessage','MessengerController@sendmessage');
    Route::post('/sendmessage','MessengerController@savemessage');
    //ShowMessages
    Route::get('/inbox','MessengerController@index')->name('inbox');
    Route::get('/outbox','MessengerController@outbox')->name('outbox');
    Route::get('/openmsg','MessengerController@openmsg');
    Route::post('/mread','MessengerController@makeread');
    Route::post('/messages/delete','MessengerController@destroy');
    //Contact US Messages
    Route::get('mymsg','SettingsController@mymsg');
    Route::post('msgreply','SettingsController@msgreply');
    Route::get('deletemsg/{id}', 'SettingsController@deletemsg');
    Route::post('makeread/{id}', 'SettingsController@makeread');
    Route::post('makeunread/{id}', 'SettingsController@makeunread');
    Route::get('openonemsg/{id}', 'SettingsController@openonemsg');
    //products categories Manager routes
    Route::resource('productsCategories', 'ProductCategoryController');
    //products Manager routes
    Route::resource('products', 'ProductController');
    //publish
    Route::any('products/publish/{id}','ProductController@publish');
    //Interests
    Route::any('interests','HomeController@myinterests')->name('myinterests');
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
    Route::get('builder/organization/{id?}','FormBuilderController@builderByOrganization');
    //Cycles
    Route::get('cycles/{id?}','CyclesController@index')->name('index.cycle');
    Route::get('cycles/{id}/registered','CyclesController@users');
    Route::post('cycles/modify','CyclesController@modify');
    Route::get('cycles/view/{c?}/{s?}','CyclesController@view');
    Route::post('cycles/save','CyclesController@saveForm');
    Route::get('cycles/my/{id}','CyclesController@my');
    Route::post('change/cyc','CyclesController@changAll');
    Route::get('/export-cyclesComp', 'CyclesController@exportcyclesComp');

    //Screening
    Route::get('screening','ScreeningController@index');
    Route::post('screening/update','ScreeningController@update');
    Route::post('screening/save','ScreeningController@save');
    Route::post('screening/add','ScreeningController@add');
    Route::get('screening/results/{id?}','ScreeningController@results');
    Route::get('{id?}/screening','ScreeningController@index');
    Route::get('screening/{fid}/{cid}','ScreeningController@view');
    Route::get('screening/get/{fid}/{sid}/{pid}','ScreeningController@getusers');
    Route::post('screening/changestep','ScreeningController@changestep');
    Route::get('/export-Result', 'ScreeningController@exportresultScreen');

    //ZOOM
    Route::get('zoom/all','MeetingController@showall');
    Route::get('zoom/{id}/get','MeetingController@show');
    Route::post('zoom/store','MeetingController@store');
    Route::post('zoom/{id}/update','MeetingController@update');
    Route::post('zoom/{id}/delete','MeetingController@destroy');
    Route::get('zoom/{id}/registrants','MeetingController@registrants');
    //Mentors
    Route::post('mentors/modify','MentorsController@modify');
    Route::post('mentorship/add','MentorsController@add');
    Route::get('mentorship/assign/{id?}','MentorsController@list');
    Route::post('mentorship/assign/{id}','MentorsController@assign');
    Route::get('mentorship/view/{id?}','MentorsController@index');
    Route::get('mentorship/sessions','MentorsController@sessions');
  	Route::post('mentorship/session','MentorsController@getsession');
  	Route::post('mentorship/modify','MentorsController@modifysession');
    Route::post('mentorship/addwait','MentorsController@addwait');
    Route::get('mentorship/mine','MentorsController@mine');
    Route::post('mentorship/rate','MentorsController@rate');
    Route::post('mentorship/delsession/{id}' ,'MentorsController@delsession');
    Route::get('/export-mentorship', 'MentorsController@expormentorship');




    //Training
    Route::get('training/all/{id?}','TrainingController@all')->name('training.all');
    Route::get('training/edit/{id}','TrainingController@edit');
    Route::post('training/store','TrainingController@store');
    Route::post('training/delete/{id}','TrainingController@delete');
    Route::post('training/delcontent/{id}','TrainingController@delcontent');
    Route::get('training/addcontent/{id}','TrainingController@addcontent');
    Route::get('training/editcontent/{id}','TrainingController@editcontent');
    Route::get('training/viewcontent/{id}','TrainingController@viewcontent');
    Route::post('training/upload','TrainingController@upload')->name('training.upload');
    Route::post('training/storecontent', 'TrainingController@storecontent');
    Route::get('training/my','TrainingController@mysessions')->name('training.my');
    Route::get('training/my/{id}','TrainingController@my');
    Route::get('training/results/{id?}','TrainingController@results');
    Route::get('training/sessions/{id?}','TrainingController@sessions');
    Route::post('training/addsession' ,'TrainingController@addsession');
    Route::post('training/delsession/{id}' ,'TrainingController@delsession');
    Route::post('training/session' ,'TrainingController@session');
    Route::post('training/editsession' ,'TrainingController@editsession');
    Route::post('trainings/getdsc/{id}' ,'TrainingController@getdsc');
    Route::get('/export-Tresult', 'TrainingController@exportTresult');



    // questions bank
    Route::resource('question-categories', 'QuestionCategoriesController');
    Route::resource('questions-bank', 'QuestionsBankController');
    Route::resource('quizzes', 'quizzesController');
    Route::get('quizzes/{quiz}/questions', 'quizzesController@get_questions')->name('quizzes.questions');
    Route::post('quizzes/{quiz}/questions', 'quizzesController@save_questions')->name('quizzes.saveQuestions');

    //Rooms routes
    Route::resource('rooms', 'RoomsController');
    Route::get('/roomImages/{id}','RoomsController@roomImages')->name('roomImages');
    Route::get('/deleteImages/{id}','RoomsController@deleteImages')->name('deleteImages');
    Route::resource('booking', 'BookingController');
    Route::get('/bookingreview', 'BookingController@review')->name('bookingreview');
    Route::get('/bookingApprove/{id}/{action}', 'BookingController@approve')->name('bookingApprove');
    Route::get('/upcomingApprovedReview', 'BookingController@upcomingApprovedReview')->name('upcomingApprovedReview');
    Route::get('/upcomingBusyReview', 'BookingController@upcomingBusyReview')->name('upcomingBusyReview');
    Route::get('/oldReview', 'BookingController@oldReview')->name('oldReview');
    //Final Screening
    Route::get('final','FinalscreeningController@index');
    Route::get('{id?}/final','FinalscreeningController@index');
    Route::post('final/update','FinalscreeningController@update');
    Route::post('final/save','FinalscreeningController@save');
    Route::post('final/addzoom','FinalscreeningController@addzoom');
    Route::get('final/results/{id?}','FinalscreeningController@results');
    Route::get('final/{step_id}/{company_id}','FinalscreeningController@view');
    Route::get('final/get/{sid}/{pid}','FinalscreeningController@getusers');
    Route::post('final/delsession/{id}' ,'FinalscreeningController@delsession');


//Program List
Route::resource('programlist', 'ProgramController');
Route::get('/export-Prog', 'ProgramController@exportProg')->name('exportProg');

    // ===============> Mentor Dashboard Routes <===============
    Route::group(['prefix' => 'mentor','namespace'=>'Mentor'],function (){
        Route::get('mentorshipSessions','MentorshipController@index')->name('mentorshipSessions.index');
        Route::get('meetingRedirect/{mentorship_request_id}','MentorshipController@meetingRedirect')->name('meetingRedirect');
        Route::resource('mentorshipTasks','MentorshipTaskController');
        Route::post('mentorshipTasks/send/comment','MentorshipTaskController@sendComment')->name('mentorshipTasks.sendComment');

    });



    // =====================> Startup Routes <===========

    Route::group(['prefix' => 'startup','as'=>'startup.','namespace'=>'Startup'],function (){
        Route::get('mentorshipTasks','MentorshipTaskController@home')->name('mentorshipTasks.home');
        Route::get('mentorshipTasks/{mentorship_id}','MentorshipTaskController@index')->name('mentorshipTasks.index');
        Route::get('mentorshipTasks/show/{mentorshipTask}','MentorshipTaskController@show')->name('mentorshipTasks.show');
        Route::post('mentorshipTasks/complete/{mentorshipTask}','MentorshipTaskController@complete')->name('mentorshipTasks.complete');
        Route::post('mentorshipTasks/send/comment','MentorshipTaskController@sendComment')->name('mentorshipTasks.sendComment');
        Route::resource('fundingRounds','FundingRoundController')->except(['index']);
    });

    // =================> Calender Routes <==================
    Route::group(['prefix' => 'calendar','namespace'=>'Calender'],function () {
        Route::get('/','CalenderController@index')->name('calendar');
        Route::post('calendarViewCall','CalenderController@ViewCall')->name('calendarAjax');
        // ===>  google sync <=====
        Route::get('login/google', 'GoogleCalendarController@redirectToGoogle')->name('calender.login.google');
        Route::get('callback/google', 'GoogleCalendarController@handleGoogleCallback')->name('calender.callback.google');
        Route::get('sync-events', 'GoogleCalendarController@syncEvents')->name('calender.syncEvents');

        // ====> Microsoft sync <=====
        Route::get('login/microsoft', 'MicrosoftCalendarController@redirectToMicrosoft')->name('calender.login.microsoft');
        Route::get('callback/microsoft', 'MicrosoftCalendarController@handleMicrosoftCallback')->name('calender.callback.microsoft');
        Route::get('microsoft-sync-events', 'MicrosoftCalendarController@syncEvents')->name('calender.MicrosoftsyncEvents');

    });

    // ===============> Reports <============
    Route::group(['prefix' => 'reports'],function (){
        Route::get('/','ReportController@index')->name('reports.index');
        // ======> Ajax Route
        Route::get('view/{type?}','ReportController@View')->name('reports.view.ajax');
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

if( request()->secure() ) { URL::forceScheme("https"); }