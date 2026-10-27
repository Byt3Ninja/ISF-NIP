<?php

namespace App\Http\Controllers\Mentor;

use App\Company;
use App\Cycle;
use App\Http\Controllers\Controller;
use App\MentorshipRequest;
use App\ProgramTask;
use App\Services\TimezoneService;
use App\SurveyResponses;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Traits\HasRoles;

class MentorshipController extends Controller
{
    public function index(){
        
        $user = auth()->user();
        $mentorRequests =  MentorshipRequest::all();
        $available_surveys = SurveyResponses::whereIn('user_id', $mentorRequests->pluck('mentor_id'))->whereIn('triggered_at', $mentorRequests->pluck('id'))->where('triggered_by', 'session')->get();

        $sessions = collect([]);
        $cycles = collect([]);
        // GV-9566: zoom_date is platform wall-clock, so bind the platform clock (not now()).
        $platformNow = TimezoneService::platformNow()->format('Y-m-d H:i');
        if(auth()->user() && auth()->user()->hasRole('Mentor')) {
            $sessions = auth()->user()->mentorSessions()->whereDate('zoom_date','<',$platformNow)->where('approved','Yes')->orderBy('zoom_date','DESC')->get();
            $cycles = auth()->user()->mentorCycles()->get();
        }
        if(auth()->user() && auth()->user()->hasRole('Registered')) {
            $company_id = auth()->user()->company_id;
            if(auth()->user()->company) {
                $usersCompany = auth()->user()->company->users()->where('company_id', $company_id)->get();
                $sessions = MentorshipRequest::whereIn('user_id', $usersCompany->pluck('id'))->whereDate('zoom_date','<',$platformNow)->where('approved','Yes')->orderBy('zoom_date','DESC')->get();
                $cycles = collect([auth()->user()->company->cycledata])->filter();
            } else {
                $sessions = collect(); // Empty collection if no company
            }
        }
        
        if(auth()->user() && auth()->user()->hasRole('Admin')) {
            $company_id = Company::all();
            $usersCompany = User::whereIn('company_id', $company_id->pluck('id'))->get();
            $sessions = MentorshipRequest::whereIn('user_id', $usersCompany->pluck('id'))->whereDate('zoom_date','<',$platformNow)->where('approved','Yes')->orderBy('zoom_date','DESC')->get();
            $cycles = Cycle::all();
        }
        //['id' => 1,  'title' => 'Cycle 1',  'completed' => 3,  'pending' => 2],
        // maping cycles with completed and pending sessions count
        // $cycles = $cycles->map(function ($cycle) use ($sessions, $user) {
        //     $completed = ProgramTask::where('program_id', $cycle->id)->where('status', 'completed')->where('status', '!=', 'archived')->where('created_by', $user->id)->count();
        //     $pending = ProgramTask::where('program_id', $cycle->id)->where('status', '!=', 'completed')->where('status', '!=', 'archived')->where('created_by', $user->id)->count();
        //     return [
        //         'id' => $cycle->id,
        //         'title' => $cycle->title,
        //         'completed' => $completed,
        //         'pending' => $pending,
        //     ];
        // });

        $isRegistered = $user->hasRole('Registered');
        $userCompanyId = $isRegistered ? $user->company_id : null;

        $cycles = $cycles->map(function ($cycle) use ($user, $isRegistered, $userCompanyId) {
            if ($isRegistered) {
                $taskIds = ProgramTask::where('program_id', $cycle->id)
                    ->whereHas('assignees', fn($q) => $q->where('companies.id', $userCompanyId))
                    ->pluck('id');

                $countByStatus = fn($status) => DB::table('program_task_assignees')
                    ->whereIn('program_task_id', $taskIds)
                    ->where('company_id', $userCompanyId)
                    ->where('status', $status)
                    ->count();

                $pending    = $countByStatus('pending');
                $inProgress = $countByStatus('progress');
                $inReview   = $countByStatus('review');
                $completed  = $countByStatus('completed');
                $archived   = $countByStatus('archived');
            } else {
                $baseQuery  = fn() => ProgramTask::where('program_id', $cycle->id);
                $pending    = (clone $baseQuery())->where('status', 'pending')->count();
                $inProgress = (clone $baseQuery())->where('status', 'progress')->count();
                $inReview   = (clone $baseQuery())->where('status', 'review')->count();
                $completed  = (clone $baseQuery())->where('status', 'completed')->count();
                $archived   = (clone $baseQuery())->where('status', 'archived')->count();
            }

            return [
                'id'          => $cycle->id,
                'title'       => $cycle->title,
                'pending'     => $pending,
                'in_progress' => $inProgress,
                'in_review'   => $inReview,
                'completed'   => $completed,
                'archived'    => $archived,
            ];
        });

        $tasksData = ProgramTask::with(['program', 'assignees', 'creator', 'feedbacks.user', 'activities.user'])
            ->where('created_by', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        $tasks = $tasksData->map(function ($task) {
            return [
                'id'          => $task->id,
                'title'       => $task->title,
                'description' => $task->description,
                'program'     => [
                    'id'    => $task->program_id,
                    'title' => $task->program->title ?? 'N/A',
                ],
                'assignees' => $task->assignees->map(function ($assignee) {
                    return [
                        'id'          => $assignee->id,
                        'name'        => $assignee->name ?? $assignee->name_en ?? 'N/A',
                        'status'      => $assignee->pivot->status,
                        'lastUpdated' => $assignee->pivot->last_updated
                            ? (is_string($assignee->pivot->last_updated)
                                ? $assignee->pivot->last_updated
                                : $assignee->pivot->last_updated->format('Y-m-d'))
                            : 'N/A',
                        'logo'        => $assignee->logo ?? null,
                        'profile_url' => url('/dashboard/companyProfile/' . $assignee->id),
                    ];
                })->toArray(),
                'in_review_count' => $task->assignees->filter(fn($a) => $a->pivot->status === 'review')->count(),
                'priority'      => $task->priority,
                'overallStatus' => $task->status,
                'dueDate'       => $task->due_date ? $task->due_date->format('Y-m-d') : 'N/A',
                'tags'          => $task->tags ?? [],
                'attachments'   => $task->attachments ?? [],
                'submissions'   => [],
                'creator'       => $task->creator ? [
                    'id'          => $task->creator->id,
                    'name'        => $task->creator->name,
                    'role'        => $task->creator->roles->first()->name ?? 'N/A',
                    'avatar'      => $task->creator->image_url,
                    'profile_url' => url('/dashboard/users/' . $task->creator->id),
                ] : null,
                'feedbacks' => $task->feedbacks->map(function ($fb) {
                    return [
                        'id'       => $fb->id,
                        'user'     => $fb->user->name ?? 'N/A',
                        'user_id'  => $fb->user_id,
                        'img_url'  => $fb->user ? User::image_handler($fb->user->img_url) : null,
                        'decision' => $fb->decision,
                        'text'     => $fb->note,
                        'time'     => $fb->created_at->format('d-m-Y H:i'),
                    ];
                })->toArray(),
                'activities' => $task->activities->map(function ($act) {
                    $action = match ($act->action) {
                        'create'   => 'created this task',
                        'update'   => 'updated ' . $act->column . ($act->was && $act->new ? ' from "' . $act->was . '" to "' . $act->new . '"' : ''),
                        'move'     => 'moved task' . ($act->was && $act->new ? ' from "' . $act->was . '" to "' . $act->new . '"' : ''),
                        'comment'  => 'added a comment',
                        'feedback' => 'submitted feedback',
                        'delete'   => 'deleted this task',
                        default    => $act->action,
                    };
                    return [
                        'user'    => $act->user->name ?? 'N/A',
                        'user_id' => $act->user_id,
                        'img_url' => $act->user ? User::image_handler($act->user->img_url) : null,
                        'action'  => $action,
                        'time'    => $act->created_at->diffForHumans(),
                    ];
                })->toArray(),
                'activities_count' => $task->activities->count(),
                'feedbacks_count'  => $task->feedbacks->count(),
                'createdAt'        => $task->created_at->timestamp,
            ];
        });
    
        $programs = $cycles->map(fn($c) => (object)['id' => $c['id'], 'title' => $c['title']]);

        $cycleIds = $cycles->pluck('id');

        $startups = Company::with('cycledata')
            ->whereHas('cycledata', fn($q) => $q->whereIn('id', $cycleIds))
            ->get()
            ->map(fn($startup) => [
                'id'    => $startup->id,
                'name'  => $startup->name ?? $startup->name_en,
                'cycle' => $startup->cycledata?->id,
                'logo'  => $startup->logo ?? null,
            ]);

        // Calculate real stats
        $stats = [
            'total_tasks' => $cycles->sum(fn($c) => ($c['pending'] ?? 0) + ($c['in_progress'] ?? 0) + ($c['in_review'] ?? 0) + ($c['completed'] ?? 0) + ($c['archived'] ?? 0)),
            'pending'     => $cycles->sum('pending'),
            'active'      => [
                'in_progress_count' => $cycles->sum('in_progress'),
                'in_review_count'   => $cycles->sum('in_review'),
            ],
            'completed' => $cycles->sum('completed'),
            'archived'  => $cycles->sum('archived'),
        ];

        if (auth()->user() && auth()->user()->hasRole('Mentor')) {
            return view('admin.tasks.index',compact('tasks', 'programs', 'startups', 'stats'));
        }
        $programs = [];
        $stats = [
            'total_tasks' => $cycles->sum(fn($c) => ($c['pending'] ?? 0) + ($c['in_progress'] ?? 0) + ($c['in_review'] ?? 0) + ($c['completed'] ?? 0) + ($c['archived'] ?? 0)),
            'pending'     => $cycles->sum('pending'),
            'active'      => [
                'in_progress_count' => $cycles->sum('in_progress'),
                'in_review_count'   => $cycles->sum('in_review'),
            ],
            'completed' => $cycles->sum('completed'),
            'archived'  => $cycles->sum('archived'),
        ];
        return view('mentor.mentorshipSessions.index', compact('sessions', 'programs', 'available_surveys', 'cycles', 'stats'));
    }



    public function meetingRedirect($mentorship_request_id)
    {
       $mentorship_request = MentorshipRequest::findOrFail($mentorship_request_id);
       if ($mentorship_request->mentorshipTasks()->first()){
           $response['message']=__('mentor.mentorshipTasks.Task created before');
           $response['join_url']=$mentorship_request->join_url;
           $response['mentorship_id']=$mentorship_request->id;
           return response()->json($response,200);
       }

       $mentorship_request->mentorshipTasks()->create([
           'mentor_id'=>$mentorship_request->mentor_id,
           'company_id'=>$mentorship_request->user->company->id,
           'user_id'=>$mentorship_request->user_id,
       ]);
        $response['message']=__('mentor.mentorshipTasks.Task created successfully');
        $response['join_url']=$mentorship_request->join_url;
        $response['mentorship_id']=$mentorship_request->id;
        return response()->json($response,200);

    }
}
