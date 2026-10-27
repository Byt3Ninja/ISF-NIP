<?php

namespace App\Http\Controllers;
// use App\Hackathon;

use Str;
use App\BuilderForm;
use App\Step;
use App\User;
use stdClass;
use App\Cycle;
use App\Company;
use App\Formfield;
use App\Messenger;
use App\Formdata;
use Carbon\Carbon;
use App\Services\TimezoneService;
use App\Team_member;
use App\Conversation;
use App\ProjectLikes;
use App\CyclesMentors;
use App\TeamFormation;
use App\ShareEvent;
use App\ScreeningEvaluation;
use Illuminate\Http\Request;
use App\Exports\WinnersExport;
use Illuminate\Support\Facades\DB;
use App\ScreeningEvaluationResults;
use App\Notifications\TemplateEmail;
use Beta\Microsoft\Graph\Model\Team;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Services\FileManagerService;
use App\Services\NextSessionsService;
use App\Notifications\InviteToHackathon;
use Illuminate\Support\Facades\Notification;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Notifications\StatusHackathonInvitation;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use App\Traits\AuthorizesTeamAccess;

class HackathonController extends Controller
{
    use AuthorizesTeamAccess;

    /**
     * Stream a team logo from the `programTeams` disk
     * (storage/app/public/programs/teams -- OUTSIDE the web root).
     *
     * Replaces the old public `programs/teams/{files?}` route, which served the
     * same disk with no authentication and a raw `.*` path segment. Logos are
     * team collateral, so viewing is gated on team membership/oversight, and the
     * filename comes from the DB rather than the URL so a caller can never
     * address an arbitrary path.
     */
    /**
     * Stream a team logo from the `programTeams` disk
     * (storage/app/public/programs/teams -- OUTSIDE the web root).
     *
     * Served by filename with NO database lookup: logos render in team lists and
     * chat, so a per-image query would add one round-trip per rendered logo for
     * data that is deliberately not access-controlled -- a team logo is
     * competitor-visible branding, not confidential. Access control is therefore
     * "authenticated user" only; the security that matters for this endpoint is
     * at UPLOAD time (FileManagerService enforces the extension/MIME blacklist
     * and generates the filename), plus the strict filename validation below so
     * no caller-supplied string can escape the disk root.
     *
     * Replaces the old UNAUTHENTICATED `programs/teams/{files?}` route, which
     * accepted a raw `.*` path segment.
     */
    public function teamLogo($filename)
    {
        // Reject anything that is not a bare, server-generated upload name.
        // basename() alone would still admit e.g. ".env"; this whitelist admits
        // only the "<time>_<uniqid>.<ext>" shape FileManagerService produces.
        if (!preg_match('/^[A-Za-z0-9._-]+\.(jpg|jpeg|png|gif|webp)$/i', $filename)
            || str_contains($filename, '..')) {
            abort(404);
        }

        $filename = basename($filename);

        try {
            $disk = Storage::disk('programTeams');

            if (!$disk->fileExists($filename)) {
                abort(404);
            }

            $response = $disk->response($filename);
            $mimeType = $disk->mimeType($filename);
            if ($mimeType === 'image/svg') {
                $mimeType = 'image/svg+xml';
            }
            $response->headers->set('Content-Type', $mimeType);

            // Every upload gets a fresh server-generated filename and the old
            // file is deleted, so a given URL's bytes never change -- the name
            // is effectively the cache key. Cache hard (and privately: these
            // are served to authenticated users, so no shared/proxy caching).
            $response->setMaxAge(31536000);
            $response->setPrivate();
            $response->headers->addCacheControlDirective('immutable');

            return $response;
        } catch (\Throwable $th) {
            abort(404);
        }
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        // $hackathons_query = "SELECT
        // c.id as 'c_id',
        // c.title as 'c_title',
        // c.description as 'c_description',
        // c.start as 'c_start_date',
        // c.end as 'c_end_date',
        // c.type as 'c_type',
        // c.img_url as 'c_img_url',
        // c.created_at as 'c_created_at',
        // c.updated_at as 'c_updated_at',
      
        // o.id as 'o_id',
        // o.name_ar as 'o_name_ar',
        // o.name_en as 'o_name_en',
        // o.img_url as 'o_logo',
        // o.created_at as 'o_created_at',
        // o.updated_at as 'o_updated_at'
        // FROM 
        // cycles as c
        // LEFT JOIN organizations as o ON c.organization_id = o.id
        // WHERE c.type = 'Hackathon'
        // ";
        // if($request->searchHackathon != null){
        //     $hackathons_query .= " AND c.title LIKE '%".$request->searchHackathon."%'";
        // }
        // if($user->organization_id != null){
        //     $hackathons_query .= " AND c.organization_id = ".$user->organization_id;
        // }
        
        // $hackathons_query .= "  ORDER BY c.start ASC";
        // $hackathons_raw = collect(DB::select($hackathons_query));

        $hackathons_raw = DB::table('cycles as c')
            ->select([
                'c.id AS c_id',
                'c.title AS c_title',
                'c.description AS c_description',
                'c.start AS c_start_date',
                'c.end AS c_end_date',
                'c.type AS c_type',
                'c.img_url AS c_img_url',
                'c.created_at AS c_created_at',
                'c.updated_at AS c_updated_at',
            
                'o.id AS o_id',
                'o.name_ar AS o_name_ar',
                'o.name_en AS o_name_en',
                'o.img_url AS o_logo',
                'o.created_at AS o_created_at',
                'o.updated_at AS o_updated_at',
            ])
            ->leftJoin('organizations as o', 'c.organization_id', '=', 'o.id')
            ->where('c.type', 'Hackathon')
            ->when($request->searchHackathon, function ($query, $searchHackathon) {
                return $query->where('c.title', 'like', '%' . $searchHackathon . '%');
            })
            ->when(!$user->hasRole('admin') && !empty($user->organizations_ids), function ($query) use ($user) {
                return $query->whereIn('c.organization_id', $user->organizations_ids);
            })
            ->orderBy('c.start', 'asc')
        ->get();
 
        $hackathon_ids = $hackathons_raw->pluck('c_id')->toArray();

        $step = Step::where('stype', 1)->whereIn('cycle_id', $hackathons_raw->pluck('c_id'))->get();
        $team_formation = TeamFormation::whereIn('cycle_id', $hackathons_raw->pluck('c_id'))->where('user_id', Auth::user()->id)->get();

        // Pending team invitations addressed to this founder, grouped by hackathon cycle,
        // so the card can offer Accept/Reject instead of Apply only. (GV-7377)
        $pendingInvitations = Team_member::query()
            ->select('team_members.*', 'tf.name as team_name', 'tf.cycle_id as cycle_id')
            ->join('team_formations as tf', 'tf.id', '=', 'team_members.team_formation_id')
            ->where('team_members.Email', $user->email)
            ->where('team_members.acceptInvition', 'pending')
            ->whereIn('tf.cycle_id', $hackathon_ids)
            ->orderByDesc('team_members.created_at')
            ->get()
            ->groupBy('cycle_id');

        // Hackathons this founder already joined as a member, and the ones they lead.
        // A founder may only hold one seat per hackathon, so either state rules out
        // Accept/Reject and instead marks the card as enrolled. (GV-7377)
        // Keyed by cycle so an accepted member can be linked to the team they joined,
        // which is not the same as $team_formation (the teams they lead).
        $memberTeams = Team_member::query()
            ->join('team_formations as tf', 'tf.id', '=', 'team_members.team_formation_id')
            ->where('team_members.Email', $user->email)
            ->where('team_members.acceptInvition', 'accepted')
            ->whereIn('tf.cycle_id', $hackathon_ids)
            ->pluck('tf.id', 'tf.cycle_id');

        $memberCycles = $memberTeams->keys();

        $leaderCycles = $team_formation->pluck('cycle_id');

        // Everything the card needs to decide what to show, resolved here so the view
        // stays free of query/branching logic. (GV-7377)
        $isAdminView = $user->hasRole('admin') || $user->hasRole('organizationmanager');

        $cardStates = collect($hackathon_ids)->mapWithKeys(function ($cycleId) use (
            $pendingInvitations, $memberCycles, $memberTeams, $leaderCycles, $isAdminView
        ) {
            $invitations = collect($pendingInvitations[$cycleId] ?? []);
            $isLeader = $leaderCycles->contains($cycleId);
            $isMember = $memberCycles->contains($cycleId);
            $isEnrolled = $isLeader || $isMember;

            return [$cycleId => [
                'invitations'      => $invitations,
                'invitation_count' => $invitations->count(),
                'single_invitation'=> $invitations->count() === 1 ? $invitations->first() : null,
                'is_enrolled'      => $isEnrolled,
                'enrolled_as'      => $isLeader ? 'leader' : ($isMember ? 'member' : null),
                // The team they joined, for members who lead no team of their own.
                'joined_team_id'   => $memberTeams[$cycleId] ?? null,
                'show_invitations' => $invitations->isNotEmpty() && !$isEnrolled && !$isAdminView,
                'show_enrolled_badge' => $isEnrolled && !$isAdminView,
            ]];
        });
        $hackathons = $hackathons_raw->map(function ($item, $key) use ($step, $team_formation) {
        $start = \Carbon\Carbon::parse($item->c_start_date);
        $end = \Carbon\Carbon::parse($item->c_end_date);
        $status = self::dateWindowStatus($start, $end, $item->o_id);

            return [
                'id' => $item->c_id,
                'title' => $item->c_title,
                'image' => $item->c_img_url,
                'date' => $start->format('Y-m-d') . ' - ' . $end->format('Y-m-d'),
                'location' => $item->o_name_en ?? 'Unknown',
                'status' => $status,
                // Fallback placeholder colour used when the cycle has no image. (GV-7377)
                'placeholder_color' => $status === 'Upcoming' ? '696cff' : '28c76f',
                'description' => $item->c_description,
                'cycle_id' => $item->c_id,
                'cycle_end' => $end ,
                'tags' => [],
                'step_name' => $step->where('cycle_id', $item->c_id)->first()?->title ?? 'N/A',
                'step_id' => $step->where('cycle_id', $item->c_id)->first()?->id ?? null,
                'step_start' => $step->where('cycle_id', $item->c_id)->first()?->from?->format('Y-m-d') ?? null,
                'step_end' => $step->where('cycle_id', $item->c_id)->first()?->to?->format('Y-m-d') ?? null,
                'team_formation_id' => $team_formation->where('cycle_id', $item->c_id)->first()?->id ?? null,
                'exist_step' => $step->where('cycle_id', $item->c_id)->where('stype', 1)->first() ? true : false
            ];
         });

        // Fold the invitation/enrollment state into each hackathon so the card can read it
        // straight off $hackathon, with no lookup or logic in the view. (GV-7377)
        $hackathons = $hackathons->map(function ($item) use ($cardStates) {
            return $item + ($cardStates[$item['id']] ?? []);
        });

        $pastHackathons = $hackathons->where('status', 'Finished')
        ->sortByDesc('cycle_end')
        ->values();

       
        
        $hackathons = $hackathons->reject(function ($item) {
            return $item['status'] === 'Finished';
        })->values();
                
        return view('admin.hackathon.index', compact('hackathons', 'pastHackathons', 'step', 'hackathon_ids'));
    }

