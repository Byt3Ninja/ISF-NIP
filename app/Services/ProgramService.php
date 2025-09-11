<?php
namespace App\Services;

use App\User;
use App\Company;
use App\Traits\ApiResponder;
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

   public function getProgramMentors($programId, $search = null, $page = 1, $limit = 10)
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

public function getProgramStartups($programId, $search = null, $page = 1, $limit = 10): mixed
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

    return $query->paginate($limit, ['*'], 'page', $page);
}

}
