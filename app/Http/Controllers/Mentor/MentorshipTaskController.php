<?php

namespace App\Http\Controllers\Mentor;

use App\Cycle;
use App\Http\Controllers\Controller;
use App\Http\Requests\Mentor\MentorshipTask\TaskRequest;
use App\Jobs\SendTaskNotificationJob;
use App\Label;
use App\MentorshipRequest;
use App\MentorshipTask;
use App\ProgramTask;
use App\Company;
use App\MentorshipTaskComment;
use App\MentorshipTaskFile;
use App\MentorshipTaskLog;
use App\Notifications\TaskNotification;
use App\Services\WhatsAppService;
use App\User;
use Carbon\Carbon;
use Exception, Log;
use Illuminate\Support\Facades\URL;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;

class MentorshipTaskController extends Controller
{


	protected $whatsAppService;
	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
	public function __construct(WhatsAppService $whatsAppService)
	{
		$this->middleware('auth');
		$this->whatsAppService = $whatsAppService;
	}
	/**
	 * Display a listing of the resource.
	 */
	public function index(Request $request)
	{
		
		// Get mentorship_id from request or session
		// $mentorshipId = $request->get('mentorship_id') ?? session('last_mentorship_id');

		// if (!$mentorshipId) {
		// 	// Redirect to sessions list if no mentorship_id available
		// 	return redirect()->route('mentorships.sessions_list_detailed');
		// }

		// Store for future use
		// session(['last_mentorship_id' => $mentorshipId]);

		// $lang = app()->getLocale();
		// $condition = "mentorship_tasks.mentorship_request_id = ? ";
		// $tasks = $this->handleTask($lang, $condition, $mentorshipId, 'list');
		// $tasks = $tasks->sortBy('row_no')->values();

		// $available_statuses = ['pending', 'progress', 'review', 'completed'];
		// $session = $this->getSession($lang, $mentorshipId);
		// $session_id = $session['id'];
		// $mentor = $session['mentor'];
		// $users = $session['startups'];
		// $company = $session['company'];
		// $labels = $session['labels'];

		// $_tasks = $tasks->groupBy('status');
		// foreach ($available_statuses as $status) {
		// 	if (!$_tasks->has($status)) {
		// 		$_tasks->put($status, collect([]));
		// 	}
		// }
		// $kanban = [];
		// foreach ($available_statuses as $status) {
		// 	$kanban[] = [
		// 		'id' => $status . '-board',
		// 		'title' => __('admin.boardTitle' . ucfirst($status)),
		// 		'item' => $_tasks->get($status),
		// 	];
		// }

		// $startup = "SELECT MR.id,
		// MR.mentor_id as MT_mentor_id,
		// MR.user_id as MT_user_id,
		// MR.id as MT_mentorship_request_id,
		// C.id as MT_company_id,
		// C.name_en as C_name_en,
		// C.logo as C_logo,
		// C.step as C_step,
		// C.cycle as C_cycle,
		// U.id as U_id,
		// Cy.title as CY_title,
		// Cy.id as CY_id,
		// S.title as S_title,
		// S.id as S_id,
		// MR.zoom_date as MR_zoom_date
		// FROM
		// mentorship_requests as MR
		// LEFT JOIN users as U ON U.id = MR.user_id
		// LEFT JOIN companies as C ON C.id = U.company_id
		// LEFT JOIN steps as S ON S.id = C.step
		// LEFT JOIN cycles as Cy ON Cy.id = C.cycle
		// WHERE MR.id = ? ";
		// $startup = DB::select($startup, [$mentorshipId]);
		// $startup = collect($startup)->first();
		// $data = [];
		// $data['id'] = $startup->id ?? '';
		// $data['company_name'] = $startup->C_name_en ?? '';
		// $data['start_up_logo'] = $startup->C_logo ?? '';
		// $data['program_name'] = $startup->CY_title ?? '';
		// $data['step_name'] = $startup->S_title ?? '';
		// $data['mentor_session_id'] = $startup->MT_mentorship_request_id ?? '';
		// $data['mentor_zoom_date'] = $startup->MR_zoom_date ?? '';
		
		$cycleId = $request->get('cycle_id');
		$cycleModel = Cycle::findOrFail($cycleId);
		$cycle = [
			'id' => $cycleModel->id,
			'title' => $cycleModel->title,
		];
		$lang = app()->getLocale();

		$startups = Company::where('cycle', $cycleId)
			->select('id', 'name_en', 'name_ar', 'logo')
			->get()
			->map(fn($c) => [
				'id'   => $c->id,
				'name' => $c->name_en ?? $c->name_ar ?? 'N/A',
			]);

		$user = auth()->user();
		$tasksQuery = ProgramTask::where('program_id', $cycleId)
			->with(['creator.roles', 'assignees', 'feedbacks.user', 'program',
				'activities' => fn($q) => $q->with('user')->orderBy('created_at', 'desc'),
			]);

		if ($user->hasRole('registered')) {
			$tasksQuery->whereHas('assignees', fn($q) => $q
				->where('companies.id', $user->company_id)
				->where('program_task_assignees.status', '!=', 'archived')
			);
		} else {
			$tasksQuery->where('status', '!=', 'archived');
		}

		$rawTasks = $tasksQuery->get();

		$tasks = $rawTasks->map(function ($task) use ($lang, $user) {
			$creator  = $task->creator;
			$company  = $task->assignees->first(); // kept for reference
			$attachments = collect($task->attachments ?? []);

			return [
				'id'          => $task->id,
				'title'       => $task->title,
				'description' => $task->description,
				'due_date'    => $task->due_date?->format('Y-m-d'),
				'priority'    => $task->priority ?? 'Medium',
				'status'      => $user->hasRole('registered')
					? ($task->assignees->firstWhere('id', $user->company_id)?->pivot->status ?? $task->status)
					: $task->status,
				'created_at'  => $task->created_at,
				'updated_at'  => $task->updated_at,

				'creator' => $creator ? [
					'id'          => $creator->id,
					'name'        => $creator->name,
					'role'        => $creator->roles->first()?->name ?? null,
					'img_url'     => User::image_handler($creator->img_url),
					'profile_url' => '/dashboard/show/UserProfile/' . $creator->id,
				] : null,

				'program' => $task->program ? [
					'id'    => $task->program->id,
					'title' => $task->program->title,
					'url'   => '/dashboard/programlist/' . $task->program->id,
				] : null,

				// 'assignee' => [
				// 	'startup' => $company ? [
				// 		'id'          => $company->id,
				// 		'name'        => $company->{'name_' . $lang} ?? $company->name_en,
				// 		'img_url'     => Company::logo_handler($company->logo),
				// 		'profile_url' => '/dashboard/companyProfile/' . $company->id,
				// 	] : null,
				// 	'founder' => null,
				// ],

				'assignees' => $task->assignees
					->when(
						$user->hasRole('registered'),
						fn($col) => $col->filter(fn($a) => $a->id == $user->company_id)
					)
					->map(fn($a) => [
						'id'          => $a->id,
						'name'        => $a->{'name_' . $lang} ?? $a->name_en ?? 'N/A',
						'img_url'     => Company::logo_handler($a->logo),
						'profile_url' => '/dashboard/companyProfile/' . $a->id,
						'status'      => $a->pivot->status,
					])->toArray(),
				'in_review_count' => $task->assignees->filter(fn($a) => $a->pivot->status === 'review')->count(),
				'attachments' => $attachments->map(fn($a, $i) => [
					'id'          => $i + 1,
					'name'        => $a['name'] ?? basename($a['path'] ?? ''),
					'url'         => $a['url'] ?? null,
					'mime_type'   => null,
					'size_bytes'  => null,
					'uploaded_at' => null,
				])->values()->toArray(),
				'comments'          => $task->comments()->with('user')->get()->map(fn($c) => [
					'id'      => $c->id,
					'user'    => $c->user?->name,
					'user_id' => $c->user_id,
					'img_url' => User::image_handler($c->user?->img_url),
					'text'    => $c->comment,
					'time'    => $c->created_at->diffForHumans(),
				])->toArray(),
				 'feedbacks' => $task->feedbacks->map(function($fb) {
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
				
                'activities' => $task->activities->map(function($act) {
                    $action = match($act->action) {
                        'create' => app()->getLocale() == 'en' ? 'created this task' : 'انشاء هذا المهمة',
                        'update' =>	__('admin.Updated') . ' ' . $act->column . ($act->was && $act->new ? __('admin.From') . ' "' . $act->was . __('admin.To') . ' "' . $act->new . '"' : ''),
                        'move'   => __('admin.Moved') . ' ' . ($act->was && $act->new ? __('admin.From') . ' "' . $act->was . __('admin.To') . ' "' . $act->new . '"' : ''),
                        'comment'  => app()->getLocale() == 'en' ? 'added a comment' : 'أضاف تعليق',
                        'feedback' => app()->getLocale() == 'en' ? 'submitted feedback' : 'أضاف تعليق',
                        'delete'   => app()->getLocale() == 'en' ? 'deleted this task' : 'حذف هذا المهمة',
                        default    => $act->action,
                    };
					return [
                        'user'       => $act->user->name ?? 'N/A',
                        'user_id'    => $act->user_id,
                        'img_url'    => $act->user ? User::image_handler($act->user->img_url) : null,
                        'action'     => $action,
                        'time'       => $act->created_at->diffForHumans(),
                        'created_at' => $act->created_at->format('Y-m-d H:i'),
                    ];
                })->toArray(),
				'submissions' => $task->assignees
					->when(
						$user->hasRole('registered'),
						fn($col) => $col->filter(fn($a) => $a->id == $user->company_id)
					)
					->flatMap(fn($assignee) => collect(
						is_string($assignee->pivot->attachment)
							? json_decode($assignee->pivot->attachment, true) ?? []
							: ($assignee->pivot->attachment ?? [])
					))
					->groupBy(fn($a) => ($a['batch_id'] ?? '') ?: md5($a['path'] ?? $a['name']))
					->map(fn($group) => [
						'status' => 'pending_review',
						'time'   => isset($group->first()['created_at'])
							? \Carbon\Carbon::parse($group->first()['created_at'])->diffForHumans()
							: '',
						'note'  => $group->first()['note'] ?? null,
						'files' => $group->map(fn($a) => [
							'name'       => $a['name'],
							'url'        => $a['url'] ?? null,
							'size_bytes' => $a['size'] ?? null,
							'created_at' => $a['created_at'] ?? null,
						])->values()->toArray(),
					])->values()->toArray(),

				'comments_count'    => $task->comments()->count(),
				'feedbacks_count'   => $task->feedbacks()->count(),
				'activities_count'  => $task->activities->count(),
				'submissions_count' => $task->assignees
					->when(
						$user->hasRole('registered'),
						fn($col) => $col->filter(fn($a) => $a->id == $user->company_id)
					)
					->flatMap(fn($assignee) => collect(
						is_string($assignee->pivot->attachment)
							? json_decode($assignee->pivot->attachment, true) ?? []
							: ($assignee->pivot->attachment ?? [])
					))
					->groupBy(fn($a) => ($a['batch_id'] ?? '') ?: md5($a['path'] ?? $a['name']))
					->count(),
				'attachments_count' => $attachments->where('status', 'attachment')->count(),
			];
		})->values()->toArray();
			
		$__unused = [
			// Task 1: New - Completed Design Task
			[
				'id' => 1,
				'title' => 'Brand Identity Guidelines',
				'description' => '<p>Develop a comprehensive brand identity guideline document.</p><ul><li>Typography & Color Palette</li><li>Logo usage rules</li><li>Tone of voice</li></ul><p>Please deliver as a high-resolution PDF.</p>',
				'due_date' => '2025-03-15',
				'priority' => 'High',
				'status' => 'completed',
				'created_at' => '2025-03-01T09:00:00Z',
				'updated_at' => '2025-03-14T16:45:00Z',

				'creator' => [
					'id' => 1,
					'name' => 'Alice Johnson',
					'img_url' => '/vuexy/assets/img/avatars/1.png',
					'profile_url' => '/dashboard/show/UserProfile/1',
				],

				'assignee' => [
					'startup' => [
						'id' => 10,
						'name' => 'Designa Studio',
						'img_url' => '/vuexy/assets/img/icons/brands/figma.png',
						'profile_url' => '/dashboard/companyProfile/112',
					],
					'founder' => [
						'id' => 2,
						'name' => 'Mark Evans',
						'img_url' => '/vuexy/assets/img/avatars/2.png',
						'profile_url' => '/dashboard/show/UserProfile/2',
					],
				],

				'attachments' => [
					[
						'id' => 101,
						'name' => 'competitor_analysis.pdf',
						'url' => '/storage/designs/competitor_analysis.pdf',
						'mime_type' => 'application/pdf',
						'size_bytes' => 1048576, // 1MB
						'uploaded_at' => '2025-03-01T09:15:00Z',
					],
				],

				'activities' => [
					[
						'user' => 'Alice Johnson',
						'user_id' => 1,
						'img_url' => '/vuexy/assets/img/avatars/1.png',
						'action' => 'created this task',
						'type' => 'created',
						'time' => 'Mar 1, 9:00 AM',
						'created_at' => '2025-03-01T09:00:00Z',
					],
					[
						'user' => 'Alice Johnson',
						'user_id' => 1,
						'img_url' => '/vuexy/assets/img/avatars/1.png',
						'action' => 'marked task as Completed',
						'type' => 'completed',
						'time' => 'Mar 14, 4:45 PM',
						'created_at' => '2025-03-14T16:45:00Z',
					],
				],

				'comments' => [
					[
						'id' => 10,
						'user' => 'Mark Evans',
						'user_id' => 2,
						'img_url' => '/vuexy/assets/img/avatars/2.png',
						'text' => '<p>The typography choices are excellent. Adding the final PDF to my submission.</p>',
						'time' => 'Mar 13, 2:00 PM',
						'created_at' => '2025-03-13T14:00:00Z',
						'updated_at' => null,
					],
				],

				'feedbacks' => [
					[
						'id' => 30,
						'user' => 'Alice Johnson',
						'user_id' => 1,
						'img_url' => '/vuexy/assets/img/avatars/1.png',
						'decision' => 'approved',
						'text' => 'Perfect execution. Client is very happy with the results.',
						'time' => 'Mar 14, 4:30 PM',
						'created_at' => '2025-03-14T16:30:00Z',
					],
				],

				'submissions' => [
					[
						'id' => 20,
						'user' => 'Mark Evans',
						'user_id' => 2,
						'img_url' => '/vuexy/assets/img/avatars/2.png',
						'note' => 'Final Brand Guidelines v1.0',
						'status' => 'approved',
						'time' => 'Mar 13, 5:00 PM',
						'created_at' => '2025-03-13T17:00:00Z',
						'files' => [
							[
								'id' => 300,
								'name' => 'Brand_Guidelines_v1.pdf',
								'url' => '/storage/designs/Brand_Guidelines_v1.pdf',
								'mime_type' => 'application/pdf',
								'size_bytes' => 5242880, // 5MB
							],
						],
					],
				],

				'comments_count' => 1,
				'feedbacks_count' => 1,
				'submissions_count' => 1,
				'attachments_count' => 1,
			],

		];

		$boards = [
			'pending'   => ['label' => __('admin.Pending'), 'color' => 'bg-label-secondary'],
			'progress'  => ['label' => __('admin.In_Progress'), 'color' => 'bg-label-warning'],
			'review'    => ['label' => __('admin.In_Review'), 'color' => 'bg-label-info'],
			'completed' => ['label' => __('admin.Completed'), 'color' => 'bg-label-success'],
		];

		$priorityConfig = [
			'Low'    => ['label' => __('admin.Low'), 'class' => 'bg-label-secondary', 'dot' => 'bg-secondary'],
			'Medium' => ['label' => __('admin.Medium'), 'class' => 'bg-label-info', 'dot' => 'bg-info'],
			'High'   => ['label' => __('admin.High'), 'class' => 'bg-label-danger', 'dot' => 'bg-danger'],
		];

		// Reformat mappings cleanly for JS injections later
		$jsPriorityMap = [];
		foreach($priorityConfig as $k => $v) {
				$jsPriorityMap[$k] = ['label' => $v['label'], 'cls' => $v['class'], 'dot' => $v['dot']];
		}

		$jsStatusMap = [];
		foreach($boards as $k => $v) {
				$jsStatusMap[$k] = $v['label'];
		}

		// Offcanvas Tab Definitions
		$offcanvasTabs = [
			[
				'target' => '#vt-details',
				'icon'   => 'ti-info-circle',
				'label'  => __('admin.Details') ?? 'Details',
				'active' => true,
				'show'   => true,
			],
			[
				'target' => '#vt-submissions',
				'icon'   => 'ti-paperclip',
				'label'  => __('admin.Submissions') ?? 'Submissions',
				'badge'  => ['id' => 'vt-submissions-count', 'class' => 'bg-label-warning'],
				'active' => false,
				'show'=> auth()->user()->hasAnyRole(['admin', 'subadmin', 'organizationManager', 'registered']),
			],
			[
				'target' => '#vt-add-feedback',
				'icon'   => 'ti-message-report',
				'label'  => __('admin.add_feedback'),
				'active' => false,
				'show'   => $user->ableTo('tasks.add_feedback'),
			],
			[
				'target' => '#vt-feedback-history',
				'icon'   => 'ti-history',
				'label'  => __('admin.feedback_history'),
				'badge'  => ['id' => 'vt-feedback-count', 'class' => 'bg-label-primary'],
				'active' => false,
				'show'   => true,
			],
			// [
			// 	'target' => '#vt-comments',
			// 	'icon'   => 'ti-message',
			// 	'label'  => __('admin.Comments'),
			// 	'badge'  => ['id' => 'vt-comment-count', 'class' => 'bg-label-danger bg-glow'],
			// 	'active' => false,
			// 	'show'   => true,
			// ],
			[
				'target' => '#vt-activity',
				'icon'   => 'ti-activity',
				'label'  => __('admin.Activities'),
				'badge'  => ['id' => 'vt-activity-count', 'class' => 'bg-label-info bg-glow'],
				'active' => false,
				'show'   => true,
			],
		];

		$archivedQuery = ProgramTask::with(['program', 'assignees'])
			->where('program_id', $cycleId);

		if ($user->hasRole('registered')) {
			$archivedQuery->whereHas('assignees', fn($q) => $q
				->where('companies.id', $user->company_id)
				->where('program_task_assignees.status', 'archived')
			);
		} else {
			$archivedQuery->where('status', 'archived');
		}

		$archivedTasks = $archivedQuery
			->orderBy('updated_at', 'desc')
			->get()
			->map(function($task) use ($user) {
				$archivedAt = $user->hasRole('registered')
					? ($task->assignees->firstWhere('id', $user->company_id)?->pivot->last_updated ?? $task->updated_at)
					: $task->updated_at;
				return [
					'id'          => $task->id,
					'title'       => $task->title,
					'description' => $task->description,
					'program'     => $task->program->title ?? null,
					'status'      => 'archived',
					'archived_at' => $archivedAt,
				];
			})->values()->toArray();

		return view('kanban.index', compact('cycle', 'tasks', 'boards', 'priorityConfig', 'jsPriorityMap', 'jsStatusMap', 'offcanvasTabs', 'archivedTasks', 'startups'));
	}

	/**
	 * Show the form for creating a new resource.
	 */
	public function create(Request $request)
	{
		$mentorship = MentorshipRequest::findOrFail($request->mentorship_id);
		return view('mentor.mentorshipTasks.create', compact('mentorship'));
	}

	public function addTask(Request $request)
	{
		$user = auth()->user();
		$lang = app()->getLocale();
		$this->validate($request, [
			'mentorship_request_id' => 'required|integer|exists:mentorship_requests,id',
			'title' => 'required|string',
			'description' => 'required',
			'assignee' => 'required|integer|exists:users,id',
			'due_date' => 'sometimes|nullable|date_format:Y-m-d',
			'status' => 'required|in:pending,progress,review,completed',
			'priority' => 'required|in:low,medium,high',
		]);

		$id = $request->mentorship_request_id;
		$assignee = intval($request->assignee);
		$labels = collect($request->input('labels', []));
		$session = $this->getSession($lang, $id);

		$mentor = $session['mentor'];
		$users = $session['startups'];
		$company = $session['company'];
		$stored_labels = $session['labels'];

		if (!in_array($assignee, $users->pluck('id')->toArray())) {
			$output = [
				'message' => 'Assignee not in session',
				'errors' => ['assignee' => 'Assignee not in session']
			];
			return response()->json($output, 422);
		} else {
			$labelsToAdd = [];
			$allowedLabels = [];
			foreach ($labels as $label) {
				if (!array_key_exists('id', $label)) {
					$labelsToAdd[] = ['name' => $label['value'], 'user_id' => $user->id, 'company_id' => $company['id']];
				} else {
					$isLabelExist = $stored_labels->where('id', $label['id'])->first();
					if (!empty($isLabelExist)) {
						$allowedLabels[] = $label['id'];
					}
				}
			}

			$task = new MentorshipTask;
			$task->parent_id = null;
			$task->mentorship_request_id = $id;
			$task->mentor_id = $user->id;
			$task->company_id = $company['id'];
			$task->user_id = $assignee;
			$task->title = $request->title;
			$task->description = $request->description;
			$task->due_date = trim($request->due_date) ? $request->due_date : null;
			$task->done_date = $request->input('done_date', null);
			$task->status = $request->status;
			$task->priority = $request->priority;
			$task->reported_by = $user->id;
			$task->save();

			$mentor = User::find($task->mentor_id);
			$startup = User::find($task->user_id);
			// Send Notification To Mentor & Startup
			$message = "New Task: " . $task->title;
			$data = [
				'task'         => $task,
				'message'      => $message,
				'action'       => 'add',
				'mentor'       => $mentor,
				'startupNames' => [$startup->name ?? ''],
			];

			if ($startup) {
				\Illuminate\Support\Facades\Notification::send([$startup], new TaskNotification($data));
			}
			$_adminOrgId = $mentor?->organization_id ?? $task->company?->cycledata?->organization_id;
			if ($_adminOrgId) {
				$_adminSkip = array_values(array_filter(array_unique([$startup?->id, $mentor?->id])));
				$_admins = User::whereHas('roles', fn($q) => $q->whereIn('default_for', ['admin', 'subadmin', 'organizationManager', 'organizationmanager']))
					->where(fn($q) => $q
						->where('organization_id', $_adminOrgId)
						->orWhereHas('organizations', fn($pq) => $pq->where('organizations.id', $_adminOrgId))
						->orWhereHas('roles', fn($rq) => $rq->where('default_for', 'admin')))
					->when(!empty($_adminSkip), fn($q) => $q->whereNotIn('id', $_adminSkip))
					->get();
				if ($_admins->isNotEmpty()) {
					Notification::send($_admins->all(), new TaskNotification($data));
				}
			}
			// try {
			// 	Notification::send([$mentor, $startup], new TaskNotification($data));
			// } catch (\Throwable $th) {
			// 	//throw $th;
			// }
			
			// if ($task->user->country_code_w_a && $task->user->whatsapp) {
			// 	$phoneNumber = $task->user->country_code_w_a . $task->user->whatsapp;
			// 	$Massage = "Dear " . $task->user->name . ", This Is notification from " . $task->company->cycledata->title . ", the mentor " . $task->mentor->name . " has created a new Task ( " . $task->title ." )";
			// 	$result = $this->whatsAppService->sendMessage($phoneNumber, $Massage);
			// 	Log::info('Message sent to user: ' . $phoneNumber . ' Result: ' . json_encode($result));
			// }
			
			// if ($task->mentor->country_code_w_a && $task->mentor->whatsapp) {
			// 	$phoneNumber = $task->mentor->country_code_w_a . $task->mentor->whatsapp;
			// 	$Massage = "Dear " . $task->mentor->name . ", This Is notification from " . $task->company->cycledata->title . ", the startup " . $task->user->name . " has created a new Task ( " . $task->title ." )";
			// 	$result = $this->whatsAppService->sendMessage($phoneNumber, $Massage);
			// 	\Log::info('Message sent to mentor: ' . $phoneNumber . ' Result: ' . json_encode($result));
			// }


			$task_id = $task->id;
			$stored_labels = $stored_labels->toArray();
			foreach ($labelsToAdd as $label) {
				$createdLabel = Label::create($label);
				$allowedLabels[] = $createdLabel->id;
				$stored_labels[] = ['id' => $createdLabel->id, 'value' => $label['name']];
			}
			$task->labels()->sync($allowedLabels);

			$condition = "mentorship_tasks.mentorship_request_id = ? ";
			$tasks = $this->handleTask($lang, $condition, $id, 'list');
			$tasks = $tasks->sortBy('row_no')->values();
			$available_statuses = ['pending', 'progress', 'review', 'completed'];
			$_tasks = $tasks->groupBy('status');
			foreach ($available_statuses as $status) {
				if (!$_tasks->has($status)) {
					$_tasks->put($status, collect([]));
				}
			}
			$kanban = [];
			foreach ($available_statuses as $status) {
				$kanban[] = [
					'id' => $status . '-board',
					'title' => __('admin.boardTitle' . ucfirst($status)),
					'item' => $_tasks->get($status),
				];
			}

			return response()->json([
				'status' => true,
				'title' => 'Done',
				'message' => 'Task created successfully',
				'data' => [
					'task' => $task,
					'kanban' => $kanban,
					'tasks' => $tasks,
					'labels' => $stored_labels,
				]
			], 200);
		}
	}

	public function moveTask(Request $request)
	{
	
		$user = auth()->user();
		$lang = app()->getLocale();
		$this->validate($request, [
			'mentorship_request_id' => 'required|integer|exists:mentorship_requests,id',
			'moved_task_id' => 'required|integer|exists:mentorship_tasks,id',
			'old_board' => 'required|in:pending-board,progress-board,review-board,completed-board',
			'new_board' => 'required|in:pending-board,progress-board,review-board,completed-board',
		]);

		$task_id = $request->moved_task_id;
		$task = MentorshipTask::find($task_id);
		$status = trim(strtolower(str_replace('-board', '', $request->new_board)));
		$old_status = trim(strtolower(str_replace('-board', '', $request->old_board)));

		$log = new MentorshipTaskLog;
		$log->mentorship_task_id = $task->id;
		$log->user_id = $user->id;
		$log->action = 'update';

		if ($status == $old_status) {
			$old = $task->row_no;
			$log->column = 'row_no';
			$new = array_search($task_id, $request->board_ids) + 1;

			$get_tasks = MentorshipTask::where('mentorship_request_id', $task->mentorship_request_id)->where('status', $status)->orderBy('row_no', 'asc')->get();
			foreach ($get_tasks as $get_task) {
				$row_no = array_search($get_task->id, $request->board_ids) + 1;
				$get_task->row_no = $row_no;
				$get_task->save();
			}
		} else {
			$old = $task->status;
			$task->status = $status;
			$task->done_date = now();
			$task->save();


			$log->column = 'status';
			$new = $status;
		}

		$log->new = $new;
		$log->was = $old;
		$log->save();

		// Send Notification To Mentor & Startup
		try {
			$mentor = User::find($task->mentor_id);
			$startup = User::find($task->user_id);

			$message = "Task Status Updated: " . $task->title;

			// The founder's own action depends on which transition actually
			// happened -- archiving/reopening get their own dedicated emails,
			// any other column move keeps the generic status-update one.
			$founderAction = match(true) {
				$status === 'archived'    => 'archive',
				$old_status === 'archived' => 'reopen',
				default                   => 'move',
			};
			$data = [
				'task'         => $task,
				'message'      => $message,
				'action'       => $founderAction,
				'mentor'       => $mentor,
				'startupNames' => [$startup->name ?? ''],
			];
			if ($startup) {
				Notification::send([$startup], new TaskNotification($data));
			}

			$adminAction = match(true) {
				$status === 'archived'    => 'archive',
				$old_status === 'archived' => 'reopen',
				default                   => null,
			};
			if ($adminAction && $mentor) {
				$_moveAdminOrgId = $mentor?->organization_id ?? $task->company?->cycledata?->organization_id;
				if ($_moveAdminOrgId) {
					$_moveAdminData = array_merge($data, ['action' => $adminAction]);
					$_moveAdminSkip = array_values(array_filter(array_unique([$startup?->id, $mentor?->id])));
					$_moveAdmins = User::whereHas('roles', fn($q) => $q->whereIn('default_for', ['admin', 'subadmin', 'organizationManager', 'organizationmanager']))
						->where(fn($q) => $q
							->where('organization_id', $_moveAdminOrgId)
							->orWhereHas('organizations', fn($pq) => $pq->where('organizations.id', $_moveAdminOrgId))
							->orWhereHas('roles', fn($rq) => $rq->where('default_for', 'admin')))
						->when(!empty($_moveAdminSkip), fn($q) => $q->whereNotIn('id', $_moveAdminSkip))
						->get();
					if ($_moveAdmins->isNotEmpty()) {
						Notification::send($_moveAdmins->all(), new TaskNotification($_moveAdminData));
					}
				}
			}

			if ($task->user->country_code_w_a && $task->user->whatsapp) {
				$phoneNumber = $task->user->country_code_w_a . $task->user->whatsapp;
				$Massage = "Dear " . $task->user->name . ", This Is notification from " . $task->company?->cycledata?->title . " The Task name : " . $task->title . " status has been changed From " . $old . " To " . $new;
				$result = $this->whatsAppService->sendMessage($phoneNumber, $Massage);
				Log::info('Message sent to user: ' . $phoneNumber . ' Result: ' . json_encode($result));
			}

			if ($task->mentor->country_code_w_a && $task->mentor->whatsapp) {
				$phoneNumber = $task->mentor->country_code_w_a . $task->mentor->whatsapp;
				$Massage = "Dear " . $task->mentor->name . ", This Is notification from " . $task->company?->cycledata?->title . " The Task name : " . $task->title . " status has been changed From " . $old . " To " . $new;
				$result = $this->whatsAppService->sendMessage($phoneNumber, $Massage);
				\Log::info('Message sent to mentor: ' . $phoneNumber . ' Result: ' . json_encode($result));
			}
		} catch (\Exception $e) {
			Log::channel('system_exceptions')->info('moveTask notification error:', [$e->getMessage()]);
		}

		$condition = "mentorship_tasks.mentorship_request_id = ? ";
		$tasks = $this->handleTask($lang, $condition, $task->mentorship_request_id, 'list');
		$tasks = $tasks->sortBy('row_no')->values();
		$available_statuses = ['pending', 'progress', 'review', 'completed'];
		$_tasks = $tasks->groupBy('status');
		foreach ($available_statuses as $status) {
			if (!$_tasks->has($status)) {
				$_tasks->put($status, collect([]));
			}
		}
		$kanban = [];
		foreach ($available_statuses as $status) {
			$kanban[] = [
				'id' => $status . '-board',
				'title' => __('admin.boardTitle' . ucfirst($status)),
				'item' => $_tasks->get($status),
			];
		}

		return response()->json([
			'status' => true,
			'title' => 'Done',
			'message' => 'Task moved successfully',
			'data' => ['kanban' => $kanban, 'tasks' => $tasks]
		], 200);
	}

	public function updateTask(Request $request)
	{
		$user = auth()->user();
		$lang = app()->getLocale();
		$this->validate($request, [
			'task_id' => 'required|integer|exists:mentorship_tasks,id',
			'mentorship_request_id' => 'required|integer|exists:mentorship_requests,id',
			'title' => 'required|string',
			'description' => 'required',
			'assignee' => 'required|integer|exists:users,id',
			'due_date' => 'sometimes|nullable|date_format:Y-m-d',
			'status' => 'required|in:pending,progress,review,completed',
			'priority' => 'required|in:low,medium,high',
		]);
		$task = MentorshipTask::where('mentorship_request_id', $request->mentorship_request_id)->find($request->task_id);
		if (empty($task)) {
			$output = [
				'message' => 'Task could not be found',
				'errors' => ['task_id' => 'Task could not be found']
			];
			return response()->json($output, 422);
		}

		$id = $request->mentorship_request_id;
		$assignee = intval($request->assignee);
		$labels = collect($request->input('labels', []));
		$session = $this->getSession($lang, $id);

		$mentor = $session['mentor'];
		$users = $session['startups'];
		$company = $session['company'];
		$stored_labels = $session['labels'];

		if (!in_array($assignee, $users->pluck('id')->toArray())) {
			$output = [
				'message' => 'Assignee not in session',
				'errors' => ['assignee' => 'Assignee not in session']
			];
			return response()->json($output, 422);
		} else {
			$labelsToAdd = [];
			$allowedLabels = [];
			foreach ($labels as $label) {
				if (!array_key_exists('id', $label)) {
					$labelsToAdd[] = ['name' => $label['value'], 'user_id' => $user->id, 'company_id' => $company['id']];
				} else {
					$isLabelExist = $stored_labels->where('id', $label['id'])->first();
					if (!empty($isLabelExist)) {
						$allowedLabels[] = $label['id'];
					}
				}
			}

			$logsToAdd = [];
			$columns = ['user_id', 'title', 'description', 'due_date', 'done_date', 'status', 'priority'];
			foreach ($columns as $column) {
				if ($request->has($column) && $request->$column != $task->$column) {
					$logData = [
						'mentorship_task_id' => $task->id,
						'user_id' => $user->id,
						'action' => 'update',
						'column' => $column,
						'new' => $request->$column,
						'was' => $task->$column,
					];
					$logsToAdd[] = $logData;
				}
			}

			$task->user_id = $assignee;
			$task->title = $request->title;
			$task->description = $request->description;
			if ($request->has('due_date') && trim($request->due_date)) {
				$task->due_date = $request->due_date;
			}
			if ($request->has('done_date') && trim($request->done_date)) {
				$task->done_date = $request->done_date;
			}
			$task->status = $request->status;
			$task->priority = $request->priority;
			$task->save();
			$task_id = $task->id;
			$stored_labels = $stored_labels->toArray();
			foreach ($labelsToAdd as $label) {
				$createdLabel = Label::create($label);
				$allowedLabels[] = $createdLabel->id;
				$stored_labels[] = ['id' => $createdLabel->id, 'value' => $label['name']];
			}
			$task->labels()->sync($allowedLabels);
			MentorshipTaskLog::insert($logsToAdd);

			$condition = "mentorship_tasks.mentorship_request_id = ? ";
			$tasks = $this->handleTask($lang, $condition, $id, 'list');
			$tasks = $tasks->sortBy('row_no')->values();
			$available_statuses = ['pending', 'progress', 'review', 'completed'];
			$_tasks = $tasks->groupBy('status');
			foreach ($available_statuses as $status) {
				if (!$_tasks->has($status)) {
					$_tasks->put($status, collect([]));
				}
			}
			$kanban = [];
			foreach ($available_statuses as $status) {
				$kanban[] = [
					'id' => $status . '-board',
					'title' => __('admin.boardTitle' . ucfirst($status)),
					'item' => $_tasks->get($status),
				];
			}
			$message = "Task Updated: " . $task->title;
			$data = [
				'task'         => $task,
				'message'      => $message,
				'action'       => 'update',
				'mentor'       => $task->mentor,
				'startupNames' => [$task->user->name ?? ''],
			];
			if ($task->user) {
				Notification::send([$task->user], new TaskNotification($data));
			}
			$_updateAdminOrgId = $task->mentor?->organization_id ?? $task->company?->cycledata?->organization_id;
			if ($_updateAdminOrgId) {
				$_updateAdminSkip = array_values(array_filter(array_unique([$task->user?->id, $task->mentor?->id])));
				$_updateAdmins = User::whereHas('roles', fn($q) => $q->whereIn('default_for', ['admin', 'subadmin', 'organizationManager', 'organizationmanager']))
					->where(fn($q) => $q
						->where('organization_id', $_updateAdminOrgId)
						->orWhereHas('organizations', fn($pq) => $pq->where('organizations.id', $_updateAdminOrgId))
						->orWhereHas('roles', fn($rq) => $rq->where('default_for', 'admin')))
					->when(!empty($_updateAdminSkip), fn($q) => $q->whereNotIn('id', $_updateAdminSkip))
					->get();
				if ($_updateAdmins->isNotEmpty()) {
					Notification::send($_updateAdmins->all(), new TaskNotification($data));
				}
			}

			if ($task->user->country_code_w_a && $task->user->whatsapp) {
				$phoneNumber = $task->user->country_code_w_a . $task->user->whatsapp;
				$Massage = "Dear " . $task->user->name . ", This Is notification from " . $task->company->cycledata->title . ", the mentor " . $task->mentor->name . " has Updated the Task Name ( " . $task->title ." )";
				$result = $this->whatsAppService->sendMessage($phoneNumber, $Massage);
				Log::info('Message sent to user: ' . $phoneNumber . ' Result: ' . json_encode($result));
			}
			
			if ($task->mentor->country_code_w_a && $task->mentor->whatsapp) {
				$phoneNumber = $task->mentor->country_code_w_a . $task->mentor->whatsapp;
				$Massage = "Dear " . $task->mentor->name . ", This Is notification from " . $task->company->cycledata->title . ", the startup " . $task->user->name . " has Updated the Task Name ( " . $task->title ." )";
				$result = $this->whatsAppService->sendMessage($phoneNumber, $Massage);
				\Log::info('Message sent to mentor: ' . $phoneNumber . ' Result: ' . json_encode($result));
			}


			return response()->json([
				'status' => true,
				'title' => 'Done',
				'message' => 'Task updated successfully',
				'data' => [
					'task' => $task,
					'kanban' => $kanban,
					'tasks' => $tasks,
					'labels' => $stored_labels,
				]
			], 200);
		}
	}

	public function removeTask(Request $request)
	{
		$user = auth()->user();
		$lang = app()->getLocale();
		$this->validate($request, [
			'task_id' => 'required|integer|exists:mentorship_tasks,id',
			'mentorship_request_id' => 'required|integer|exists:mentorship_requests,id',
		]);
		$id = $request->mentorship_request_id;
		$task = MentorshipTask::where('mentorship_request_id', $id)->find($request->task_id);
		if (empty($task)) {
			$output = [
				'message' => 'Task could not be found',
				'errors' => ['task_id' => 'Task could not be found']
			];
			return response()->json($output, 422);
		}
		$task->delete();
		$condition = "mentorship_tasks.mentorship_request_id = ? ";
		$tasks = $this->handleTask($lang, $condition, $id, 'list');
		$tasks = $tasks->sortBy('row_no')->values();
		$available_statuses = ['pending', 'progress', 'review', 'completed'];
		$_tasks = $tasks->groupBy('status');
		foreach ($available_statuses as $status) {
			if (!$_tasks->has($status)) {
				$_tasks->put($status, collect([]));
			}
		}
		$kanban = [];
		foreach ($available_statuses as $status) {
			$kanban[] = [
				'id' => $status . '-board',
				'title' => __('admin.boardTitle' . ucfirst($status)),
				'item' => $_tasks->get($status),
			];
		}

		return response()->json([
			'status' => true,
			'title' => 'Done',
			'message' => 'Task deleted successfully',
			'data' => [
				'task' => $task,
				'kanban' => $kanban,
				'tasks' => $tasks
			]
		], 200);
	}

	public function addComment(Request $request)
	{
		$user = auth()->user();
		$lang = app()->getLocale();
		$this->validate($request, [
			'task_id' => 'required|integer|exists:mentorship_tasks,id',
			'mentorship_request_id' => 'required|integer|exists:mentorship_requests,id',
			'comment' => 'required',
		]);
		$id = $request->mentorship_request_id;
		$task = MentorshipTask::where('mentorship_request_id', $id)->find($request->task_id);

		if (empty($task)) {
				$output = [
					'message' => 'Task could not be found',
					'errors' => ['task_id' => 'Task could not be found']
				];
				return response()->json($output, 422);
		}

			// Normalize comment text
		$commentText = trim($request->comment);

		// Prevent duplicate comment (same user, same task, same text)
		$exists = MentorshipTaskComment::where('mentorship_task_id', $request->task_id)
			->where('user_id', $user->id)
			->where('comment', $commentText)
			->exists();

		if ($exists) {
			return response()->json([
				'status' => false,
				'title' => 'Duplicate',
				'message' => __('mentor.mentorshipTasks.comment_already_exists'),
			], 409);
		}

		$comment = new MentorshipTaskComment;
		$comment->mentorship_task_id = $request->task_id;
		$comment->user_id = $user->id;
		$comment->comment = $request->comment;
		$comment->attached = null; // keep it null for now
		$comment->edited = false;
		$comment->save();

		$mentor = User::find($task->mentor_id);
		$startup = User::find($task->user_id);

		$message = "New Comment: " . $task->title;
		$data = [
			'task' => $task,
			'message' => $message,
			'action' => 'comment'
		];
		Notification::send([$mentor, $startup], new TaskNotification($data));

		
		if ($task->user->country_code_w_a && $task->user->whatsapp) {
			$phoneNumber = $task->user->country_code_w_a . $task->user->whatsapp;
			$Massage = "Dear " . $task->user->name . ", This Is notification from " . $task->company->cycledata->title . " Comment Added in Task Name " . $task->title . " ( " . $comment->comment ." )";
			$result = $this->whatsAppService->sendMessage($phoneNumber, $Massage);
			Log::info('Message sent to user: ' . $phoneNumber . ' Result: ' . json_encode($result));
		}
		
		if ($task->mentor->country_code_w_a && $task->mentor->whatsapp) {
			$phoneNumber = $task->mentor->country_code_w_a . $task->mentor->whatsapp;
			$Massage = "Dear " . $task->mentor->name . ", This Is notification from " . $task->company->cycledata->title . " Comment Added in Task Name " . $task->title . " ( " . $comment->comment ." )";
			$result = $this->whatsAppService->sendMessage($phoneNumber, $Massage);
			\Log::info('Message sent to mentor: ' . $phoneNumber . ' Result: ' . json_encode($result));
		}

		return response()->json([
			'status' => true,
			'title' => 'Done',
			'message' => 'Comment added successfully',
		], 200);
	}

	public function attach(Request $request)
	{
		$user = auth()->user();
		$request->validate([
			'mentorship_request_id' => 'required|integer|exists:mentorship_requests,id',
			'task_id' => 'required|integer|exists:mentorship_tasks,id',
			'attachments' => 'required|array',
			'attachments.*' => 'required|file|mimes:pdf,docx,xlsx,jpg,jpeg,png|max:10240'
		]);
		$task = MentorshipTask::where('mentorship_request_id', $request->mentorship_request_id)->find($request->task_id);
		if (empty($task)) {
			return response()->json(['status' => false, 'title' => 'Failure', 'message' => 'Task not found'], 200);
		} else {
			$card = (int) $task->id;
			$session = (int) $task->mentorship_request_id;
			$fileManager = app(\App\Services\FileManagerService::class);
			$targetDir  = $session . '/' . $card;

			$filesToAdd = [];
			foreach ($request->attachments as $attachment) {
				$path = $fileManager->upload($attachment, $targetDir, 'mentorship_tasks', 'common');
				$filesToAdd[] = [
					'mentorship_request_id' => $task->mentorship_request_id,
					'mentorship_task_id' => $task->id,
					'user_id' => $user->id,
					'file_name' => $attachment->getClientOriginalName(),
					'file' => $path,
				];
			}
			MentorshipTaskFile::insert($filesToAdd);
			$lang = app()->getLocale();
			$condition = "mentorship_tasks.id = ? ";
			$tasks = $this->handleTask($lang, $condition, $card, 'show');
			$task = $tasks->first();
			return response()->json([
				'status' => true,
				'title' => 'Done',
				'message' => 'Task files uploaded successfully',
				'task' => $task
			]);
		}
	}

	public function deAttach(Request $request)
	{
		$file = MentorshipTaskFile::find($request->file);
		if (empty($file)) {
			return response()->json(['status' => false, 'message' => 'File not found']);
		} else {
			$fileManager = app(\App\Services\FileManagerService::class);
			$fileManager->delete($file->getRawOriginal('file'), 'mentorship_tasks');
			$file->delete();
			return response()->json(['status' => true, 'message' => 'File deleted successfully']);
		}
	}

	/**
	 * Store a newly created resource in storage.
	 */
	public function store(Request $request)
	{
		try {
			$mentorship = MentorshipRequest::find($request->mentorship_request_id);
			$labelNames = $request->label; // أسماء الـ labels المدخلة من الطلب
			if ($labelNames != null) {
				$jsonArray = $labelNames;

				// تحويل النص JSON إلى مصفوفة PHP
				$array = json_decode($jsonArray, true);

				// استخراج القيم داخل الفاليو
				$values = array_map(function ($item) {
					return $item['value'];
				}, $array);


				// تحويل إلى مصفوفة إذا لم يكن كذلك
				if (!is_array($values)) {
					$values = [$values];
				}

				$labelIds = []; // مصفوفة لتخزين معرفات الـ labels

				if ($values) {
					$user = auth()->user();
					foreach ($values as $labelName) {
						// تحقق إذا كانت الـ label موجودة في جدول labels
						$label = Label::where('name', $labelName)->first();

						// إذا لم تكن موجودة، قم بإنشائها في جدول labels
						if (!$label) {
							$label = new Label();
							$label->name = $labelName;
							$label->user_id = $user->id;
							$label->save(); // حفظ الـ label في جدول labels
						}

						// أضف معرف الـ label إلى المصفوفة
						$labelIds[] = $label->id;
					}
				}
			}

			// إنشاء المهمة في جدول mentorship_tasks
			$result = MentorshipTask::create($request->validated() + [
				'mentor_id' => $mentorship->mentor_id,
				'company_id' => $mentorship->user->company->id,
				'user_id' => $mentorship->user_id,
				'title' => $request->title,
				'description' => $request->description,
				'due_date' => $request->due_date,
				'status' => $request->status,
				'attached' => $request->attachments,
			]);

			// ربط المعرفات من جدول label_tasks يدويًا
			if (!empty($labelIds)) {
				foreach ($labelIds as $labelId) {
					// إضافة السجل إلى جدول label_tasks يدويًا
					DB::table('mentorship_task_labels')->insert([
						'mentorship_tasks_id' => $result->id,
						'label_id' => $labelId,
						'created_at' => now(),
						'updated_at' => now(),
					]);
				}
			}
			if ($result) {
				return redirect(route('mentorshipTasks.index', ['mentorship_id' => (int) $mentorship->id]))->with(['message' => 'StoredSuccessfully']);
			} else {
				return back()->withInput()->with(['message2' => 'FailedStore']);
			}
		} catch (Exception $e) {
			// log what happend
			Log::channel('system_exceptions')->info('Exceptions:', [$e]);
			abort(500);
		}
	}

	public function display(string $id)
	{
		$lang = app()->getLocale();
		$condition = "mentorship_tasks.id = ? ";
		$instance = Storage::disk('mentorship_tasks');
		$tasks = $this->handleTask($lang, $condition, $id, 'show');
		if ($tasks == '404') {
			abort(404);
		} else {
			$task = $tasks->first();
			$session = $task['request_id'];
			$card = $task['id'];
			if (!$instance->directoryExists($session)) {
				$instance->makeDirectory("$session");
				$instance->makeDirectory("$session/$card");
			} elseif (!$instance->directoryExists("$session/$card")) {
				$instance->makeDirectory("$session/$card");
			}
			return $task;
		}
	}

	/**
	 * Display the specified resource.
	 */
	public function show(string $id)
	{
		$lang = app()->getLocale();
		$condition = "mentorship_tasks.id = ? ";
		$tasks = $this->handleTask($lang, $condition, $id, 'show');
		if ($tasks == '404') {
			abort(404);
		} else {
			$task = $tasks->first();
			return view('mentor.mentorshipTasks.show', compact('task'));
		}
	}

	/**
	 * Show the form for editing the specified resource.
	 */
	public function edit(string $id)
	{
		$task = MentorshipTask::findOrFail($id);
		return view('mentor.mentorshipTasks.edit', compact('task'));
	}

	/**
	 * Update the specified resource in storage.
	 */
	public function update(TaskRequest $request, string $id)
	{
		try {
			$task = MentorshipTask::findOrFail($id);
			$result = $task->update($request->validated());
			if ($result) {
				return redirect(route('mentorshipTasks.index', ['mentorship_id' => $task->mentorship_request_id]))->with(['message' => 'StoredSuccessfully']);
			} else {
				return back()->withInput()->with(['message2' => 'FailedStore']);
			}
		} catch (Exception $e) {
			// log what happend
			Log::channel('system_exceptions')->info('Exceptions:', [$e]);
			abort(500);
		}
	}

	/**
	 * Remove the specified resource from storage.
	 */
	public function destroy(string $id)
	{
		if (!$task = MentorshipTask::find($id)) {
			return back()->with('class', 'alert alert-danger')->with('message', trans('dash.try_2_access_not_found_content'));
		}
		$mentorship_request_id = $task->mentorship_request_id;
		$task->delete();
		return redirect(route('mentorshipTasks.index', ['mentorship_id' => $mentorship_request_id]))
			->with('class', 'alert alert-success')
			->with('message', trans('dash.deleted_successfully'));
	}

	public function sendComment(Request $request)
	{
		try {
			$task = MentorshipTask::findOrFail($request->mentorship_task_id);
			$result = $task->comments()->create(['user_id' => auth()->id(), 'comment' => $request->comment]);

			if ($result)
				return redirect()->back()->with(['message' => 'StoredSuccessfully']);
			else
				return back()->withInput()->with(['message2' => 'FailedStore']);
		} catch (Exception $e) {
			//log what happend
			Log::channel('system_exceptions')->info('Exceptions:', [$e]);
			abort(500);
		}
	}

	public function updateWith(TaskRequest $request)
	{
		try {
			$mentorship = MentorshipRequest::find($request->mentorship_request_id);
			$mentorshipTask = MentorshipTask::findOrFail($request->MTaskId);

			$labelNames = $request->label; // أسماء الـ labels المدخلة من الطلب
			if ($labelNames != null) {
				$jsonArray = $labelNames;

				// تحويل النص JSON إلى مصفوفة PHP
				$array = json_decode($jsonArray, true);

				// استخراج القيم داخل الفاليو
				$values = array_map(function ($item) {
					return $item['value'];
				}, $array);

				// تحويل إلى مصفوفة إذا لم يكن كذلك
				if (!is_array($values)) {
					$values = [$values];
				}

				$labelIds = []; // مصفوفة لتخزين معرفات الـ labels

				if ($values) {
					$user = auth()->user();

					foreach ($values as $labelName) {
						// تحقق إذا كانت الـ label موجودة في جدول labels
						$label = Label::where('name', $labelName)->first();

						// إذا لم تكن موجودة، قم بإنشائها في جدول labels
						if (!$label) {
							$label = new Label();
							$label->name = $labelName;
							$label->user_id = $user->id;
							$label->save(); // حفظ الـ label في جدول labels
						}

						// أضف معرف الـ label إلى المصفوفة
						$labelIds[] = $label->id;
					}
				}
			}

			// تحديث معلومات المهمة
			$result = $mentorshipTask->update($request->validated() + [
				'mentor_id' => $mentorship->mentor_id,
				'company_id' => $mentorship->user->company->id,
				'user_id' => $mentorship->user_id,
				'title' => $request->title,
				'description' => $request->description,
				'due_date' => $request->due_date,
				'status' => $request->status, // ربط الـ label بالمهمة
			]);
			// بعد التحديث، جلب الكائن المحدث من قاعدة البيانات
			$result = $mentorshipTask->fresh();

			// ربط المعرفات من جدول label_tasks يدويًا
			if (!empty($labelIds)) {
				foreach ($labelIds as $labelId) {
					// إضافة السجل إلى جدول label_tasks يدويًا
					DB::table('mentorship_task_labels')->insert([
						'mentorship_tasks_id' => $result->id,
						'label_id' => $labelId,
						'created_at' => now(),
						'updated_at' => now(),
					]);
				}
			}
			// إضافة تعليق إذا كان موجودًا
			if (!empty($request->comment)) {
				$mentorshipTask->comments()->create([
					'user_id' => auth()->id(),
					'comment' => $request->comment,
					'attached' => $request->attachments, // حفظ المرفقات إذا كانت موجودة
				]);
			}

			// إضافة تعليق إذا كانت المرفقات موجودة فقط
			if (!empty($request->attachments) && empty($request->comment)) {
				$mentorshipTask->comments()->create([
					'user_id' => auth()->id(),
					'comment' => 'Attached', // إذا لم يكن هناك تعليق، يمكن وضع نص ثابت
					'attached' => $request->attachments,
				]);
			}

			if ($result) {
				return redirect(route('mentorshipTasks.index', ['mentorship_id' => $request->mentorship_request_id]))->with(['message' => 'StoredSuccessfully']);
			} else {
				return back()->withInput()->with(['message2' => 'FailedStore']);
			}
		} catch (Exception $e) {
			//log what happend
			Log::channel('system_exceptions')->info('Exceptions:', [$e]);
			abort(500);
		}
	}

	private function getImage($image = null)
	{
		if (!$image) {
			return URL::to('/images/placeholder.jpg');
		}
		return URL::to("/191014/$image");
	}

	private function handleTask($lang, $condition = null, $data = null, $type)
	{
		$query = "SELECT
				mentorship_tasks.id AS id,
				mentorship_tasks.title AS title,
				mentorship_tasks.description AS description,
				mentorship_tasks.due_date AS due_at,
				mentorship_tasks.done_date AS done_at,
				mentorship_tasks.status AS status,
				mentorship_tasks.priority AS priority,
				mentorship_tasks.mentor_id AS mentor_id,
				mentorship_tasks.row_no AS row_no,
				mentorship_tasks.mentorship_request_id AS request_id,
				mentorship_tasks.created_at AS reported_at,
				mentorship_tasks.updated_at AS updated_at,
				CONCAT(TRIM(COALESCE(mentor.first_name_$lang, '')), ' ', TRIM(COALESCE(mentor.last_name_$lang, ''))) AS mentor,
				mentor.img_url AS mentor_img,
				mentorship_tasks.user_id AS `user_id`,
				CONCAT(TRIM(COALESCE(assignee.first_name_$lang, '')), ' ', TRIM(COALESCE(assignee.last_name_$lang, ''))) AS assignee,
				assignee.img_url AS assignee_img,
				mentorship_tasks.reported_by AS reported_by,
				CONCAT(TRIM(COALESCE(reporter.first_name_$lang, '')), ' ', TRIM(COALESCE(reporter.last_name_$lang, ''))) AS reporter,
				reporter.img_url AS reporter_img,
				labels.id AS label_id,
				labels.name AS label_name,
				comments.id AS comment_id,
				comments.comment AS comment,
				comments.user_id AS commenter_id,
				CONCAT(TRIM(COALESCE(commenter.first_name_$lang, '')), ' ', TRIM(COALESCE(commenter.last_name_$lang, ''))) AS commenter,
				commenter.img_url AS commenter_img,
				comments.attached AS attachment,
				comments.edited AS edited,
				comments.created_at AS comment_created_at,
				comments.updated_at AS comment_updated_at,
				comments.deleted_at AS comment_deleted_at,
				logs.id AS log_id,
				logs.user_id AS log_user_id,
				CONCAT(TRIM(COALESCE(user.first_name_$lang, '')), ' ', TRIM(COALESCE(user.last_name_$lang, ''))) AS log_user,
				user.img_url AS log_img,
				logs.action AS log_action,
				logs.column AS log_column,
				logs.was AS log_was,
				logs.new AS log_new,
				logs.created_at AS log_created_at,
				files.id AS `file_id`,
				files.file AS `file`,
				files.file_name AS `file_name`,
				files.user_id AS uploader_id,
				CONCAT(TRIM(COALESCE(uploader.first_name_$lang, '')), ' ', TRIM(COALESCE(uploader.last_name_$lang, ''))) AS uploader,
				uploader.img_url AS uploader_img
			FROM
				mentorship_tasks mentorship_tasks
			LEFT JOIN
				companies company ON mentorship_tasks.company_id = company.id
			LEFT JOIN
				users mentor ON mentorship_tasks.mentor_id = mentor.id
			LEFT JOIN
				users assignee ON mentorship_tasks.user_id = assignee.id
			LEFT JOIN
				users reporter ON mentorship_tasks.reported_by = reporter.id
			LEFT JOIN
				mentorship_task_comments comments ON mentorship_tasks.id = comments.mentorship_task_id
			LEFT JOIN
				users commenter ON comments.user_id = commenter.id
			LEFT JOIN
				mentorship_task_labels task_labels ON mentorship_tasks.id = task_labels.mentorship_tasks_id
			LEFT JOIN
				mentorship_task_files files ON mentorship_tasks.id = files.mentorship_task_id
			LEFT JOIN
				users uploader ON files.user_id = uploader.id
			LEFT JOIN
				labels labels ON task_labels.label_id = labels.id
			LEFT JOIN
				mentorship_task_logs logs ON mentorship_tasks.id = logs.mentorship_task_id
			LEFT JOIN
				users user ON logs.user_id = user.id
			WHERE
				$condition
				AND mentorship_tasks.parent_id IS null";
		$tasks = DB::select($query, [$data]);
		if (empty($tasks) && $type == 'show') {
			return '404';
		} else {
			$tasks = collect($tasks);
			$ids_to_search = [];
			$tasks->where('log_action', 'update')->where('log_column', 'user_id')->each(function ($task) use (&$ids_to_search) {
				$was = intval($task->log_was);
				$new = intval($task->log_new);
				if (!in_array($was, $ids_to_search)) {
					$ids_to_search[] = $was;
				}
				if (!in_array($new, $ids_to_search)) {
					$ids_to_search[] = $new;
				}
			});
			$users = collect([]);
			if (count($ids_to_search)) {
				$placeholders = implode(',', array_fill(0, count($ids_to_search), '?'));
				$sql = "SELECT id, CONCAT(TRIM(COALESCE(first_name_$lang, '')), ' ', TRIM(COALESCE(last_name_$lang, ''))) AS name, img_url FROM users WHERE id IN ($placeholders)";
				$users = collect(DB::select($sql, $ids_to_search))->map(function ($user) {
					$user->img_url = $this->getImage($user->img_url);
					return $user;
				});
			}

			$tasks = $tasks->groupBy('id')->map(function ($groupedTasks) use ($users) {
				$task = $groupedTasks->first();
				$labels = $groupedTasks->filter(function ($task) {
					return $task->label_id !== null;
				})->map(function ($task) {
					return [
						'id' => $task->label_id,
						'name' => $task->label_name,
					];
				})->unique('id')->values()->whereNotNull('id');
				$comments = $groupedTasks->whereNull('deleted_at')->map(function ($task) {
					return [
						'id' => $task->comment_id,
						'comment' => $task->comment,
						'commenter' => ['id' => $task->commenter_id, 'name' => $task->commenter, 'photo' => $this->getImage($task->commenter_img)],
						'attached' => $task->attachment,
						'edited' => $task->edited,
						'created_at' => $task->comment_created_at,
						'updated_at' => $task->comment_updated_at,
						'created_since' => Carbon::parse($task->comment_created_at)->diffForHumans(),
					];
				})->unique('id')->values()->whereNotNull('id');
				$logs = $groupedTasks->sortByDesc('log_created_at')->map(function ($log) use ($users) {
					$footprint = '';
					if ($log->log_action == 'create') {
						$footprint = __('dashboard.logTaskCreated', ['url' => URL::to('dashboard/users/' . $log->log_user_id . '/edit'), 'user' => $log->log_user]);
					} elseif ($log->log_action == 'update') {
						if ($log->log_column != 'user_id') {
							if ($log->log_column == 'status') {
								$old = __('admin.boardTitle' . ucfirst(strtolower($log->log_was)));
								$new = __('admin.boardTitle' . ucfirst(strtolower($log->log_new)));
							} elseif ($log->log_column == 'priority') {
								$old = __('admin.priority' . ucfirst(strtolower($log->log_was)));
								$new = __('admin.priority' . ucfirst(strtolower($log->log_new)));
							} else {
								$old = $log->log_was;
								$new = $log->log_new;
							}
							$changes = [
								'old' => $old,
								'new' => $new,
								'user' => $log->log_user,
								'url' => URL::to('dashboard/users/' . $log->log_user_id . '/edit'),
							];
							$footprint = __('dashboard.logTaskUpdate' . ucfirst($log->log_column), $changes);
						} else {
							$was_id = $log->log_was;
							$new_id = $log->log_new;
							$old = $users->where('id', $was_id)->first();
							$new = $users->where('id', $new_id)->first();
							$changes = [
								'user' => $log->log_user,
								'url' => URL::to('dashboard/users/' . $log->log_user_id . '/edit'),
							];
							if (!empty($was_id) && !empty($old) && !empty($new_id) && !empty($new)) {
								$changes['old_user'] = $old->name;
								$changes['old_url'] = URL::to('dashboard/users/' . $old->id . '/edit');
								$changes['new_user'] = $new->name;
								$changes['new_url'] = URL::to('dashboard/users/' . $new->id . '/edit');

								$footprint = __('dashboard.logTaskUpdateUserChange', $changes);
							} elseif (!empty($was_id) && !empty($old) && empty($new_id) && empty($new)) {
								$changes['old_user'] = $old->name;
								$changes['old_url'] = URL::to('dashboard/users/' . $old->id . '/edit');

								$footprint = __('dashboard.logTaskUpdateUserRemove', $changes);
							} elseif (empty($was_id) && empty($old) && !empty($new_id) && !empty($new)) {
								$changes['new_user'] = $new->name;
								$changes['new_url'] = URL::to('dashboard/users/' . $new->id . '/edit');

								$footprint = __('dashboard.logTaskUpdateUserAdd', $changes);
							}
						}
					} elseif ($log->log_action == 'delete') {
						//
					}
					return [
						'id' => $log->log_id,
						'user' => ['id' => $log->log_user_id, 'name' => $log->log_user, 'photo' => $this->getImage($log->log_img)],
						'action' => $log->log_action,
						'column' => $log->log_column,
						'was' => $log->log_was,
						'new' => $log->log_new,
						'created_at' => $log->log_created_at,
						'created_since' => Carbon::parse($log->log_created_at)->diffForHumans(),
						'footprint' => $footprint
					];
				})->unique('id')->values()->whereNotNull('id');
				$files = $groupedTasks->filter(function ($file) {
					return $file->file !== null;
				})->map(function ($task) {
					return [
						'id' => $task->file_id,
						'name' => $task->file_name,
						'url' => URL::to('dashboard/uploads/tasks/' . $task->request_id . '/cards/' . $task->id . '/' . $task->file),
						'uploader' => ['id' => $task->uploader_id, 'name' => $task->uploader, 'photo' => $this->getImage($task->uploader_img)],
					];
				})->unique('id')->values()->whereNotNull('id');
				return [
					'id' => $task->id,
					'request_id' => $task->request_id,
					'name' => $task->title,
					'content' => $task->description,
					'due_date' => !empty($task->due_at) && trim($task->due_at) ? trim($task->due_at) : null,
					'done_date' => !empty($task->done_at) && trim($task->done_at) ? trim($task->done_at) : null,
					'status' => $task->status,
					'row_no' => $task->row_no,
					'visible_status' => __('admin.boardTitle' . ucfirst($task->status)),
					'priority' => $task->priority,
					'priority_visible' => __('admin.priority' . ucfirst($task->priority)),
					'mentor' => ['id' => $task->mentor_id, 'name' => $task->mentor, 'photo' => $this->getImage($task->mentor_img)],
					'assignee' => ['id' => $task->user_id, 'name' => $task->assignee, 'photo' => $this->getImage($task->assignee_img)],
					'reporter' => ['id' => $task->reported_by, 'name' => $task->reporter, 'photo' => $this->getImage($task->reporter_img)],
					'reported_at' => $task->reported_at,
					'updated_at' => $task->updated_at,
					'labels' => $labels,
					'comments' => $comments,
					'attachments' => $files->count(),
					'logs' => $logs,
					'files' => $files,
				];
			})->values();

			return $tasks;
		}
	}

	private function getSession($lang, $id)
	{
		$sessions = "SELECT
				mr.id,
				m.id AS mentor_id,
				CONCAT(TRIM(COALESCE(m.first_name_$lang, '')), ' ', TRIM(COALESCE(m.last_name_$lang, ''))) AS mentor,
				c.id AS company_id,
				TRIM(COALESCE(c.name_$lang, '')) AS company,
				u.id AS startup_id,
				CONCAT(TRIM(COALESCE(u.first_name_$lang, '')), ' ', TRIM(COALESCE(u.last_name_$lang, ''))) AS startup,
				l.id AS label_id,
				l.name AS label
			FROM
				mentorship_requests mr
			LEFT JOIN
				users m ON mr.mentor_id = m.id
			LEFT JOIN
				users s ON mr.user_id = s.id
			LEFT JOIN
				companies c ON s.company_id = c.id
			LEFT JOIN
				users u ON c.id = u.company_id
			LEFT JOIN
				labels l ON l.company_id = c.id
			WHERE
				mr.id = ?
		";
		$sessions = collect(DB::select($sessions, [$id]));
		$sessions = $sessions->groupBy('id')->map(function ($groupedSession) {
			$session = $groupedSession->first();
			return [
				'id' => $session->id,
				'mentor' => ['id' => $session->mentor_id, 'name' => $session->mentor],
				'company' => ['id' => $session->company_id, 'name' => $session->company],
				'startups' => $groupedSession->map(function ($item) {
					return ['id' => $item->startup_id, 'value' => $item->startup];
				})->unique('id')->values()->whereNotNull('id'),
				'labels' => $groupedSession->map(function ($item) {
					return ['id' => $item->label_id, 'value' => $item->label];
				})->unique('id')->values()->whereNotNull('id'),
			];
		})->values()->first();
		return $sessions;
	}

	public function attachments($id, $card_id, $path)
	{
		try {
			// Storage::disk('mentorship_tasks')->makeDirectory("$result->id");
			// Storage::disk('mentorship_tasks')->makeDirectory("$result->id/BusinessPlans");
			$instance = Storage::disk('mentorship_tasks');
			$fullPath = $id . '/' . $card_id . '/' . $path;
			if ($instance->directoryExists($fullPath)) {
				return response()->view('errors.404')->setStatusCode(404);
			}
			$file = $instance->response($fullPath);
			$mimeType = $instance->mimeType($fullPath);
			if ($mimeType == 'image/svg') {
				$mimeType = 'image/svg+xml';
			}
			$file->headers->set('Content-Type', $mimeType);
			return $file;
		} catch (\Throwable $th) {
			return response()->view('errors.404')->setStatusCode(404);
		}
	}
}