    public function manage($id)
    {
        $user = Auth::user();

        // The team id comes straight from the URL: without this check any
        // authenticated founder could open (and, via the write endpoints below,
        // edit) another team's page, and a removed member kept their old access.
        if (!$this->canViewTeam($user, $id)) {
            abort(404, __('admin.You are not authorized to view this team.'));
        }

        $l = app()->getLocale();
                 
        $team_Formation = "SELECT 
                tf.id as 'tf_id',
                tf.name as 'tf_name',
                tf.description as 'tf_description',
                tf.cycle_id as 'tf_cycle_id',
                tf.step_id as 'tf_step_id',
                tf.user_id as 'tf_user_id',
                tf.img_team as 'tf_img_team',
                tf.project_name_ar as 'tf_projectName_ar',
                tf.project_name_en as 'tf_projectName_en',
                tf.project_description_ar as 'tf_projectDescription_ar',
                tf.project_description_en as 'tf_projectDescription_en',
                tf.target_track as 'tf_targetTrack',
                tf.project_status as 'tf_projectStatus',
                tf.region as 'tf_region',
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
                c.cycle as 'c_cycle',
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
            LEFT JOIN
                team_members as tm ON tm.team_formation_id = tf.id
            LEFT JOIN
                users as u ON u.id = tm.user_id
            LEFT JOIN
                companies as c ON c.id = u.company_id
            LEFT JOIN
                steps as s ON s.id = tf.step_id
            LEFT JOIN
                cycles as cy ON cy.id = tf.cycle_id
            WHERE
                tm.team_formation_id = ? ";

        $team_Formation_raw = collect(DB::select($team_Formation, [$id]));
        if( $team_Formation_raw->isEmpty() ) { return redirect()->back()->with('error', 'Team Formation not found'); }
        $step = Step::whereIn('cycle_id', $team_Formation_raw->pluck('tf_cycle_id')->toArray())->where('stype', '6')->first();
        $count_team = $step ? $step->count_team_number : 0;
        $team_Pending = $team_Formation_raw->where('tm_isTeamLeader', 0)
            ->whereIn('tm_acceptInvition', ['pending', 'failed']);
        $team_members = $team_Formation_raw->where('tm_isTeamLeader', 0)->where('tm_acceptInvition', 'accepted');

        $team_Formation_leader = $team_Formation_raw->where('tm_isTeamLeader', 1)->where('tm_user_id', Auth::user()->id)->first();

        $existInTeam = Team_member::where('TeamMember', 'hackathon')->where('acceptInvition', 'accepted')->pluck('user_id')->toArray();
        $pendingInvites = Team_member::where('TeamMember', 'hackathon')
            ->whereIn('acceptInvition', ['pending', 'join', 'failed'])->get();
        $existInTeam = array_filter($existInTeam, function ($value) {
            return $value !== null;
        });
        $existInTeam = array_values( array_unique($existInTeam) );
        $placeholders = implode(',', array_fill(0, count($existInTeam), '?'));

        $viewMember_q = "SELECT
                u.id as 'u_id',
                concat(u.first_name_en, ' ', u.last_name_en) as 'user_name_en',
                concat(u.first_name_ar, ' ', u.last_name_ar) as 'user_name_ar',
                u.email as 'u_email',
                u.img_url as 'u_image',
                co.name_ar as co_country_ar,
                co.name_en as co_country_en,
                ci.name_ar as ci_city_ar,
                ci.name_en as ci_city_en,
                s.name_ar as s_state_ar,
                s.name_en as s_state_en,
                u.title_ar as 'u_title_ar',
                u.title_en as 'u_title_en'
            FROM
                companies as c
            LEFT JOIN
                users as u ON u.company_id = c.id
            LEFT JOIN
                countries as co ON u.country_id = co.id
            LEFT JOIN
                cities as ci ON u.city_id = ci.id
            LEFT JOIN
                states as s ON u.state_id = s.id
            WHERE c.cycle = ?
        ";
        if($user->team_member != null){
            $params = [$user->team_member->teamFormation->user->company->cycle];
        }else{
            $params = [$user->company->cycle];
        }
        if( count($existInTeam) > 0 )
        {
            $viewMember_q .= " AND u.id NOT IN ($placeholders)";
            $params = array_merge($params, $existInTeam);
        }

        // تنفيذ الاستعلام
        $results = DB::select($viewMember_q, $params);

        // تحويل النتائج إلى الشكل المطلوب
        $availableMembers = collect($results)->map(function ($member) use ($l, $pendingInvites, $user, $id) {
            return [
                'id'        => $member->u_id,
                'name'      => $member->user_name_en,
                'email'     => $member->u_email,
                'image'     => $user->u_image ?? '/vuexy/assets/img/avatars/default.png',
                'country'   => $user->{'co_country_'. $l},
                'city'      => $user->{'ci_city_'. $l},
                'state'     => $user->{'s_state_'. $l},
                'title'     => $user->{'u_title_'. $l} ?? '',
                'isInvited' => $pendingInvites
                    ->where('team_formation_id', $id)
                    ->contains(fn ($p) => mb_strtolower((string) $p->Email) === mb_strtolower((string) $member->u_email)),
            ];
        })->toArray();

        $todoList_q = "SELECT 
                tl.id as tl_id,
                tl.title as tl_title,
                tl.description as tl_description,
                tl.created_at as tl_created_at,
                tl.due_date as tl_due_date,
                tl.status as tl_status,
                tl.assignee as tl_assignee_id,
                tf.id as tf_id,
                tf.name as tf_name,
                tf.user_id as tf_user_id,
                tf.description as tf_description,
                tm.id as tm_id,
                tm.user_id as tm_user_id,
                tm.team_formation_id as tm_team_formation_id,
                tm.Name as tm_name,
                tm.email as tm_email,
                tm.acceptInvition as tm_acceptInvition,
                tm.isTeamLeader as tm_isTeamLeader,
                u.id as u_id,
                concat(u.first_name_en, ' ', u.last_name_en) as 'user_name_en',
                concat(u.first_name_ar, ' ', u.last_name_ar) as 'user_name_ar',
                u.email as 'u_email',
                u.img_url as 'u_img_url',
                us.id as us_id,
                concat(us.first_name_en, ' ', us.last_name_en) as 'assignee_name_en',
                concat(us.first_name_ar, ' ', us.last_name_ar) as 'assignee_name_ar',
                us.email as 'assignee_email',
                us.img_url as 'assignee_img_url'
            FROM 
                todo_lists as tl
            LEFT JOIN
                team_formations as tf ON tf.id = tl.team_formation_id
            LEFT JOIN
                team_members as tm ON tm.id = tl.team_member_id
            LEFT JOIN
                users as u ON u.id = tl.user_id
            LEFT JOIN
                users as us ON us.id = tl.assignee
            WHERE
                tl.team_formation_id = ? 
        ";
        $todoList = collect(DB::select($todoList_q, [$id]));

        $sharedFile_q = "SELECT 
                sf.id as sf_id,
                sf.file as sf_file,
                sf.original_name as sf_original_name,
                sf.user_id as sf_user_id,
                sf.description as sf_description,
                sf.created_at as sf_created_at,
                tf.id as tf_id,
                tf.name as tf_name,
                tf.user_id as tf_user_id,
                tf.description as tf_description,
                tm.id as tm_id,
                tm.user_id as tm_user_id,
                tm.team_formation_id as tm_team_formation_id,
                tm.Name as tm_name,
                tm.email as tm_email,
                tm.acceptInvition as tm_acceptInvition,
                tm.isTeamLeader as tm_isTeamLeader,
                u.id as u_id,
                concat(u.first_name_en, ' ', u.last_name_en) as 'user_name_en',
                concat(u.first_name_ar, ' ', u.last_name_ar) as 'user_name_ar',
                u.email as 'u_email',
                u.img_url as 'u_img_url'
            FROM 
                shared_files as sf
            LEFT JOIN
                team_formations as tf ON tf.id = sf.team_formation_id
            LEFT JOIN
                team_members as tm ON tm.id = sf.team_member_id
            LEFT JOIN
                users as u ON u.id = sf.user_id
            WHERE sf.team_formation_id = ? 
        ORDER BY sf.created_at DESC";

        $sharedFile = collect(DB::select($sharedFile_q, [$id]));


        // ---- View-model (all logic moved out of the Blade template) ----
        $team = $team_Formation_raw->first();

        // Single source of truth for leader-only UI, matching the server-side
        // guards (canManageTeam) so the page never renders a control the
        // endpoint will reject.
        $isLeader = $this->canManageTeam($user, $team->tf_id);

        $teamLeaderRow = $team_Formation_raw->firstWhere('tm_isTeamLeader', 1);

        // Seat data (same rule as invitation(): accepted + join + pending, incl. leader).
        $occupied = Team_member::where('team_formation_id', $team->tf_id)
            ->whereIn('acceptInvition', ['accepted', 'join', 'pending'])->count();
        $maxMembers = $count_team; // steps.count_team_number for the stype=6 step
        $remaining = max(0, $maxMembers - $occupied);
        $canInvite = $isLeader && $remaining > 0;

        // Join requests (was inline in Blade).
        $joinRequests = $team_Formation_raw
            ->where('tm_isTeamLeader', 0)
            ->where('tm_acceptInvition', 'join')
            ->where('tm_team_formation_id', $team->tf_id);

        // Project-status badge mapping (was the @if chain in Blade).
        $statusMap = [
            20  => ['label' => __('admin.Idea + Team'), 'class' => 'bg-label-primary', 'description' => __('admin.Define the problem and form a team with clear roles.')],
            40  => ['label' => __('admin.Research'), 'class' => 'bg-label-info', 'description' => __('admin.Validate the idea, study users, and outline key features.')],
            60  => ['label' => __('admin.Design & Build'), 'class' => 'bg-label-warning', 'description' => __('admin.Sketch UI, set up the project, and develop core functionality.')],
            80  => ['label' => __('admin.Ready for Pitch'), 'class' => 'bg-label-success', 'description' => __('admin.Prepare demo, finalize slides, and rehearse the presentation.')],
            100 => ['label' => __('admin.Project Submitted'), 'class' => 'bg-label-dark', 'description' => __('admin.Submit code, share the demo, and complete documentation. story.')],
        ];
        $projectStatusMeta = $statusMap[$team->tf_projectStatus]
            ?? ['label' => ($l == 'en' ? 'Not Set' : 'غير محدد'), 'class' => 'bg-label-secondary', 'description' => ''];

        // Shared files: precompute human-readable size + display name (was Storage calc in Blade).
        $sharedFiles = collect($sharedFile ?? [])->map(function ($file) use ($l) {
            $fullPath = $file->sf_file;
            $pathInfo = pathinfo($fullPath);
            $displayName = $file->sf_original_name
                ?? (($pathInfo['filename'] ?? 'file') . '.' . ($pathInfo['extension'] ?? ''));
            if (\Illuminate\Support\Facades\Storage::disk('hackathon_shared')->exists($fullPath)) {
                $bytes = \Illuminate\Support\Facades\Storage::disk('hackathon_shared')->size($fullPath);
                $units = ['B', 'KB', 'MB', 'GB', 'TB'];
                $factor = floor((strlen($bytes) - 1) / 3);
                $sizeReadable = sprintf('%.2f', $bytes / pow(1024, $factor)) . $units[$factor];
            } else {
                $sizeReadable = $l == 'en' ? 'File not found' : 'الملف غير موجود';
            }
            $file->size_readable = $sizeReadable;
            $file->display_name = $displayName;
            return $file;
        });

        // Todo items: precompute status meta (was the @php status block in Blade).
        $todoItems = collect($todoList ?? [])->map(function ($task) use ($l) {
            if ($task->tl_status == 'DONE') {
                $task->status_meta = ['class' => 'success', 'text' => ($l == 'en' ? 'Done' : 'مكتملة'), 'icon' => 'ti-check'];
            } elseif ($task->tl_status == 'inprogress') {
                $task->status_meta = ['class' => 'warning', 'text' => ($l == 'en' ? 'In Progress' : 'قيد التنفيذ'), 'icon' => 'ti-progress'];
            } else {
                $task->status_meta = ['class' => 'secondary', 'text' => ($l == 'en' ? 'Todo' : 'مهمة'), 'icon' => 'ti-circle'];
            }
            return $task;
        });

        return view('admin.hackathon.manage-team', compact(
            'team_Formation_raw', 'team_Pending', 'team_members', 'step', 'count_team',
            'availableMembers', 'todoList', 'sharedFile', 'team_Formation_leader',
            'team', 'isLeader', 'teamLeaderRow', 'occupied', 'maxMembers', 'remaining',
            'canInvite', 'joinRequests', 'projectStatusMeta', 'sharedFiles', 'todoItems'
        ));
    }

    public function myTeam()
    {
        // $hackathon = Hackathon::findOrFail($id);
        // return view('admin.hackathon.my-team', compact('hackathon'));
        $user = Auth::user();
        $userTeam_q = "SELECT team_formation_id FROM team_members WHERE user_id = ? ";
        $userTeam = collect(DB::select($userTeam_q, [$user->id]));
        $TF_id = $userTeam->first()?->team_formation_id;
        $team_Formation_q = "SELECT 
                tf.id as 'tf_id',
                tf.name as 'tf_name',
                tf.description as 'tf_description',
                tf.cycle_id as 'tf_cycle_id',
                tf.step_id as 'tf_step_id',
                tf.user_id as 'tf_user_id',
                tf.project_name_en as 'tf_projectName',
                tf.project_description_en as 'tf_projectDescription',
                tf.project_status as 'tf_projectStatus',
                tf.region as 'tf_region',
                tf.created_at as 'tf_created_at',
                tf.updated_at as 'tf_updated_at',
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
                tm.isTeamLeader as 'tm_isTeamLeader',
                cy.id as 'cy_id',
                cy.title as 'cy_title'
            FROM 
                team_formations as tf
            LEFT JOIN
                team_members as tm ON tm.team_formation_id = tf.id
            LEFT JOIN
                users as u ON u.id = tm.user_id
            LEFT JOIN
                cycles as cy ON cy.id = tf.cycle_id
            WHERE
                tf.id = ?
                AND tm.acceptInvition = 'accepted'
        ";
        $team_Formation_raw = collect(DB::select($team_Formation_q, [$TF_id]));

        if( $team_Formation_raw->isEmpty() ) { return null; }

        $first = $team_Formation_raw->first();

        $members = $team_Formation_raw->map(function ($row) {
            return [
                'id' => $row->u_id,
                'name' => $row->user_name_en ?? $row->tm_name ?? null,
                'name_ar' => $row->user_name_ar ?? null,
            ];
        })->unique(function ($member) {
            return strtolower(($member['name'] ?? '') . '|' . ($member['name_ar'] ?? ''));
        })->values()->all();
        $teamData = [
            'name' => $first->tf_name,
            'name_ar' => $first->tf_name,
            'hackathon' => $first->cy_title,
            'hackathon_ar' => $first->cy_title,
            'members' => $members,
        ];

        $sharedFile_q = "SELECT 
                sf.id as sf_id,
                sf.file as sf_file,
                sf.original_name as sf_original_name,
                sf.user_id as sf_user_id,
                sf.description as sf_description,
                sf.created_at as sf_created_at,
                tf.id as tf_id,
                tf.name as tf_name,
                tf.user_id as tf_user_id,
                tf.description as tf_description,
                tm.id as tm_id,
                tm.user_id as tm_user_id,
                tm.team_formation_id as tm_team_formation_id,
                tm.Name as tm_name,
                tm.email as tm_email,
                tm.acceptInvition as tm_acceptInvition,
                tm.isTeamLeader as tm_isTeamLeader,
                u.id as u_id,
                concat(u.first_name_en, ' ', u.last_name_en) as 'user_name_en',
                concat(u.first_name_ar, ' ', u.last_name_ar) as 'user_name_ar',
                u.email as 'u_email',
                u.img_url as 'u_img_url'
            FROM 
                shared_files as sf
            LEFT JOIN
                team_formations as tf ON tf.id = sf.team_formation_id
            LEFT JOIN
                team_members as tm ON tm.id = sf.team_member_id
            LEFT JOIN
                users as u ON u.id = sf.user_id
            WHERE sf.team_formation_id = ? 
        ORDER BY sf.created_at DESC";

        $sharedFile = collect(DB::select($sharedFile_q, [$TF_id]));

        $todoList_q = "SELECT 
                tl.id as tl_id,
                tl.title as tl_title,
                tl.description as tl_description,
                tl.created_at as tl_created_at,
                tl.due_date as tl_due_date,
                tl.status as tl_status,
                tl.assignee as tl_assignee_id,
                tf.id as tf_id,
                tf.name as tf_name,
                tf.user_id as tf_user_id,
                tf.description as tf_description,
                tm.id as tm_id,
                tm.user_id as tm_user_id,
                tm.team_formation_id as tm_team_formation_id,
                tm.Name as tm_name,
                tm.email as tm_email,
                tm.acceptInvition as tm_acceptInvition,
                tm.isTeamLeader as tm_isTeamLeader,
                u.id as u_id,
                concat(u.first_name_en, ' ', u.last_name_en) as 'user_name_en',
                concat(u.first_name_ar, ' ', u.last_name_ar) as 'user_name_ar',
                u.email as 'u_email',
                u.img_url as 'u_img_url',
                us.id as us_id,
                concat(us.first_name_en, ' ', us.last_name_en) as 'assignee_name_en',
                concat(us.first_name_ar, ' ', us.last_name_ar) as 'assignee_name_ar',
                us.email as 'assignee_email',
                us.img_url as 'assignee_img_url'
            FROM 
                todo_lists as tl
            LEFT JOIN
                team_formations as tf ON tf.id = tl.team_formation_id
            LEFT JOIN
                team_members as tm ON tm.id = tl.team_member_id
            LEFT JOIN
                users as u ON u.id = tl.user_id
            LEFT JOIN
                users as us ON us.id = tl.assignee
            WHERE
                tl.team_formation_id = ? 
        ";
        $todoList = collect(DB::select($todoList_q, [$TF_id]));
        return view('admin.hackathon.team-workspace', compact('teamData', 'sharedFile', 'todoList'));
    }


