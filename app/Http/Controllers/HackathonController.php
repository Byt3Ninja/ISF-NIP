<?php

namespace App\Http\Controllers;
// use App\Hackathon;

use App\Step;
use App\User;
use stdClass;
use App\Cycle;
use App\Company;
use App\Formfield;
use Carbon\Carbon;
use App\Team_member;
use App\CyclesMentors;
use App\TeamFormation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Beta\Microsoft\Graph\Model\Team;
use Illuminate\Support\Facades\Auth;
use App\Notifications\InviteToHackathon;
use Illuminate\Support\Facades\Notification;

class HackathonController extends Controller
{
    public function index()
    {
        $hackathons_query = "SELECT
        c.id as 'c_id',
        c.title as 'c_title',
        c.description as 'c_description',
        c.start as 'c_start_date',
        c.end as 'c_end_date',
        c.type as 'c_type',
        c.img_url as 'c_img_url',
        c.created_at as 'c_created_at',
        c.updated_at as 'c_updated_at',
      
        o.id as 'o_id',
        o.name_ar as 'o_name_ar',
        o.name_en as 'o_name_en',
        o.img_url as 'o_logo',
        o.created_at as 'o_created_at',
        o.updated_at as 'o_updated_at'
        FROM 
        cycles as c
        LEFT JOIN organizations as o ON c.organization_id = o.id
        WHERE c.type = 'Hackathon'
        AND `type` = 'Hackathon' ORDER BY c.created_at DESC";
        $hackathons_raw = collect(DB::select($hackathons_query));

        $step = Step::where('stype', 1)->whereIn('cycle_id', $hackathons_raw->pluck('c_id'))->get();
        $team_formation = TeamFormation::whereIn('cycle_id', $hackathons_raw->pluck('c_id'))->where('user_id', Auth::user()->id)->get();
        $hackathons = $hackathons_raw->map(function ($item, $key) use ($step, $team_formation) {
        $now = now();
        $start = \Carbon\Carbon::parse($item->c_start_date);
        $end = \Carbon\Carbon::parse($item->c_end_date);
        $status = $now->lt($start)
            ? 'Upcoming'
            : ($now->between($start, $end) ? 'Ongoing' : 'Finished');

            return [
                'id' => $item->c_id,
                'title' => $item->c_title,
                'image' => $item->c_img_url,
                'date' => $start->format('F d') . ' - ' . $end->format('d, Y'),
                'location' => $item->o_name_en ?? 'Unknown',
                'status' => $status,
                'description' => $item->c_description,
                'tags' => [],
                'step_name' => $step->where('cycle_id', $item->c_id)->first()?->title ?? 'N/A',
                'step_id' => $step->where('cycle_id', $item->c_id)->first()?->id ?? null,
                'step_start' => $step->where('cycle_id', $item->c_id)->first()?->from?->format('Y-m-d') ?? null,
                'step_end' => $step->where('cycle_id', $item->c_id)->first()?->to?->format('Y-m-d') ?? null,
                'team_formation_id' => $team_formation->where('cycle_id', $item->c_id)->first()?->id ?? null,
            ];
         });

            // استخراج الهاكاثونات المنتهية فقط
            $pastHackathons = $hackathons->where('status', 'Finished')->values();
            $hackathons = $hackathons->reject(function ($item) {
                return $item['status'] === 'Finished';
            })->values();
        // dd($hackathons);
           
        
        // return view('admin.hackathon.index', compact('hackathons'));
        return view('admin.hackathon.index', compact('hackathons', 'pastHackathons', 'step'));
    }

    public function manage($id)
    {
        $user = Auth::user();
                 
        $team_Formation = "SELECT 
        tf.id as 'tf_id',
        tf.name as 'tf_name',
        tf.description as 'tf_description',
        tf.cycle_id as 'tf_cycle_id',
        tf.step_id as 'tf_step_id',
        tf.user_id as 'tf_user_id',
        tf.project_name_ar as 'tf_projectName_ar',
        tf.project_name_en as 'tf_projectName_en',
        tf.project_description_ar as 'tf_projectDescription_ar',
        tf.project_description_en as 'tf_projectDescription_en',
        tf.target_track as 'tf_targetTrack',
        tf.project_status as 'tf_projectStatus',
        tf.created_at as 'tf_created_at',
        tf.updated_at as 'tf_updated_at',
        u.id as 'u_id',
        concat(u.first_name_en, ' ', u.last_name_en) as 'user_name_en',
        concat(u.first_name_ar, ' ', u.last_name_ar) as 'user_name_ar',
        u.email as 'u_email',
        u.img_url as 'u_img_url',
        c.id as 'c_id',
        c.name_en as 'c_name_en',
        c.name_ar as 'c_name_ar',
        c.logo as 'c_logo',
        tm.id as 'tm_id',
        tm.user_id as 'tm_user_id',
        tm.team_formation_id as 'tm_team_formation_id',
        tm.Name as 'tm_name',
        tm.email as 'tm_email',
        tm.acceptInvition as 'tm_acceptInvition',
        tm.isTeamLeader as 'tm_isTeamLeader',
        tm.created_at as 'tm_created_at',
        tm.updated_at as 'tm_updated_at',
        s.id as 's_id',
        s.title as 's_title',
        s.count_team_number as 's_count_team_number',
        s.stype as 's_stype'
        FROM 
        team_formations as tf
        LEFT JOIN users as u ON u.id = tf.user_id
        LEFT JOIN companies as c ON c.id = u.company_id
        LEFT JOIN team_members as tm ON tm.team_formation_id = tf.id
        LEFT JOIN steps as s ON s.id = tf.step_id
        LEFT JOIN cycles as cy ON cy.id = tf.cycle_id
        WHERE tm.team_formation_id = ? ";
        $team_Formation_raw = collect(DB::select($team_Formation, [$id]));
        $step = Step::where('cycle_id', $team_Formation_raw->first()->tf_cycle_id)
            ->where('stype', '6')
            ->first();
            $count_team = $step ? $step->count_team_number : 0;
       
        $team_Pinding = $team_Formation_raw->where('tm_isTeamLeader', 0)->where('tm_acceptInvition', 'pending');
       $team_members = $team_Formation_raw->where('tm_isTeamLeader', 0)->where('tm_acceptInvition', 'accepted');
        return view('admin.hackathon.manage-team', compact('team_Formation_raw', 'team_Pinding', 'team_members', 'step', 'count_team'));
    }

