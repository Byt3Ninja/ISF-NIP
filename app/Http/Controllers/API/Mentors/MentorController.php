<?php

namespace App\Http\Controllers\API\Mentors;

use App\User;
use App\Traits\ApiResponder;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\MentorResource;

class MentorController extends Controller
{
    use ApiResponder;
    /**
 * @OA\Get(
 *     path="/api/v1/mentors/{mentor_id}",
 *     summary="Get full profile of a mentor",
 *     tags={"Mentors"},
 *     security={{"sanctum":{}}},
 *     @OA\Parameter(
 *         name="mentor_id",
 *         in="path",
 *         required=true,
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Mentor Profile",
 *         @OA\JsonContent(
 *             @OA\Property(property="id", type="integer", example=12),
 *             @OA\Property(property="name", type="string", example="Ahmed Mohamed"),
 *             @OA\Property(property="email", type="string", example="ahmed@example.com"),
 *             @OA\Property(property="title", type="string", example="Senior Consultant"),
 *             @OA\Property(property="imgUrl", type="string", format="url", example="https://yourdomain.com/storage/uploads/mentor.jpg"),
 *             @OA\Property(property="bio", type="string", example="An experienced mentor in business strategy."),
 *             @OA\Property(property="linkedin", type="string", format="url", example="https://linkedin.com/in/ahmedmentor"),
 *             @OA\Property(property="specialization", type="string", example="Business Strategy"),
 *             @OA\Property(
 *                 property="mentorExpertises",
 *                 type="array",
 *                 @OA\Items(type="string", example="Marketing")
 *             ),
 *             @OA\Property(property="rating", type="number", format="float", example=4.5),
 *             @OA\Property(property="programCount", type="integer", example=3),
 *             @OA\Property(
 *                 property="programIds",
 *                 type="array",
 *                 @OA\Items(type="integer", example=15)
 *             ),
 *             @OA\Property(property="role", type="string", example="Mentor")
 *         )
 *     ),
 *     @OA\Response(response=401, description="Unauthorized"),
 *     @OA\Response(response=403, description="Forbidden"),
 *     @OA\Response(response=404, description="Mentor not found")
 * )
 */
      public function show($mentorId)
    {
        $user = auth()->user();
        if(!$user->hasAnyRole(['Admin','organizationManager','subadmin','Registered'])){
            return $this->forbidden(__('api.forbidden'));
        }
        
        $query = User::with([
            'bio:id,user_id,bio_en,bio_ar,linkedin,specialization_en,specialization_ar',
            'mentorExpertises:id,title,title_ar',
            'roles:name'
        ])->whereHas('roles', function ($q) {
            $q->where('default_for', 'LIKE', '%mentor%');
        });

        if (!$user->hasRole('admin')) {
            $orgIds = $user->organizations_ids;
            $query->whereHas('mentorCycles', function ($q) use ($orgIds) {
                $q->whereIn('organization_id', $orgIds);
            });
        }

        $mentor = $query->find($mentorId);

          if (!$mentor) {
            return $this->notFound(__('api.mentorNotFound'));
        }
        return $this->success(new MentorResource($mentor));
    }

    /**
 * @OA\Get(
 *     path="/api/v1/mentors",
 *     summary="Get a list of all mentors",
 *     tags={"Mentors"},
 *     security={{"sanctum":{}}},
 *     @OA\Response(
 *         response=200,
 *         description="List of mentors",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="status", type="boolean", example=true),
 *             @OA\Property(
 *                 property="data",
 *                 type="array",
 *                 @OA\Items(
 *                     type="object",
 *                     @OA\Property(property="id", type="integer", example=12),
 *                     @OA\Property(property="name", type="string", example="Ahmed Mohamed"),
 *                     @OA\Property(property="email", type="string", example="ahmed@example.com"),
 *                     @OA\Property(property="title", type="string", example="Senior Consultant"),
 *                     @OA\Property(property="imgUrl", type="string", format="url", example="https://yourdomain.com/storage/uploads/mentor.jpg"),
 *                     @OA\Property(property="bio", type="string", example="An experienced mentor in business strategy."),
 *                     @OA\Property(property="linkedin", type="string", format="url", example="https://linkedin.com/in/ahmedmentor"),
 *                     @OA\Property(property="specialization", type="string", example="Business Strategy"),
 *                     @OA\Property(
 *                         property="mentorExpertises",
 *                         type="array",
 *                         @OA\Items(type="string", example="Marketing")
 *                     ),
 *                     @OA\Property(property="rating", type="number", format="float", example=4.5),
 *                     @OA\Property(property="programCount", type="integer", example=3),
 *                     @OA\Property(
 *                         property="programIds",
 *                         type="array",
 *                         @OA\Items(type="integer", example=15)
 *                     ),
 *                     @OA\Property(property="role", type="string", example="Mentor")
 *                 )
 *             )
 *         )
 *     ),
 *     @OA\Response(response=401, description="Unauthorized"),
 *     @OA\Response(response=403, description="Forbidden")
 * )
 */
    public function index()
    {
        $user = auth()->user();

        if (!$user->hasAnyRole(['Admin', 'organizationManager', 'subadmin', 'Registered'])) {
            return $this->forbidden(__('api.forbidden'));
        }

        $mentors = User::role('Mentor')->with([
            'bio:id,user_id,bio_en,bio_ar,linkedin,specialization_en,specialization_ar',
            'mentorExpertises:id,title,title_ar',
            'roles:name'
        ])->get();

        return $this->success(MentorResource::collection($mentors));
    }
}
