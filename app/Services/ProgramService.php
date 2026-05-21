<?php
namespace App\Services;

use App\User;
use App\Company;
use App\Cycle;
use App\Traits\ApiResponder;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Repositories\ProgramRepository;
use Illuminate\Support\Facades\Storage;

class ProgramService
{
    use ApiResponder;
    protected $programRepository;

    public function __construct(ProgramRepository $programRepository)
    {
        $this->programRepository = $programRepository;
    }

   public function getProgramMentors($programId, $search = null, $page = 1, $limit = 10, $sort = 'joinedAt', $order = 'desc')
{
    $program = $this->programRepository->findById($programId);

    if (!$program) {
        return $this->notFound(__('api.program_not_found'));
    }

    $query = DB::table('cycles_mentors as cm')
        ->join('users', 'users.id', '=', 'cm.user_id')
        ->leftJoin('users_bio', 'users.id', '=', 'users_bio.user_id')
        ->leftJoin('mentor_expertise as me', 'users.id', '=', 'me.mentor_id')
        ->leftJoin('expertises as e', 'me.expertise_id', '=', 'e.id')
        ->leftJoin('mentorship_requests as mr', function ($join) {
            $join->on('users.id', '=', 'mr.mentor_id')
                ->whereNotNull('mr.rating');
        })
        ->where('cm.cycle_id', $programId)
        ->select(
            'users.id',
            'users.first_name_en',
            'users.last_name_en',
            'users.email',
            'users.title_en',
            'users.img_url',
            'users_bio.bio_en as bio',
            'users_bio.specialization_en as specialization',
            'users_bio.linkedin',
            'cm.created_at as joinedAt',
            DB::raw('AVG(mr.rating) as rating')
        );

    if ($search) {
        $query->where(function ($q) use ($search) {
            $q->where(DB::raw("CONCAT(users.first_name_en, ' ', users.last_name_en)"), 'like', "%{$search}%")
              ->orWhere('e.title', 'like', "%{$search}%")
              ->orWhere('users_bio.bio_en', 'like', "%{$search}%")
              ->orWhere('users_bio.specialization_en', 'like', "%{$search}%");
        });
    }

    $query->groupBy(
        'users.id',
        'users.first_name_en',
        'users.last_name_en',
        'users.title_en',
        'users.email',
        'users.img_url',
        'users_bio.bio_en',
        'users_bio.specialization_en',
        'users_bio.linkedin',
        'cm.created_at'
    );
    $allowedSorts = ['rating', 'joinedAt', 'first_name_en', 'last_name_en'];
    if (!in_array($sort, $allowedSorts)) {
        $sort = 'joinedAt';
    }

    $order = strtolower($order) === 'asc' ? 'asc' : 'desc';

    $query->orderBy($sort, $order);
    $mentors = $query->paginate($limit, ['*'], 'page', $page);

    $mentorIds = $mentors->pluck('id')->toArray();

    $expertiseMap = DB::table('mentor_expertise')
        ->join('expertises', 'mentor_expertise.expertise_id', '=', 'expertises.id')
        ->whereIn('mentor_expertise.mentor_id', $mentorIds)
        ->select('mentor_expertise.mentor_id', 'expertises.title')
        ->get()
        ->groupBy('mentor_id');

    $finalMentors = collect();

    foreach ($mentors as $mentor) {
        $user = User::find($mentor->id);

        $name = trim($mentor->first_name_en . ' ' . $mentor->last_name_en);
        $imgUrl = $mentor?->img_url
            ? Storage::disk('public_uploads')->url($mentor->img_url)
            : null;

        $formatted = [
            'id' => $mentor->id,
            'name' => $name,
            'imgUrl' => $imgUrl,
            'title' => $mentor->title_en,
            'email' => $mentor->email,
            'bio' => $mentor->bio,
            'specialization' => $mentor->specialization,
            'linkedin' => $mentor->linkedin,
            'joinedAt' => \Carbon\Carbon::parse($mentor->joinedAt)->toISOString(),
            'rating' => $mentor->rating ? round($mentor->rating, 1) : null,
            'mentorExpertises' => $expertiseMap->get($mentor->id)?->pluck('title')->toArray() ?? [],
            'programCount' => DB::table('cycles_mentors')->where('user_id', $mentor->id)->count(),
            'role' => $user?->getRoleNames()->first(),
        ];

        $finalMentors->push($formatted);
    }

    return $mentors->setCollection($finalMentors);
}

public function getProgramStartups($programId, $search = null, $page = 1, $limit = 10, $sort = 'id', $order = 'desc'): mixed
{
    $program = $this->programRepository->findById($programId);

    if (!$program) {
        return $this->notFound(__('api.program_not_found'));
    }

    $query = Company::with([
        'sectoR:id,name_en,name_ar',
        'state:id,name_en,name_ar',
        'city:id,name_en,name_ar',
        'country:id,name_en,name_ar',
        'teamMembers',
        'cycleRelation.organization' 
    ])
    ->where('cycle', $programId);

    if ($search) {
        $query->where(function ($q) use ($search) {
            $q->where('name_en', 'like', "%$search%")
              ->orWhere('name_ar', 'like', "%$search%");
        })
        ->orWhereHas('sectoR', function ($q) use ($search) {
            $q->where('name_en', 'like', "%$search%")
              ->orWhere('name_ar', 'like', "%$search%");
        });
    }
    $allowedSorts = ['id', 'name_en', 'name_ar', 'est_date', 'created_at'];
    if (!in_array($sort, $allowedSorts)) {
        $sort = 'id';
    }

    $order = strtolower($order) === 'asc' ? 'asc' : 'desc';
    $query->orderBy($sort, $order);

    return $query->paginate($limit, ['*'], 'page', $page);
}