    public function myTeam()
    {
        // $hackathon = Hackathon::findOrFail($id);
        // return view('admin.hackathon.my-team', compact('hackathon'));
        $user = Auth::user();
        $team_Formation_q = "SELECT 
        tf.*,
        u.id as 'u_id',
        concat(u.first_name_en, ' ', u.last_name_en) as 'user_name_en',
        concat(u.first_name_ar, ' ', u.last_name_ar) as 'user_name_ar',
        u.email as 'u_email',
        u.img_url as 'u_img_url',
        tm.id as 'tm_id',
        tm.user_id as 'tm_user_id',
        tm.team_formation_id as 'tm_team_formation_id',
        tm.Name as 'tm_name',
        tm.email as 'tm_email',
        tm.acceptInvition as 'tm_acceptInvition',
        tm.isTeamLeader as 'tm_isTeamLeader' 
        FROM 
        team_formations as tf
        LEFT JOIN users as u ON u.id = tf.user_id
        LEFT JOIN team_members as tm ON tm.team_formation_id = tf.id
        WHERE tf.user_id = ? AND tm.acceptInvition = 'accepted'";
        $team_Formation_raw = collect(DB::select($team_Formation_q, [$user->id]));


        // dd($team_Forsmation_raw);
        return view('admin.hackathon.team-workspace', compact('team_Formation_raw'));
    }

