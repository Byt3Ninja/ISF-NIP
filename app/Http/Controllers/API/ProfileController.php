<?php

namespace App\Http\Controllers\API;

use App\User;
use App\UsersBio;
use App\MentorshipTask;
use App\MentorshipRequest;
use App\Traits\ApiResponder;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\UpdateBioRequest;
use App\Http\Resources\ProfileResource;
use Illuminate\Support\Facades\Storage;
use App\Http\Resources\MentorBioResource;
use App\Http\Requests\UpdateProfileRequest;
use App\Http\Requests\UpdatePasswordRequest;
use Illuminate\Validation\ValidationException;
use Intervention\Image\ImageManagerStatic as Image;
use App\Services\FileManagerService;

class ProfileController extends Controller
{
    use ApiResponder;
 /**
 * @OA\Get(
 *     path="/api/me",
 *     summary="Get authenticated user profile",
 *     description="Returns profile data of the authenticated user including mentorship statistics, sessions, and mentor bio if applicable.",
 *     operationId="getMe",
 *     tags={"Profile"},
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
 *                 @OA\Property(property="id", type="integer", example=1),
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
      $data = $this->getProfileData($user);

      return $this->success($data);
    }
 /**
 * @OA\Post(
 *     path="/api/profile",
 *     summary="Update user profile",
 *     description="Update the authenticated user's profile information including contact info, language, timezone, and optionally image.",
 *     operationId="updateProfile",
 *     tags={"Profile"},
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
 *                 @OA\Property(property="countryCodeWA", type="string", example="+20"),
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
 *                 @OA\Property(property="HaveCompany", type="boolean", example=1, description="1 = Yes, 0 = No"),
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
 *                 @OA\Property(property="countryCodeWA", type="string", example="+20"),
 *                 @OA\Property(property="zipCode", type="string", example="12345"),
 *                 @OA\Property(property="address", type="string", example="123 Nile Street"),
 *                 @OA\Property(property="title", type="string", example="Software Engineer"),
 *                 @OA\Property(property="language", type="string", example="en"),
 *                 @OA\Property(property="timezone", type="string", example="Africa/Cairo"),
 *                 @OA\Property(property="currency", type="string", example="EGP"),
 *                 @OA\Property(property="country", type="string", example="Egypt"),
 *                 @OA\Property(property="state", type="string", example="Cairo"),
 *                 @OA\Property(property="imgUrl", type="string", example="https://yourapp.com/uploads/users/avatar.jpg")
 *             )
 *         )
 *     )
 * )
 */
    public function updateProfile(UpdateProfileRequest $request)
{
    $user = Auth::user();

    $user->fill([
        'first_name_en'   => $request->firstName ?? $user->first_name_en,
        'first_name_ar'   => $request->firstName ?? $user->first_name_ar,
        'last_name_en'    => $request->lastName ?? $user->last_name_en,
        'last_name_ar'    => $request->lastName ?? $user->last_name_ar,
        'email'           => $request->email ?? $user->email,
        'gender'          => $request->gender ?? $user->gender,
        'country_code'    => $request->countryCode ?? $user->country_code,
        'country_code_w_a'=> $request->countryCodeWA ?? $user->country_code_w_a,
        'phone'           => $request->phone ?? $user->phone,
        'whatsapp'        => $request->whatsapp ?? $user->whatsapp,
        'zip_code'        => $request->zipCode ?? $user->zip_code,
        'address'         => $request->address ?? $user->address,
        'title_en'        => $request->title ?? $user->title_en,
        'language'        => $request->language ?? $user->language,
        'timezone'        => $request->timezone ?? $user->timezone,
        'currency'        => $request->currency ?? $user->currency,
        'country_id'      => $request->country_id ?? $user->country_id,
        'state_id'        => $request->state_id ?? $user->state_id,
        'HaveCompany'     => $request->HaveCompany ?? $user->HaveCompany,
    ]);
    
    if ($request->hasFile('imgUrl')) {
        $img = $request->file('imgUrl');
        $imgPath = app(FileManagerService::class)->upload($img, 'users', 'public_uploads', 'image');

        // Resize image
        Image::make(Storage::disk('public_uploads')->path($imgPath))
            ->resize(null, 200, function ($con) {
                $con->aspectRatio();
            })->save();

        $user->img_url = $imgPath;
    }

    $user->save();

    return $this->success(new ProfileResource($user),[], __('api.ProfileUpdated'));
}
 /**
     * @OA\Put(
     *     path="/api/profile/password",
     *     summary="Update user password",
     *     description="Change the authenticated user's password.",
     *     operationId="updatePassword",
     *     tags={"Profile"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"currentPassword", "password", "password_confirmation"},
     *             @OA\Property(property="currentPassword", type="string", example="oldpassword123"),
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
public function updatePassword(UpdatePasswordRequest  $request)
{
    $user = Auth::user();

    if (!Hash::check($request->currentPassword, $user->password)) {
        throw ValidationException::withMessages([
            'currentPassword' => [__('api.passwordIncorrect')],
        ]);
    }

    $user->password = Hash::make($request->password);
    $user->save();

    return $this->success(null,[], __('api.Passwordupdated'));
}
/**
     * @OA\Put(
     *     path="/api/profile/bio",
     *     summary="Update mentor bio",
     *     description="Update mentor bio details such as specialization, experience, and mentorship approach.",
     *     operationId="updateBio",
     *     tags={"Profile"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="bio", type="string"),
     *             @OA\Property(property="specialization", type="string"),
     *             @OA\Property(property="linkedin", type="string", format="url"),
     *                  @OA\Property(
     *                 property="entrepreneurialExperience",
     *                 type="integer",
     *                 enum={0,1},
     *                 description="1 for true, 0 for false",
     *                 example=1
     *             ),
     *             @OA\Property(property="entrepreneurialExperienceBrief", type="string"),
     *              @OA\Property(
     *                 property="profilePrivacy",
     *                 type="integer",
     *                 enum={0,1},
     *                 description="1 for true, 0 for false",
     *                 example=1
     *             ),
     *             @OA\Property(property="mentorshipApproach", type="array", @OA\Items(type="string")),
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
public function updateBio(UpdateBioRequest  $request)
{
    $user = Auth::user();

    if (!$user->hasRole('Mentor')) {
        return response()->json([
            'success' => false,
            'message' => __('api.forbidden')
        ], 403);
    }

    $validated = $request->validated();

    $bio = UsersBio::firstOrNew(['user_id' => $user->id]);

    if (isset($validated['mentorshipApproach'])) {
        $approachInput = $request->input('mentorshipApproach');
        $allowedApproaches = ['E-mail', 'Phone Calls', 'Video Calls', 'In-Person Meetings'];
        $mentorshipApproach = [];

        if (is_array($approachInput)) {
            foreach ($approachInput as $value) {
                if (in_array($value, $allowedApproaches)) {
                    $mentorshipApproach[] = $value;
                }
            }
        } elseif (is_string($approachInput) && in_array($approachInput, $allowedApproaches)) {
            $mentorshipApproach[] = $approachInput;
        }

        $bio->mentorship_approach = json_encode($mentorshipApproach);
    }

    if (array_key_exists('bio', $validated)) {
        $bio->bio_en = $validated['bio'];
        $bio->bio_ar = $validated['bio'];
    }

    if (array_key_exists('specialization', $validated)) {
        $bio->specialization_en = $validated['specialization'];
        $bio->specialization_ar = $validated['specialization'];
    }

    if (array_key_exists('linkedin', $validated)) {
        $bio->linkedin = $validated['linkedin'];
    }

    if (array_key_exists('entrepreneurialExperience', $validated)) {
        $bio->entrepreneurial_experience = $validated['entrepreneurialExperience'];
    }

    if (array_key_exists('entrepreneurialExperienceBrief', $validated)) {
        $bio->entrepreneurial_experience_brief = $validated['entrepreneurialExperienceBrief'];
    }

    if (array_key_exists('profilePrivacy', $validated)) {
        $bio->profile_privacy = $validated['profilePrivacy'];
    }

    $bio->save();
    

    if (isset($validated['expertiseIds'])) {
        $user->mentorExpertises()->sync($validated['expertiseIds']);
    }

    $bio->load('user.mentorExpertises');
    return $this->success(new MentorBioResource($bio),[], __('api.MentorBioUpdated'));
}

public function showProfile($id)
{
    $user = User::with(['country', 'state', 'city', 'company'])->findOrFail($id);
    $data = $this->getProfileData($user);

    return $this->success($data);
}


private function getProfileData(User $user): array
{
    $user->img_url = $user->img_url ? Storage::disk('public_uploads')->url($user->img_url) : null;
    $locale = app()->getLocale();

    $data = [
        'imgUrl'           => $user->img_url,
        'id'               => $user->id,
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
        'country'          => optional($user->country)->{"name_$locale"},
        'state'            => optional($user->state)->{"name_$locale"},
        'city'             => optional($user->city)->{"name_$locale"},
        'language'         => $user->language,
        'haveCompany'      => (bool) $user->HaveCompany,
        'timeZone'         => $user->timezone,
        'currency'         => $user->currency,
        'role'             => $user->getRoleNames()->first(),
    ];
    
    if ((bool) $user->HaveCompany && $user->company) {
        $data['startupId'] = $user->company->id;
        $data['startupName'] = $user->company->{"name_$locale"};
    }

    if ($user->hasRole('Mentor') || $user->hasRole('Registered')) {
        $requests = MentorshipRequest::where('mentor_id', $user->id)->get();

        if ($user->hasRole('Mentor')) {
            $data['mentorshipTotalRequests'] = $requests->count();
            $data['mentorshipTotalHours'] = round($requests->sum('period') / 60, 2);
            $data['mentorshipAverageRating'] = round($requests->whereNotNull('rating')->avg('rating'), 1);
        }

        $tasks = MentorshipTask::with(['user.company', 'mentorshipRequest'])
            ->whereNotNull('title');

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

        $data['mentorshipSessions'] = $tasks->get()->map(function ($task) use ($locale) {
            $taskUser = $task->user;

            $userImg = $taskUser?->img_url
                ? Storage::disk('public_uploads')->url($taskUser->img_url)
                : null;

            $company = optional($taskUser->company);

            return [
                'fullName'    => trim($taskUser?->first_name_en . ' ' . $taskUser?->last_name_en),
                'imgUrl'      => $userImg,
                'company'     => $company?->{"name_$locale"},
                'companyLogo' => $company?->logo_url,
                'done' => optional($task->mentorshipRequest)->done == 1 ? 'Completed' : 'Uncompleted',
                'taskTitle'   => $task->title,
            ];
        })->filter()->values();
    }

    if ($user->hasRole('Mentor')) {
        $bio = UsersBio::where('user_id', $user->id)->first();
        $mentorBio = [];

        if ($bio) {
            $mentorBio = [
                'bio'                             => $locale === 'ar' ? $bio->bio_ar : $bio->bio_en,
                'specialization'                  => $locale === 'ar' ? $bio->specialization_ar : $bio->specialization_en,
                'linkedin'                        => $bio->linkedin,
                'entrepreneurialExperience'       => (bool) $bio->entrepreneurial_experience,
                'entrepreneurialExperience_brief' => $bio->entrepreneurial_experience_brief,
                'profilePrivacy'                  => (bool) $bio->profile_privacy,
                'mentorshipApproach'              => json_decode($bio->mentorship_approach, true) ?? [],
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
   
   if ($user->hasRole('Registered')) {
    $company = optional($user->company);
    $cycle   = optional($company->cycledata);

    $data['organizationId'] = $cycle->organization_id;
    $data['cycleId']        = $cycle->id;
   }

    return $data;
}


}
