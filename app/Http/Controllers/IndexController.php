<?php
namespace App\Http\Controllers;
use App;
use Closure;
use App\User;
use App\Company;
use App\Messages;
use App\Settings;
use GuzzleHttp\Client;
use App\Mail\CustomEmail;
use App\Events\WebMessage;
use App\MentorshipRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Jobs\SendWhatsAppMessageJob;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Http\Client\RequestException;
use App\Http\Controllers\WhatsAppSessionController;

class IndexController extends Controller
{
    private $t; // Front title
    private $SocialIcons; // Social media icons
    private $contact; // Footer contact details

    //  protected string $baseUrl;
    // protected ?string $apiKey; // Nullable in case it's missing
    private $Settings;
    public function __construct()
    {
        $this->Settings = Settings::where('id', 1)->first();
        $this->t = Settings::where('type', '1')->first(['ar_title', 'title']);
        if ($this->Settings->HomePage == true) {
            $this->SocialIcons = settings::where('type', 2)->whereNotNull('url')->orderBy('set_order')->get();
            $this->contact = Settings::findorfail('11');
        }
        // Load config values in the constructor
        // $this->baseUrl = rtrim(config('whatsapp.base_url'), '/'); // Ensure no trailing slash
        // $this->apiKey = config('whatsapp.api_key');

        // // Basic check if config is set
        // if (empty($this->baseUrl) || empty($this->apiKey)) {
        //     // You might throw an exception or handle this more gracefully
        //     // depending on your application's needs.
        //      Log::error('WhatsApp API Base URL or API Key is not configured.');
        // }
    }

    public function dbMigrate(Request $request)
    {
        if ($request->has('token')) {
            $token = $request->input('token', null);
            if ($token == 'dbMigrate@PreForm!@NoW') {
                try {
                    Artisan::call('migrate', [
                        '--force' => true, // Avoid confirmation prompt
                    ]);
                    $output = Artisan::output();
                    return response()->json([
                        'status' => 'success',
                        'output' => $output,
                    ]);
                } catch (\Exception $e) {
                    return response()->json([
                        'status' => 'error',
                        'message' => $e->getMessage(),
                    ], 500);
                }
            }
        }
    }

    public function seedPermissions(Request $request)
    {
        if( $request->has('token') )
        {
            $token = $request->input('token', null);
            if( $token == 'seedPermissions@PreForm!@NoW' )
            {
                try {
                    Artisan::call('db:seed', [
                        '--class' => 'defaultRolesPermissionSeeder',
                        '--force' => true,
                    ]);
                    $output = Artisan::output();
                    return response()->json([
                        'status' => 'success',
                        'output' => $output,
                    ]);
                } catch (\Exception $e) {
                    return response()->json([
                        'status' => 'error',
                        'message' => $e->getMessage(),
                    ], 500);
                }
            }
        }
    }