    public function show($id) {

        $user = Auth::user();
        $l = app()->getLocale();
        $step = Step::where('cycle_id', $id)->get();
         if(!$step){
            if($user->hasRole('admin') || $user->hasRole('organizationmanager')){
                 return redirect()->back()->with(['class' => 'danger', 'message', 'Program not ready. Add step first.']);
            }else{
            return redirect()->back()->with(['class' => 'danger', 'message', 'Program not ready.']);
            }
            }

        
        $hackathons_query = "SELECT
        c.id as 'c_id',
        c.title as 'c_title',
        c.description as 'c_description',
        c.start as 'c_start_date',
        c.end as 'c_end_date',
        c.type as 'c_type',
        c.created_at as 'c_created_at',
        c.updated_at as 'c_updated_at',
        o.id as 'o_id',
        o.name_ar as 'o_name_ar',
        o.name_en as 'o_name_en',
        o.img_url as 'o_logo',
        o.created_at as 'o_created_at',
        o.updated_at as 'o_updated_at',
        u.id as 'U_id',
        concat(u.first_name_en, ' ', u.last_name_en) as 'user_name_en',
        concat(u.first_name_ar, ' ', u.last_name_ar) as 'user_name_ar',
        u.email as 'u_email',
        com.id as 'Com_id',
        com.name_en as 'Com_name_en',
        com.name_ar as 'Com_name_ar',
        com.logo as 'Com_logo',
        com.cycle as 'Com_cycle',
        com.step as 'Com_step'
        FROM 
        cycles as c
        LEFT JOIN companies as com ON c.id = com.cycle
        LEFT JOIN users as u ON u.company_id = com.id
        LEFT JOIN organizations as o ON c.organization_id = o.id
        WHERE c.type = 'Hackathon'
        AND c.id = ? ";
        
        $hackathons_query .= " ORDER BY c.created_at DESC";
        $hackathons_raw = collect(DB::select($hackathons_query, [$id]));
       
       
        if ($hackathons_raw->isNotEmpty()) {
            $item = $hackathons_raw->first();

            $now = now();
            $start = Carbon::parse($item->c_start_date);
            $end = Carbon::parse($item->c_end_date);

            $status = $now->lt($start)
                ? 'Upcoming'
                : ($now->between($start, $end) ? 'Ongoing' : 'Finished');

            $status_ar = match($status) {
                'Upcoming' => 'قادم',
                'Ongoing' => 'قائم',
                'Finished' => 'منتهي',
                default => '',
            };
                $step = Step::where('cycle_id', $item->Com_cycle)->get();
            $currentHackathon = [
                'title' => $item->c_title,
                'title_ar' => $item->c_title_ar ?? '',
                'date' => $start->format('F d') . ' - ' . $end->format('d, Y'),
                'date_ar' => $start->format('d') . ' - ' . $end->format('d') . ' ' . __('months.'.$end->format('m')) . ' ' . $end->format('Y'),
                'location' => $item->o_name_en ?? 'Unknown',
                'location_ar' => $item->o_name_ar ?? 'غير معروف',
                'status' => $status,
                'status_ar' => $status_ar,
                'description' => $item->c_description,
                'description_ar' => $item->c_description_ar ?? '',
                'tags' => [],      // أضف التاجز هنا إذا عندك جدول مرتبط
                'tags_ar' => [],   // نفس الشيء
                'step_id' => $step->where('id', $item->Com_step)->first()?->id ?? null,
                'step_name' => $step->where('id', $item->Com_step)->first()?->title ?? 'N/A',
                'step_stype' => $step->where('id', $item->Com_step)->first()?->stype ?? null,
                'step_start' => $step->where('id', $item->Com_step)->first()?->from?->format('Y-m-d') ?? null,
                'step_end' => $step->where('id', $item->Com_step)->first()?->to?->format('Y-m-d') ?? null,
                'cycle_id' => $id,
                'Company_id' => $item->Com_id,
                'max_team' => $step->where('id', $item->Com_step)->first()?->count_team_number ?? 0,
               
            ];
        }

                $data = [];
            $l = app()->getLocale();
            $forms = "SELECT 
            FD.company_id as 'FD_company_id',
            FD.user_id as 'FD_user_id',
            FD.step_id as 'FD_step_id',
            FD.question as 'FD_question',
            FD.answer as 'FD_answer',
            FD.created_at as 'FD_created_at',
            FD.updated_at as 'FD_updated_at',
            FD.deleted_at as 'FD_deleted_at',
            C.name_en as 'C_name_en',
            C.name_ar as 'C_name_ar',
            C.id as 'C_id',
            C.step as 'C_step',
            Concat(U.first_name_en, ' ', U.last_name_en) as 'user_name_en',
            Concat(U.first_name_ar, ' ', U.last_name_ar) as 'user_name_ar',
            U.id as 'U_id',
            U.email as 'U_email',
            S.title as 'S_title',
            S.id as 'S_id',
            S.cycle_id as 'S_cycle_id',
            S.data as 'S_data',
            CYC.title as 'CYC_title',
            CYC.id as 'CYC_id',
            CYC.organization_id as 'CYC_organization_id',
            O.name_en as 'O_name_en',
            O.name_ar as 'O_name_ar',
            O.id as 'O_id',
            BF.id as 'BF_id',
            BF.form_name as 'BF_form_name'
            FROM form_data as FD 
            LEFT JOIN companies as C ON C.id = FD.company_id
            LEFT JOIN users as U ON U.id = FD.user_id
            LEFT JOIN steps as S ON S.id = FD.step_id
            LEFT JOIN builder_forms as BF ON BF.id = S.data
            LEFT JOIN cycles as CYC ON CYC.id = S.cycle_id
            LEFT JOIN organizations as O ON O.id = CYC.organization_id
            WHERE FD.deleted_at IS NULL AND CYC.id = ? ";
            $params = [];
            
            if($user->organization_id != null){
                $forms .= " AND CYC.organization_id = ? ";
                $forms = DB::select($forms, [$id, $user->organization_id]);
            }else{
                $forms = DB::select($forms, [$id]);
            }
            $forms = collect($forms);
            
            $C = 1;
            // نبدأ بتجميع كل الأسئلة حسب step_id
            $grouped = $forms->groupBy('FD_user_id');

            $data = $grouped->map(function ($group, $stepId) use ($l) {
                $first = $group->first();
            
                $submission = new stdClass;
                $submission->company_id = $first->FD_company_id;
                // $submission->user_id = $first->FD_user_id;
                $submission->user_name = $first->{'user_name_' . $l};
                $submission->email = $first->U_email;
                $submission->cycle_id = $first->CYC_id;
                $submission->step_id = $first->FD_step_id;
                $submission->user_id = $first->FD_user_id;
                $submission->company_step = $first->C_step;
                // $submission->form_name = $first->BF_form_name;
                // $submission->startup_name = $first->{'C_name_' . $l};
                // $submission->submitted_by = $first->{'user_name_' . $l};
                // $submission->step_name = $first->S_title;
                // $submission->program_name = $first->CYC_title;
                // $submission->organization_name = $first->O_name_en;
                $submission->submissionDate = $first->FD_created_at ?? $first->FD_updated_at;
                return $submission;
            })->values()->all(); // remove collection keys (numeric indexing)
        
                $data = array_values($data);

                            // dd($data);
                            $teams_q = "SELECT 
                            tf.id as tf_id,
                            tf.name as tf_name,
                            tf.description as tf_description,
                            tf.cycle_id as tf_cycle_id,
                            tf.step_id as tf_step_id,
                            tf.img_team as tf_img_team,
                            tm.id as tm_id,
                            tm.user_id as tm_user_id,
                            tm.acceptInvition as tm_acceptInvition,
                            tm.Name as tm_name,
                            tm.Email as tm_email,
                            tm.isTeamLeader as tm_isTeamLeader,
                            tm.acceptInvition as tm_acceptInvition,
                            tm.team_formation_id as tm_team_formation_id,
                            u.id as u_id,
                            concat(u.first_name_en, ' ', u.last_name_en) as user_name_en,
                            concat(u.first_name_ar, ' ', u.last_name_ar) as user_name_ar,
                            u.email as u_email,
                            u.img_url as u_img_url
                            FROM 
                            team_members as tm
                            LEFT JOIN team_formations as tf ON tm.team_formation_id = tf.id
                            LEFT JOIN users as u ON tm.user_id = u.id
                            
                            WHERE   tf.cycle_id = ?";
                            $teams = collect(DB::select($teams_q, [$id]));
            // $teamUser = $teams->where('tm_user_id', Auth::user()->id);
            // $teams_with_user = $teams->where('tm_team_formation_id', $teamUser->first()->tm_team_formation_id);
            // dd($teams_with_user);
            
            $teams = $teams->groupBy('tm_team_formation_id');

          $teams = $teams->map(function ($group, $teamFormationId) use ($id, $step, $user) {
    $first = $group->first();

    $team = new stdClass;
    $team->id = $first->tf_id;
    $team->name = $first->tf_name;
    $team->desc = $first->tf_description;
    $team->cycle_id = $first->tf_cycle_id;
    $team->step_id = $first->tf_step_id;
    $team->photo = $first->tf_img_team ?? null;
    $team->max_members = $step->where('stype', 6)->where('cycle_id', $id)->first()->count_team_number;
    $team->your_team = $group->where('tm_user_id', $user->id)->where('tm_isTeamLeader', 1)->where('tm_acceptInvition', 'accepted')->first()? true : false;
    $team->request_sent = $group->where('tm_user_id', $user->id)->whereIn('tm_acceptInvition', ['join', 'accepted'])->first()? true : false; //is_user_requested
    $team->already_joined = $group->where('tm_user_id', $user->id)->where('tm_acceptInvition', 'accepted')->first() ? true : false; //is_user_member
    $team->is_full = $group->where('tm_acceptInvition', 'accepted')->count() >= $team->max_members; //members_count
    $team->status = $group->where('tm_user_id', $user->id)->where('tm_acceptInvition', 'accepted')->first() ? 'Accepted' : 'join';
    

    // المستخدمين في هذا الفريق
    $team->members = $group->where('tm_acceptInvition', 'accepted')->map(function ($member) {
        $user = new stdClass;
        $user->id = $member->u_id;
        $user->name = $member->user_name_en;
        $user->email = $member->u_email;
        $user->img_url = $member->u_img_url;
        $user->is_leader = $member->tm_isTeamLeader;
        $user->request_sent = $member->tm_acceptInvition == 'join' ? true : false;
        $user->status = $member->tm_acceptInvition == 'accepted' ? 'Accepted' : 'join';
        return $user;
    })->values()->all();

    return $team;
});
        $teamsData = $teams->values()->all();

// dd($teams);
                                $allteams_q = "SELECT 
                                tf.id as tf_id,
                                tf.name as tf_name,
                                tf.description as tf_description,
                                tf.cycle_id as tf_cycle_id,
                                tf.step_id as tf_step_id,
                                tm.id as tm_id,
                                tm.user_id as tm_user_id,
                                tm.acceptInvition as tm_acceptInvition,
                                tm.Name as tm_name,
                                tm.Email as tm_email,
                                tm.isTeamLeader as tm_isTeamLeader,
                                tm.team_formation_id as tm_team_formation_id,
                                u.id as u_id,
                                concat(u.first_name_en, ' ', u.last_name_en) as user_name_en,
                                concat(u.first_name_ar, ' ', u.last_name_ar) as user_name_ar,
                                u.email as u_email
                                FROM 
                                team_formations as tf
                                LEFT JOIN team_members as tm ON tm.team_formation_id = tf.id
                                LEFT JOIN users as u ON tm.user_id = u.id

                                WHERE cycle_id=?";
                                $allteams_raw = collect(DB::select($allteams_q, [$id]));
                                $team_invition = $allteams_raw->where('tm_user_id', $user->id);
                // dd($team_invition);
                                $allteams = $allteams_raw->groupBy('tf_id')->map(function ($group) use ($user, $team_invition) {
                                   
                                    return [
                                        'tf_id' => $group->first()->tf_id,
                                        'tf_name' => $group->first()->tf_name,
                                        'tf_description' => $group->first()->tf_description,
                                        'tf_cycle_id' => $group->first()->tf_cycle_id,
                                        'tf_step_id' => $group->first()->tf_step_id,
                                        'tm_user_id' => $group->first()->tm_user_id,
                                        'tm_acceptInvition' => $team_invition->first()?->tm_acceptInvition,
                                        'members_count' => $group->count(),
                                        'members' => $group,
                                    ];
                                });
            // dd($allteams);

            $step = Step::where('cycle_id', $id)->get();
            $count_team = $step ? $step?->where('stype', '6')->first()?->count_team_number : 0;
                            
        // dd($allteams->first()['tm_acceptInvition']);
              
        if(Auth::user()->company?->step != null){
            
            $companyStep = Step::where('id', Auth::user()->company->step)->first();
        }else{
            $companyStep = null;
        }

		$mentors_q = "SELECT 
        u.id as u_id,
        concat(u.first_name_en, ' ', u.last_name_en) as user_name_en,
        concat(u.first_name_ar, ' ', u.last_name_ar) as user_name_ar,
        u.email as u_email,
        u.img_url as u_img_url,
        u.title_ar as u_title_ar,
        u.title_en as u_title_en,
        cm.cycle_id as cm_cycle_id,
        cm.user_id as cm_user_id,
        c.id as c_id,
        c.title as c_title,
        c.description as c_description
        FROM 
        users as u
        LEFT JOIN cycles_mentors as cm ON u.id = cm.user_id
        LEFT JOIN cycles as c ON cm.cycle_id = c.id
        WHERE c.id = ?";
        $mentors = collect(DB::select($mentors_q, [$id]));
        
        
      

            $judges_q = "SELECT * FROM finalscreenings as FS
            LEFT JOIN steps as s ON FS.step_id = s.id
            WHERE s.stype = 5 AND s.cycle_id = ?";
            $judges = collect(DB::select($judges_q, [$id]));
            if($judges->isEmpty()){
                $judges = collect([]);
                $judges_users = collect([]);
            }else{
                $judges_ids = json_decode($judges->first()?->judges);
                $judges_ids = array_map('intval', $judges_ids);
                $judges_users = DB::table('users as u')
                ->whereIn('u.id', $judges_ids)
                ->select(
                    'u.id',
                    DB::raw("CONCAT(u.first_name_ar, ' ', u.last_name_ar) as user_name_ar"),
                    DB::raw("CONCAT(u.first_name_en, ' ', u.last_name_en) as user_name_en"),
                    'u.email',
                    'u.img_url',
                    'u.title_ar',
                    'u.title_en'
                    )
                    ->get();
                }

        
   
        //        if ($step->to < now()->format('Y-m-d')){return redirect()->back()->with(['msg' => 'Error can\'t open form, step timed out.', 'class' => 'danger']);}
        
        $id_evi = $user->company?->step;
       $step_evi = $step->where('id', $id_evi)->first();
       $step_evi_next = $step->where('cycle_id', $id)->where('stype', 5)->first();
        //  if(!$step_evi){
        //     return redirect()->back()->with(['class' => 'danger', 'message', 'Program not ready.']);
        //     }else{
        $field_map_ids = Formfield::where('step_id', $id_evi)->where('builder_forms_id', $step_evi?->data)->select('id')->pluck('id')->toArray();
        $answers = [];
        $data_evi = [
            'start' => $step_evi_next?->from,
            'end' => $step_evi_next?->to,
            'stype' => $step_evi?->stype,
            'cycle_id' => $id,
            'title' => $step_evi?->title,
            'fields' => $step_evi?->fields()->where('builder_forms_id', $step_evi?->data)->orderBy('form_fields.id', 'asc')->get(),
            'form_id' => $step_evi?->id,
            'field_ids' => implode(",", $field_map_ids)
        ];
        //        if (Auth::user()->company->step==$id) {
        $answers = Formfield::join('form_data', 'form_fields.id', 'form_data.field_id')->where('user_id', Auth::id())->whereIn('form_data.field_id', $field_map_ids)->orderBy('id', 'ASC')->get();
    // }
		
        $days = request()->query('days', 14);
        $company = $user->company;
        $stepData = $company?->stepdata;
        $start = Carbon::now()->startOfDay()->format('Y-m-d H:i:s');
        $end = Carbon::now()->addDays($days)->endOfDay()->format('Y-m-d H:i:s');
 if ($company && $company->cycledata) {
        $sessions_q = "SELECT
                    ts.id,
                    ts.type,
                    ts.title,
                    ts.datetime,
                    ts.enddatetime AS `end_datetime`,
                    ts.duration,
                    ts.join_url,
                    ts.trainer_name,
                    ts.training_location AS `location`
                FROM
                    training_sessions ts
                WHERE
                    ts.step_id = ?
                ORDER BY
                    ts.datetime ASC
                LIMIT 5";
            $sessions = collect(DB::select($sessions_q, [$company->step]));
 }else{
     $sessions = collect([]);
 }
        $steps_q = "SELECT
                s.id,
                s.title,
                s.arr,
                s.stype,
                s.from,
                s.to,
                s.description,
                s.data
            FROM
                steps s
            WHERE
                s.cycle_id = $id
                AND s.cycle_id != 0
                AND s.cycle_id IS NOT null
            ORDER BY
                s.arr asc
        ";

        $steps = collect(DB::select($steps_q))->map(function ($step) use ($l, $company, $stepData, $id) {
            $step->from = Carbon::parse($step->from)->format('d-m-Y');
            $step->to = Carbon::parse($step->to)->format('d-m-Y');

            $status = $l == 'ar' ? 'لم تبدأ' : 'Not started';
            $color = 'label-light';
            $textColor = 'light';
            if (!empty($step) && !empty($stepData)) {

                if ($stepData->arr > $step->arr) {
                    $status = $l == 'ar' ? 'انتهت' : 'Completed';
                    $color = 'label-success';
                    $textColor = 'success';
                } elseif ($stepData->arr == $step->arr) {
                    $status = $l == 'ar' ? 'بدأت' : 'Started';
                    $color = 'label-primary';
                    $textColor = 'primary';
                }
            }
            $trType = $fLink = null;
            if ($company?->approved) {
                $trType = 'my/' . $step->id;
            } else {
                $fLink = 'view/' . $id;
            }

            $step->status = $status;
            $step->color = $color;
            $step->textColor = $textColor;
            $step->trType = $trType;
            $step->fLink = $fLink;
            return $step;
        });
    $nextSessions_q = "SELECT
                mr.id,
                mr.mentor_id,
                mr.user_id,
                mr.comment,
                mr.approved,
                mr.rating,
                mr.join_url,
                mr.zoom_date,
                mr.period,
                mr.rate_comment,
                mr.done,
                u.img_url AS `user_img_url`,
                u.first_name_en AS `user_first_name_en`,
                u.first_name_ar AS `user_first_name_ar`,
                u.last_name_en AS `user_last_name_en`,
                u.last_name_ar AS `user_last_name_ar`,
                u.title_en AS `user_title_en`,
                u.title_ar AS `user_title_ar`,
                c.id AS `company_id`,
                c.logo AS `company_logo`,
                c.name_en AS `company_name_en`,
                c.name_ar AS `company_name_ar`,
                m.first_name_en AS `mentor_first_name_en`,
                m.first_name_ar AS `mentor_first_name_ar`,
                m.last_name_en AS `mentor_last_name_en`,
                m.last_name_ar AS `mentor_last_name_ar`,
                m.img_url AS `mentor_img_url`,
                msr.rating AS `mentor_rating`
            FROM
                mentorship_requests mr
            LEFT JOIN
                users m ON m.id = mr.mentor_id
            LEFT JOIN
                users u ON u.id = mr.user_id
            LEFT JOIN
                companies c ON c.id = u.company_id
            LEFT JOIN
               mentorship_requests msr ON msr.mentor_id = m.id
            WHERE
                mr.user_id = $user->id
                AND (
                    mr.approved = 'Yes'
                    OR mr.approved IS null
                )
                AND mr.zoom_date between '$start' AND '$end'
            ORDER BY
                mr.zoom_date ASC
        ";
        $nextSessions = collect(DB::select($nextSessions_q));
        $nextSessions = $nextSessions->groupBy('id')->map(function ($groupedSession) use ($l) {
            $_session = $groupedSession->first();
            $session = new stdClass;
            $session->id = $_session->id;
            $session->mentor_id = $_session->mentor_id;
            $session->user_id = $_session->user_id;
            $session->comment = $_session->comment;
            $session->approved = $_session->approved;
            $session->rating = $_session->rating;
            $session->join_url = $_session->join_url;
            $session->zoom_date = $_session->zoom_date;
            $session->period = $_session->period;
            $session->rate_comment = $_session->rate_comment;
            $session->done = $_session->done;

            $user = new stdClass;
            $user->id = $_session->user_id;
            $user->image_url = User::image_handler($_session->user_img_url);
            $user->first_name = $_session->{'user_first_name_' . $l};
            $user->last_name = $_session->{'user_last_name_' . $l};
            $user->name = trim($user->first_name . ' ' . $user->last_name);
            $user->title = $_session->{'user_title_' . $l};

            $company = new stdClass;
            $company->id = $_session->company_id;
            $company->logo_url = Company::logo_handler($_session->company_logo);
            $company->name = $_session->{'company_name_' . $l};
            $user->company = $company;
            $session->user = $user;

            $mentor = new stdClass;
            $mentor->id = $_session->mentor_id;
            $mentor->first_name = $_session->{'mentor_first_name_' . $l};
            $mentor->last_name = $_session->{'mentor_last_name_' . $l};
            $mentor->name = trim($mentor->first_name . ' ' . $mentor->last_name);
            $mentor->image_url = User::image_handler($_session->mentor_img_url);
            $mentor->ratings = $groupedSession->sum('mentor_rating');
            $session->mentor = $mentor;
            return $session;
        })->values();

        if (!empty($stepData)) {
                $cType = $stepData->stype;
            }else{
                $cType = 0;
            }

        // return view('admin.hackathon.show', compact('hackathon'));
        return view('admin.hackathon.hackathon-details', compact('currentHackathon',
         'data', 
         'l', 
         'id', 
         'teamsData', 
         'allteams', 
         'team_invition', 
         'companyStep', 
         'count_team',
         'answers', 
         'data_evi', 
         'mentors', 
         'judges_users',
         'nextSessions',
         'sessions',
         'cType'
        ));
    }

