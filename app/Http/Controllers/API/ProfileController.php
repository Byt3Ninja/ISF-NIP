<?php

namespace App\Http\Controllers\API;

use App\MentorshipTask;
use App\MentorshipRequest;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
   /**
     * @OA\Get(
     *     path="/api/me",
     *     summary="Get authenticated user profile",
     *     description="Returns profile data of the authenticated user including mentorship statistics and sessions if the user has role Mentor or Registered.",
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


    return response()->json([
            'success' => true,
            'data'   => $data,
        ],200);
    }
}