    /**
     * Idempotent program resolver for the importer.
     *
     * Resolution rules:
     *   1. Search cycles by (title ci, type) across ALL orgs reachable to the user.
     *   2. Exact match (same title+type+start+end) in any reachable org → reuse it
     *      (the row's program implicitly determines the target org).
     *   3. Title+type match exists but different dates:
     *      - one match in a reachable org → create new cycle in that match's org
     *        (treats the match as the org-anchor; new cohort, same program family).
     *      - multiple matches across reachable orgs → create new cycle in $fallbackOrgId.
     *   4. No match anywhere → create new cycle in $fallbackOrgId.
     *
     * $data: ['title'=>string,'type'=>string,'start'=>'Y-m-d','end'=>'Y-m-d']
     * $fallbackOrgId: dropdown selection — used only when row can't self-resolve.
     * $reachableOrgIds: orgs the user is allowed to write to (Admin: all → null/[]).
     */
    public function findOrCreateForImport(int $fallbackOrgId, array $data, ?array $reachableOrgIds = null): Cycle
    {
        $title = trim($data['title']);
        $type  = trim($data['type']);
        $start = $data['start'];
        $end   = $data['end'];

        $q = Cycle::query()
            ->whereRaw('LOWER(title) = ?', [mb_strtolower($title, 'UTF-8')])
            ->where('type', $type);

        // null = no scoping (Admin); [] would match nothing → don't apply.
        if (is_array($reachableOrgIds) && !empty($reachableOrgIds)) {
            $q->whereIn('organization_id', $reachableOrgIds);
        }

        $existing = $q->get();

        // Exact-date reuse first.
        foreach ($existing as $cyc) {
            $cs = $cyc->start ? Carbon::parse($cyc->start)->toDateString() : null;
            $ce = $cyc->end   ? Carbon::parse($cyc->end)->toDateString()   : null;
            if ($cs === $start && $ce === $end) {
                return $cyc;
            }
        }

        // No exact match → create new cycle in the fallback (dropdown-selected) org.
        // Different-dates matches in other orgs are treated as different cohorts,
        // not as anchors for org resolution (they're a different program family).
        $cycle = Cycle::create([
            'title'           => $title,
            'type'            => $type,
            'start'           => $start,
            'end'             => $end,
            'organization_id' => $fallbackOrgId,
        ]);

        // Hackathon enrollment (HackathonEnrollmentService::resolveStepId) requires
        // a step on the cycle. The UI cycle-builder asks the admin to create a
        // Team Formation step (stype=6) before founders can join; the importer
        // can't pause for that, so seed a minimal one here.
        if ($type === 'Hackathon') {
            $now = Carbon::now();
            DB::table('steps')->insert([
                'cycle_id'          => $cycle->id,
                'title'             => 'Team Formation',
                'description'       => 'Team Formation',
                'stype'             => 6,
                'data'              => null,
                'arr'               => 1,
                'from'              => $start,
                'to'                => $end,
                'count_team_number' => 100,
                'created_at'        => $now,
                'updated_at'        => $now,
            ]);
        }

        return $cycle;
    }

}
