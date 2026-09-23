<?php

namespace App;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use stdClass;
use App\Enums\StepType;

class TeamFormation extends Model
{
    use HasFactory;
    protected $table = 'team_formations';
    
    protected $fillable = [
        'name',
        'description',
        'cycle_id',
        'step_id',
        'max_teams_members',
        'user_id',
        'project_name_ar',
        'project_name_en',
        'project_description_ar',
        'project_description_en',
        'target_track',
        'project_status',
        'region',
        'winner_status',
        'img_team',
        'group_id_chat'
    ];

    public function cycle()
    {
        return $this->belongsTo(Cycle::class);
    }
    public function step()
    {
        return $this->belongsTo(Step::class);
    }
    public function teamMembers()
    {
        return $this->hasMany(Team_member::class, 'team_formation_id');
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public static function maxAcceptedMembers($stepId)
    {
        $members = self::where('step_id', $stepId)->with(['teamMembers'])->get();
        $numberOfMembers = $members->map(function ($team) {
            $teamMembers = $team->teamMembers->where('acceptInvition', 'accepted')->values();
            return $teamMembers->count();
        })->max();

        return $numberOfMembers;
    }

    public static function teams( $cycleId )
    {
        $l = app()->getLocale();
        $teams = DB::table('team_formations AS tf')
            ->select([
                'tf.id AS team_id',
                'tf.region AS team_region',
                'tf.name AS team_name',
                'tf.project_name_ar AS team_project_name_ar',
                'tf.project_name_en AS team_project_name_en',
                'tf.project_description_ar AS team_project_description_ar',
                'tf.project_description_en AS team_project_description_en',
                'tf.project_status AS team_project_status',
                'tf.target_track AS team_target_track',
                'tf.winner_status AS team_winner_status',
                'tf.img_team AS team_img_team',
                'u.id AS user_id',
                'u.first_name_en AS user_first_name_en',
                'u.first_name_ar AS user_first_name_ar',
                'u.last_name_en AS user_last_name_en',
                'u.last_name_ar AS user_last_name_ar',
                'u.img_url AS user_img_url',
                'u.title_ar AS user_title_ar',
                'u.title_en AS user_title_en',
                'tm.id AS team_member_id',
                'tm.Name AS team_member_name',
                'tm.Email AS team_member_email',
                'tm.isTeamLeader AS team_member_is_leader',
                'c.id AS company_id',
                'c.name_en AS company_name_en',
                'c.name_ar AS company_name_ar',
                'ser.evaluation_id AS evaluation_id',
                'se.step_id AS step_id',
                'stp.title AS step_title',
                'pl.user_id AS like_user_id',
            ])
            ->leftJoin('team_members AS tm', 'tm.team_formation_id', '=', 'tf.id')
            ->leftJoin('users AS u', 'u.id', '=', 'tm.user_id')
            ->leftJoin('companies AS c', 'tm.Company_id', '=', 'c.id')
            // The cycle's final-evaluation step, resolved from the CYCLE rather
            // than from `companies.step`. `companies.step` is a moving pointer to
            // wherever the startup currently sits, so joining on it only matched
            // during the window a startup was parked on the final step: every
            // other team resolved a null step and was then dropped by winners(),
            // leaving the winner-selection table empty (GV-3798 / GV-3799).
            ->leftJoin('steps AS stp', function( $join ) use ( $cycleId ) {
                $join->on('stp.cycle_id', '=', 'c.cycle')
                     ->where('stp.stype', '=', StepType::FINAL_EVALUATION)
                     ->where('stp.cycle_id', '=', $cycleId);
            })
            ->leftJoin('screening_evaluation AS se', 'se.step_id', '=', 'stp.id')
            ->leftJoin('screening_evaluation_results AS ser', function( $join ) {
                $join->on('ser.company_id', '=', 'c.id')
                     ->on('ser.evaluation_id', '=', 'se.id');
            })
            ->leftJoin('project_likes AS pl', 'pl.team_id', '=', 'tf.id')
            ->where('tf.cycle_id', $cycleId)
            ->where('tm.acceptInvition', 'accepted')
            ->get();

        $teams = $teams->groupBy('team_id')->map(function( $groupedTeam ) use ( $l ) {
            $team = new stdClass;
            $t = $groupedTeam->first();
            $team->id = $t->team_id;
            $team->region = $t->team_region;
            $team->name = $t->team_name;
            $team->winner = $t->team_winner_status;
            // Team logos live outside the web root; expose the authorized route.
            $team->img = $t->team_img_team
                ? route('hackathon.teamLogo', basename($t->team_img_team))
                : null;
            $team->project = new stdClass;
            $team->project->name = $t->{'team_project_name_' . $l};
            $team->project->description = $t->{'team_project_description_' . $l};
            $team->project->status = $t->team_project_status;
            $team->project->track = $t->team_target_track;
            $team->project->winner = $t->team_winner_status;
            $team->members = $groupedTeam->groupBy('team_member_id')->map(function( $groupedMembers ) use ( $l ) {
                $teamMember = $groupedMembers->first();
                $member = new stdClass;
                $member->id = $teamMember->team_member_id;
                $member->name = $teamMember->team_member_name;
                $member->email = $teamMember->team_member_email;
                $member->role = $teamMember->{'user_title_' . $l};
                $member->isLeader = $teamMember->team_member_is_leader;
                $member->image = User::image_handler( $teamMember->user_img_url );
                return $member;
            })->values();
            $team->company = new stdClass;
            $team->company->id = $t->company_id;
            $team->company->name = $t->{'company_name_' . $l};
            $team->evaluation = $groupedTeam->whereNotNull('evaluation_id')->map(function($x) {
                return $x->evaluation_id;
            })->unique()->first();
            $team->step = $groupedTeam->whereNotNull('step_id')->map(function($x) {
                return $x->step_id;
            })->unique()->first();
            $team->step_title = $groupedTeam->whereNotNull('step_title')->map(function($x) {
                return $x->step_title;
            })->unique()->first();
            $team->likes = $groupedTeam->whereNotNull('like_user_id')->map(function($x) {
                return $x->like_user_id;
            })->unique();
            return $team;
        })->values();

        return $teams;
    }

    public static function winners( $teams )
    {
        $steps_ids = $teams->whereNotNull('step')->pluck('step')->unique()->toArray();
        $output = collect([]);
        foreach( $steps_ids as $stepId )
        {
            $check = ScreeningEvaluation::where('step_id', $stepId)->first();
            if( empty( $check ) )
			{
				$evaluation = new ScreeningEvaluation();
				$evaluation->step_id = $stepId;
				$evaluation->save();
			}
            $evaluationForm = ScreeningEvaluation::getEvaluationData( $stepId );
            // $allCompanies = true: metadata() otherwise scopes companies to
            // `where('step', $stepId)`, the same moving-pointer trap fixed in
            // teams(). Without it a startup that has already advanced past the
            // final step loses its judges and score even though a published
            // result exists for it.
            $metadata = ScreeningEvaluationResults::metadata( $stepId, null, null, true );
            $judges = $metadata['judges'];
            $judges = $judges->map(function( $item ) {
                $item->companies_ids = collect($item->companies)->pluck('id')->toArray();
                return $item;
            });

            $scoped_teams = $teams->where('step', $stepId)->values();
            foreach( $scoped_teams as $index => $team )
            {
                $totalScore = 0;
                $teamJudges = [];
                $company_id = $team->company->id;
                $publishedEvaluations = 0;
                $averageScore = 0;

                foreach( $judges as $judge )
                {
                    if( in_array($company_id, $judge->startups->toArray()) )
                    {
                        $scoped_company = collect($judge->companies)->where('id', $company_id)->first();
                        if( $scoped_company )
                        {
                            if( strtolower($scoped_company->status) != 'published' ) { continue; }
                            if( strtolower($scoped_company->status) === 'published' && isset( $scoped_company->score ) )
                            {
                                $scopedJudge = new stdClass;
                                $scopedJudge->id = $judge->id;
                                $scopedJudge->name = $judge->name;
                                $scopedJudge->phone = $judge->phone;
                                $scopedJudge->email = $judge->email;
                                $scopedJudge->image_url = $judge->image_url;
                                $scopedJudge->score = $scoped_company->score;
                                $teamJudges[] = $scopedJudge;
                                $totalScore += (float) $scoped_company->score;
                                $publishedEvaluations++;
                            }
                        }
                    }
                }

                if( $publishedEvaluations > 0 ) { $averageScore = round($totalScore / $publishedEvaluations, 1); }
                $scoped_teams[$index]->score = $averageScore;
                $scoped_teams[$index]->judges = $teamJudges;
            }
            $output = $output->merge($scoped_teams);
        }

        // Teams whose step never resolved (no final-evaluation step on the cycle
        // yet, or no company linked to the leader) are still submitted projects
        // and must appear in the winner-selection table so a rank can be assigned
        // -- previously they were silently dropped here. They render unscored,
        // behind the view's existing "awaiting evaluation" badge.
        $scored_ids = $output->pluck('id')->all();
        $unscored = $teams->whereNotIn('id', $scored_ids)->values();
        foreach( $unscored as $team )
        {
            $team->score = 0;
            $team->judges = [];
        }
        $output = $output->merge($unscored);

        $output = $output->values();
        $winnersData = $output->map(function( $team ) {
            return [
                'id' => $team->id,
                'team' => $team->name,
                'step' => $team->step_title,
                // 'project' => $team->project->name ?? '-',
                'project' => $team->project->name ?? ( $team->company->name ?? '-' ),
                'score' => $team->score,
                'judges' => $team->judges,
                'rank' => $team->project->winner ?? '',
                'leader' => $team->members->where('isLeader', 1)->first(),
                'likes' => $team->likes
            ];
        });

        return $winnersData;
    }

    public static function winnersMapping( $teams )
    {
        $teams = $teams->where('winner', 'selected')->values();
        $winners = $teams->map(function( $team ) {
            $winner = new stdClass;
            $winner->id = $team->id;
            $winner->name = $team->company->name;
            $winner->logo = $team->img ? asset($team->img) : 'https://placehold.co/400x200?text=' . urlencode($team->name);
            $winner->projectTitle = $team->project->name;
            $winner->teamName = $team->name;
            $winner->description = $team->project->description;
            $leader = $team->members->where('isLeader', true)->first(); 
            $winner->teamLeader = new stdClass;
            $winner->teamLeader->name = $leader?->name;
            $winner->teamLeader->role = $leader?->role;
            $winner->teamLeader->image = $leader?->image;
            $winner->teamMembers = $team->members->map(function( $team_member ) {
                $member = new stdClass;
                $member->name = $team_member->name;
                $member->role = $team_member->role;
                $member->image = $team_member->image;
                $member->isLeader = $team_member->isLeader == 1 ? true : false;
                return $member;
            });
            $winner->likes = $team->likes;
            return $winner;
        });

        return $winners;
    }
}