    function applyHackathon($id)
    {
        $user = Auth::user();
        if($user->company == null){
            $company = new Company();
            $company->name_en = $user->first_name_en . ' ' . $user->last_name_en;
            $company->name_ar = $user->first_name_en . ' ' . $user->last_name_en;
            $company->state_id = $user->state_id;
            $company->address_en = 'tempAddress';
            $company->address_ar = 'tempAddress';
            $company->save();

            $user_update = User::find($user->id);
            $user_update->company_id = $company->id;
            $user_update->save();

        }

        $step = Step::where('stype', 1)->where('id', $id)->first();
        if (!$step) {
            return redirect()->back()->with(['msg' => 'Error opening the selected form.', 'class' => 'danger']);
        }
        //        if ($step->to < now()->format('Y-m-d')){return redirect()->back()->with(['msg' => 'Error can\'t open form, step timed out.', 'class' => 'danger']);}
        $id = $step->id;
        $field_map_ids = Formfield::where('step_id', $id)->where('builder_forms_id', $step->data)->select('id')->pluck('id')->toArray();
        $answers = [];
        $data = [
            'title' => $step->title,
            'fields' => $step->fields()->where('builder_forms_id', $step->data)->orderBy('form_fields.id', 'asc')->get(),
            'form_id' => $step->id,
            'field_ids' => implode(",", $field_map_ids)
        ];
        //        if (Auth::user()->company->step==$id) {
        $answers = Formfield::join('form_data', 'form_fields.id', 'form_data.field_id')->where('user_id', Auth::id())->whereIn('form_data.field_id', $field_map_ids)->orderBy('id', 'ASC')->get();
        //        }
        return view('admin.settings.cycles.form', $data)->with('step', $step)->with('c', $step->cycle_id)->with('answers', $answers);
    }