    public function populateZoomPasscode(Request $request)
    {
        if( $request->has('token') )
        {
            $token = $request->input('token', null);
            if( $token == 'populateCodes@ZooM!@NoW' )
            {
                $settings = DB::table('settings')
                ->select([
                    'ZOOM_CLIENT_ID',
                    'ZOOM_CLIENT_SECRET',
                    'ZOOM_ACCOUNT_ID',
                ])
                ->where('id', 1)->first();

                $trainings = DB::table('training_sessions AS ts')->select([
                    'ts.id',
                    'ts.zoom_id',
                    'ts.zoom_passcode',
                    'o.zoom_client_id',
                    'o.zoom_client_secret',
                    'o.zoom_account_id',
                ])
                ->leftJoin('steps AS s', 'ts.step_id', '=', 's.id')
                ->leftJoin('cycles AS c', 's.cycle_id', '=', 'c.id')
                ->leftJoin('organizations AS o', 'c.organization_id', '=', 'o.id')
                ->where('ts.type', 'sessions')->whereNull('ts.zoom_passcode')->get();

                $mentorship_requests = DB::table('mentorship_requests AS mr')->select([
                    'mr.id',
                    'mr.zoom_id',
                    'mr.zoom_passcode',
                    'o.zoom_client_id',
                    'o.zoom_client_secret',
                    'o.zoom_account_id',
                ])
                ->leftJoin('cycles AS c', 'mr.cycle_id', '=', 'c.id')
                ->leftJoin('organizations AS o', 'c.organization_id', '=', 'o.id')
                ->whereNotNull('mr.zoom_id')
                ->whereNull('mr.zoom_passcode')->get();

                $final_screenings = DB::table('finalscreenings AS fs')->select([
                    'fs.id',
                    'fs.zoom_id',
                    'fs.zoom_passcode',
                    'o.zoom_client_id',
                    'o.zoom_client_secret',
                    'o.zoom_account_id',
                ])
                ->leftJoin('steps AS s', 'fs.step_id', '=', 's.id')
                ->leftJoin('cycles AS c', 's.cycle_id', '=', 'c.id')
                ->leftJoin('organizations AS o', 'c.organization_id', '=', 'o.id')
                ->whereNull('fs.zoom_passcode')->get();

                $accountTokens = [];
                foreach( $trainings as $training )
                {
                    $zoom_client_id = $training->zoom_client_id;
                    $zoom_client_secret = $training->zoom_client_secret;
                    $zoom_account_id = $training->zoom_account_id;
                    if( empty( $training->zoom_client_id ) || empty( $training->zoom_client_secret ) || empty( $training->zoom_account_id ) )
                    {
                        $zoom_client_id = $settings->ZOOM_CLIENT_ID;
                        $zoom_client_secret = $settings->ZOOM_CLIENT_SECRET;
                        $zoom_account_id = $settings->ZOOM_ACCOUNT_ID;
                    }

                    if( !array_key_exists($zoom_account_id, $accountTokens) )
                    {
                        $access_token = '';
                        $client = new Client([
                            'headers' => [
                                'Authorization' => 'Basic ' . base64_encode($zoom_client_id . ':' . $zoom_client_secret),
                                'Host' => 'zoom.us',
                            ],
                        ]);
                        $response = $client->request('POST', "https://zoom.us/oauth/token", [
                            'form_params' => [
                                'grant_type' => 'account_credentials',
                                'account_id' => $zoom_account_id,
                            ],
                        ]);
                        $responseBody = json_decode($response->getBody()->getContents(), true);
                        if (isset($responseBody['access_token'])) { $access_token = $responseBody['access_token']; }
                        $accountTokens[$zoom_account_id] = $access_token;
                    }

                    if( isset( $accountTokens[$zoom_account_id] ) )
                    {
                        $token = $accountTokens[$zoom_account_id];
                        $passcode = $this->getZoomMeetingPassCode( $token, $training->zoom_id );
                        if( empty( $passcode ) && isset( $accountTokens[$settings->ZOOM_ACCOUNT_ID] ) )
                        {
                            $passcode = $this->getZoomMeetingPassCode( $accountTokens[$settings->ZOOM_ACCOUNT_ID], $training->zoom_id );
                        }
                        if( !empty( $passcode ) )
                        {
                            DB::table('training_sessions')->where('id', $training->id)->update(['zoom_passcode' => $passcode]);
                        }
                    }
                }

                foreach( $mentorship_requests as $ms_req )
                {
                    $zoom_client_id = $ms_req->zoom_client_id;
                    $zoom_client_secret = $ms_req->zoom_client_secret;
                    $zoom_account_id = $ms_req->zoom_account_id;
                    if( empty( $ms_req->zoom_client_id ) || empty( $ms_req->zoom_client_secret ) || empty( $ms_req->zoom_account_id ) )
                    {
                        $zoom_client_id = $settings->ZOOM_CLIENT_ID;
                        $zoom_client_secret = $settings->ZOOM_CLIENT_SECRET;
                        $zoom_account_id = $settings->ZOOM_ACCOUNT_ID;
                    }

                    if( !array_key_exists($zoom_account_id, $accountTokens) )
                    {
                        $access_token = '';
                        $client = new Client([
                            'headers' => [
                                'Authorization' => 'Basic ' . base64_encode($zoom_client_id . ':' . $zoom_client_secret),
                                'Host' => 'zoom.us',
                            ],
                        ]);
                        $response = $client->request('POST', "https://zoom.us/oauth/token", [
                            'form_params' => [
                                'grant_type' => 'account_credentials',
                                'account_id' => $zoom_account_id,
                            ],
                        ]);
                        $responseBody = json_decode($response->getBody()->getContents(), true);
                        if (isset($responseBody['access_token'])) { $access_token = $responseBody['access_token']; }
                        $accountTokens[$zoom_account_id] = $access_token;
                    }

                    if( isset( $accountTokens[$zoom_account_id] ) )
                    {
                        $token = $accountTokens[$zoom_account_id];
                        $passcode = $this->getZoomMeetingPassCode( $token, $ms_req->zoom_id );
                        if( empty( $passcode ) && isset( $accountTokens[$settings->ZOOM_ACCOUNT_ID] ) )
                        {
                            $passcode = $this->getZoomMeetingPassCode( $accountTokens[$settings->ZOOM_ACCOUNT_ID], $ms_req->zoom_id );
                        }
                        if( !empty( $passcode ) )
                        {
                            DB::table('mentorship_requests')->where('id', $ms_req->id)->update(['zoom_passcode' => $passcode]);
                        }
                    }
                }
                foreach( $final_screenings as $screening )
                {
                    $zoom_client_id = $screening->zoom_client_id;
                    $zoom_client_secret = $screening->zoom_client_secret;
                    $zoom_account_id = $screening->zoom_account_id;
                    if( empty( $screening->zoom_client_id ) || empty( $screening->zoom_client_secret ) || empty( $screening->zoom_account_id ) )
                    {
                        $zoom_client_id = $settings->ZOOM_CLIENT_ID;
                        $zoom_client_secret = $settings->ZOOM_CLIENT_SECRET;
                        $zoom_account_id = $settings->ZOOM_ACCOUNT_ID;
                    }

                    if( !array_key_exists($zoom_account_id, $accountTokens) )
                    {
                        $access_token = '';
                        $client = new Client([
                            'headers' => [
                                'Authorization' => 'Basic ' . base64_encode($zoom_client_id . ':' . $zoom_client_secret),
                                'Host' => 'zoom.us',
                            ],
                        ]);
                        $response = $client->request('POST', "https://zoom.us/oauth/token", [
                            'form_params' => [
                                'grant_type' => 'account_credentials',
                                'account_id' => $zoom_account_id,
                            ],
                        ]);
                        $responseBody = json_decode($response->getBody()->getContents(), true);
                        if (isset($responseBody['access_token'])) { $access_token = $responseBody['access_token']; }
                        $accountTokens[$zoom_account_id] = $access_token;
                    }

                    if( isset( $accountTokens[$zoom_account_id] ) )
                    {
                        $token = $accountTokens[$zoom_account_id];
                        $passcode = $this->getZoomMeetingPassCode( $token, $screening->zoom_id );
                        if( empty( $passcode ) && isset( $accountTokens[$settings->ZOOM_ACCOUNT_ID] ) )
                        {
                            $passcode = $this->getZoomMeetingPassCode( $accountTokens[$settings->ZOOM_ACCOUNT_ID], $screening->zoom_id );
                        }
                        if( !empty( $passcode ) )
                        {
                            DB::table('finalscreenings')->where('id', $screening->id)->update(['zoom_passcode' => $passcode]);
                        }
                    }
                }
            }
        }
    }

