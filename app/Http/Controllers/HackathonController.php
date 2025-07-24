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
use App\TeamFormation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Notifications\InviteToHackathon;
use Beta\Microsoft\Graph\Model\Team;
use Illuminate\Support\Facades\Notification;

class HackathonController extends Controller
{
    public function index()
{
    $hackathons_query = "
        SELECT
            c.id as c_id,
            c.title as c_title,
            c.description as c_description,
            c.start as c_start_date,
            c.end as c_end_date,
            c.type as c_type,
            c.created_at as c_created_at,
            c.updated_at as c_updated_at,
            o.id as o_id,
            o.name_ar as o_name_ar,
            o.name_en as o_name_en,
            o.img_url as o_logo,
            o.created_at as o_created_at,
            o.updated_at as o_updated_at
        FROM 
            cycles as c
        LEFT JOIN 
            organizations as o ON c.organization_id = o.id
        WHERE 
            c.type = 'Hackathon'
        ORDER BY 
            c.created_at DESC
    ";

    $hackathons_raw = collect(DB::select($hackathons_query));
    $cycleIds = $hackathons_raw->pluck('c_id');

    // Grouped by cycle_id for fast access
    $steps = Step::where('stype', 1)->whereIn('cycle_id', $cycleIds)->get()->groupBy('cycle_id');
    $teamFormations = TeamFormation::whereIn('cycle_id', $cycleIds)
        ->where('user_id', Auth::id())
        ->get()
        ->groupBy('cycle_id');

    $now = now();

    $hackathons = $hackathons_raw->map(function ($item) use ($steps, $teamFormations, $now) {
        $start = \Carbon\Carbon::parse($item->c_start_date);
        $end = \Carbon\Carbon::parse($item->c_end_date);

        $status = $now->lt($start)
            ? 'Upcoming'
            : ($now->between($start, $end) ? 'Ongoing' : 'Finished');

        $step = $steps[$item->c_id][0] ?? null;
        $teamFormation = $teamFormations[$item->c_id][0] ?? null;

        return [
            'id' => $item->c_id,
            'title' => $item->c_title,
            'date' => $start->format('F d') . ' - ' . $end->format('d, Y'),
            'location' => $item->o_name_en ?? 'Unknown',
            'status' => $status,
            'description' => $item->c_description,
            'tags' => [],
            'step_name' => $step?->title ?? 'N/A',
            'step_id' => $step?->id,
            'step_start' => $step?->from?->format('Y-m-d'),
            'step_end' => $step?->to?->format('Y-m-d'),
            'team_formation_id' => $teamFormation?->id,
        ];
    });

    // Separate past hackathons
    $pastHackathons = $hackathons->where('status', 'Finished')->values();
    $hackathons = $hackathons->where('status', '!=', 'Finished')->values();

    return view('admin.hackathon.index', compact('hackathons', 'pastHackathons', 'steps'));
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
        
        $team_Pinding = $team_Formation_raw->where('tm_isTeamLeader', 0)->where('tm_acceptInvition', 'pending');
       $team_members = $team_Formation_raw->where('tm_isTeamLeader', 0)->where('tm_acceptInvition', 'accepted');
        return view('admin.hackathon.manage-team', compact('team_Formation_raw', 'team_Pinding', 'team_members'));
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

public function show($id)
{
    $user = Auth::user();

    $hackathons_query = "SELECT
        c.id as c_id,
        c.title as c_title,
        c.title_ar as c_title_ar,
        c.description as c_description,
        c.description_ar as c_description_ar,
        c.start as c_start_date,
        c.end as c_end_date,
        o.name_en as o_name_en,
        o.name_ar as o_name_ar,
        com.step as Com_step
    FROM cycles as c
    LEFT JOIN companies as com ON c.id = com.cycle
    LEFT JOIN organizations as o ON c.organization_id = o.id
    WHERE c.type = 'Hackathon' AND c.id = ?
    ORDER BY c.created_at DESC";

    $hackathon_raw = collect(DB::select($hackathons_query, [$id]))->first();

    if (!$hackathon_raw) {
        abort(404, 'Hackathon not found');
    }

    $start = Carbon::parse($hackathon_raw->c_start_date);
    $end = Carbon::parse($hackathon_raw->c_end_date);
    $now = now();

    $status = $now->lt($start)
        ? 'Upcoming'
        : ($now->between($start, $end) ? 'Ongoing' : 'Finished');

    $status_ar = match($status) {
        'Upcoming' => 'قادم',
        'Ongoing' => 'قائم',
        'Finished' => 'منتهي',
        default => '',
    };

    $step = Step::where('cycle_id', $id)->get();
    $matchedStep = $step->where('id', $hackathon_raw->Com_step)->first();

    $currentHackathon = [
        'title' => $hackathon_raw->c_title,
        'title_ar' => $hackathon_raw->c_title_ar ?? '',
        'date' => $start->format('F d') . ' - ' . $end->format('d, Y'),
        'date_ar' => $start->format('d') . ' - ' . $end->format('d') . ' ' . __('months.' . $end->format('m')) . ' ' . $end->format('Y'),
        'location' => $hackathon_raw->o_name_en ?? 'Unknown',
        'location_ar' => $hackathon_raw->o_name_ar ?? 'غير معروف',
        'status' => $status,
        'status_ar' => $status_ar,
        'description' => $hackathon_raw->c_description,
        'description_ar' => $hackathon_raw->c_description_ar ?? '',
        'tags' => [],
        'tags_ar' => [],
        'step_id' => $matchedStep?->id,
        'step_name' => $matchedStep?->title ?? 'N/A',
        'step_stype' => $matchedStep?->stype,
        'step_start' => $matchedStep?->from?->format('Y-m-d'),
        'step_end' => $matchedStep?->to?->format('Y-m-d'),
        'cycle_id' => $id,
    ];

    // === Forms & Submission Data ===
    $l = app()->getLocale();
    $formQuery = "
        SELECT 
            FD.*,
            C.name_en as C_name_en,
            C.name_ar as C_name_ar,
            C.step as C_step,
            CONCAT(U.first_name_en, ' ', U.last_name_en) as user_name_en,
            CONCAT(U.first_name_ar, ' ', U.last_name_ar) as user_name_ar,
            S.title as S_title,
            S.data as S_data,
            CYC.title as CYC_title,
            CYC.organization_id as CYC_organization_id,
            O.name_en as O_name_en,
            O.name_ar as O_name_ar,
            BF.form_name as BF_form_name
        FROM form_data FD
        LEFT JOIN companies C ON C.id = FD.company_id
        LEFT JOIN users U ON U.id = FD.user_id
        LEFT JOIN steps S ON S.id = FD.step_id
        LEFT JOIN builder_forms BF ON BF.id = S.data
        LEFT JOIN cycles CYC ON CYC.id = S.cycle_id
        LEFT JOIN organizations O ON O.id = CYC.organization_id
        WHERE FD.deleted_at IS NULL AND CYC.id = ?
    ";

    $params = [$id];
    if ($user->organization_id) {
        $formQuery .= " AND CYC.organization_id = ?";
        $params[] = $user->organization_id;
    }

    $forms = collect(DB::select($formQuery, $params));
    $grouped = $forms->groupBy('FD_step_id');

    $data = $grouped->map(function ($group) use ($l) {
        $first = $group->first();
        return (object)[
            'company_id' => $first->company_id,
            'user_id' => $first->user_id,
            'cycle_id' => $first->CYC_id,
            'submission_id' => $first->C_step,
            'form_name' => $first->BF_form_name,
            'startup_name' => $first->{'C_name_' . $l},
            'submitted_by' => $first->{'user_name_' . $l},
            'step_name' => $first->S_title,
            'program_name' => $first->CYC_title,
            'organization_name' => $first->O_name_en,
            'submissionDate' => $first->created_at ?? $first->updated_at,
            'answers' => $group->map(function ($item) use ($l) {
                $value = $item->answer;
                if (is_string($value) && (str_contains($value, '/') || str_contains($value, '\\'))) {
                    $filename = basename($value);
                    $value = [
                        'type' => 'file',
                        'path' => $value,
                        'file_name' => $filename,
                        'file_url' => asset($value)
                    ];
                }
                return (object)[
                    'user_id' => $item->user_id,
                    'company_id' => $item->company_id,
                    'step_id' => $item->step_id,
                    'company_step' => $item->C_step,
                    'name' => $item->{'user_name_' . $l},
                    'label' => $item->question,
                    'value' => $value
                ];
            })->groupBy('user_id')->values()
        ];
    })->values();

    return view('admin.hackathon.hackathon-details', compact('currentHackathon', 'data', 'l', 'id'));
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

}