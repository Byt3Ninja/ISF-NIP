<?php

namespace App\Http\Controllers;
// use App\Hackathon;

use App\Company;
use App\Step;
use App\User;
use stdClass;
use App\Cycle;
use App\Formfield;
use Carbon\Carbon;
use App\Team_member;
use App\TeamFormation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Notifications\InviteToHackathon;

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
        
        $hackathons = $hackathons_raw->map(function ($item, $key) use ($step) {
        $now = now();
        $start = \Carbon\Carbon::parse($item->c_start_date);
        $end = \Carbon\Carbon::parse($item->c_end_date);
        $status = $now->lt($start)
            ? 'Upcoming'
            : ($now->between($start, $end) ? 'Ongoing' : 'Finished');

            return [
                'id' => $item->c_id,
                'title' => $item->c_title,
                'date' => $start->format('F d') . ' - ' . $end->format('d, Y'),
                'location' => $item->o_name_en ?? 'Unknown',
                'status' => $status,
                'description' => $item->c_description,
                'tags' => [],
                'step_name' => $step->where('cycle_id', $item->c_id)->first()->title ?? 'N/A',
                'step_id' => $step->where('cycle_id', $item->c_id)->first()->id ?? null,
                'step_start' => $step->where('cycle_id', $item->c_id)->first()->from?->format('Y-m-d') ?? null,
                'step_end' => $step->where('cycle_id', $item->c_id)->first()->to?->format('Y-m-d') ?? null,
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

    public function manage( )
    {
        return view('admin.hackathon.manage-team');
    }

    public function myTeam()
    {
        // $hackathon = Hackathon::findOrFail($id);
        // return view('admin.hackathon.my-team', compact('hackathon'));
        return view('admin.hackathon.team-workspace');
    }

    public function show($id) {

        $user = Auth::user();
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
        AND c.id = ?";
        if(!$user->hasRole('admin')){
        $hackathons_query .= " AND u.id = ?";
        }
        $hackathons_query .= " ORDER BY c.created_at DESC";

       if(!$user->hasRole('admin')){
        $hackathons_raw = collect(DB::select($hackathons_query, [$id, $user->id]));
       }else{
        $hackathons_raw = collect(DB::select($hackathons_query, [$id]));
       }
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
                $step = Step::where('cycle_id', $id)->get();
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
                'step_id' => $step->where('id', $item->Com_step)->first()->id ?? null,
                'step_name' => $step->where('id', $item->Com_step)->first()->title ?? 'N/A',
                'step_stype' => $step->where('id', $item->Com_step)->first()->stype ?? null,
                'step_start' => $step->where('id', $item->Com_step)->first()->from?->format('Y-m-d') ?? null,
                'step_end' => $step->where('id', $item->Com_step)->first()->to?->format('Y-m-d') ?? null,
                'cycle_id' => $id,
            ];
        }
            // dd($currentHackathon);
      


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
      $grouped = $forms->groupBy('FD_step_id');

      $data = $grouped->map(function ($group, $stepId) use ($l) {
          $first = $group->first();
      
          $submission = new stdClass;
          $submission->company_id = $first->FD_company_id;
          $submission->user_id = $first->FD_user_id;
          $submission->cycle_id = $first->CYC_id;
          $submission->submission_id = $first->C_step;
          $submission->form_name = $first->BF_form_name;
          $submission->startup_name = $first->{'C_name_' . $l};
          $submission->submitted_by = $first->{'user_name_' . $l};
          $submission->step_name = $first->S_title;
          $submission->program_name = $first->CYC_title;
          $submission->organization_name = $first->O_name_en;
          $submission->submissionDate = $first->FD_created_at ?? $first->FD_updated_at;
      
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
                'file_url' => asset($value)
              ];
            }
            
            return (object)[
              'user_id' => $item->FD_user_id,
              'company_id' => $item->FD_company_id,
              'step_id' => $item->FD_step_id,
              'company_step' => $item->C_step,
              'name' => $item->{'user_name_' . $l},
              'label' => $item->FD_question,
              'value' => $value,
            ];
          })->groupBy('user_id')->values();

          return $submission;
      })->values()->all(); // remove collection keys (numeric indexing)
      
      $data = array_values($data);


        // return view('admin.hackathon.show', compact('hackathon'));
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
        $teamformation->user_id = $user->id; // Assuming the user creating the team is the authenticated user
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
        $team_member->save();

        



        return redirect()->back()->with('success', 'Team created successfully.');
    }

    public function invitation(Request $request){
        $request->validate([
            'email' => 'required|email',
            'user_id' => 'required|exists:users,id',
        ]);

        $user = Auth::user();
        $team_q = TeamFormation::where('user_id', $user->id)->first();
        if (!$team_q) {
            return redirect()->back()->with('error', 'You are not authorized to send invitations.');
        }
        $user_q = User::where('email', $request->email)->where('id', $request->user_id)->first();
        if (!$user_q) {
            return redirect()->back()->with('error', 'User not found.');
        }
        $teamMember = new Team_member();
        $teamMember->user_id = $request->user_id;
        $teamMember->Name = $user_q->first_name . ' ' . $user_q->last_name;
        $teamMember->TeamMember = 'hackathon';
        $teamMember->isTeamLeader = 0;
        $teamMember->team_formation_id = $team_q->id;
        $teamMember->email = $request->email;
        $teamMember->acceptInvition = 'pending';
        $teamMember->save();

        $user_q->notify(new InviteToHackathon($user, $team_q));

        // Send invitation email logic here

        return redirect()->back()->with('success', 'Invitation sent successfully.');
    }

    public function acceptInvitation($id)
    {
        $user = Auth::user();
        $teamMember = Team_member::where('user_id', $user->id)->where('id', $id)->where('acceptInvition', 'pending')->first();
        if (!$teamMember) {
            return redirect()->back()->with('error', 'Invitation not found.');
        }
        $teamMember->acceptInvition = 'accepted';
        $teamMember->save();

        $allteamMembers = Team_member::where('user_id', $user->id)->where('acceptInvition', 'pending')
            ->get();
            $allteamMembers->acceptInvition = 'rejected';
            $allteamMembers->save();

        return redirect()->back()->with('success', 'Invitation accepted successfully.');
    }

    public function rejectInvitation($id)
    {
        $user = Auth::user();
        $teamMember = Team_member::where('user_id', $user->id)->where('id', $id)->where('acceptInvition', 'pending')->first();
        if (!$teamMember) {
            return redirect()->back()->with('error', 'Invitation not found.');
        }
        $teamMember->acceptInvition = 'rejected';
        $teamMember->save();

        return redirect()->back()->with('success', 'Invitation rejected successfully.');
    }

    public function getOutTeam()
    {
        $user = Auth::user();
        $teamMember = Team_member::where('user_id', $user->id)->where('acceptInvition', 'accepted')->first();
        if (!$teamMember) {
            return redirect()->back()->with('error', 'You are not part of any team.');
        }
        $teamMember->acceptInvition = 'rejected';
        $teamMember->save();

        return redirect()->back()->with('success', 'You have successfully left the team.');
    }

}