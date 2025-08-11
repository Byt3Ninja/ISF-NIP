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
            ])
            ->leftJoin('team_members AS tm', 'tm.team_formation_id', '=', 'tf.id')
            ->leftJoin('users AS u', 'u.id', '=', 'tm.user_id')
            ->leftJoin('companies AS c', 'tm.Company_id', '=', 'c.id')
            ->leftJoin('screening_evaluation_results AS ser', 'ser.company_id', '=', 'c.id')
            ->leftJoin('screening_evaluation AS se', 'ser.evaluation_id', '=', 'se.id')
            ->leftJoin('steps AS stp', 'stp.id', '=', 'se.step_id')
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
            return $team;
        })->values();

        return $teams;
    }
}