     public function hackathonSubmissions($id) {
      $user = Auth::user();
      $data = [];
      $l = app()->getLocale();
      $forms = "SELECT 
      FD.company_id as 'FD_company_id',
      FD.user_id as 'FD_user_id',
      FD.step_id as 'FD_step_id',
      FD.question as 'FD_questions',
      FD.answer as 'FD_answers',
      FD.created_at as 'FD_created_at',
      FD.updated_at as 'FD_updated_at',
      FD.deleted_at as 'FD_deleted_at',
      C.name_en as 'C_name_en',
      C.name_ar as 'C_name_ar',
      C.id as 'C_id',
      Concat(U.first_name_en, ' ', U.last_name_en) as 'user_name_en',
      Concat(U.first_name_ar, ' ', U.last_name_ar) as 'user_name_ar',
      U.id as 'U_id',
      S.title as 'S_title',
      S.id as 'S_id',
      S.cycle_id as 'S_cycle_id',
      CYC.title as 'CYC_title',
      CYC.id as 'CYC_id',
      CYC.organization_id as 'CYC_organization_id',
      O.name_en as 'O_name_en',
      O.name_ar as 'O_name_ar',
      O.id as 'O_id'
      FROM form_data as FD 
      LEFT JOIN companies as C ON C.id = FD.company_id
      LEFT JOIN users as U ON U.id = FD.user_id
      LEFT JOIN steps as S ON S.id = FD.step_id
      LEFT JOIN cycles as CYC ON CYC.id = S.cycle_id
      LEFT JOIN organizations as O ON O.id = CYC.organization_id
      LEFT JOIN form_fields as FF ON FF.builder_forms_id = S.data
      WHERE CYC.type = 'Hackathon' AND S.id = ? AND FD.deleted_at IS NULL";
      if($user->organization_id != null){
      $forms .= " AND CYC.organization_id = ? ";
      $forms = DB::select($forms, [$user->organization_id]);
      }else{
      $forms = DB::select($forms,[$id]);
      }
        $forms = collect($forms);

    
        $C = 1;
        $grouped = $forms->groupBy('FD_step_id');

        $data = $grouped->map(function ($group, $stepId) use ($l, &$C) {
            $first = $group->first();
            $item = new stdClass;
            $item->submission_id = $C++;
            $item->step_id = $stepId;
            $item->startup_name = $first->{'C_name_' . $l};
            $item->submitted_by = $group->pluck('user_name_' . $l)->unique()->implode(', ') ?? '';
            $item->step_name = $first->S_title;
            $item->program_name = $first->CYC_title;
            $item->question = $group->pluck('FD_questions')->unique()->values()->toArray();
            $item->answers = $group->pluck('FD_answers')->unique()->values()->toArray();
            $item->qa_an = $group->map(function ($row) {
            return [
                'question' => $row->FD_questions,
                'answer'   => $row->FD_answers,
            ];
            })->unique(function ($pair) {
                return $pair['question'] . '|' . $pair['answer']; // عشان يزيل التكرارات
            })->values()->toArray();
            $item->organization_name = $first->O_name_en;
            $item->created_at = $first->FD_created_at;
            return $item;
        })->values()->all();
        //    dd($data);
      return view('admin.settings.forms.form-submissions.index', compact('data'));
    }