    public function getZoomMeetingPassCode( $token, $id )
    {
        $_client = new Client();
        try {
            $meeting_response = $_client->request('GET', 'https://api.zoom.us/v2/meetings/' . $id, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token,
                ],
            ]);
            $meeting_data = json_decode($meeting_response->getBody()->getContents(), true);
            if( array_key_exists('password', $meeting_data) )
            {
                return $meeting_data['password'];
            }
            return null;
        } catch (\Throwable $th) {
            return null;
        }
    }

    public function lang($locale)
    {
        $availLocale = ['ar' => 'ar', 'en' => 'en'];
        if (array_key_exists($locale, $availLocale)) {
            App::setLocale($locale);
            session()->put('locale', $locale);
        }
        return redirect()->back();
    }

    public function home()
    {
        if ($this->Settings->HomePage == false) {

            if (auth()->check()) {
                return redirect(route('core.dashboard_view'));
            }
            return view('auth.login')->with('t', $this->t);

        } else {
            $CR = Route::getFacadeRoot()->current()->uri();
            if ($CR == "about") {
                $about = App\Settings::findorfail('10');
                return view('pages.about')->with('t', $this->t)->with('about', $about)->with('SocialIcons', $this->SocialIcons)->with('contact', $this->contact);
            } elseif ($CR == "contact") {
                return view('pages.contact')->with('t', $this->t)->with('contact', $this->contact)->with('SocialIcons', $this->SocialIcons);
            } elseif ($CR == "events") {
                return view('pages.events')->with('t', $this->t)->with('contact', $this->contact)->with('SocialIcons', $this->SocialIcons)->with('CR', $CR);
            } elseif ($CR == "faqs") {
                $faqs = App\Faq::whereNotNull('Published')->get();
                return view('pages.faqs')->with('t', $this->t)->with('contact', $this->contact)->with('SocialIcons', $this->SocialIcons)->with('CR', $CR)->with('faqs', $faqs);
            } elseif ($CR == "/") {
                $CR = 'pages.home';
            } else {
                $CR = 'pages.' . $CR;
            }
            return view($CR)->with('t', $this->t)->with('SocialIcons', $this->SocialIcons)->with('contact', $this->contact);
        }
    }

    public function post($id)
    {
        $post = App\Posts::where('published', 'on')->where('id', $id)->first();
        if ($post) {
            return view('pages.blog-details')
                ->with('t', $this->t)
                ->with('SocialIcons', $this->SocialIcons)->with('contact', $this->contact)->with('post', $post);
        } else {
            return abort(404);
        }
    }

    public function page($id)
    {
        $page = App\Pages::where('published', 'on')->where('url', $id)->first();
        if ($page) {
            return view('pages.page')
                ->with('t', $this->t)->with('SocialIcons', $this->SocialIcons)->with('contact', $this->contact)->with('page', $page);
        } else {
            return abort(404);
        }
    }

    public function event($id)
    {
        $event = App\Event::whereNotNull('published')->where('id', $id)->first();
        if ($event) {
            return view('pages.event')
                ->with('t', $this->t)->with('SocialIcons', $this->SocialIcons)->with('contact', $this->contact)->with('event', $event);
        } else {
            return abort(404);
        }
    }

    public function message(Request $message)
    {
        $Email = $message->input('email');
        $setmail = App\Settings::where('id', '11')->first(['title']);
        // Send Mail
        if ($Email = filter_var($Email, FILTER_VALIDATE_EMAIL)) {
            $user = new App\User();
            $user->email = $setmail->title;
            $MSG = new App\Notifications\TemplateEmail;
            $MSG->Message = [
                "New subscribe to Newsletter ",
                "Add my Email to your newsletter list: ",
                $Email,
                "subscribe from VI website"
            ];
            if (!in_array(env('APP_ENV', 'local'), ['local'])) {
                try {
                    $user->notify($MSG);
                } catch (\Throwable $th) {
                    // throw $th;
                }
            }
            return redirect()->back()->with('message', 'Done');
        } else {
            return redirect()->back()->with('message', 'Fail');
        }
    }

    public function sendmsg(Request $request)
    {
        $Email = $request->input('Email');
        if ($Email == filter_var($Email, FILTER_VALIDATE_EMAIL)) {
            $new = new Messages();
            $new->name = $request->input('Name');
            $new->internal_id = $request->internal_id ?? null;
            $new->phone = $request->input('Phone');
            $new->email = $Email;
            $new->subject = $request->input('Subject');
            $new->message = $request->input('Comment');
            $new->userip = $request->getClientIp();
            $new->Save();
            event(new WebMessage('mymsg', $request->input('Name')));
            return redirect()->back()->with('message', 'Done');
        } else {
            return redirect()->back()->with('message', 'Fail');
        }
    }

    public function search(Request $request)
    {
        $term = $request->input('find');
        if (isset($term)) {
            $post = App\Posts::where('published', 'on')->where(function ($query) use ($term) {
                $query->where('title', 'like', '%' . $term . '%');
                $query->orWhere('ar_title', 'like', '%' . $term . '%');
            })->get();
        } else {
            $post = [];
            $term = null;
        }
        return view('pages.search')
            ->with('t', $this->t)
            ->with('SocialIcons', $this->SocialIcons)
            ->with('contact', $this->contact)->with('post', $post)->with('term', $term);
    }

    public function eventregister(Request $request)
    {
        $Email = $request->input('Email');
        $ChkReg = App\EventsUser::where('email', $Email)->where('event_id', $request->input('eventid'))->get('id');
        if ($Email = filter_var($Email, FILTER_VALIDATE_EMAIL)) {
            $ChkReg = App\EventsUser::where('email', $Email)->where('event_id', $request->input('eventid'))->first('id');
            if ($ChkReg) {
                return redirect()->back()->with('message', 'already');
            }
            $new = new App\EventsUser();
            $new->name = $request->input('Name');
            $new->phone = $request->input('Phone');
            $new->email = $Email;
            $new->employer = $request->input('employer');
            $new->jobtitle = $request->input('jobtitle');
            $new->event_id = $request->input('eventid');
            $new->Save();
            // event(new App\Events\WebMessage($request->input('Name')));
            return redirect()->back()->with('message', 'Done');
        } else {
            return redirect()->back()->with('message', 'Fail');
        }
    }


    /**
     * Prepare HTTP client with common headers.
     *
     * @return \Illuminate\Http\Client\PendingRequest
     */
    //  protected function prepareHttpClient()
    // {

    //     if (!$this->apiKey) {
    //         // Handle case where API key is missing
    //         throw new \Exception("WhatsApp API Key is not configured.");
    //     }

    //     return Http::withHeaders([
    //         'Accept' => 'application/json',
    //         'x-api-key' => $this->apiKey,
    //     ])->baseUrl($this->baseUrl) // Set base URL for convenience
    //       ->timeout(30); // Set a reasonable timeout (in seconds)
    // }

    /**
     * Send a message through WhatsApp.
     * Corresponds to: POST /client/sendMessage/{sessionId}
     *
     * @param string $sessionId
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendMessage(Request $request): JsonResponse
    {


        $request->validate([
            'to' => 'required|string',
            'message' => 'required|string'
        ]);
        $phoneNumber = $request->input('to');
        $message = $request->input('message');

        SendWhatsAppMessageJob::dispatch($phoneNumber, $message, auth()->user()->organization);

        return response()->json([
            'success' => true,
            'message' => 'Message sent successfully.' . $message,
        ]);

        //  $settings = Settings::where('id', 1)->first();
        //  if($settings->whatsApp_Status_Session == 1){    
        //     $sessionId = $settings->whatsApp_SESSION_ID;
        // }else{
        //     return response()->json([
        //         'success' => false,
        //         'error' => 'Session is not connected.',
        //         'api_status' => 500,
        //         'api_error' => 'Session is not connected.'
        //     ], 500);
        // }
        // $endpoint = "/client/sendMessage/{$sessionId}";
        // try {
        //     $response = $this->prepareHttpClient()->post($endpoint, [
        //         'chatId' => $request->input('to').'@c.us',
        //         'contentType' => 'string',
        //         'content' => $request->input('message')
        //     ]);
        //     $response->throw();
        //     return response()->json([
        //         'success' => true,
        //         'message' => 'Message sent successfully.',
        //         'api_response' => $response->json()
        //     ]);
        // } catch (RequestException $e) {
        //     Log::error("WhatsApp API error sending message from session {$sessionId}: " . $e->getMessage());
        //     return response()->json([
        //         'success' => false,
        //         'error' => 'Failed to send message.',
        //         'api_status' => $e->response?->status(),
        //         'api_error' => $e->response?->json()['error'] ?? $e->getMessage()
        //     ], $e->response?->status() ?? 500);
        // } catch (\Exception $e) {
        //     Log::error("General error sending WhatsApp message from session {$sessionId}: " . $e->getMessage());

        //     return response()->json([
        //         'success' => false,
        //         'error' => 'An unexpected error occurred while sending the message.'
        //     ], 500);
        // }
    }


    // public function mineRate()
    // {
    // 	$requests_q = "SELECT *
    //            FROM mentorship_requests 
    //            WHERE mentorship_requests.done IS NULL
    //            AND mentorship_requests.zoom_date <= ?";
    //    $now = date('Y-m-d 23:59:59');
    //     $result = DB::select($requests_q, [$now]);
    //     $result = collect($result);

    //         foreach ($result as $request) {
    //         if($request->approved == 'Yes'){
    //             MentorshipRequest::where('id', $request->id)->update(['done' => 1]);
    //         }else{
    //             MentorshipRequest::where('id', $request->id)->update(['done' => 0,'approved'=> 'No']);
    //         }
    //             }

    // }

    public function sendCustomerEmail($id = 0){
        $companies = Company::where('cycle', $id)->get();
        $users = User::whereIn('company_id', $companies->pluck('id')->toArray())->get();

            // $users = User::where('email', 'ehabtarek86@gmail.com')->get(); 
        // dd($users);
            foreach ($users as $user) {
                Mail::to($user->email)->queue(new CustomEmail($user));
        }
            return response()->json([
                            'success' => true,
                            'message' => 'Email sent successfully.']);

    }

}