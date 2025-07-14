<?php

namespace App\Http\Controllers\API;

use App\UsersBio;
use App\MentorshipTask;
use App\MentorshipRequest;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Mail\ConfirmEmailUpdate;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Intervention\Image\ImageManagerStatic as Image;

class ProfileController extends Controller
{
 /**
 * @OA\Get(
 *     path="/api/me",
 *     summary="Get authenticated user profile",
 *     description="Returns profile data of the authenticated user including mentorship statistics, sessions, and mentor bio if applicable.",
 *     operationId="getMe",
 *     tags={"User"},
 *     security={{"sanctum":{}}},
 *     @OA\Response(
 *         response=200,
 *         description="User info returned successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(
 *                 property="data",
 *                 type="object",
 *                 @OA\Property(property="imgUrl", type="string", format="url", example="https://example.com/uploads/user.png"),
 *                 @OA\Property(property="firstName", type="string", example="Ahmed"),
 *                 @OA\Property(property="lastName", type="string", example="Mohamed"),
 *                 @OA\Property(property="gender", type="string", example="Male"),
 *                 @OA\Property(property="countryCode", type="string", example="+20"),
 *                 @OA\Property(property="phone", type="string", example="0123456789"),
 *                 @OA\Property(property="countryCodeWA", type="string", example="+20"),
 *                 @OA\Property(property="whatsapp", type="string", example="0123456789"),
 *                 @OA\Property(property="whatsappVerified", type="integer", example=1),
 *                 @OA\Property(property="email", type="string", example="user@example.com"),
 *                 @OA\Property(property="zipCode", type="integer", example=12345),
 *                 @OA\Property(property="address", type="string", example="Cairo, Egypt"),
 *                 @OA\Property(property="title", type="string", example="Software Engineer"),
 *                 @OA\Property(property="country", type="string", example="Egypt"),
 *                 @OA\Property(property="state", type="string", example="Cairo"),
 *                 @OA\Property(property="city", type="string", example="Nasr City"),
 *                 @OA\Property(property="language", type="string", example="en"),
 *                 @OA\Property(property="haveCompany", type="boolean", example=true),
 *                 @OA\Property(property="timeZone", type="string", example="Africa/Cairo"),
 *                 @OA\Property(property="currency", type="string", example="EGP"),
 *                 @OA\Property(property="role", type="string", example="Mentor"),
 *
 *                 @OA\Property(property="mentorshipTotalRequests", type="integer", example=5),
 *                 @OA\Property(property="mentorshipTotalHours", type="number", format="float", example=4.5),
 *                 @OA\Property(property="mentorshipAverageRating", type="number", format="float", example=4.2),
 *
 *                 @OA\Property(
 *                     property="mentorshipSessions",
 *                     type="array",
 *                     @OA\Items(
 *                         type="object",
 *                         @OA\Property(property="fullName", type="string", example="John Doe"),
 *                         @OA\Property(property="imgUrl", type="string", example="https://example.com/uploads/john.png"),
 *                         @OA\Property(property="company", type="string", example="TechCorp"),
 *                         @OA\Property(property="companyLogo", type="string", example="https://example.com/uploads/logo.png"),
 *                         @OA\Property(property="done", type="boolean", example=false),
 *                         @OA\Property(property="taskTitle", type="string", example="Pitch Deck Review")
 *                     )
 *                 ),
 *
 *                 @OA\Property(
 *                     property="mentorBio",
 *                     type="object",
 *                     @OA\Property(property="bio", type="string", example="Experienced software engineer with 10+ years..."),
 *                     @OA\Property(property="specialization", type="string", example="FinTech, SaaS"),
 *                     @OA\Property(property="linkedin", type="string", example="https://linkedin.com/in/example"),
 *                     @OA\Property(property="entrepreneurialExperience", type="boolean", example=true),
 *                     @OA\Property(property="entrepreneurialExperience_brief", type="string", example="Founded and exited 2 startups in e-commerce"),
 *                     @OA\Property(property="profilePrivacy", type="boolean", example=true),
 *                     @OA\Property(
 *                         property="mentorshipApproach",
 *                         type="array",
 *                         @OA\Items(type="string", example="Video Calls")
 *                     ),
 *                     @OA\Property(
 *                         property="expertises",
 *                         type="array",
 *                         @OA\Items(
 *                             type="object",
 *                             @OA\Property(property="id", type="integer", example=2),
 *                             @OA\Property(property="title", type="string", example="Business Development")
 *                         )
 *                     )
 *                 )
 *             )
 *         )
 *     )
 * )
 */
     public function me(Request $request)
    {
        $user = Auth::user();
        
        $user->img_url = $user->img_url ? Storage::disk('public_uploads')->url($user->img_url) : null;
        $locale = app()->getLocale();

        $data = [
            'imgUrl'           => $user->img_url,
            'firstName'        => $user->first_name_en,
            'lastName'         => $user->last_name_en,
            'gender'           => $user->gender,
            'countryCode'      => $user->country_code,
            'phone'            => $user->phone,
            'countryCodeWA'    => $user->country_code_w_a,
            'whatsapp'         => $user->whatsapp,
            'whatsappVerified' => $user->whatsapp_verified,
            'email'            => $user->email,
            'zipCode'          => $user->zip_code,
            'address'          => $user->address,
            'title'            => $user->title_en,
            'country'          => optional($user->country)->{'name_' . $locale},
            'state'            => optional($user->state)->{'name_' . $locale},
            'city'             => optional($user->city)->{'name_' . $locale}, 
            'language'         => $user->language,
            'haveCompany'      => (bool) $user->HaveCompany,
            'timeZone'         => $user->timezone,
            'currency'         => $user->currency,
            'role'             => $user->getRoleNames()->first(),
        ];

    if ($user->hasRole('Mentor') || $user->hasRole('Registered')) {

        $requestsQuery = MentorshipRequest::query();

        $requestsQuery->where('mentor_id', $user->id);

        $requests = $requestsQuery->get();

        if ($user->hasRole('Mentor')) {
            $data['mentorshipTotalRequests'] = $requests->count();
            $data['mentorshipTotalHours'] = round($requests->sum('period') / 60, 2);
            $data['mentorshipAverageRating'] = round($requests->whereNotNull('rating')->avg('rating'), 1);
        }

        $tasks = MentorshipTask::with(['user.company', 'mentorshipRequest'])->whereNotNull('title');

        if ($user->hasRole('Mentor')) {
            $tasks->where('mentor_id', $user->id)->where('status', 'pending');
        } else {
            $companyId = optional($user->company)->id;
            if ($companyId) {
                $tasks->where('company_id', $companyId);
            } else {
                $tasks->where('user_id', $user->id);
            }
        }

        $tasks = $tasks->get();

        $data['mentorshipSessions'] = $tasks->map(function ($task) use ($locale) {
            $taskUser = $task->user;

            $userImg = $taskUser?->img_url
                ? Storage::disk('public_uploads')->url($taskUser->img_url)
                : null;

            $company = optional($taskUser->company);
            $companyLogo = $company?->logo_url;

            return [
                'fullName'    => trim($taskUser?->first_name_en . ' ' . $taskUser?->last_name_en),
                'imgUrl'      => $userImg,
                'company'     => $company?->{"name_$locale"},
                'companyLogo' => $companyLogo,
                'done'        => optional($task->mentorshipRequest)->done,
                'taskTitle'   => $task->title,
            ];
        })->filter()->values();
    }

    // === Mentor Bio Data ===
  if ($user->hasRole('Mentor')) {
    $bio = UsersBio::where('user_id', $user->id)->first();

    $mentorBio = [];

    if ($bio) {
        $mentorBio = [
            'bio'                             => $locale === 'ar' ? $bio->bio_ar : $bio->bio_en,
            'specialization'                 => $locale === 'ar' ? $bio->specialization_ar : $bio->specialization_en,
            'linkedin'                       => $bio->linkedin,
            'entrepreneurialExperience'     => (bool) $bio->entrepreneurial_experience,
            'entrepreneurialExperience_brief' => $bio->entrepreneurial_experience_brief,
            'profilePrivacy'                => (bool) $bio->profile_privacy,
            'mentorshipApproach'            => json_decode($bio->mentorship_approach, true) ?? [],
        ];
    }

    $expertiseItems = $user->mentorExpertises()->get();

    $mentorBio['expertises'] = $expertiseItems->map(function ($expertise) use ($locale) {
        return [
            'id'    => $expertise->id,
            'title' => $locale === 'ar' ? $expertise->title_ar : $expertise->title,
        ];
    })->values();

    $data['mentorBio'] = $mentorBio;
}


    return response()->json([
            'success' => true,
            'data'   => $data,
        ],200);
    }
 /**
 * @OA\Post(
 *     path="/api/profile",
 *     summary="Update user profile",
 *     description="Update the authenticated user's profile information including contact info, language, timezone, and optionally image.",
 *     operationId="updateProfile",
 *     tags={"User"},
 *     security={{"sanctum":{}}},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\MediaType(
 *             mediaType="multipart/form-data",
 *             @OA\Schema(
 *                 @OA\Property(property="firstName", type="string", example="Ahmed"),
 *                 @OA\Property(property="lastName", type="string", example="Mohamed"),
 *                 @OA\Property(property="gender", type="string", example="male"),
 *                 @OA\Property(property="countryCode", type="string", example="+20"),
 *                 @OA\Property(property="country_code_w_a", type="string", example="+20"),
 *                 @OA\Property(property="phone", type="string", example="0100000000"),
 *                 @OA\Property(property="whatsapp", type="string", example="0100000000"),
 *                 @OA\Property(property="email", type="string", format="email", example="ahmed@example.com"),
 *                 @OA\Property(property="zipCode", type="string", example="12345"),
 *                 @OA\Property(property="address", type="string", example="123 Nile Street"),
 *                 @OA\Property(property="title", type="string", example="Software Engineer"),
 *                 @OA\Property(property="language", type="string", example="en"),
 *                 @OA\Property(property="timezone", type="string", example="Africa/Cairo"),
 *                 @OA\Property(property="currency", type="string", example="EGP"),
 *                 @OA\Property(property="country_id", type="integer", example=1),
 *                 @OA\Property(property="state_id", type="integer", example=10),
 *                 @OA\Property(property="HaveCompany", type="boolean", example=true),
 *                 @OA\Property(property="imgUrl", type="string", format="binary")
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Profile updated successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="Profile updated successfully"),
 *             @OA\Property(property="data", type="object",
 *                 @OA\Property(property="firstName", type="string", example="Ahmed"),
 *                 @OA\Property(property="lastName", type="string", example="Mohamed"),
 *                 @OA\Property(property="email", type="string", format="email", example="ahmed@example.com"),
 *                 @OA\Property(property="phone", type="string", example="0100000000"),
 *                 @OA\Property(property="gender", type="string", example="male"),
 *                 @OA\Property(property="whatsapp", type="string", example="0100000000"),
 *                 @OA\Property(property="countryCode", type="string", example="+20"),
 *                 @OA\Property(property="country_code_w_a", type="string", example="+20"),
 *                 @OA\Property(property="zipCode", type="string", example="12345"),
 *                 @OA\Property(property="address", type="string", example="123 Nile Street"),
 *                 @OA\Property(property="title", type="string", example="Software Engineer"),
 *                 @OA\Property(property="language", type="string", example="en"),
 *                 @OA\Property(property="timezone", type="string", example="Africa/Cairo"),
 *                 @OA\Property(property="currency", type="string", example="EGP"),
 *                 @OA\Property(property="country", type="string", example="Egypt"),
 *                 @OA\Property(property="state", type="string", example="Cairo"),
 *                 @OA\Property(property="HaveCompany", type="boolean", example=true),
 *                 @OA\Property(property="imgUrl", type="string", example="https://yourapp.com/uploads/users/avatar.jpg")
 *             )
 *         )
 *     )
 * )
 */
    public function updateProfile(Request $request)
{
    $user = Auth::user();
    $locale = app()->getLocale();

    $request->validate([
        'firstName'     => 'nullable|string|max:255',
        'lastName'      => 'nullable|string|max:255',
        'countryCode'      => 'nullable|string|max:10',
        'country_code_w_a'  => 'nullable|string|max:10',
        'phone'             => 'nullable|string|max:20',
        'whatsapp'          => 'nullable|string|max:20',
        'email'             => ['nullable', 'email', Rule::unique('users')->ignore($user->id)],
        'zipCode'          => 'nullable|string|max:20',
        'address'           => 'nullable|string|max:255',
        'title'          => 'nullable|string|max:255',
        'language'          => 'nullable|string|max:10',
        'timezone'          => 'nullable|string|max:100',
        'currency'          => 'nullable|string|max:10',
        'country_id'        => 'nullable|exists:countries,id',
        'state_id'          => 'nullable|exists:states,id',
        'imgUrl'            => 'nullable|image|mimes:jpg,jpeg,png,gif|max:5120',
    ]);

    if ($request->filled('firstName')) {
        $user->first_name_en = $request->input('firstName');
        $user->first_name_ar = $request->input('firstName');
    }

    if ($request->filled('lastName')) {
        $user->last_name_en = $request->input('lastName');
        $user->last_name_ar = $request->input('lastName');
    }
     if ($request->filled('email')) {
        $user->email = $request->input('email');
    }
     if ($request->filled('gender')) {
        $user->gender = $request->input('gender');
    }
    if ($request->has('countryCode')) {
        $user->country_code = $request->input('countryCode');
    }

    if ($request->has('country_code_w_a')) {
        $user->country_code_w_a = $request->input('country_code_w_a');
    }

    if ($request->has('phone')) {
        $user->phone = $request->input('phone');
    }

    if ($request->has('whatsapp')) {
        $user->whatsapp = $request->input('whatsapp');
    }

    if ($request->has('zipCode')) {
        $user->zip_code = $request->input('zipCode');
    }

    if ($request->has('address')) {
        $user->address = $request->input('address');
    }
    if ($request->filled('title')) {
     $user->title_en    = $request->input('title');
    }
    if ($request->has('language')) {
        $user->language = $request->input('language');
    }
    if ($request->filled('timezone')) {
      $user->timezone      = $request->input('timezone');
    }
     if ($request->has('currency')) {
        $user->currency = $request->input('currency');
    }

    if ($request->has('country_id')) {
        $user->country_id = $request->input('country_id');
    }

    if ($request->has('state_id')) {
        $user->state_id = $request->input('state_id');
    }

    if ($request->has('HaveCompany')) {
        $user->HaveCompany = $request->input('HaveCompany');
    }

    if ($request->hasFile('imgUrl')) {
        $img = $request->file('imgUrl');
        $imgPath = $img->store('users', ['disk' => 'public_uploads']);

        // Resize image
        Image::make(Storage::disk('public_uploads')->path($imgPath))
            ->resize(null, 200, function ($con) {
                $con->aspectRatio();
            })->save();

        $user->img_url = $imgPath;
    }

    $user->save();

    return response()->json([
        'success' => true,
        'message' => __('api.ProfileUpdated'),
        'data' => [
           'firstName' => $user->first_name_en,
           'lastName'  => $user->last_name_en,
           'lastName'  => $user->last_name_en,
            'email'         => $user->email,
            'phone'          => $user->phone,
            'gender'         => $user->gender,
            'whatsapp'       => $user->whatsapp,
            'countryCode'    => $user->country_code,
            'country_code_w_a' => $user->country_code_w_a,
            'zipCode'       => $user->zip_code,
            'address'        => $user->address,
            'title'          => $user->title_en,
            'language'       => $user->language,
            'timezone'       => $user->timezone,
            'currency'       => $user->currency,
            'country'          => optional($user->country)->{'name_' . $locale},
            'state'            => optional($user->state)->{'name_' . $locale},
            'HaveCompany'    => $user->HaveCompany,
            'imgUrl'    => $user->img_url ? Storage::disk('public_uploads')->url($user->img_url) : null,
        ]
    ]);
}
 /**
     * @OA\Put(
     *     path="/api/profile/password",
     *     summary="Update user password",
     *     description="Change the authenticated user's password.",
     *     operationId="updatePassword",
     *     tags={"User"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"current_password", "password", "password_confirmation"},
     *             @OA\Property(property="current_password", type="string", example="oldpassword123"),
     *             @OA\Property(property="password", type="string", example="newpassword456"),
     *             @OA\Property(property="password_confirmation", type="string", example="newpassword456")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Password updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Password updated successfully")
     *         )
     *     )
     * )
     */
public function updatePassword(Request $request)
{
    $user = Auth::user();

    $request->validate([
        'current_password'      => ['required'],
        'password'              => ['required', 'confirmed', 'min:8'],
    ]);

    if (!Hash::check($request->current_password, $user->password)) {
        throw ValidationException::withMessages([
            'current_password' => [__('api.passwordIncorrect')],
        ]);
    }

    $user->password = Hash::make($request->password);
    $user->save();

    return response()->json([
        'success' => true,
        'message' => __('api.Passwordupdated'),
    ]);
}
/**
     * @OA\Put(
     *     path="/api/profile/bio",
     *     summary="Update mentor bio",
     *     description="Update mentor bio details such as specialization, experience, and mentorship approach.",
     *     operationId="updateBio",
     *     tags={"User"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="bio", type="string"),
     *             @OA\Property(property="specialization", type="string"),
     *             @OA\Property(property="linkedin", type="string", format="url"),
     *             @OA\Property(property="entrepreneurial_experience", type="boolean"),
     *             @OA\Property(property="entrepreneurial_experience_brief", type="string"),
     *             @OA\Property(property="profile_privacy", type="boolean"),
     *             @OA\Property(property="mentorship_approach", type="array", @OA\Items(type="string")),
     *             @OA\Property(property="expertise_ids", type="array", @OA\Items(type="integer"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Mentor bio updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Mentor bio updated successfully")
     *         )
     *     )
     * )
     */
public function updateBio(Request $request)
{
    $user = Auth::user();

    if (!$user->hasRole('Mentor')) {
        return response()->json([
            'success' => false,
            'message' => __('api.forbidden')
        ], 403);
    }

    $validated = $request->validate([
        'bio'                            => 'nullable|string|max:255',
        'specialization'                => 'nullable|string|max:250',
        'linkedin'                      => 'nullable|url|max:250',
        'entrepreneurialExperience'    => 'nullable|boolean',
        'entrepreneurialExperienceBrief' => 'nullable|string',
        'profilePrivacy'               => 'nullable|boolean',
        'expertise_ids'                 => 'nullable|array',
        'expertise_ids.*'               => 'exists:expertises,id',
    ]);

    $approachInput = $request->input('mentorshipApproach');
    $allowedApproaches = ['E-mail', 'Phone Calls', 'Video Calls', 'In-Person Meetings'];

    $mentorshipApproach = [];

    if ($approachInput) {
        if (is_array($approachInput)) {
            foreach ($approachInput as $value) {
                if (in_array($value, $allowedApproaches)) {
                    $mentorshipApproach[] = $value;
                }
            }
        } elseif (is_string($approachInput) && in_array($approachInput, $allowedApproaches)) {
            $mentorshipApproach[] = $approachInput;
        }
    }

    UsersBio::updateOrCreate(
        ['user_id' => $user->id],
        [
            'bio_ar'                          => $validated['bio'] ?? null,
            'bio_en'                          => $validated['bio'] ?? null,
            'specialization_ar'              => $validated['specialization'] ?? null,
            'specialization_en'              => $validated['specialization'] ?? null,
            'linkedin'                       => $validated['linkedin'] ?? null,
            'entrepreneurial_experience'     => $validated['entrepreneurialExperience'] ?? 0,
            'entrepreneurial_experience_brief' => $validated['entrepreneurialExperienceBrief'] ?? '',
            'profile_privacy'                => $validated['profilePrivacy'] ?? 1,
            'mentorship_approach'            => json_encode($mentorshipApproach),
        ]
    );

    if (isset($validated['expertise_ids'])) {
        $user->mentorExpertises()->sync($validated['expertise_ids']);
    }

    return response()->json([
        'success' => true,
        'message' => __('api.MentorBioUpdated'),
    ]);
}


}