    public function createTeam(Request $request)
    {
      
        $request->validate([
            'team_name' => 'required|string|unique:team_formations,name,max:255',
            'description' => 'nullable|string|max:1000',
            'cycle_id' => 'required|exists:cycles,id',
            'step_id' => 'required|exists:steps,id',
            'Project_Name_ar' => 'nullable|string|max:255',
            'Project_Name_en' => 'nullable|string|max:255',
            'Project_Description_ar' => 'nullable|string|max:1000',
            'Project_Description_en' => 'nullable|string|max:1000',
            'Target_Track' => 'nullable|integer',
            'Project_Status' => 'nullable|integer',

        ]);
        
        $user = Auth::user();
        if($user->company != null){
            $company = Company::find($user->company->id);
            if (!$company) {
                $company->name_en = $request->team_name;
                $company->name_ar = $request->team_name;
                $company->save();
            }
        }else{
            $company = new Company();
            $company->name_en = $request->team_name;
            $company->name_ar = $request->team_name;
            $company->state_id = $user->state_id;
            $company->address_en = $user->address;
            $company->address_ar = $user->address;
            $company->save();

            $user_update = User::find($user->id);
            $user_update->company_id = $company->id;
            $user_update->save();

        }



            $team_q = Team_member::where('user_id', $user->id)->where('acceptInvition', 'accepted')->get();
        if ($team_q->count() > 0) {
            return redirect()->back()->with('error', 'You already have a team.');
        }

         

        $teamformation = new TeamFormation();
        $teamformation->name = $request->team_name;
        $teamformation->description = $request->description;
        $teamformation->cycle_id = $request->cycle_id;
        $teamformation->step_id = $request->step_id;
        $teamformation->user_id = $user->id;
        $teamformation->project_name_ar = $request->Project_Name_ar;
        $teamformation->project_name_en = $request->Project_Name_en;
        $teamformation->project_description_ar = $request->Project_Description_ar;
        $teamformation->project_description_en = $request->Project_Description_en;
        $teamformation->target_track = $request->Target_Track ?? 0;
        $teamformation->project_status = $request->Project_Status ?? 0;

        if ($request->hasFile('img_team')) {
                    $image = $request->file('img_team');
                    $imageName = time() . '.' . $image->getClientOriginalExtension();
                    $image->move(public_path('programs/teams/'), $imageName);
                    $teamformation->img_team = 'programs/teams/' . $imageName;
                }

        $teamformation->save();

        
        $team_member = new Team_member();
        $team_member->user_id = $user->id; // Assuming the user creating the team is the authenticated user
        $team_member->team_formation_id = $teamformation->id;
        $team_member->TeamMember = 'hackathon';
        $team_member->Name = $user->first_name . ' ' . $user->last_name;
        $team_member->email = $user->email;
        $team_member->acceptInvition = 'accepted';
        $team_member->isTeamLeader = 1;
        $team_member->company_id = $user->company_id; 
        $team_member->team_formation_id = $teamformation->id;
        $team_member->save();

        return redirect()->back()->with('success', 'Team created successfully.');
    }