    public function show(Request $request, $id)
    {
        $user = Auth::user();
        $l = app()->getLocale();
        $allSteps = Step::where('cycle_id', $id)->get();
        $pages = DB::table('cycle_custom_pages')
            ->where('cycle_id', $id)
            ->where('is_active', 1)
            ->whereIn('visibility', ['public', 'private'])
            ->orderBy('row_no')
            ->get();

        $pages = $pages->map(function( $page ) {
            $page->code = Str::random(5);
            return $page;
        });
        
        if( $allSteps->isEmpty() )
        {
            if($user->hasRole('admin') || $user->hasRole('organizationmanager'))
            {
                return redirect()->back()->with(['class' => 'danger', 'message', 'Program not ready. Add step first.']);
            }
            else
            {
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
                c.img_url as 'c_img_url',
                c.created_at as 'c_created_at',
                c.updated_at as 'c_updated_at',
                c.winners_announcement as 'c_winners_announcement',
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
            LEFT JOIN
                companies as com ON c.id = com.cycle
            LEFT JOIN
                users as u ON u.company_id = com.id
            LEFT JOIN
                organizations as o ON c.organization_id = o.id
            WHERE
                c.type = 'Hackathon'
                AND c.id = ?
        ";

        $hackathons_query .= " ORDER BY c.created_at DESC";
        $hackathons_raw = collect(DB::select($hackathons_query, [$id]));

        $currentHackathon = null;
        if( $hackathons_raw->isNotEmpty() )
        {
            $item = $hackathons_raw->first();
            $start = Carbon::parse($item->c_start_date);
            $end = Carbon::parse($item->c_end_date);

            $status = self::dateWindowStatus($start, $end, $item->o_id);

            $status_ar = match($status) {
                'Upcoming' => 'قادم',
                'Ongoing' => 'قائم',
                'Finished' => 'منتهي',
                default => '',
            };
            $hackathonSteps = Step::where('cycle_id', $item->Com_cycle)->get();
            $currentHackathon = [
                'title' => $item->c_title,
                'title_ar' => $item->c_title_ar ?? '',
                'image' => $item->c_img_url,
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
                'step_id' => $hackathonSteps->where('id', $item->Com_step)->first()?->id ?? null,
                'step_name' => $hackathonSteps->where('id', $item->Com_step)->first()?->title ?? 'N/A',
                'step_stype' => $hackathonSteps->where('id', $item->Com_step)->first()?->stype ?? null,
                'step_start' => $hackathonSteps->where('id', $item->Com_step)->first()?->from?->format('Y-m-d') ?? null,
                'step_end' => $hackathonSteps->where('id', $item->Com_step)->first()?->to?->format('Y-m-d') ?? null,
                'cycle_id' => $id,
                'Company_id' => $item->Com_id,
                'max_team' => $hackathonSteps->where('id', $item->Com_step)->first()?->count_team_number ?? 0,
                'announcement' => $item->c_winners_announcement == 1 ? true : false,
            ];
        } else {
            return redirect()->back()->with(['class' => 'danger', 'msg' => 'Hackathon not found.']);
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
            FROM
                form_data as FD 
            LEFT JOIN
                companies as C ON C.id = FD.company_id
            LEFT JOIN
                users as U ON U.id = FD.user_id
            LEFT JOIN
                steps as S ON S.id = FD.step_id
            LEFT JOIN
                builder_forms as BF ON BF.id = S.data
            LEFT JOIN
                cycles as CYC ON CYC.id = S.cycle_id
            LEFT JOIN
                organizations as O ON O.id = CYC.organization_id
            WHERE
                FD.deleted_at IS NULL
                AND CYC.id = ?
        ";
            $params = [$id];

            // خطوة آمنة لـ step_id
            // 'all' is the "All Steps" sentinel; '' or a missing key also mean no filter.
            // Neither 'all' nor '' may reach intval(), since intval('all') === 0
            // and 0 is the Disapprove step.
            if ($request->step_id !== null && $request->step_id !== '' && $request->step_id !== 'all') {
                $forms .= " AND C.step = ? ";
                $params[] = intval($request->step_id);
            }

            // فلترة حسب organization إذا موجودة
            $orgIds = $user->organizations_ids;
            if (!$user->hasRole('admin') && !empty($orgIds)) {
                $forms .= " AND CYC.organization_id IN (" . implode(',', array_map('intval', $orgIds)) . ") ";
            }
            $forms .= " ORDER BY FD.created_at";
            $forms = DB::select($forms, $params);
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
            LEFT JOIN
                team_formations as tf ON tm.team_formation_id = tf.id
            LEFT JOIN
                users as u ON tm.user_id = u.id
            WHERE
                tf.cycle_id = ?
        ";
        $teams = collect(DB::select($teams_q, [$id]));

        // $teamUser = $teams->where('tm_user_id', Auth::user()->id);
        // $teams_with_user = $teams->where('tm_team_formation_id', $teamUser->first()->tm_team_formation_id);
            
        $teams = $teams->groupBy('tm_team_formation_id');

        $teams = $teams->map(function ($group, $teamFormationId) use ($id, $allSteps, $user) {
            $first = $group->first();

            $team = new stdClass;
            $team->id = $first->tf_id;
            $team->name = $first->tf_name;
            $team->desc = $first->tf_description;
            $team->cycle_id = $first->tf_cycle_id;
            $team->step_id = $first->tf_step_id;
            // Logos stream from storage via the authorized route, not a public path.
            $team->photo = $first->tf_img_team
                ? route('hackathon.teamLogo', basename($first->tf_img_team))
                : null;
            $team->max_members = $allSteps->where('stype', 6)->where('cycle_id', $id)->first()?->count_team_number ?? 0;
            $team->your_team = $group->where('tm_user_id', $user->id)->where('tm_isTeamLeader', 1)->where('tm_acceptInvition', 'accepted')->first()? true : false;
            $team->request_sent = $group->where('tm_user_id', $user->id)->whereIn('tm_acceptInvition', ['join'])->first()? true : false; //is_user_requested
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
            LEFT JOIN
                team_members as tm ON tm.team_formation_id = tf.id
            LEFT JOIN
                users as u ON tm.user_id = u.id
            WHERE
                cycle_id = ?
        ";

        $allteams_raw = collect(DB::select($allteams_q, [$id]));
        $team_invition = $allteams_raw->where('tm_user_id', $user->id);
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

        $count_team = $allSteps ? $allSteps->where('stype', '6')->first()?->count_team_number : 0;
       
        $companyStep = null;
        if( Auth::user()->company?->step != null ) { $companyStep = Step::where('id', Auth::user()->company->step)->first(); }

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
            LEFT JOIN
                cycles_mentors as cm ON u.id = cm.user_id
            LEFT JOIN
                cycles as c ON cm.cycle_id = c.id
            WHERE
                c.id = ?
        ";
        $mentors = collect(DB::select($mentors_q, [$id]));

        $judges_q = "SELECT
                *
            FROM
                finalscreenings as FS
            LEFT JOIN
                steps as s ON FS.step_id = s.id
            WHERE
                s.stype = 5
                AND s.cycle_id = ?
        ";
        $judges = collect(DB::select($judges_q, [$id]));
        if( $judges->isEmpty() )
        {
            $judges = collect([]);
            $judges_users = collect([]);
        }
        else
        {
            $judges_ids = (array) json_decode($judges->first()?->judges);
            $judges_ids = array_map('intval', $judges_ids);
            $judges_users = DB::table('users as u')->whereIn('u.id', $judges_ids)->select(
                'u.id',
                DB::raw("CONCAT(u.first_name_ar, ' ', u.last_name_ar) as user_name_ar"),
                DB::raw("CONCAT(u.first_name_en, ' ', u.last_name_en) as user_name_en"),
                'u.email',
                'u.img_url',
                'u.title_ar',
                'u.title_en'
            )->get();
        }

        $id_evi = $user->company?->step;
        $step_evi = $allSteps->where('id', $id_evi)->first();
        $step_evi_next = $allSteps->where('cycle_id', $id)->where('stype', 5)->first();

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

        // Mirrors CyclesController's answers query: scope by step so a BuilderForm
        // shared across several steps does not leak another step's answers.
        $answers = Formfield::rightJoin('form_data', 'form_fields.id', 'form_data.field_id')
            ->where('user_id', Auth::id())
            ->where('form_data.step_id', $id)
            ->whereIn('form_data.field_id', $field_map_ids)
            ->orderBy('form_data.id', 'ASC')
            ->get();
        $days = request()->query('days', 14);
        $company = $user->company;
        $stepData = $company?->stepdata;
        $start = Carbon::now()->startOfDay()->format('Y-m-d H:i:s');
        $end = Carbon::now()->addDays($days)->endOfDay()->format('Y-m-d H:i:s');
        $steps_ids = [];
        if( $company && $company->cycledata )
        {
            $steps = $company?->cycledata?->steps;
            if( !empty( $steps ) )
            {
                // GV-9566: step dates are inclusive days in the program's platform zone.
                $today = TimezoneService::platformToday(TimezoneService::platform($company->cycledata->organization_id));
                $steps_ids = $steps->filter(function ($step) use ($today) {
					return $step->from && $step->to
						&& $step->from->format('Y-m-d') <= $today && $step->to->format('Y-m-d') >= $today;
				})->values()->pluck('id')->toArray();
            }
            $sessions = DB::table('training_sessions AS ts')
                ->select([
                    'ts.id',
                    'ts.type',
                    'ts.title',
                    'ts.datetime',
                    'ts.enddatetime AS end_datetime',
                    'ts.duration',
                    'ts.join_url',
                    'ts.trainer_name',
                    'ts.training_location AS location'
                ])
                ->whereIn('ts.step_id', $steps_ids)
                ->orderBy('ts.datetime', 'ASC')
                ->limit(5)
                ->get();
            $sessions = collect($sessions)->sortBy('datetime')->values();
            /* $sessions_q = "SELECT
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
                LIMIT 5
            ";
            $sessions = collect(DB::select($sessions_q, [$company->step])); */
        } else { $sessions = collect([]); }

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
            if( !empty( $step ) && !empty( $stepData ) )
            {
                if( $stepData->arr > $step->arr )
                {
                    $status = $l == 'ar' ? 'انتهت' : 'Completed';
                    $color = 'label-success';
                    $textColor = 'success';
                }
                elseif( $stepData->arr == $step->arr )
                {
                    $status = $l == 'ar' ? 'بدأت' : 'Started';
                    $color = 'label-primary';
                    $textColor = 'primary';
                }
            }
            $trType = $fLink = null;
            if( $company?->approved ) { $trType = 'my/' . $step->id; }
            else { $fLink = 'view/' . $id; }

            $step->status = $status;
            $step->color = $color;
            $step->textColor = $textColor;
            $step->trType = $trType;
            $step->fLink = $fLink;
            return $step;
        });

        $service = new \App\Services\NextSessionsService();
            $nextSessions = $service->getUpcomingSessions((int)$days, $l, $user->id, $id);
       

        $cType = 0;
        if( !empty( $stepData ) ) { $cType = $stepData->stype; }

        $showStep = in_array( $companyStep?->id, $steps_ids );

        
                // $teamJoins = App\Team_member::where('Email', auth()->user()->email)->where('acceptInvition',
                // 'pending')->get();
                $teamJoins_q = "SELECT 
                  tm.id as tm_id,
                  tm.team_formation_id as tm_team_formation_id,
                  tm.user_id as tm_user_id,
                  tm.Email as tm_Email,
                  tm.acceptInvition as tm_acceptInvition,
                  tf.id as tf_id,
                  tf.name as tf_name,
                  tf.cycle_id as tf_cycle_id,
                  tf.created_at as tf_created_at,
                  u.id as u_id,
                  concat(u.first_name_en, ' ', u.last_name_en) as 'user_name_en',
                  concat(u.first_name_ar, ' ', u.last_name_ar) as 'user_name_ar',
                  u.email as u_email
                FROM 
                team_members as tm
                JOIN team_formations as tf ON tm.team_formation_id = tf.id
                JOIN users as u ON tm.user_id = u.id
                WHERE tm.acceptInvition = 'pending'
                AND tm.Email = ? ORDER BY tf.created_at DESC";
                $teamJoins = DB::select($teamJoins_q, [auth()->user()->email]);
                $teamJoins = collect($teamJoins);
              


        // return view('admin.hackathon.show', compact('hackathon'));
        $winners = collect([]);
        if( !empty($currentHackathon) && $currentHackathon['announcement'] == true )
        {
            $teams = TeamFormation::teams( $id );
            $winners = TeamFormation::winnersMapping( $teams );
        }
        
        $finishedHackathon = [];
        $hackathonCycle = Cycle::find($id);

        // GV-9566: finished once its last day has passed in the platform zone.
        if($hackathonCycle && $hackathonCycle->is_expired){
            $finishedHackathon['id'] = $hackathonCycle->id;
            $finishedHackathon['title'] = $hackathonCycle->title;
            $finishedHackathon['description'] = $hackathonCycle->description;
            $finishedHackathon['start'] = $hackathonCycle->start ? $hackathonCycle->start->format('Y-m-d') : null;
            $finishedHackathon['end'] = $hackathonCycle->end ? $hackathonCycle->end->format('Y-m-d') : null;
            $finishedHackathon['img'] = $hackathonCycle->img_url;
            $finishedHackathon['teams'] = TeamFormation::teams($hackathonCycle->id);
            $finishedHackathon['winners']= TeamFormation::winnersMapping($finishedHackathon['teams']);
        }

        $hasSubmission = isset($answers) && $answers->isNotEmpty();
        
        $start = null;
        if (isset($data_evi['start'])) {
            $start = $data_evi['start'] instanceof \Carbon\Carbon 
                ? $data_evi['start'] 
                : \Carbon\Carbon::parse($data_evi['start']);
        }
        
        $end = null;
        if (isset($data_evi['end'])) {
            $end = $data_evi['end'] instanceof \Carbon\Carbon 
                ? $data_evi['end'] 
                : \Carbon\Carbon::parse($data_evi['end']);
        }
        
        // GV-9566: step dates are inclusive days in the program's platform zone.
        $isOpen = $start && $end
            && self::dateWindowStatus($start, $end, $hackathonCycle?->organization_id) === 'Ongoing';

        return view('admin.hackathon.hackathon-details', compact(
            'pages',
            'currentHackathon',
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
            'judges_users',
            'nextSessions',
            'sessions',
            'cType',
            'steps',
            'showStep',
            'steps_ids',
            'teamJoins',
            'winners',
            'finishedHackathon',
            'hasSubmission',
            'start',
            'end',
            'isOpen'
        ));
    }

    public function applyHackathon($id)
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

        $the_form = BuilderForm::findOrFail( $step->data );
        // Step-scoped pivot (`form_fields`), NOT the BuilderForm template pivot
        // (`builder_fields`). `$the_form->fields()` walks `builder_fields`, whose
        // ids live in a different id space than `form_data.field_id` and than the
        // list CyclesController@saveForm validates against -- so this form posted
        // template ids (83..90) while the step's real pivot ids were 389..396 and
        // every submission aborted with 422 "Data error" (GV-7355 follow-up).
        $fields = $step->fields()
            ->where('builder_forms_id', $step->data)
            ->orderBy('form_fields.id', 'asc')
            ->get();

        $field_map_ids = $fields->pluck('pivot.id')->toArray();
        // تجهيز البيانات بشكل منسق
        $structured = $fields->map(function ($field) {
            $options = json_decode($field->pivot->options ?? '{}', true) ?? [];

            return [
                'id'          => $field->pivot->id,
                'label'       => $options['label'] ?? '',
                'description' => $options['description'] ?? '',
                'section'     => $options['section'] ?? 'N/A',
                'type'        => $options['type'] ?? null,
                'is_required' => (bool)($options['validation']['required'] ?? 0),
                'options'     => form_field_options($options['values'] ?? null),
                'logic'       => $options['logic'] ?? 'continue',
            ];
        })
        ->groupBy('section')
        ->map(function ($questions, $section_title) {
            $first = $questions->first();

            return [
                'title'       => $section_title,
                'description' => $first['description'] ?? '',
                'logic'       => is_array($first['logic']) ? ($first['logic']['section'] ?? 'continue') : ($first['logic'] ?? 'continue'),
                'questions'   => $questions->values()->map(function ($q) {
                    return [
                        'id'          => $q['id'],
                        'label'       => $q['label'],
                        'is_required' => $q['is_required'],
                        'type'        => $q['type'],
                        'options'     => $q['options'],
                        'logic'       => $q['logic'],
                    ];
                })->toArray(),
            ];
        })
        ->values()
        ->toArray();

        $data = [
            'id'          => $step->id,
            'title'       => $step->title,
            'description' => $step->description,
            'build_data'  => $structured,
            'field_ids' => implode(",", $field_map_ids)
        ];

        $answers = Formfield::join('form_data', 'form_fields.id', 'form_data.field_id')->where('user_id', Auth::id())->whereIn('form_data.field_id', $field_map_ids)->orderBy('form_fields.id', 'ASC')->get();

        return view('admin.settings.cycles.form', $data)->with('data', $data)->with('step', $step)->with('c', $step->cycle_id)->with('answers', $answers);
    }

    public function hackathonSubmissions($id) 
    {
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
      $orgIds = $user->organizations_ids;
      if(!$user->hasRole('admin') && !empty($orgIds)){
          $forms .= " AND CYC.organization_id IN (" . implode(',', array_map('intval', $orgIds)) . ") ";
      }
      $forms = DB::select($forms, [$id]);
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
      return view('admin.settings.forms.form-submissions.index', compact('data'));
    }

    public function createTeam(Request $request)
    {
        
        $request->validate([
            'team_name' => 'required|string|max:50|unique:team_formations,name',
            'team_description' => 'nullable|string|max:1000',
            'cycle_id' => 'required|exists:cycles,id',
            'step_id' => 'required|exists:steps,id',
            'Project_Name_ar' => 'nullable|string|max:255',
            'Project_Name_en' => 'nullable|string|max:255',
            'Project_Description_ar' => 'nullable|string|max:1000',
            'Project_Description_en' => 'nullable|string|max:1000',
            'Target_Track' => 'nullable|integer',
            'Project_Status' => 'nullable|integer',
            'Region' => 'nullable|string|max:255',
            'team_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);
        
        $user = Auth::user();
        // Checked before any write: this used to run after a new company had been saved.
        $team_q = Team_member::where('user_id', $user->id)->where('acceptInvition', 'accepted')->get();
        if ($team_q->count() > 0) {
            return redirect()->back()->with('error', 'You already have a team.');
        }

        // C24: company, team, leader seat, invite cleanup and team chat are one unit;
        // a failure part-way used to leave a half-created team behind.
        DB::transaction(function () use ($request, $user) {
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




         

            $teamformation = new TeamFormation();
            $teamformation->name = $request->team_name;
            $teamformation->description = $request->team_description;
            $teamformation->cycle_id = $request->cycle_id;
            $teamformation->step_id = $request->step_id;
            $teamformation->user_id = $user->id;
            $teamformation->project_name_ar = $request->Project_Name_ar;
            $teamformation->project_name_en = $request->Project_Name_en;
            $teamformation->project_description_ar = $request->Project_Description_ar;
            $teamformation->project_description_en = $request->Project_Description_en;
            $teamformation->target_track = $request->Target_Track ?? 0;
            $teamformation->project_status = $request->Project_Status ?? 0;
            $teamformation->region = $request->Region ?? '';

            if ($request->hasFile('team_image')) {
                // Store on the `programTeams` disk (storage/, outside the web root)
                // via FileManagerService, which enforces the image whitelist (extension
                // + real content type) and generates the filename server-side. The old code
                // move()d the file into public/ using the CLIENT-supplied
                // extension, so an uploaded .phtml landed in a web-served,
                // executable directory.
                $imageName = app(FileManagerService::class)
                    ->upload($request->file('team_image'), '', 'programTeams', 'image');
                $teamformation->img_team = 'programs/teams/' . basename($imageName);
            }

            $teamformation->save();

            if($teamformation){
                $getAllTeams = Team_member::where('user_id', $user->id)->whereIn('acceptInvition', ['join', 'pending'])->delete();
            }

        
            $team_member = new Team_member();
            $team_member->user_id = $user->id; // Assuming the user creating the team is the authenticated user
            $team_member->team_formation_id = $teamformation->id;
            $team_member->TeamMember = 'hackathon';
            $team_member->Name = $user->first_name . ' ' . $user->last_name;
            $team_member->email = $user->email;
            $team_member->acceptInvition = 'accepted';
            $team_member->isTeamLeader = 1;
            $team_member->company_id = $company->id; // $user is the pre-request instance; a new founder's company_id is only on $company
            $team_member->team_formation_id = $teamformation->id;
            $team_member->save();


            if($teamformation){
                $createChatGroup = Conversation::create([
                    'type' => 'group',
                    'name' => $request->team_name,
                    'description' => $request->team_description
                ]);

                // Add current user as admin
                $createChatGroup->participants()->create([
                    'participantable_id' => auth()->id(),
                    'participantable_type' => get_class(auth()->user()),
                    'role' => 'admin'
                ]);

                $teamformation->group_id_chat = $createChatGroup->id;
                $teamformation->save();

            }
        });

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
        $team_q = TeamFormation::where('id', $request->team_formation_id)->first();
        if (!$team_q) {
            return redirect()->back()->with('error', __('admin.Team_not_found.'));
        }
        // Leader-only: the project idea is team-owned data. Previously this
        // endpoint trusted the posted team_formation_id outright.
        if (!$this->canManageTeam($user, $team_q->id)) {
            return redirect()->back()->with('error', __('admin.You are not authorized to edit the project idea.'));
        }
        $team_q->project_name_ar = $request->Project_Name_ar;
        $team_q->project_name_en = $request->Project_Name_en;
        $team_q->project_description_ar = $request->Project_Description_ar;
        $team_q->project_description_en = $request->Project_Description_en;
        $team_q->target_track = $request->Target_Track ?? 0;
        $team_q->project_status = $request->Project_Status ?? 0;
        $team_q->region = $request->Region ?? '';
        $team_q->save();
        return redirect()->back()->with('success', 'Project idea updated successfully.');
        
    }

    public function EditTeamInfo(Request $request)
    {
        $request->validate([
            'team_name' => 'required|string|max:50',
            'team_description' => 'nullable|string|max:1000',
            'team_formation_id' => 'required|exists:team_formations,id',
            'team_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'remove_image' => 'nullable|boolean'
        ]);

        $user = Auth::user();
        
        // Get the team formation and verify the user is the team leader
        $teamFormation = TeamFormation::find($request->team_formation_id);
        
        if (!$teamFormation) {
            return response()->json(['error' => 'Team not found.'], 404);
        }

        // Check if the current user is the team leader. Uses the shared helper so
        // that acceptInvition is honoured — the previous inline query matched a
        // leader row in ANY state, including one already removed from the team.
        $isLeader = $this->canManageTeam($user, $teamFormation->id);

        if (!$isLeader) {
            return response()->json(['error' => 'You are not authorized to edit team information.'], 403);
        }

        // Update team information
        $teamFormation->name = $request->team_name;
        $teamFormation->description = $request->team_description;

        // Handle image upload
        $newImageUrl = null;

        if ($request->hasFile('team_image')) {
            // Validate the replacement before touching the current logo: a rejected
            // upload must never cost the team its existing image.
            app(FileManagerService::class)->assertAllowed($request->file('team_image'), 'image');

            // Delete old image if exists
            if ($teamFormation->img_team) {
                /* $oldImagePath = public_path($teamFormation->img_team);
                if (file_exists($oldImagePath)) {
                    unlink($oldImagePath);
                } */
                $storage_image_path = str_replace('programs/teams/', '', $teamFormation->img_team);
                if( Storage::disk('programTeams')->exists($storage_image_path) )
                {
                    Storage::disk('programTeams')->delete($storage_image_path);
                }
            }
            
            // Upload new image through FileManagerService (image whitelist +
            // server-generated filename), same as createTeam().
            $imageName = basename(
                app(FileManagerService::class)
                    ->upload($request->file('team_image'), '', 'programTeams', 'image')
            );

            $teamFormation->img_team = 'programs/teams/' . $imageName;
            $newImageUrl = route('hackathon.teamLogo', $imageName);
        } elseif ($request->remove_image) {
            // Remove existing image
            if ($teamFormation->img_team) {
                /* $oldImagePath = public_path($teamFormation->img_team);
                if (file_exists($oldImagePath)) {
                    unlink($oldImagePath);
                } */

                $storage_image_path = str_replace('programs/teams/', '', $teamFormation->img_team);
                if( Storage::disk('programTeams')->exists($storage_image_path) )
                {
                    Storage::disk('programTeams')->delete($storage_image_path);
                }
                $teamFormation->img_team = null;
            }
        }

        $teamFormation->save();

        if($teamFormation->group_id_chat == null){
            $createChatGroup = Conversation::create([
                'type' => 'group',
                'name' => $request->team_name,
                'description' => $request->team_description
            ]);

            // Add current user as admin
            $createChatGroup->participants()->create([
                'participantable_id' => auth()->id(),
                'participantable_type' => get_class(auth()->user()),
                'role' => 'admin'
            ]);

            $teamFormation->group_id_chat = $createChatGroup->id;
            $teamFormation->save();

        }

        return response()->json([
            'success' => true,
            'message' => 'Team information updated successfully.',
            'team_name' => $teamFormation->name,
            'new_image_url' => $newImageUrl
        ]);
    }

    public function invitation(Request $request)
    {
        $request->validate([
            'memberEmail' => 'required|email',
            'user_id' => 'nullable|exists:users,id',
        ]);

        $wantsJson = $request->expectsJson() || $request->ajax();
        // $ok=true -> HTTP 200 ; $ok=false -> HTTP 422 for AJAX callers.
        $respond = function (bool $ok, string $message, array $extra = []) use ($wantsJson) {
            if ($wantsJson) {
                return response()->json(
                    array_merge(['success' => $ok, 'message' => $message], $extra),
                    $ok ? 200 : 422
                );
            }
            return redirect()->back()->with($ok ? 'success' : 'error', $message);
        };

        $email = mb_strtolower(trim($request->memberEmail));
        $user = Auth::user();
        $team_q = TeamFormation::where('user_id', $user->id)->first();
        if (!$team_q) {
            return $respond(false, __('admin.You are not authorized to send invitations.'));
        }

        // Seat check — mirrors RequestJoin(); counts accepted + join + pending (incl. leader).
        $occupied = Team_member::where('team_formation_id', $team_q->id)
            ->whereIn('acceptInvition', ['accepted', 'join', 'pending'])->count();
        $max = optional(Step::where('cycle_id', $team_q->cycle_id)->where('stype', '6')->first())
            ->count_team_number ?? 0;
        if ($occupied >= $max) {
            return $respond(false, __('admin.hackathon_team_full'), [
                'code' => 'team_full', 'occupied' => $occupied, 'max' => $max, 'remaining' => 0,
            ]);
        }

        if ($user->email == $email) {
            return $respond(false, __('admin.You cannot invite yourself.'));
        }

        $haveTeam = Team_member::where('Email', $email)->where('acceptInvition', 'accepted')->first();
        if ($haveTeam) {
            return $respond(false, __('admin.This user is already a member of another team.'));
        }

        // Block re-inviting the same email on this team while an invite is
        // still active (pending / join) or awaiting retry (failed).
        $alreadyInvited = Team_member::where('team_formation_id', $team_q->id)
            ->whereRaw('LOWER(Email) = ?', [$email])
            ->whereIn('acceptInvition', ['pending', 'join', 'failed'])
            ->exists();
        if ($alreadyInvited) {
            return $respond(false, __('admin.hackathon_already_invited'), ['code' => 'already_invited']);
        }

        // Resolve the account by email regardless of whether the invite came
        // from the "Available Members" card (sends user_id) or the email field
        // (does not). This prevents a duplicate placeholder row for a user who
        // is actually registered.
        $user_q = User::whereRaw('LOWER(email) = ?', [$email])->first();
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

            try {
                Notification::route('mail', $email)->notify(new InviteToHackathon($user, $team_q, $teamMember));
            } catch (\Throwable $e) {
                \Log::error('Hackathon invite dispatch failed', ['email' => $email, 'error' => $e->getMessage()]);
                $teamMember->delete();
                return $respond(false, __('admin.invitation_could_not_be_sent'), ['code' => 'send_failed']);
            }
            $message = __('admin.Invitation sent successfully. Waiting for user to join.');
        } else {
            $teamMember = new Team_member();
            $teamMember->user_id = $user_q->id;
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
            try {
                $user_q->notify(new InviteToHackathon($user, $team_q, $teamMember));
            } catch (\Throwable $e) {
                \Log::error('Hackathon invite dispatch failed', ['email' => $email, 'error' => $e->getMessage()]);
                $teamMember->delete();
                return $respond(false, __('admin.invitation_could_not_be_sent'), ['code' => 'send_failed']);
            }
            $message = __('admin.Invitation sent successfully.');
        }

        $occupied++;
        $remaining = max(0, $max - $occupied);

        return $respond(true, $message, [
            'occupied' => $occupied,
            'max' => $max,
            'remaining' => $remaining,
            'member' => [
                'id' => $teamMember->id,
                'email' => $teamMember->email,
                'name' => $teamMember->Name,
                'user_id' => $teamMember->user_id,
            ],
        ]);
    }

    public function deleteTeamInfo(Request $request)
    {
        
        $teamFormation = TeamFormation::where('id', $request->team_formation_id)->first();
        if (!$teamFormation) {
            return response()->json(['success' => false, 'message' => __("admin.Team_not_found.")]);
        }
        // Leader-only, and only while still on the team.
        if (!$this->canManageTeam(Auth::user(), $teamFormation->id)) {
            return response()->json(['success' => false, 'message' => __("admin.Team_not_found.")], 404);
        }
        $team_member = Team_member::where('team_formation_id', $teamFormation->id)->delete();
       
        $teamFormation->delete();
        return redirect()->route('hackathon.view')->with('success', 'Team information deleted successfully.');
    }

    public function cancelInvitation(Request $request)
    {
        $user = Auth::user();
        // A leader may cancel an invite that is still pending OR one whose email
        // failed to send (they can abandon it instead of retrying).
        $teamMember = Team_member::where('id', $request->id)
            ->whereIn('acceptInvition', ['pending', 'failed'])->first();
        if (!$teamMember || !$this->canManageTeam($user, $teamMember->team_formation_id)) {
            return response()->json(['success' => false, 'message' => __("admin.Invitation_not_found.")]);
        }
        $teamMember->acceptInvition = 'cancelled';
        $teamMember->save();

        // Cancelling an invitation is a silent action: the leader performing it
        // must not be notified about their own action. Notifying $user (which is
        // Auth::user(), the leader) is what mailed the leader a "Status
        // Invitation" message on every cancel (GV-7355).

        $team_q = TeamFormation::find($teamMember->team_formation_id);
        $occupied = $team_q ? Team_member::where('team_formation_id', $team_q->id)
            ->whereIn('acceptInvition', ['accepted', 'join', 'pending'])->count() : 0;
        $max = $team_q ? (optional(Step::where('cycle_id', $team_q->cycle_id)
            ->where('stype', '6')->first())->count_team_number ?? 0) : 0;

        return response()->json([
            'success' => true,
            'message' => __("admin.Invitation_cancelled_successfully."),
            // user_id of the cancelled invite (null for email-only invites) so the
            // Available Members card button can be reset to "Send Invitation".
            'member_id' => $teamMember->user_id,
            'occupied' => $occupied,
            'max' => $max,
            'remaining' => max(0, $max - $occupied),
        ]);
    }

    public function retryInvitation(Request $request)
    {
        $request->validate(['id' => 'required|exists:team_members,id']);

        $user = Auth::user();
        $team_q = TeamFormation::where('user_id', $user->id)->first();
        if (!$team_q) {
            return response()->json(['success' => false, 'message' => __('admin.You are not authorized to send invitations.')], 422);
        }

        // Only the leader's own failed invite can be retried.
        $teamMember = Team_member::where('id', $request->id)
            ->where('team_formation_id', $team_q->id)
            ->where('acceptInvition', 'failed')
            ->first();
        if (!$teamMember) {
            return response()->json(['success' => false, 'message' => __('admin.Invitation_not_found.')], 422);
        }

        // Reuse the same row: back to pending with a fresh 24h window.
        $teamMember->acceptInvition = 'pending';
        $teamMember->expire_at = now()->addHours(24);
        $teamMember->save();

        $email = mb_strtolower(trim((string) $teamMember->Email));
        $user_q = $teamMember->user_id
            ? User::find($teamMember->user_id)
            : User::whereRaw('LOWER(email) = ?', [$email])->first();

        try {
            if ($user_q) {
                $user_q->notify(new InviteToHackathon($user, $team_q, $teamMember));
            } else {
                Notification::route('mail', $email)->notify(new InviteToHackathon($user, $team_q, $teamMember));
            }
        } catch (\Throwable $e) {
            \Log::error('Hackathon invite retry dispatch failed', ['id' => $teamMember->id, 'error' => $e->getMessage()]);
            $teamMember->acceptInvition = 'failed';
            $teamMember->save();
            return response()->json(['success' => false, 'message' => __('admin.invitation_could_not_be_sent')], 422);
        }

        return response()->json([
            'success'   => true,
            'message'   => __('admin.Invitation sent successfully.'),
            'member_id' => $teamMember->id,
        ]);
    }

    public function acceptInvitation($id)
    {
      
        $user = Auth::user();
        // H8: only an invitation addressed to this user, and only while pending. A 'join'
        // row is the user's own request to join; the team leader approves it, not the requester.
        $isExpired = $this->ownInvitation($id, $user, ['pending'])
            ->where('created_at', '<', now()->subHours(24))
            ->first();

            if($isExpired) {
                $isExpired->acceptInvition = 'expired';
                $isExpired->Name = $user->first_name . ' ' . $user->last_name;
                $isExpired->TeamMember = 'hackathon';
                $isExpired->isTeamLeader = 0;
                $isExpired->user_id = $user->id;
                $isExpired->save();
                return redirect()->back()->with('error', 'Invitation_has_expired.');
            }
            $teamMember = $this->ownInvitation($id, $user, ['pending'])->first();
            
            if (!$teamMember) {
                return redirect()->back()->with('error', 'Invitation_not_found.');
            }

            // A founder may hold only one seat per hackathon, whether as a team leader
            // or as an accepted member of someone else's team. (GV-7377)
            $cycleId = $teamMember->teamFormation?->cycle_id;
            if ($cycleId) {
                $leadsATeam = TeamFormation::where('cycle_id', $cycleId)
                    ->where('user_id', $user->id)
                    ->exists();

                $alreadyJoined = Team_member::where('team_members.Email', $user->email)
                    ->where('team_members.acceptInvition', 'accepted')
                    ->where('team_members.id', '!=', $teamMember->id)
                    ->whereHas('teamFormation', function ($query) use ($cycleId) {
                        $query->where('cycle_id', $cycleId);
                    })
                    ->exists();

                if ($leadsATeam || $alreadyJoined) {
                    return redirect()->back()->with('error', 'Already_in_a_team_for_this_hackathon');
                }
            }

                $teamMember->acceptInvition = 'accepted';
                $teamMember->Name = $user->first_name . ' ' . $user->last_name;
                $teamMember->TeamMember = 'hackathon';
                $teamMember->isTeamLeader = 0;
                $teamMember->user_id = $user->id;
                $teamMember->save();
            
                $teams = TeamFormation::where('id', $teamMember->team_formation_id)->first();
                if($teams->group_id_chat == null){
                    $team_leader = Team_member::where('team_formation_id', $teams->id)->where('isTeamLeader', 1)->first();
                    $conversation = Conversation::create([
                        'name' => $team_leader->teamFormation->name,
                        'description' => $team_leader->teamFormation->description,
                        'type' => 'group',
                    ]);
                    $teams->group_id_chat = $conversation->id;
                    $teams->save();
                }
                $conversation = Conversation::findOrFail($teams->group_id_chat);
                $teams = TeamFormation::where('id', $teamMember->team_formation_id)->first();
               

            $count_team_q = Team_member::where('team_formation_id', $teams->id)->get();
            $count_team = $count_team_q->where('acceptInvition', 'accepted')->count();
            $step = Step::where('cycle_id', $teams->cycle_id)->where('stype', '6')->first();
           
            if($step && $step->count_team_number <= $count_team){
                foreach ($count_team_q->whereIn('acceptInvition', ['join', 'pending']) as $member){
                    $member->acceptInvition = 'rejected';
                    $member->save();
                }
            }
            
                // C12: decline the user's other invites to THIS hackathon only; invites to other
                // hackathons stay open (one seat per hackathon, not one seat overall).
                $allteamMembers = Team_member::where('Email', $teamMember->Email)
                ->where('acceptInvition', 'pending')
                ->where('id', '!=', $teamMember->id)
                ->whereHas('teamFormation', function ($query) use ($teams) {
                    $query->where('cycle_id', $teams->cycle_id);
                })
                ->get();
            
                foreach ($allteamMembers as $allteamMember) {
                    $allteamMember->acceptInvition = 'rejected';
                    $allteamMember->Name = $user->first_name . ' ' . $user->last_name;
                    $allteamMember->TeamMember = 'hackathon';
                    $allteamMember->isTeamLeader = 0;
                    $allteamMember->user_id = $user->id;
                    $allteamMember->save();
                }
               
                if($teams->group_id_chat == null){
                    return redirect()->back()->with('error', 'Conversation_Chat_not_found');
                }
                $conversation = Conversation::findOrFail($teams->group_id_chat);
                $user = User::findOrFail($user->id);

                // Check if user is already a participant
                $existingParticipant = $conversation->participants()
                    ->where('participantable_id', $user->id)
                    ->where('participantable_type', get_class($user))
                    ->exists();

                if ($existingParticipant) {
                    return redirect()->back()->with([
                        'error' => 'User is already in this conversation'
                    ], 400);
                }

                // Add user as participant
                $conversation->participants()->create([
                    'participantable_id' => $user->id,
                    'participantable_type' => get_class($user),
                    'role' => 'participant'
                ]);

                $userteam = User::where('id', $teamMember->user_id)->first();
                $leader = User::findOrFail($teams ->user_id);
                 $leader->notify(new StatusHackathonInvitation($user, $userteam, $teams, 'Accept'));

        return redirect()->back()->with('success', 'Invitation_accepted_successfully');
    }

    public function rejectInvitation($id)
    {
        $user = Auth::user();
        // H8: decline only an invitation addressed to this user, or withdraw their own join request.
        $isExpired = $this->ownInvitation($id, $user, ['pending'])
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
                
                $teamMember = $this->ownInvitation($id, $user, ['pending', 'join'])->first();

            // C1: check before the first dereference (was after it, so a missing row crashed).
            if (!$teamMember) {
                return redirect()->back()->with('error', 'Invitation not found.');
            }

            $teams = TeamFormation::where('id', $teamMember->team_formation_id)->first();
            $count_team_q = Team_member::where('team_formation_id', $teams->id)
                ->get();
            $count_team = $count_team_q->where('acceptInvition', 'accepted')->count();
                $step = Step::where('cycle_id', $teams->cycle_id)->where('stype', '6')->first();
            if($step && $step->count_team_number <= $count_team){
                foreach ($count_team_q->whereIn('acceptInvition', ['join', 'pending']) as $member){
                    $member->acceptInvition = 'rejected';
                    $member->save();
                }
                return response()->json(['success' => false, 'message' => __("admin.Team_is_full.")]);
            }

                $teamMember->acceptInvition = 'rejected';
                $teamMember->Name = $user->first_name . ' ' . $user->last_name;
                $teamMember->TeamMember = 'hackathon';
                $teamMember->isTeamLeader = 0;
                $teamMember->user_id = $user->id;
                $teamMember->save();

                $userteam = User::where('id', $teamMember->user_id)->first();
                $leader = User::findOrFail($teams ->user_id);
               
                 $leader->notify(new StatusHackathonInvitation($user, $userteam, $teams, 'Reject'));

        return redirect()->back()->with('success', 'Invitation rejected successfully.');
    }

    /** Team_member rows with this id that are addressed to $user (email, case-insensitive) in one of $statuses. */
    private function ownInvitation($id, $user, array $statuses)
    {
        return Team_member::where('id', $id)
            ->whereIn('acceptInvition', $statuses)
            ->whereRaw('LOWER(Email) = ?', [strtolower((string) $user->email)]);
    }

    public function getOutTeam(Request $request)
    {
        $user = Auth::user();
        $teamMember = Team_member::where('id', $request->id)->where('acceptInvition', 'accepted')->first();
        if (!$teamMember) {
            return response()->json(['success' => false, 'message' => __("admin.Invitation_not_found.")]);
        }
        // Only the leader of THAT team may remove one of its members: the row id
        // is client-supplied, so without this anyone could remove any member of
        // any team.
        if (!$this->canManageTeam($user, $teamMember->team_formation_id)) {
            return response()->json(['success' => false, 'message' => __("admin.Invitation_not_found.")], 404);
        }
        $teamMember->acceptInvition = 'removed';
        $teamMember->save();

        $teams = TeamFormation::where('id', $teamMember->team_formation_id)->first();
        $groupId = $teams->group_id_chat;

        if($groupId != null)
        {
            $conversation = Conversation::findOrFail($groupId);
            $user = User::findOrFail($teamMember->user_id);

            $participant = $conversation->participants()
                ->where('participantable_id', $user->id)
                ->where('participantable_type', get_class($user))
                ->first();

            // GV-7375: the member has already been marked 'removed' above. Not being
            // a group participant (group never created, or already detached) is not
            // a failure — returning 404 here made the UI show "delete failed" and
            // keep the row on screen even though the removal had been saved.
            if ($participant) {
                $participant->delete();
            }
        }
        return response()->json(['success' => true, 'message' => __("admin.Member_removed_successfully.")]);
    }

    public function RequestJoin(Request $request)
    {
        $user = Auth::user();
        $teams = TeamFormation::where('id', $request->id)->first();

        if(!$teams){
            return redirect()->back()->with('error', 'Team not found.');
        }
        $teamMember = Team_member::where('team_formation_id', $teams->id)->whereIn('acceptInvition', ['accepted', 'join', 'pending'])->get();
        $maxMembers = Step::where('cycle_id', $teams->cycle_id)->where('stype', '6')->first()->count_team_number;

        if (count($teamMember) >= $maxMembers) {
            return response()->json(['success' => false, 'message' => __("admin.Team_is_full.")]);
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
            // Only that team's leader may approve a join request.
            if (!$teamMember || !$this->canManageTeam(Auth::user(), $teamMember->team_formation_id)) {
                return response()->json(['success' => false, 'message' => __("admin.Invitation_not_found.")], 404);
            }
            $teams = TeamFormation::where('id', $teamMember->team_formation_id)->first();
                if($teams->group_id_chat == null){
                    $team_leader = Team_member::where('team_formation_id', $teams->id)->where('isTeamLeader', 1)->first();
                    $conversation = Conversation::create([
                        'name' => $team_leader->teamFormation->name,
                        'description' => $team_leader->teamFormation->description,
                        'type' => 'group',
                    ]);
                    $teams->group_id_chat = $conversation->id;
                    $teams->save();
                }
            $conversation = Conversation::findOrFail($teams->group_id_chat);
        $teams = TeamFormation::where('id', $teamMember->team_formation_id)->first();
        $count_team_q = Team_member::where('team_formation_id', $teams->id)
            ->get();
        $count_team = $count_team_q->where('acceptInvition', 'accepted')->count();

        $step = Step::where('cycle_id', $teams->cycle_id)->where('stype', '6')->first();
        if($step && $step->count_team_number < $count_team){
            foreach ($count_team_q->whereIn('acceptInvition', ['join', 'pending']) as $member){
                $member->acceptInvition = 'rejected';
                $member->save();
            }
            return response()->json(['success' => false, 'message' => __("admin.Team_is_full.")]);
        }
            if(!$teamMember) {
                return redirect()->back()->with('error', 'Request not found.');
            }

                $teamMember->acceptInvition = 'accepted';
                $teamMember->save();
                $allteamMembers = Team_member::where('Email', $teamMember->Email)
                ->where('acceptInvition', 'join')
                ->where('id', '!=', $teamMember->id)
                ->whereHas('teamFormation', function ($query) use ($teams) {
                    $query->where('cycle_id', $teams->cycle_id);
                })
                ->get();
                foreach ($allteamMembers as $allteamMember) {
                    $allteamMember->acceptInvition = 'rejected';
                    $allteamMember->save();
                }

                
                $user = User::findOrFail($teamMember->user_id);

                // Check if user is already a participant
                $existingParticipant = $conversation->participants()
                    ->where('participantable_id', $user->id)
                    ->where('participantable_type', get_class($user))
                    ->exists();

                if ($existingParticipant) {
                    return redirect()->back()->with([
                        'error' => 'User is already in this conversation'
                    ], 400);
                }

                // Add user as participant
                $conversation->participants()->create([
                    'participantable_id' => $user->id,
                    'participantable_type' => get_class($user),
                    'role' => 'participant'
                ]);

        return response()->json(['success' => true, 'message' => __("admin.Request_Approved_successfully.")]);
    }

    public function RejectedRequestJoin(Request $request)
    {
        
        $teamMember = Team_member::where('id', $request->id)
            ->where('acceptInvition', 'join')
            ->first();
            // Only that team's leader may reject a join request.
            if(!$teamMember || !$this->canManageTeam(Auth::user(), $teamMember->team_formation_id)) {
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
            FROM
                form_data as FD 
            LEFT JOIN
                companies as C ON C.id = FD.company_id
            LEFT JOIN
                users as U ON U.id = FD.user_id
            LEFT JOIN
                steps as S ON S.id = FD.step_id
            LEFT JOIN
                builder_forms as BF ON BF.id = S.data
            LEFT JOIN
                cycles as CYC ON CYC.id = S.cycle_id
            LEFT JOIN
                organizations as O ON O.id = CYC.organization_id
            WHERE
                FD.deleted_at IS NULL
                AND FD.step_id = ?
                AND FD.user_id = ?
        ";
        $orgIds = $user->organizations_ids;
        if( !$user->hasRole('admin') && !empty($orgIds) )
        {
            $forms .= " AND CYC.organization_id IN (" . implode(',', array_map('intval', $orgIds)) . ") ";
        }
        $forms = DB::select($forms, [$step_id, $user_id]);
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
                $value = Formdata::fileAnswer($value, $item->FD_company_id) ?? $value;
                return (object) [
                    'user_id' => $item->FD_user_id,
                    'name' => $item->{'user_name_' . $l},
                    'label' => $item->FD_question,
                    'value' => $value,
                ];
            })->groupBy('user_id')->values();

            return $submission;
        })->values()->all(); // remove collection keys (numeric indexing)
      $data = array_values($data);
      return view('admin.hackathon.show', compact('data'));
    }

    public function viewHackathonTeam($id)
    {
        $l = app()->getLocale();
        
        // Comprehensive team query with all available information
        $teams_q = "SELECT 
                tf.id as tf_id,
                tf.name as tf_name,
                tf.description as tf_description,
                tf.cycle_id as tf_cycle_id,
                tf.step_id as tf_step_id,
                tf.img_team as tf_img_team,
                tf.user_id as tf_user_id,
                tf.project_name_ar as tf_project_name_ar,
                tf.project_name_en as tf_project_name_en,
                tf.project_description_ar as tf_project_description_ar,
                tf.project_description_en as tf_project_description_en,
                tf.project_status as tf_project_status,
                tf.target_track as tf_target_track,
                tf.region as tf_region,
                tf.winner_status as tf_winner_status,
                tf.max_teams_members as tf_max_teams_members,
                tf.created_at as tf_created_at,
                tf.updated_at as tf_updated_at,
                tm.id as tm_id,
                tm.user_id as tm_user_id,
                tm.acceptInvition as tm_acceptInvition,
                tm.Name as tm_name,
                tm.Email as tm_email,
                tm.isTeamLeader as tm_isTeamLeader,
                tm.team_formation_id as tm_team_formation_id,
                tm.Company_id as tm_company_id,
                tm.created_at as tm_created_at,
                u.id as u_id,
                concat(u.first_name_en, ' ', u.last_name_en) as user_name_en,
                concat(u.first_name_ar, ' ', u.last_name_ar) as user_name_ar,
                u.email as u_email,
                u.phone as u_phone,
                u.img_url as u_img_url,
                u.title_ar as u_title_ar,
                u.title_en as u_title_en,
                u.address as u_address,
                u.gender as u_gender,
                ub.id as ub_id,
                ub.bio_ar as ub_bio_ar,
                ub.bio_en as ub_bio_en,
                ub.linkedin as ub_linkedin,
                c.id as c_id,
                c.name_en as c_name_en,
                c.name_ar as c_name_ar,
                c.logo as c_logo,
                cy.id as cy_id,
                cy.title as cy_title,
                cy.description as cy_description,
                s.id as s_id,
                s.title as s_title,
                s.description as s_description
            FROM 
                team_members as tm
            LEFT JOIN
                team_formations as tf ON tm.team_formation_id = tf.id
            LEFT JOIN
                users as u ON tm.user_id = u.id
            LEFT JOIN
                users_bio as ub ON u.id = ub.user_id
            LEFT JOIN
                companies as c ON tm.Company_id = c.id
            LEFT JOIN
                cycles as cy ON tf.cycle_id = cy.id
            LEFT JOIN
                steps as s ON tf.step_id = s.id
            WHERE
                tm.team_formation_id = ?
        ";

        // Get shared files for the team
        $files_q = "SELECT 
                sf.id as sf_id,
                sf.file as sf_file,
                sf.description as sf_description,
                sf.created_at as sf_created_at,
                sf.updated_at as sf_updated_at,
                u.id as u_id,
                concat(u.first_name_en, ' ', u.last_name_en) as uploader_name_en,
                concat(u.first_name_ar, ' ', u.last_name_ar) as uploader_name_ar,
                u.img_url as uploader_img_url
            FROM 
                shared_files as sf
            LEFT JOIN
                users as u ON sf.user_id = u.id
            WHERE
                sf.team_formation_id = ?
            ORDER BY sf.created_at DESC
        ";

        // Get todo lists for the team
        $todos_q = "SELECT 
                tl.id as tl_id,
                tl.title as tl_title,
                tl.description as tl_description,
                tl.status as tl_status,
                tl.due_date as tl_due_date,
                tl.team_formation_id as tl_team_formation_id,
                tl.created_at as tl_created_at,
                tl.updated_at as tl_updated_at,
                u_creator.id as creator_id,
                concat(u_creator.first_name_en, ' ', u_creator.last_name_en) as creator_name_en,
                u_assignee.id as assignee_id,
                concat(u_assignee.first_name_en, ' ', u_assignee.last_name_en) as assignee_name_en
            FROM 
                todo_lists as tl
            LEFT JOIN
                users as u_creator ON tl.user_id = u_creator.id
            LEFT JOIN
                users as u_assignee ON tl.assignee = u_assignee.id
            WHERE
                tl.team_formation_id = ?
            ORDER BY tl.created_at DESC
        ";

        $teams_data = collect(DB::select($teams_q, [$id]));
        $shared_files = collect(DB::select($files_q, [$id]));
        $todo_lists = collect(DB::select($todos_q, [$id]));

        if ($teams_data->isEmpty()) {
            abort(404, 'Team not found');
        }

        $teams = $teams_data->groupBy('tm_team_formation_id');

        $teams = $teams->map(function ($group) use ($l, $shared_files, $todo_lists) {
            $first = $group->first();
            $team = new stdClass;
            
            // Basic team information
            $team->id = $first->tf_id;
            $team->name = $first->tf_name;
            $team->desc = $first->tf_description;
            $team->cycle_id = $first->tf_cycle_id;
            $team->step_id = $first->tf_step_id;
            // Logos stream from storage via the authorized route, not a public path.
            $team->photo = $first->tf_img_team
                ? route('hackathon.teamLogo', basename($first->tf_img_team))
                : null;
            $team->region = $first->tf_region;
            $team->winner_status = $first->tf_winner_status;
            $team->max_members = $first->tf_max_teams_members;
            $team->created_at = $first->tf_created_at;
            $team->updated_at = $first->tf_updated_at;
            
            // Cycle information
            $team->cycle = new stdClass;
            $team->cycle->id = $first->cy_id;
            $team->cycle->title = $first->cy_title;
            $team->cycle->description = $first->cy_description;
            
            // Step information
            $team->step = new stdClass;
            $team->step->id = $first->s_id;
            $team->step->title = $first->s_title;
            $team->step->description = $first->s_description;
            
            // Project information
            $team->project = new stdClass;
            $team->project->name = $first->tf_project_name_en;
            $team->project->description = $first->tf_project_description_en;
            $team->project->status = $first->tf_project_status;
            $team->project->track = $first->tf_target_track;
            $team->project->region = $first->tf_region;

            // Team Members
            $team->members = $group->where('tm_acceptInvition', 'accepted')->map(function ($member) use ($l) {
                $user = new stdClass;
                $user->id = $member->u_id;
                $user->name = $l == 'ar' ? $member->user_name_ar : $member->user_name_en;
                $user->email = $member->u_email;
                $user->phone = $member->u_phone;
                $user->img_url = $member->u_img_url;
                $user->title = $l == 'ar' ? $member->u_title_ar : $member->u_title_en;
                $user->address = $member->u_address;
                $user->gender = $member->u_gender;
                $user->is_leader = $member->tm_isTeamLeader;
                $user->joined_at = $member->tm_created_at;
                $user->status = $member->tm_acceptInvition;
                
                // Social media links
                $user->linkedin = $member->ub_linkedin;
                
                // Bio information
                $user->bio = $l == 'ar' ? $member->ub_bio_ar : $member->ub_bio_en;
                
                // Company information
                $user->company = new stdClass;
                $user->company->id = $member->c_id;
                $user->company->name = $l == 'ar' ? $member->c_name_ar : $member->c_name_en;
                $user->company->logo = $member->c_logo;
                
                return $user;
            })->values()->all();
            // Shared files
            $team->shared_files = $shared_files->map(function ($file) use ($l) {
                $attachment = new stdClass;
                $attachment->id = $file->sf_id;
                $attachment->file = $file->sf_file;
                $attachment->description = $file->sf_description;
                $attachment->created_at = $file->sf_created_at;
                $attachment->updated_at = $file->sf_updated_at;
                $attachment->uploader = new stdClass;
                $attachment->uploader->id = $file->u_id;
                $attachment->uploader->name = $l == 'ar' ? $file->uploader_name_ar : $file->uploader_name_en;
                $attachment->uploader->img_url = $file->uploader_img_url;
                return $attachment;
            })->all();
            
            // Todo lists
            $team->todo_lists = $todo_lists->map(function ($todo) use ($l) {
                $task = new stdClass;
                $task->id = $todo->tl_id;
                $task->title = $todo->tl_title;
                $task->description = $todo->tl_description;
                $task->status = $todo->tl_status;
                $task->team_formation_id = $todo->tl_team_formation_id;
                $task->due_date = $todo->tl_due_date;
                $task->created_at = $todo->tl_created_at;
                $task->updated_at = $todo->tl_updated_at;
                $task->creator = new stdClass;
                $task->creator->id = $todo->creator_id;
                $task->creator->name = $todo->creator_name_en;
                $task->assignee = new stdClass;
                $task->assignee->id = $todo->assignee_id;
                $task->assignee->name = $todo->assignee_name_en;
                return $task;
            })->all();
            
            return $team;
        });
        
        // Get the first team from the collection, or null if it doesn't exist.
        $team = $teams->values()->first();
            
        // If no team is found, you might want to abort or handle it gracefully.
        if (!$team) { abort(404, 'Team not found'); }
        
        return view('admin.hackathon.team', compact('team', 'l'));
    }

    public function viewAdminHackaton($id)
    {
        $l = app()->getLocale();
        $teams_q = "SELECT 
                tf.id as tf_id,
                tf.name as tf_name,
                tf.description as tf_description,
                tf.cycle_id as tf_cycle_id,
                tf.step_id as tf_step_id,
                tf.img_team as tf_img_team,
                tf.user_id as tf_user_id,
                tf.project_name_ar as tf_projectName_ar,
                tf.project_name_en as tf_projectName_en,
                tf.project_description_ar as tf_projectDescription_ar,
                tf.project_description_en as tf_projectDescription_en,
                tf.project_status as tf_projectStatus,
                tf.region as 'tf_region',
                tf.created_at as tf_created_at,
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
                u.phone as u_phone,
                u.img_url as u_img_url,
                u.title_ar as u_title_ar,
                u.title_en as u_title_en,
                c.id as c_id,
                c.cycle as c_cycle,
                c.step as c_step,
                cstep.title as c_step_title
            FROM
                team_members as tm
            LEFT JOIN
                team_formations as tf ON tm.team_formation_id = tf.id
            LEFT JOIN
                users as u ON tm.user_id = u.id
            LEFT JOIN
                companies as c ON c.id = u.company_id
            LEFT JOIN
                steps as cstep ON cstep.id = c.step
            WHERE
                tf.cycle_id = ?
            AND
                tm.acceptInvition = 'accepted'
        ";

        $teams = collect(DB::select($teams_q, [$id]));
        $step_q = "SELECT * FROM steps WHERE cycle_id = ?";
        $step = collect(DB::select($step_q, [$id]));

        // Alumni for this cycle, keyed for O(1) lookup. Used to tell apart a team
        // that graduated (has an alumni row) from one dismissed by disapproval when
        // the leader's company is no longer attached to the cycle.
        $cycleAlumni = DB::table('alumni')->where('cycle_id', $id)->get();
        $alumniCompanyIds = $cycleAlumni->pluck('company_id')->filter()->unique()->flip();
        $alumniUserIds = $cycleAlumni->pluck('user_id')->filter()->unique()->flip();

        $usersInTeams = $teams->pluck('u_id')->unique();

        // المستخدمين المرتبطين بشركات في نفس الـ cycle_id
        $allUsersInCycle = collect(DB::select("SELECT
                u.id
            FROM
                users as u
            INNER JOIN
                companies as c ON c.id = u.company_id
            WHERE
                c.cycle = ?
            ", [$id]
        ))->pluck('id');

        // المستخدمين الغير موجودين في الفريق
        $usersNotInTeams = $allUsersInCycle->diff($usersInTeams);

        // العدادات

        $data = [];
        $data['countInTeams'] = $usersInTeams->count();
        $data['countNotInTeams'] = $usersNotInTeams->count();
       
        $data['count_teams'] = $teams->where('tm_isTeamLeader', 1)->count();
        
        $data['count_members'] = $allUsersInCycle->count();
        $data['all_teams'] = $teams
            ->filter(fn($member) => $member->tm_isTeamLeader == 1)
            ->map(function ($leader) use ($teams, $l, $step, $alumniCompanyIds, $alumniUserIds) {
                $teamMembers = $teams->where('tf_id', $leader->tf_id);
                $team = new stdClass;
                $team->id = $leader->tf_id;
                $team->name = $leader->tf_name;
                $team->email = $leader->u_email;
                $team->user_name = $leader->{'user_name_'.$l};
                $team->u_phone = $leader->u_phone;
                $team->desc = $leader->tf_description;
                $team->cycle_id = $leader->tf_cycle_id;
                $team->step_id = $leader->tf_step_id;
                // Where the leader's company currently sits, from companies.step — the
                // same source the winners/final-screening pages use. When the company
                // is still attached to this cycle we show its step title. When it is
                // detached (cycle nulled), the team either graduated (an alumni row
                // exists for this cycle) or was dismissed by disapproval — so we label
                // 'alumni' vs 'disapproved' instead of a bare step. 'none' means no
                // company/step at all (e.g. a stub that never entered the cycle).
                if ( $leader->c_cycle == $leader->tf_cycle_id && !empty( $leader->c_step_title ) ) {
                    $team->step_state = 'step';
                    $team->company_step = $leader->c_step_title;
                } elseif ( $alumniCompanyIds->has($leader->c_id) || $alumniUserIds->has($leader->u_id) ) {
                    $team->step_state = 'alumni';
                    $team->company_step = null;
                } elseif ( empty( $leader->c_cycle ) && ( !empty( $leader->c_id ) ) ) {
                    $team->step_state = 'disapproved';
                    $team->company_step = null;
                } else {
                    $team->step_state = 'none';
                    $team->company_step = null;
                }
                // Logos stream from storage via the authorized route, not a public path.
                $team->photo = $leader->tf_img_team
                    ? route('hackathon.teamLogo', basename($leader->tf_img_team))
                    : null;
                $team->count_members = $teamMembers->where('tm_acceptInvition', 'accepted')->count();
                $team->created_at = $leader->tf_created_at;
                $team->max_count_members = $step->where('stype', 6)->first()->count_team_number;
                $team->project_name_en = $leader->tf_projectName_en;
                $team->project_name_ar = $leader->tf_projectName_ar;
                $team->project_description_en = $leader->tf_projectDescription_en;
                $team->project_description_ar = $leader->tf_projectDescription_ar;
                $team->project_status = $leader->tf_projectStatus;
                $team->region = $leader->tf_region;

                $team->members = $teamMembers->map(function ($member) use ($l) {
                    $user = new stdClass;
                    $user->id = $member->u_id;
                    $user->name = $member->{'user_name_'.$l};
                    $user->email = $member->u_email;
                    $user->img_url = $member->u_img_url;
                    $user->is_leader = $member->tm_isTeamLeader;
                    $user->request_sent = $member->tm_acceptInvition == 'join' ? true : false;
                    $user->status = $member->tm_acceptInvition == 'accepted' ? 'Accepted' : 'join';
                    $user->title = $member->u_title_en;
                    return $user;
                })->values();
                return $team;
            })->unique('id')->values();

        $groupedTeams = $teams->groupBy('tf_id')->map(function ( $teamMembers ) {
            $leader = $teamMembers->where('tm_isTeamLeader', 1)->first();
            $output = new stdClass;
            $output->id = $teamMembers->first()->tf_id;
            $output->leader = new stdClass;
            $output->leader->id = $leader->tm_id;
            $output->leader->name = $leader->tm_name;
            $output->leader->email = $leader->tm_email;
            $output->leader->phone = $leader->u_phone;
            return $output;
        })->values();
        $data['all_projects'] = $teams->whereNotNull('tf_projectName_en')
            ->map(function ($project) use ($l, $groupedTeams) {
                $team = new stdClass;
                $team->id = $project->tf_id;
                $team->project_name = $project->tf_projectName_en;
                $team->project_description = $project->tf_projectDescription_en;
                $team->project_status = $project->tf_projectStatus;
                $team->region = $project->tf_region;
                $team->created_at = $project->tf_created_at;
                $team->leader = $groupedTeams->where('id', $project->tf_id)->first()->leader;
                return $team;
            })->values()->unique('id');
        $data['count_projects'] = $data['all_projects']->count();
        
        $service = new \App\Services\NextSessionsService();
        $days = request()->query('days', 14);
        $l = app()->getLocale();
        $nextSessions = $service->getUpcomingSessions((int)$days, $l, null, $id);

        $data['CountNextSessions'] = $nextSessions->whereNull('approved')->count();

        $cType = 0;
        if( !empty( $stepData ) ) { $cType = $stepData->stype; }

         // 2. نحدد آخر 7 شهور (من الشهر الحالي أو ممكن تعدل لو عايز فترة مختلفة)
        $months = collect();
        for ($i = 11; $i >= 0; $i--) {
            $months->push(Carbon::now()->subMonths($i));
        }

        // 3. نجهز المصفوفات النهائية
        $registrationMonths = [];
        $registrationCounts = [];

        foreach ($months as $dt) {
            $monthLabel = strtolower($dt->format('F')); // 'january', 'february', ...
            $start = $dt->copy()->startOfMonth();
            $end = $dt->copy()->endOfMonth();

            // نعدّ العناصر اللي tf_created_at داخل الشهر ده
            $count = $teams->filter(function ($item) use ($start, $end) {
                if (empty($item->tf_created_at)) {
                    return false;
                }
                try {
                    $created = Carbon::parse($item->tf_created_at);
                } catch (\Exception $e) {
                    return false;
                }
                return $created->between($start, $end);
            })->count();

            $registrationMonths[] = $monthLabel;
            $registrationCounts[] = $count;
        }
        $data['registrationMonths'] = $registrationMonths;
        $data['registrationCounts'] = $registrationCounts;
        
        $data['Count_per_status'] = $teams->groupBy('tf_projectStatus')->map(function ($item) {
            return $item->count();
        })->toArray();
                $mapping = [
                    0  => 'null',
                    20 => __('admin.Idea + Team'),
                    40 => __('admin.Research'),
                    60 => __('admin.Design & Build'),
                    80 => __('admin.Ready for Pitch'),
                    100 => __('admin.Project Submitted'),
                ];
                $keys = array_keys($data['Count_per_status']);
                $labels = array_map(function($k) use ($mapping) {
                    return $mapping[$k] ?? $k;
                }, $keys);
                $values = array_values($data['Count_per_status']);
        $data['submissionLabels'] = $labels;
        $data['submissionCounts'] = $values;

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
                BF.form_name as 'BF_form_name',
                TF.id as 'TF_id',
                TF.name as 'TF_name',
                TF.user_id as 'TF_user_id',
                TF.project_name_en as 'TF_project_name_en',
                TF.Region as 'TF_region',
                Concat(UTF.first_name_en, ' ', UTF.last_name_en) as 'UTF_user_name_en',
                Concat(UTF.first_name_ar, ' ', UTF.last_name_ar) as 'UTF_user_name_ar',
                UTF.id as 'UTF_id',
                UTF.email as 'UTF_email',
                UTF.phone as 'UTF_phone'
            FROM
                form_data as FD 
            LEFT JOIN
                companies as C ON C.id = FD.company_id
            LEFT JOIN
                users as U ON U.id = FD.user_id
            LEFT JOIN
                team_formations as TF ON TF.user_id = U.id
            LEFT JOIN
                users as UTF ON UTF.id = FD.user_id
            LEFT JOIN
                steps as S ON S.id = FD.step_id
            LEFT JOIN
                builder_forms as BF ON BF.id = S.data
            LEFT JOIN
                cycles as CYC ON CYC.id = S.cycle_id
            LEFT JOIN
                organizations as O ON O.id = CYC.organization_id
            WHERE
                FD.deleted_at IS NULL
                AND CYC.id = ? 
                AND S.stype = 5
        ";
        $params = [$id];

        $forms = DB::select($forms, $params);
        $forms = collect($forms);
            
        $C = 1;
        // نبدأ بتجميع كل الأسئلة حسب step_id
        $grouped = $forms->groupBy('FD_user_id');
        $data['Count_submitted_projects'] = $grouped->count();
        $data['submitted_projects'] = $grouped->map(function ($group, $stepId) use ($l) {
            $first = $group->first();
            $submission = new stdClass;
            $submission->company_id = $first->FD_company_id;
            // $submission->user_id = $first->FD_user_id;
            $submission->user_name = $first->{'user_name_' . $l};
            $submission->team_name = $first->TF_name;
            $submission->project_name = $first->TF_project_name_en;
            $submission->email = $first->U_email;
            $submission->cycle_id = $first->CYC_id;
            $submission->step_id = $first->FD_step_id;
            $submission->user_id = $first->FD_user_id;
            $submission->company_step = $first->C_step;
            $submission->team_leader = $first->UTF_user_name_en;
            $submission->region = $first->TF_region;
            $submission->step_name = $first->S_title;
            $submission->eamil = $first->UTF_email;
            $submission->phone = $first->UTF_phone;
            $submission->submissionDate = $first->FD_created_at ?? $first->FD_updated_at;
            return $submission;
        })->values()->all(); // remove collection keys (numeric indexing)

        $data['submitted_projects'] = array_values($data['submitted_projects']);
        
        $allUsers = DB::table('users')
            ->join('team_formations', 'team_formations.user_id', '=', 'users.id')
            ->join('steps', 'steps.cycle_id', '=', 'team_formations.cycle_id')
            ->where('steps.cycle_id', $id)
            ->select('users.id')
            ->distinct()
            ->pluck('users.id')
            ->toArray();

        $usersWithFormData = DB::table('form_data')
            ->join('steps', 'steps.id', '=', 'form_data.step_id')
            ->where('steps.cycle_id', $id)
            ->where('steps.stype', 5)
            ->whereNull('form_data.deleted_at')
            ->distinct()
            ->pluck('form_data.user_id')
            ->toArray();

        $data['projects_NotSubmitted'] = array_diff($allUsers, $usersWithFormData);

        $notSubmittedUsers = DB::table('users as U')
            ->select([
                'C.name_en as C_name_en',
                'C.name_ar as C_name_ar',
                'C.id as C_id',
                'C.step as C_step',
                DB::raw("Concat(U.first_name_en, ' ', U.last_name_en) as user_name_en"),
                DB::raw("Concat(U.first_name_ar, ' ', U.last_name_ar) as user_name_ar"),
                'U.id as U_id',
                'U.email as U_email',
                'U.phone as U_phone',
                'CYC.title as CYC_title',
                'CYC.id as CYC_id',
                'CYC.organization_id as CYC_organization_id',
                'TF.id as TF_id',
                'TF.name as TF_name',
                'TF.user_id as TF_user_id',
                'TF.project_name_en as TF_project_name_en',
                'TF.Region as TF_region',
                'S.id as S_id',
                'S.title as S_title',
                'S.stype as S_stype'
            ])
            ->leftJoin('companies as C', 'C.id', '=', 'U.company_id')
            ->leftJoin('team_formations as TF', 'TF.user_id', '=', 'U.id')
            ->leftJoin('cycles as CYC', 'CYC.id', '=', 'TF.cycle_id')
            ->leftJoin('organizations as O', 'O.id', '=', 'CYC.organization_id')
            ->leftJoin('steps as S', 'S.id', '=', 'C.step')
            ->where('CYC.id', $id)
            ->where('S.stype', 5)
            ->whereNotIn('U.id', $usersWithFormData)
            ->whereIn('U.id', $data['projects_NotSubmitted'])
            ->get();


        $data['not_submitted_projects'] = $notSubmittedUsers->map(function ($group, $stepId) use ($l) {
            $submission = new stdClass;
            $submission->company_id = $group->C_id;
            $submission->team_leader = $group->{'user_name_' . $l};
            $submission->team_name = $group->TF_name;
            $submission->project_name = $group->TF_project_name_en;
            $submission->email = $group->U_email;
            $submission->phone = $group->U_phone;
            $submission->cycle_title = $group->CYC_title;
            $submission->cycle_id = $group->CYC_id;
            $submission->step_id = 5; // Assuming step_id is always 5 for not submitted projects
            $submission->user_id = $group->U_id;
            $submission->company_step = $group->S_title;
            $submission->region = $group->TF_region;
            return $submission;
        })->values()->all();

        $data['totalUsers']= count($allUsers);
        $data['usersWithFormDataCount'] = count($usersWithFormData);
        $data['usersWithoutFormDataCount'] =$data['totalUsers']- $data['usersWithFormDataCount'];

        return view('admin.hackathon.admin.dashboard_admin', compact('teams', 'step', 'data', 'nextSessions', 'cType'));
    }

    /**
     * GV-9566: status of a window whose start/end are inclusive DATE days in the
     * platform zone. Comparing now() with their midnights ended it on its last morning.
     */
    private static function dateWindowStatus($start, $end, $organizationId = null): string
    {
        $today = TimezoneService::platformToday(TimezoneService::platform($organizationId ? (int) $organizationId : null));
        $start = Carbon::parse($start)->format('Y-m-d');
        $end = Carbon::parse($end)->format('Y-m-d');

        return $today < $start ? 'Upcoming' : ($today <= $end ? 'Ongoing' : 'Finished');
    }

    /** H1: winners pages are for staff of the organization that runs the hackathon. */
    private function authorizeWinners($id): void
    {
        $user = auth()->user();
        abort_unless($user->ableTo('hackathon.viewAdminHackaton') && $user->managesOrganization(Cycle::whereKey($id)->value('organization_id')), 403);
    }

    public function viewHackathonWinners( $id )
    {
        $this->authorizeWinners($id);
        $l = app()->getLocale();
        $cycle = Cycle::getHackathon( $id );

        if( empty( $cycle ) ) { return abort(404); }
        $teams = TeamFormation::teams( $id );
        $winnersData = TeamFormation::winners( $teams );

        $start = Carbon::parse($cycle->start);
        $end = Carbon::parse($cycle->end);
        $status = self::dateWindowStatus($start, $end, $cycle->organization_id);
        $status_ar = match($status) {
            'Upcoming' => 'قادم',
            'Ongoing' => 'قائم',
            'Finished' => 'منتهي',
            default => '',
        };
        $duration = $start->format('F d') . ' - ' . $end->format('d, Y');
        $duration_ar = $start->format('d') . ' - ' . $end->format('d') . ' ' . __('months.'.$end->format('m')) . ' ' . $end->format('Y');

        $hackathon = new stdClass;
        $hackathon->id = $cycle->id;
        $hackathon->title = $cycle->title;
        $hackathon->description = $cycle->description;
        $hackathon->status = $l == 'en' ? $status : $status_ar;
        $hackathon->duration = $l == 'en' ? $duration : $duration_ar;
        $hackathon->announcement = $cycle->winners_announcement == 1 ? true : false;
        $hackathon->visibility = $cycle->winners_visibility == 'private' ? 'private' : 'public';
        $hackathon->organization = new stdClass;
        $hackathon->organization->id = $cycle->organization_id;
        $hackathon->organization->name =  $cycle->{'organization_name_' . $l};

        return view('admin.hackathon.admin.winners', compact('teams', 'winnersData', 'hackathon'));
    }

    public function exportHackathonWinners( $id )
    {
        // organizations_ids also holds a founder's own program org, so it is not a staff check.
        $this->authorizeWinners($id);

        $tail = 'xlsx';
        $request = request();
        $type = $request->has('type') ? ( in_array( $request->input('type', 'excel'), ['csv', 'excel'] ) ? $request->input('type', 'excel') : 'excel' ) : 'excel';
        if( $type == 'csv' ) { $tail = 'csv'; } elseif( $type == 'excel' ) { $tail = 'xlsx'; } else { return abort(404); }
        $cycle = Cycle::getHackathon( $id );

        if( empty( $cycle ) ) { return abort(404); }
        $teams = TeamFormation::teams( $id );
        $winnersData = TeamFormation::winners( $teams );

        $date = date('Y_m_d_H_i_s');
        return Excel::download(new WinnersExport( $winnersData ), 'winners_report_' . $date . '.' . $tail);
    }

    public function publishHackathonWinners( Request $request, $id )
    {
        $this->authorizeWinners($id);
        $cycle = DB::table('cycles as c')
            ->select([
                'c.id',
                'c.title',
                'c.description',
                'c.start',
                'c.end',
                'c.winners_announcement',
                'o.id AS organization_id',
                'o.name_ar AS organization_name_ar',
                'o.name_en AS organization_name_en',
            ])
            ->leftJoin('organizations as o', 'c.organization_id', '=', 'o.id')
            ->where('c.type', 'Hackathon')
            ->whereNull('c.deleted_at')
            ->where('c.id', $id)
            ->first();

        $l = app()->getLocale();
        if( empty( $cycle ) )
        {
            $notFound = $l == 'en' ? 'Hackathon not found' : 'الهاكاتون غير موجود';
            return redirect()->back()->with('error', $notFound);
        }

        DB::beginTransaction();
        try {
            $status = intval($request->announcementStatus);
            $visiblity = $request->announcementVisibility == 'private' ? 'private' : 'public';
            DB::table('cycles')->where('id', $cycle->id)->update([
                'winners_announcement' => $status,
                'winners_visibility' => $visiblity
            ]);
            $winners = $request->winners;
            $teamsIds = array_keys($winners);
            $teams = TeamFormation::where('cycle_id', $cycle->id)->whereIn('id', $teamsIds)->get();
            foreach( $teams as $team )
            {
                $team->winner_status = array_key_exists($team->id, $winners) ? $winners[$team->id] : null;
                $team->save();
            }
            DB::commit();
            return redirect()->back()->with('success', $l == 'en' ? 'Saved successfully' : 'تم الحفظ بنجاح');
        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()->back()->with('error', $th->getMessage());
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function likeProject(Request $request)
    {
        $user = auth()->user();
        $teamProjectId = $request->teamProjectId;
        $like = ProjectLikes::where('team_id', $teamProjectId)->where('user_id', $user->id)->first();
        if( !empty( $like ) ) { $like->delete(); }
        else
        {
            $like = new ProjectLikes;
            $like->team_id = $teamProjectId;
            $like->user_id = $user->id;
            $like->save();
        }
        $total = ProjectLikes::where('team_id', $teamProjectId)->count();
        return response()->json([
            'total' => $total
        ]);
    }

    public function sendTeamMessage(Request $request)
    {
        try {
            $request->validate([
                'receiver' => 'required',
                'subject' => 'required',
                'message' => 'required',
            ]);

            $cuser = auth()->user();
            $rec = $request->receiver;
            $rname = User::where('id', $rec)->first();

            if (!$rname) {
                return response()->json(['success' => false, 'message' => 'Receiver not found.'], 404);
            }

            $new = new Messenger();
            $new->sender = $cuser->id;
            $new->receiver = $rec;
            $new->subject = $request->input('subject');
            $new->message = $request->input('message');
            $new->save();

            // Prepare Email
            $rfullname = $rname->first_name_en . " " . $rname->last_name_en;
            $sfullname = $cuser->first_name_en . " " . $cuser->last_name_en;
            // TemplateEmail reads [subject, greeting, body, action path, database text] by position.
            $body = "You have a new message from " . $sfullname . ", Needs your review.";
            $MSG = new TemplateEmail([
                "New message received - VI portal",
                "Dear " . $rfullname . ",",
                $body,
                "/dashboard/inbox",
                $body,
            ]);

            // Send Notification (only in production)
            if (!in_array(env('APP_ENV', 'local'), ['local'])) {
                try {
                    // Assuming TemplateEmail is a Mailable class, you'd use Mail::to()
                    // If it's a notification, your original approach is fine, but ensure it's
                    // properly configured for the Notifiable trait.
                    // For Mailable: Mail::to($rname->email)->send($MSG);
                    $rname->notify($MSG); // If MSG is a Notification class
                } catch (\Throwable $th) {
                    Log::error('Notification Error: ' . $th->getMessage());
                }
            }

            return response()->json(['success' => true, 'message' => 'Message sent successfully']);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage(), 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Error sending message: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }

    public function showSolution($hackathonId, $solutionId)
    {
        $l = app()->getLocale();
        $cycle = Cycle::getHackathon($hackathonId);

        if (empty($cycle)) {
            return abort(404);
        }

        // Check if winners are announced
        if (!$cycle->winners_announcement) {
            return abort(404);
        }

        // Check visibility - if private, require authentication
        if ($cycle->winners_visibility === 'private' && !Auth::check()) {
            return abort(403);
        }

        // Get teams and find the specific solution
        $teams = TeamFormation::teams($hackathonId);
        $team = $teams->where('id', $solutionId)->where('winner', 'selected')->first();

        if (empty($team)) {
            return abort(404);
        }

        // Map to winner format
        $winner = new stdClass;
        $winner->id = $team->id;
        $winner->name = $team->company->name ?? $team->name;
        $winner->logo = $team->img ? asset($team->img) : 'https://placehold.co/400x200?text=' . urlencode($team->name);
        $winner->projectTitle = $team->project->name;
        $winner->teamName = $team->name;
        $winner->description = $team->project->description;
        $leader = $team->members->where('isLeader', true)->first();
        $winner->teamLeader = new stdClass;
        $winner->teamLeader->name = $leader?->name;
        $winner->teamLeader->role = $leader?->role;
        $winner->teamLeader->image = $leader?->image;
        $winner->teamMembers = $team->members->map(function ($team_member) {
            $member = new stdClass;
            $member->name = $team_member->name;
            $member->role = $team_member->role;
            $member->image = $team_member->image;
            $member->isLeader = $team_member->isLeader == 1 ? true : false;
            return $member;
        });
        $winner->likes = $team->likes;

        // Prepare hackathon data
        $hackathon = new stdClass;
        $hackathon->id = $cycle->id;
        $hackathon->title = $cycle->title;
        $hackathon->description = $cycle->description;
        $hackathon->organization = new stdClass;
        $hackathon->organization->name = $cycle->{'organization_name_' . $l};

        return view('public.hackathon.solution', compact('winner', 'hackathon', 'l'));
    }

    public function trackShare(Request $request)
    {
        $request->validate([
            'solution_id' => 'required|integer|exists:team_formations,id',
            'platform' => 'required|in:linkedin,twitter,facebook,copy_link'
        ]);

        ShareEvent::create([
            'team_formation_id' => $request->solution_id,
            'user_id' => Auth::id(),
            'platform' => $request->platform,
            'ip_address' => $request->ip(),
            'user_agent' => Str::limit($request->userAgent(), 255)
        ]);

        return response()->json(['success' => true]);
    }

    public function trackProgramShare(Request $request)
    {
        $request->validate([
            'cycle_id' => 'required|integer|exists:cycles,id',
            'platform' => 'required|in:linkedin,twitter,facebook,copy_link'
        ]);

        ShareEvent::create([
            'cycle_id' => $request->cycle_id,
            'user_id' => Auth::id(),
            'platform' => $request->platform,
            'ip_address' => $request->ip(),
            'user_agent' => Str::limit($request->userAgent(), 255)
        ]);

        return response()->json(['success' => true]);
    }

}
