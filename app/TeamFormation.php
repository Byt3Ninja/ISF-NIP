<?php

namespace App;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use stdClass;

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
        'img_team'
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
            ->leftJoin('screening_evaluation_results AS ser', 'ser.company_id', '=', 'c.id')
            ->leftJoin('screening_evaluation AS se', 'ser.evaluation_id', '=', 'se.id')
            ->leftJoin('steps AS stp', 'stp.id', '=', 'se.step_id')
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
            $team->img = $t->team_img_team;
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
            $metadata = ScreeningEvaluationResults::metadata( $stepId );
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
                $published = true;

                foreach( $judges as $judge )
                {
                    if( in_array($company_id, $judge->startups->toArray()) )
                    {
                        $scoped_company = collect($judge->companies)->where('id', $company_id)->first();
                        if( $scoped_company )
                        {
                            if( strtolower($scoped_company->status) != 'published' ) { $published = false; }
                            if( $published == false ) { continue( 2 ); }
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
                $scoped_teams[$index]->published = $published;
            }
            $output = $output->merge($scoped_teams);
        }
        $output = $output->where('published', true)->filter(function($item) {
            return count( $item->judges ) > 0;
        })->values();
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