    public function EditProjectIdea(Request $request)
    {

        $request->validate([
            'Project_Name_ar' => 'nullable|string|max:255',
            'Project_Name_en' => 'nullable|string|max:255',
            'Project_Description_ar' => 'nullable|string|max:1000',
            'Project_Description_en' => 'nullable|string|max:1000',
            'Target_Track' => 'nullable|integer',
            'Project_Status' => 'nullable|integer',
        ]);
        $user = Auth::user();
        $team_q = TeamFormation::where('user_id', $user->id)->where('id', $request->team_formation_id)->first();
        if (!$team_q) {
            return redirect()->back()->with('error', 'You are not authorized to edit the project idea.');
        }
        $team_q->project_name_ar = $request->Project_Name_ar;
        $team_q->project_name_en = $request->Project_Name_en;
        $team_q->project_description_ar = $request->Project_Description_ar;
        $team_q->project_description_en = $request->Project_Description_en;
        $team_q->target_track = $request->Target_Track ?? 0;
        $team_q->project_status = $request->Project_Status ?? 0;
        $team_q->save();
        return redirect()->back()->with('success', 'Project idea updated successfully.');
        
    }

    public function invitation(Request $request){
        $request->validate([
            'memberEmail' => 'required|email',
            'user_id' => 'nullable|exists:users,id',
        ]);

        $email = $request->memberEmail;
        $user = Auth::user();
        $team_q = TeamFormation::where('user_id', $user->id)->first();
        if (!$team_q) {
            return redirect()->back()->with('error', 'You are not authorized to send invitations.');
        }
        $user_q = User::where('email', $request->memberEmail)->where('id', $request->user_id)->first();
        if (!$user_q) {
            $teamMember = new Team_member();
            $teamMember->Name = 'Waiting for user';
            $teamMember->TeamMember = 'hackathon';
            $teamMember->isTeamLeader = 0;
            $teamMember->team_formation_id = $team_q->id;
            $teamMember->email = $email;
            $teamMember->acceptInvition = 'pending';
            $teamMember->Company_id = $user->company_id;
            $teamMember->code = uniqid('inv_');
            $teamMember->expire_at = now()->addHours(24);
            $teamMember->save();
            
           Notification::route('mail', $email)->notify(new InviteToHackathon($user, $team_q, $teamMember));
           
            return redirect()->back()->with('success', 'Invitation sent successfully. Waiting for user to join.');
        }else{
     
        $teamMember = new Team_member();
        $teamMember->user_id = $request->user_id;
        $teamMember->Name = $user_q->first_name . ' ' . $user_q->last_name;
        $teamMember->TeamMember = 'hackathon';
        $teamMember->isTeamLeader = 0;
        $teamMember->team_formation_id = $team_q->id;
        $teamMember->email = $email;
        $teamMember->acceptInvition = 'pending';
        $teamMember->Company_id = $user->company_id;
        $teamMember->code = uniqid('inv_');
        $teamMember->expire_at = now()->addHours(24);
        $teamMember->save();
        $user_q->notify(new InviteToHackathon($user, $team_q, $teamMember));
        }
        

        // Send invitation email logic here

        return redirect()->back()->with('success', 'Invitation sent successfully.');
    }

    public function cancelInvitation(Request $request)
    {
        $user = Auth::user();
        $teamMember = Team_member::where('id', $request->id)->where('acceptInvition', 'pending')->first();
        if (!$teamMember) {
            return response()->json(['success' => false, 'message' => __("admin.Invitation_not_found.")]);
        }
        $teamMember->acceptInvition = 'cancelled';
        $teamMember->save();

        return response()->json(['success' => true, 'message' => __("admin.Invitation_cancelled_successfully.")]);
    }

    public function acceptInvitation($id)
    {
       
        $user = Auth::user();
        $isExpired = Team_member::where('id', $id)
            ->where('acceptInvition', 'pending')
            ->where('created_at', '<', now()->subHours(24))
            ->first();
           
            if($isExpired) {
                $isExpired->acceptInvition = 'expired';
                $isExpired->Name = $user->first_name . ' ' . $user->last_name;
                $isExpired->TeamMember = 'hackathon';
                $isExpired->isTeamLeader = 0;
                $isExpired->user_id = $user->id;
                $isExpired->save();
                return redirect()->back()->with('error', 'Invitation has expired.');
            }
                
                $teamMember = Team_member::where('id', $id)
                ->where('acceptInvition', 'pending')
                ->first();
            if (!$teamMember) {
                return redirect()->back()->with('error', 'Invitation not found.');
            }
                $teamMember->acceptInvition = 'accepted';
                $teamMember->Name = $user->first_name . ' ' . $user->last_name;
                $teamMember->TeamMember = 'hackathon';
                $teamMember->isTeamLeader = 0;
                $teamMember->user_id = $user->id;
                $teamMember->save();

                $allteamMembers = Team_member::where('Email', $teamMember->Email)
                ->where('acceptInvition', 'pending')
                ->get();
            
                foreach ($allteamMembers as $allteamMember) {
                    $allteamMember->acceptInvition = 'rejected';
                    $allteamMember->Name = $user->first_name . ' ' . $user->last_name;
                    $allteamMember->TeamMember = 'hackathon';
                    $allteamMember->isTeamLeader = 0;
                    $allteamMember->user_id = $user->id;
                    $allteamMember->save();
                }

        return redirect()->back()->with('success', 'Invitation accepted successfully.');
    }

    public function rejectInvitation($id)
    {
        $user = Auth::user();
        $isExpired = Team_member::where('id', $id)
            ->where('acceptInvition', 'pending')
            ->where('created_at', '<', now()->subHours(24))
            ->first();
           
            if($isExpired) {
                $isExpired->acceptInvition = 'expired';
                $isExpired->Name = $user->first_name . ' ' . $user->last_name;
                $isExpired->TeamMember = 'hackathon';
                $isExpired->isTeamLeader = 0;
                $isExpired->user_id = $user->id;
                $isExpired->save();
                return redirect()->back()->with('error', 'Invitation has expired.');
            }
                
                $teamMember = Team_member::where('id', $id)
                ->where('acceptInvition', 'pending')
                ->first();
            if (!$teamMember) {
                return redirect()->back()->with('error', 'Invitation not found.');
            }
                $teamMember->acceptInvition = 'rejected';
                $teamMember->Name = $user->first_name . ' ' . $user->last_name;
                $teamMember->TeamMember = 'hackathon';
                $teamMember->isTeamLeader = 0;
                $teamMember->user_id = $user->id;
                $teamMember->save();

                

        return redirect()->back()->with('success', 'Invitation rejected successfully.');
    }

    public function getOutTeam(Request $request)
    {
        $user = Auth::user();
        $teamMember = Team_member::where('id', $request->id)->where('acceptInvition', 'accepted')->first();
        if (!$teamMember) {
            return response()->json(['success' => false, 'message' => __("admin.Invitation_not_found.")]);
        }
        $teamMember->acceptInvition = 'removed';
        $teamMember->save();

        return response()->json(['success' => true, 'message' => __("admin.Member_removed_successfully.")]);
    }

        public function RequestJoin(Request $request)
    {
        $user = Auth::user();
        $teams = TeamFormation::where('id', $request->id)->first();

        if(!$teams){
            return redirect()->back()->with('error', 'Team not found.');
        }
                $teamMember = new Team_member;
                $teamMember->acceptInvition = 'join';
                $teamMember->Email = $user->email;
                $teamMember->Name = $user->first_name . ' ' . $user->last_name;
                $teamMember->TeamMember = 'hackathon';
                $teamMember->isTeamLeader = 0;
                $teamMember->user_id = $user->id;
                $teamMember->Company_id = $user->company_id ?? 0;
                $teamMember->team_formation_id = $teams->id;
                $teamMember->save();
                
        return response()->json(['success' => true, 'message' => __("admin.Request_sent_successfully.")]);
        
    }

       public function ApprovedRequestJoin(Request $request)
    {
        
        $teamMember = Team_member::where('id', $request->id)
            ->where('acceptInvition', 'join')
            ->first();
            if(!$teamMember) {
                return redirect()->back()->with('error', 'Request not found.');
            }
                $teamMember->acceptInvition = 'accepted';
                $teamMember->save();
                $allteamMembers = Team_member::where('Email', $teamMember->Email)
                ->where('acceptInvition', 'join')
                ->get();
                foreach ($allteamMembers as $allteamMember) {
                    $allteamMember->acceptInvition = 'rejected';
                    $allteamMember->save();
                }
        return response()->json(['success' => true, 'message' => __("admin.Request_Approved_successfully.")]);
    }
    public function RejectedRequestJoin(Request $request)
    {
        
        $teamMember = Team_member::where('id', $request->id)
            ->where('acceptInvition', 'join')
            ->first();
            if(!$teamMember) {
                return redirect()->back()->with('error', 'Request not found.');
            }
                $teamMember->acceptInvition = 'rejected';
                $teamMember->save();
                // $allteamMembers = Team_member::where('Email', $teamMember->Email)
                // ->where('acceptInvition', 'join')
                // ->get();
                // foreach ($allteamMembers as $allteamMember) {
                //     $allteamMember->acceptInvition = 'rejected';
                //     $allteamMember->save();
                // }
        return response()->json(['success' => true, 'message' => __("admin.Request_Approved_successfully.")]);
    }
    public function ViewSubmission($step_id,$user_id)
    {
        
        $user = Auth::user();
      $data = [];
      $l = app()->getLocale();
      $forms = "SELECT 
      FD.company_id as 'FD_company_id',
      FD.user_id as 'FD_user_id',
      FD.step_id as 'FD_step_id',
      FD.question as 'FD_question',
      FD.answer as 'FD_answer',
      FD.created_at as 'FD_created_at',
      FD.updated_at as 'FD_updated_at',
      FD.deleted_at as 'FD_deleted_at',
      C.name_en as 'C_name_en',
      C.name_ar as 'C_name_ar',
      C.id as 'C_id',
      C.step as 'C_step',
      Concat(U.first_name_en, ' ', U.last_name_en) as 'user_name_en',
      Concat(U.first_name_ar, ' ', U.last_name_ar) as 'user_name_ar',
      U.id as 'U_id',
      S.title as 'S_title',
      S.id as 'S_id',
      S.cycle_id as 'S_cycle_id',
      S.data as 'S_data',
      CYC.title as 'CYC_title',
      CYC.id as 'CYC_id',
      CYC.organization_id as 'CYC_organization_id',
      O.name_en as 'O_name_en',
      O.name_ar as 'O_name_ar',
      O.id as 'O_id',
      BF.id as 'BF_id',
      BF.form_name as 'BF_form_name'
      FROM form_data as FD 
      LEFT JOIN companies as C ON C.id = FD.company_id
      LEFT JOIN users as U ON U.id = FD.user_id
      LEFT JOIN steps as S ON S.id = FD.step_id
      LEFT JOIN builder_forms as BF ON BF.id = S.data
      LEFT JOIN cycles as CYC ON CYC.id = S.cycle_id
      LEFT JOIN organizations as O ON O.id = CYC.organization_id
      WHERE FD.deleted_at IS NULL AND FD.step_id = ? AND FD.user_id = ? ";
      $params = [];
     
      if($user->organization_id != null){
        $forms .= " AND CYC.organization_id = ? ";
        $forms = DB::select($forms, [$step_id, $user_id, $user->organization_id]);
      }else{
        $forms = DB::select($forms, [$step_id, $user_id]);
      }
      $forms = collect($forms);
      $C = 1;
      // نبدأ بتجميع كل الأسئلة حسب step_id
      $grouped = $forms->groupBy('FD_step_id');

      $data = $grouped->map(function ($group, $stepId) use ($l) {
          $first = $group->first();
      
          $submission = new stdClass;
          $submission->submission_id = $stepId;
          $submission->form_name = $first->BF_form_name;
          $submission->startup_name = $first->{'C_name_' . $l};
          $submission->submitted_by = $first->{'user_name_' . $l};
          $submission->step_name = $first->S_title;
          $submission->program_name = $first->CYC_title;
          $submission->organization_name = $first->O_name_en;
          $submission->submissionDate = $first->FD_created_at ?? $first->FD_updated_at;
          $submission->cycle_id = $first->S_cycle_id;
          $submission->company_id = $first->C_id;
          $submission->company_step = $first->C_step;
      
          // answers
          $submission->answers = $group->map(function ($item) use ($l) {
            $value = $item->FD_answer;
            
            // Check if the value is a file path
            if (is_string($value) && (strpos($value, '/') !== false || strpos($value, '\\') !== false)) {
              // Extract filename from path
              $filename = basename($value);
              $value = [
                'type' => 'file',
                'path' => $value,
                'file_name' => $filename,
                'file_url' => asset($value) // Generate accessible URL
              ];
            }
            
            return (object)[
              'user_id' => $item->FD_user_id,
              'name' => $item->{'user_name_' . $l},
              'label' => $item->FD_question,
              'value' => $value,
            ];
          })->groupBy('user_id')->values();

          return $submission;
      })->values()->all(); // remove collection keys (numeric indexing)
      
      $data = array_values($data);

// dd($data);
      return view('admin.hackathon.show', compact('data'));

    }

    public function viewAdminHackaton($id)
    {
      
        return view('admin.hackathon.admin.dashboard_admin');
    }
}
