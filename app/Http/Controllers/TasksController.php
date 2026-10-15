<?php

namespace App\Http\Controllers;

use App\Company;
use App\Cycle;
use App\MentorshipTask;
use App\ProgramTask;
use App\ProgramTaskActivity;
use App\ProgramTaskAssignee;
use App\ProgramTaskComment;
use App\Tags;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class TasksController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = auth()->user();
        $orgIds = $user->organizations_ids;

        $programs = Cycle::select('id', 'title')
        ->when(!$user->hasRole('admin') && !empty($orgIds), function ($q) use ($orgIds) {
            $q->whereIn('organization_id', $orgIds);
        })
        ->get();

        $startups = Company::with('cycledata')
            ->whereHas('cycledata', function ($q) use ($user, $orgIds) {
                $q->when(!$user->hasRole('admin') && !empty($orgIds), function ($q) use ($orgIds) {
                    $q->whereIn('organization_id', $orgIds);
                });
            })
            ->get()
            ->map(function($startup) {
                return [
                    'id' => $startup->id,
                    'name' => $startup->name ?? $startup->name_en,
                    'name_en' => $startup->name_en,
                    'cycle' => $startup->cycledata ? $startup->cycledata->id : null,
                    'logo' => $startup->logo ?? null,
                ];
            });

        $tags = Tags::select('id', 'name')->orderBy("id", "desc")->get();

        // Fetch real tasks from database
        $isRegistered  = $user->hasRole('registered');
        $userCompanyId = $user->company_id;

        $tasksData = ProgramTask::with(['program', 'assignees', 'creator', 'feedbacks.user', 'activities.user'])
            ->when(!$user->hasRole('admin') && !empty($orgIds), function ($q) use ($orgIds) {
                $q->whereHas('program', function($pq) use ($orgIds) {
                    $pq->whereIn('organization_id', $orgIds);
                });
            })
            ->when($isRegistered && $userCompanyId, function ($q) use ($userCompanyId) {
                $q->whereHas('assignees', fn($q) => $q->where('companies.id', $userCompanyId));
            })
            ->orderBy('created_at', 'desc')
            ->get();
        $tasks = $tasksData->map(function($task) use ($isRegistered, $userCompanyId) {
            return [
                'id' => $task->id,
                'title' => $task->title,
                'description' => $task->description,
                'program' => [
                    'id' => $task->program_id,
                    'title' => $task->program->title ?? 'N/A',
                ],
                'assignees' => $task->assignees
                    ->when($isRegistered && $userCompanyId, fn($c) => $c->filter(fn($a) => $a->id == $userCompanyId))
                    ->map(function($assignee) {
                        return [
                            'id' => $assignee->id,
                            'name' => $assignee->name ?? $assignee->name_en ?? 'N/A',
                            'status' => $assignee->pivot->status,
                            'lastUpdated' => $assignee->pivot->last_updated
                                ? (is_string($assignee->pivot->last_updated)
                                    ? $assignee->pivot->last_updated
                                    : $assignee->pivot->last_updated->format('Y-m-d'))
                                : 'N/A',
                            'logo' => $assignee->logo ?? null,
                            'profile_url' => url('/dashboard/companyProfile/' . $assignee->id),
                        ];
                    })->values()->toArray(),
                'inReviewCount' => $task->assignees->filter(fn($a) => $a->pivot->status === 'review')->count(),
                'priority' => $task->priority,
                'overallStatus' => $task->status,
                'dueDate' => $task->due_date ? $task->due_date->format('Y-m-d') : 'N/A',
                'tags' => $task->tags ?? [],
                'attachments' => $task->attachments ?? [],
                'submissions' => $task->assignees
                    ->when($isRegistered && $userCompanyId, fn($c) => $c->filter(fn($a) => $a->id == $userCompanyId))
                    ->flatMap(fn($assignee) => collect(
                        is_string($assignee->pivot->attachment)
                            ? json_decode($assignee->pivot->attachment, true) ?? []
                            : ($assignee->pivot->attachment ?? [])
                    ))
                    ->groupBy(fn($a) => $a['batch_id'] ?? md5($a['path'] ?? $a['name']))
                    ->map(fn($group) => [
                        'user' => null, 'img_url' => null, 'user_id' => null,
                        'status' => 'pending_review', 'time' => isset($group->first()['created_at'])
                            ? \Carbon\Carbon::parse($group->first()['created_at'])->diffForHumans()
                            : '',
                        'note' => $group->first()['note'] ?? null,
                        'files' => $group->map(fn($a) => [
                            'name'       => $a['name'],
                            'url'        => app(\App\Services\FileManagerService::class)->getUrl($a['path'], 'public'),
                            'size_bytes' => $a['size'] ?? null,
                        ])->values()->toArray(),
                    ])->values()->toArray(),
                'creator' => $task->creator ? [
                    'id' => $task->creator->id,
                    'name' => $task->creator->name,
                    'role' => $task->creator->roles->first()->name ?? 'N/A',
                    'avatar' => $task->creator->image_url,
                    'profile_url' => url('/dashboard/show/UserProfile/' . $task->creator->id),
                ] : null,
                'feedbacks' => $task->feedbacks
                    ->when($isRegistered && $userCompanyId, fn($c) => $c->filter(fn($fb) => $fb->company_id == $userCompanyId))
                    ->map(function($fb) {
                        return [
                            'id'         => $fb->id,
                            'user'       => $fb->user->name ?? 'N/A',
                            'user_id'    => $fb->user_id,
                            'company_id' => $fb->company_id,
                            'img_url'    => $fb->user ? User::image_handler($fb->user->img_url) : null,
                            'decision'   => $fb->decision,
                            'text'       => $fb->note,
                            'time'       => $fb->created_at->format('d-m-Y H:i'),
                        ];
                    })->values()->toArray(),
                'activities' => $task->activities
                    ->when($isRegistered && $userCompanyId, fn($c) => $c->filter(fn($act) => $act->company_id == $userCompanyId))
                    ->sortByDesc('created_at')->map(function($act) {
                    $action = match($act->action) {
                        'create' => 'created this task',
                        'update' => 'updated ' . $act->column . ($act->was && $act->new ? ' from "' . $act->was . '" to "' . $act->new . '"' : ''),
                        'move'   => 'moved task' . ($act->was && $act->new ? ' from "' . $act->was . '" to "' . $act->new . '"' : ''),
                        'comment'    => 'added a comment',
                        'feedback'   => 'submitted feedback',
                        'submission' => 'submitted a file',
                        'delete'     => 'deleted this task',
                        default      => $act->action,
                    };
                    return [
                        'user'       => $act->user->name ?? 'N/A',
                        'user_id'    => $act->user_id,
                        'img_url'    => $act->user ? User::image_handler($act->user->img_url) : null,
                        'action'     => $action,
                        'time'       => $act->created_at->diffForHumans(),
                        'created_at' => $act->created_at->format('Y-m-d H:i'),
                    ];
                })->values()->toArray(),
                'in_review_count'  => $task->assignees->filter(fn($a) => $a->pivot->status === 'review')->count(),
                'activities_count' => $task->activities->count(),
                'feedbacks_count'  => $task->feedbacks->count(),
                'createdAt'        => $task->created_at->timestamp,
            ];
        });
        
        $archivedTasks = $tasksData->where('status', 'archived');
        $taskIds = $tasksData->pluck('id');
        // Calculate real stats
        $stats = [
            'total_tasks' => $tasksData->count(),
            'pending' => $tasksData->where('status', 'pending')->count(),
            'active' => [
                'in_progress_count' => $tasksData->where('status', 'progress')->count(),
                'in_review_count' => ProgramTaskAssignee::whereIn('program_task_id', $taskIds)->where('status', 'review')->count(),
            ],
            'completed' => $tasksData->where('status', 'completed')->count(),
            'archived' => $tasksData->where('status', 'archived')->count(),
        ];

        
        return view("admin.tasks.index", compact("programs", "startups", "tags", "stats", "tasks", "archivedTasks"));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validation rules
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:200',
            'description' => 'nullable|string|max:2000',
            'program_id' => 'required|exists:cycles,id',
            'assignees' => 'nullable|array',
            'assignees.*' => 'exists:companies,id',
            'priority' => 'required|in:Low,Medium,High',
            'status' => 'required|in:pending,progress,review,completed,archived',
            'tags' => 'nullable|array',
            'attachments' => 'nullable|array|max:10',
            'attachments.*' => 'file|max:10240|mimes:jpeg,jpg,png,pdf,docx,xlsx,pptx',
        ]);
     
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors()
            ], 422);
        }

        // Prevent duplicate submissions using a cache lock
        $lockKey = 'task_store_' . auth()->id() . '_' . md5($request->input('title') . $request->input('program_id'));
        if (!Cache::lock($lockKey, 5)->get()) {
            return response()->json([
                'success' => false,
                'message' => app()->getLocale() == 'ar'
                    ? 'يتم معالجة طلبك بالفعل، يرجى الانتظار.'
                    : 'Your request is already being processed, please wait.'
            ], 429);
        }

        try {
            // Handle file attachments
            /** @var \App\User $uploader */
            $uploader = auth()->user();
            $attachments = [];
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    // Validate file size
                    if ($file->getSize() > 10485760) { // 10 MB in bytes
                        return response()->json([
                            'success' => false,
                            'message' => app()->getLocale() == 'ar' 
                                ? 'يتجاوز حجم المرفق الحد المسموح به (10 ميجابايت)' 
                                : 'Attachment size exceeds the allowed limit (10 MB).'
                        ], 422);
                    }

                    // Store the file
                    $path = app(\App\Services\FileManagerService::class)->upload($file, 'tasks/attachments', 'public', 'attachment');
                    $attachments[] = [
                        'name'       => $file->getClientOriginalName(),
                        'path'       => $path,
                        'size'       => $file->getSize(),
                        'type'       => $file->getMimeType(),
                        'status'     => $uploader->hasRole('registered') ? 'submission' : 'attachment',
                        'company_id' => $uploader->company_id,
                        'user_id'    => $uploader->id,
                        'created_at' => now()->toDateTimeString(),
                    ];
                }
            }


            // Process tags - create new ones if they don't exist
            $processedTags = [];
            if ($request->has('tags') && is_array($request->tags)) {
                foreach ($request->tags as $tagData) {
                    // Handle escaped JSON strings and nested objects
                    $tagName = $tagData;
                    
                    // If it's a JSON string, decode it first
                    if (is_string($tagData) && strpos($tagData, '{') !== false) {
                        $decoded = json_decode($tagData, true);
                        if (isset($decoded['value'])) {
                            $tagName = $decoded['value'];
                        }
                    }
                    
                    // Clean up escaped quotes and extra characters
                    $tagName = stripslashes($tagName);
                    $tagName = trim($tagName, '"');
                    $tagName = trim($tagName);
                    
                    if (!empty($tagName)) {
                        // Check if tag exists, if not create it
                        $tag = Tags::firstOrCreate(['name' => $tagName]);
                        $processedTags[] = $tagName;
                    }
                }
            }

            // Create the task
            $task = ProgramTask::create([
                'title' => $request->title,
                'description' => $request->description,
                'program_id' => $request->program_id,
                'priority' => $request->priority,
                'status' => $request->status,
                'due_date' => $request->due_date ?: null,
                'tags' => $processedTags,
                'attachments' => $attachments,
                'created_by' => auth()->id(),
            ]);

            // Attach assignees (companies/startups) - use sync to prevent duplicates
            $assigneeData = [];
            foreach ($request->assignees ?? [] as $companyId) {
                $assigneeData[$companyId] = [
                    'status' => 'pending',
                    'last_updated' => now(),
                ];
            }
            $task->assignees()->sync($assigneeData);

            ProgramTaskAssignee::where('program_task_id', $task->id)
                ->update(['status' => $request->status, 'last_updated' => now()]);

            $assignees_company = $task->assignees()->pluck('company_id')->toArray();
            $activity = [];
            foreach ($assignees_company as $cId) {
                $activity[] = [
                    'program_task_id' => $task->id,
                    'user_id'         => auth()->id(),
                    'action'          => 'create',
                    'company_id'      => $cId,
                ];
                }
                ProgramTaskActivity::insert($activity);

            $assignees = User::whereIn('company_id', $assignees_company)->get();
            $mentor = User::find($task->created_by);
            $startupNames = $task->assignees->pluck('name')->filter()->values()->toArray();

            $message = "New Task: " . $task->title;
            $data = [
                'task'         => $task,
                'message'      => $message,
                'action'       => 'add',
                'mentor'       => $mentor,
                'startupNames' => $startupNames,
                'program_name'      => $task->program ? $task->program->title : null,
            ];
            $notifiedIds = [];
            foreach ($assignees as $startup) {
                \Illuminate\Support\Facades\Notification::send([$startup], new \App\Notifications\TaskNotification($data));
                $notifiedIds[] = $startup->id;
            }
            // if ($mentor && !in_array($mentor->id, $notifiedIds)) {
            //     \Illuminate\Support\Facades\Notification::send([$mentor], new \App\Notifications\TaskNotification($data));
            //     $notifiedIds[] = $mentor->id;
            // }
            // if ($mentor) {
            //     $_storeAdminOrgId = $mentor->organization_id ?? $task->program?->organization_id;
            //     if ($_storeAdminOrgId) {
            //         $_storeAdminSkip = array_values(array_filter(array_unique(array_merge($notifiedIds, [$mentor->id]))));
            //         $_storeAdmins = User::whereHas('roles', fn($q) => $q->whereIn('default_for', ['admin', 'subadmin', 'organizationManager', 'organizationmanager']))
            //             ->where(fn($q) => $q
            //                 ->where('organization_id', $_storeAdminOrgId)
            //                 ->orWhereHas('organizations', fn($pq) => $pq->where('organizations.id', $_storeAdminOrgId))
            //                 ->orWhereHas('roles', fn($rq) => $rq->where('default_for', 'admin')))
            //             ->when(!empty($_storeAdminSkip), fn($q) => $q->whereNotIn('id', $_storeAdminSkip))
            //             ->where(fn($q) => $q->whereNull('company_id')->orWhereNotIn('company_id', $assignees_company))
            //             ->get();
            //         if ($_storeAdmins->isNotEmpty()) {
            //             \Illuminate\Support\Facades\Notification::send($_storeAdmins->all(), new \App\Notifications\TaskNotification($data));
            //         }
            //     }
            // }

            return response()->json([
                'success' => true,
                'message' => app()->getLocale() == 'ar'
                    ? 'تم إنشاء المهمة بنجاح'
                    : 'Task created successfully.',
                'data' => $task->load('program', 'assignees', 'creator.roles')
            ], 201);

        } catch (\Exception $e) {
            \Log::error('Error creating task: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => app()->getLocale() == 'ar'
                    ? 'حدث خطأ أثناء إنشاء المهمة'
                    : 'An error occurred while creating the task.'
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $task = ProgramTask::with([
            'program', 'creator.roles',
            'assignees',
            'comments.user', 'feedbacks.user', 'activities.user'
        ])->findOrFail($id);

        $allAtt  = $task->attachments ?? [];

        $fmtAtt = collect($allAtt)
            ->filter(fn($a) => ($a['status'] ?? 'attachment') !== 'submission')
            ->map(fn($a) => [
                'id'   => md5($a['path']),
                'name' => $a['name'],
                'url'  => app(\App\Services\FileManagerService::class)->getUrl($a['path'], 'public'),
                'size' => $a['size'] ?? null,
                'created_at' => $a['created_at'] ?? '',
            ])->values();

        /** @var \App\User $showUser */
        $showUser        = auth()->user();
        $filterCompanyId = $showUser->hasRole('registered') ? $showUser->company_id : null;

        $assigneeRows = ProgramTaskAssignee::where('program_task_id', $task->id)
            ->when($filterCompanyId, fn($q) => $q->where('company_id', $filterCompanyId))
            ->get();

        $fmtSubs = $assigneeRows
            ->flatMap(fn($a) => collect($a->attachment ?? []))
            ->groupBy(fn($a) => $a['batch_id'] ?? md5($a['path'] ?? $a['name']))
            ->map(fn($group) => [
                'user' => null, 'img_url' => null, 'user_id' => null,
                'status' => 'pending_review', 'time' => isset($group->first()['created_at'])
                    ? \Carbon\Carbon::parse($group->first()['created_at'])->diffForHumans()
                    : '',
                'note' => $group->first()['note'] ?? null,
                'files' => $group->map(fn($a) => [
                    'name'       => $a['name'],
                    'url'        => app(\App\Services\FileManagerService::class)->getUrl($a['path'], 'public'),
                    'size_bytes' => $a['size'] ?? null,
                ])->values()->toArray(),
            ])->values();

        $assignees = $task->assignees
            ->when($filterCompanyId, fn($c) => $c->filter(fn($a) => $a->id == $filterCompanyId))
            ->map(function ($company) {
                $founder = $company->resolveUser();
                return [
                    'id'          => $company->id,
                    'name'        => $company->name ?? $company->name_en,
                    'logo'        => $company->logo ? (str_starts_with($company->logo, 'http') ? $company->logo : asset('storage/' . $company->logo)) : null,
                    'profile_url' => url('/dashboard/companyProfile/' . $company->id),
                    'status'      => $company->pivot->status,
                    'founder'     => $founder ? [
                        'id'          => $founder->id,
                        'name'        => $founder->name,
                        'avatar'      => User::image_handler($founder->img_url),
                        'profile_url' => url('/dashboard/show/UserProfile/' . $founder->id),
                    ] : null,
                ];
            });

        $creator = $task->creator ? [
            'id'          => $task->creator->id,
            'name'        => $task->creator->name,
            'role'        => $task->creator->roles->first()->name ?? 'N/A',
            'avatar'      => User::image_handler($task->creator->img_url),
            'profile_url' => url('/dashboard/show/UserProfile/' . $task->creator->id),
        ] : null;

        $comments = $task->comments->map(fn($c) => [
            'user'    => $c->user->name ?? 'N/A',
            'user_id' => $c->user_id,
            'img_url' => $c->user ? User::image_handler($c->user->img_url) : null,
            'text'    => $c->comment,
            'time'    => $c->created_at->diffForHumans(),
        ]);

        $feedbacks = $task->feedbacks
            ->when($filterCompanyId, fn($c) => $c->filter(fn($fb) => $fb->company_id == $filterCompanyId || is_null($fb->company_id)))
            ->sortByDesc('created_at')->values()->map(fn($fb) => [
            'id'         => $fb->id,
            'user'       => $fb->user->name ?? 'N/A',
            'user_id'    => $fb->user_id,
            'company_id' => $fb->company_id,
            'img_url'    => $fb->user ? User::image_handler($fb->user->img_url) : null,
            'decision'   => $fb->decision,
            'text'       => $fb->note,
            'time'       => $fb->created_at->format('d-m-Y H:i'),
        ]);

        $activities = $task->activities
            ->when($filterCompanyId, fn($c) => $c->filter(fn($act) => $act->company_id == $filterCompanyId || is_null($act->company_id)))
            ->sortByDesc('created_at')->map(function ($act) {
            $action = match($act->action) {
                'create'   => 'created this task',
                'update'   => 'updated ' . $act->column . ($act->was && $act->new ? ' from "' . $act->was . '" to "' . $act->new . '"' : ''),
                'move'     => 'moved task' . ($act->was && $act->new ? ' from "' . $act->was . '" to "' . $act->new . '"' : ''),
                'comment'  => 'added a comment',
                'feedback'   => 'submitted feedback',
                'submission' => 'submitted a file',
                'delete'     => 'deleted this task',
                default      => $act->action,
            };
            return [
                'user'       => $act->user->name ?? 'N/A',
                'user_id'    => $act->user_id,
                'img_url'    => $act->user ? User::image_handler($act->user->img_url) : null,
                'action'     => $action,
                'time'       => $act->created_at->diffForHumans(),
                'created_at' => $act->created_at->format('Y-m-d H:i'),
                'company_id' => $act->company_id,
            ];
        })->values();

        return response()->json([
            'success' => true,
            'data'    => [
                'id'          => $task->id,
                'title'       => $task->title,
                'description' => $task->description,
                'due_date'    => $task->due_date?->format('Y-m-d'),
                'priority'    => $task->priority,
                'status'      => $task->status,
                'creator'     => $creator,
                'assignees'   => $assignees,
                'attachments' => $fmtAtt,
                'submissions' => $fmtSubs,
                'comments'    => $comments,
                'feedbacks'   => $feedbacks,
                'activities'  => $activities,
            ],
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Tags $tags)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $task = ProgramTask::find($id);
        
        if (!$task) {
            return response()->json([
                'success' => false,
                'message' => app()->getLocale() == 'ar'
                    ? 'المهمة غير موجودة'
                    : 'Task not found.'
            ], 404);
        }

        // Validation rules (similar to store but without required for most)
        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:200',
            'description' => 'nullable|string|max:2000',
            'program_id' => 'sometimes|required|exists:cycles,id',
            'assignees' => 'sometimes|required|array|min:1',
            'assignees.*' => 'exists:companies,id',
            'priority' => 'sometimes|required|in:Low,Medium,High',
            'status' => 'sometimes|required|in:pending,progress,review,completed,archived',
            'due_date' => 'nullable|date',
            'tags' => 'nullable|array',
            'attachments' => 'nullable|array|max:10',
            'attachments.*' => 'file|max:10240|mimes:jpeg,jpg,png,pdf,docx,xlsx,pptx',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $oldValues = $task->only(['title', 'description', 'program_id', 'priority', 'status', 'due_date', 'tags']);

            // Process tags - create new ones if they don't exist
            $data = $request->only(['title', 'description', 'program_id', 'priority', 'status']);
            
            if ($request->has('tags') && is_array($request->tags)) {
                $processedTags = [];
                foreach ($request->tags as $tagName) {
                    $tagName = trim($tagName);
                    if (!empty($tagName)) {
                        // Check if tag exists, if not create it
                        $tag = Tags::firstOrCreate(['name' => $tagName]);
                        $processedTags[] = $tagName;
                    }
                }
                $data['tags'] = $processedTags;
            }
            
            if ($request->has('due_date')) {
                $data['due_date'] = $request->due_date ?: null;
            }

            $task->update($data);

            $trackable         = ['title', 'description', 'priority', 'status', 'due_date'];
            $assigneeCompanies = $task->assignees()->pluck('company_id')->toArray();
            $now     = now();
            $changes = collect($trackable)
                ->filter(fn($col) => (string) ($oldValues[$col] ?? null) !== (string) ($task->$col ?? null))
                ->map(fn($col) => ['col' => $col, 'old' => $oldValues[$col] ?? null, 'new' => $task->$col ?? null]);

            $activities = $changes
                ->crossJoin($assigneeCompanies)
                ->map(fn($pair) => [
                    'program_task_id' => $task->id,
                    'user_id'         => auth()->id(),
                    'action'          => 'update',
                    'column'          => $pair[0]['col'],
                    'was'             => $pair[0]['old'],
                    'new'             => $pair[0]['new'],
                    'company_id'      => $pair[1],
                    'created_at'      => $now,
                    'updated_at'      => $now,
                ])
                ->toArray();

            if (!empty($activities)) {
                ProgramTaskActivity::insert($activities);
            }

            // Update assignees if provided — always sync status to task status on edit
            if ($request->has('assignees')) {
                $assigneeData = [];
                foreach ($request->assignees as $companyId) {
                    $assigneeData[$companyId] = [
                        'status' => $task->status,
                        'last_updated' => now(),
                    ];
                }
                $task->assignees()->sync($assigneeData);
            } else {
                // No assignees in request — still update all existing assignees' status
                ProgramTaskAssignee::where('program_task_id', $task->id)
                    ->update(['status' => $task->status, 'last_updated' => now()]);
            }

            // Handle file attachments
            if ($request->hasFile('attachments')) {
                /** @var \App\User $uploader */
                $uploader = auth()->user();
                $attachments = $task->attachments ?? [];
                foreach ($request->file('attachments') as $file) {
                    // Validate file size (10 MB)
                    if ($file->getSize() > 10485760) continue;

                    // Store the file
                    $path = app(\App\Services\FileManagerService::class)->upload($file, 'tasks/attachments', 'public', 'attachment');
                    $attachments[] = [
                        'name'       => $file->getClientOriginalName(),
                        'path'       => $path,
                        'size'       => $file->getSize(),
                        'type'       => $file->getMimeType(),
                        'status'     => $uploader->hasRole('registered') ? 'submission' : 'attachment',
                        'company_id' => $uploader->company_id,
                        'user_id'    => $uploader->id,
                        'created_at' => now()->toDateTimeString(),
                    ];
                }
                $task->update(['attachments' => $attachments]);
            }

            $task->load('assignees');
            $updateAssigneesCompany = $task->assignees()->pluck('company_id')->toArray();
            $updateAssignees        = User::whereIn('company_id', $updateAssigneesCompany)->get();
            $updateMentor           = User::find($task->created_by);
            $updateStartupNames     = $task->assignees->pluck('name')->filter()->values()->toArray();

            $newStatus = $task->status;
            $oldStatus = $oldValues['status'] ?? null;
            $notifAction = match(true) {
                $newStatus === 'archived'                          => 'archive',
                $oldStatus === 'archived' && $newStatus !== 'archived' => 'reopen',
                default                                            => 'update',
            };
            $updateNotifData = [
                'task'         => $task,
                'message'      => "Task Updated: " . $task->title,
                'action'       => $notifAction,
                'mentor'       => $updateMentor,
                'startupNames' => $updateStartupNames,
                'program_name' => $task->program ? $task->program->title : null,
            ];
            $updateNotifiedIds = [];
            foreach ($updateAssignees as $startup) {
                \Illuminate\Support\Facades\Notification::send([$startup],
                new \App\Notifications\TaskNotification($updateNotifData));
                $updateNotifiedIds[] = $startup->id;
            }
            // if ($updateMentor && !in_array($updateMentor->id, $updateNotifiedIds)) {
            //     \Illuminate\Support\Facades\Notification::send([$updateMentor], new \App\Notifications\TaskNotification($updateNotifData));
            //     $updateNotifiedIds[] = $updateMentor->id;
            // }
            // if ($updateMentor) {
            //     $_updateAdminOrgId = $updateMentor->organization_id ?? $task->program?->organization_id;
            //     if ($_updateAdminOrgId) {
            //         $_updateAdminSkip = array_values(array_filter(array_unique(array_merge($updateNotifiedIds, [$updateMentor->id]))));
            //         $_updateAdmins = User::whereHas('roles', fn($q) => $q->whereIn('default_for', ['admin', 'subadmin', 'organizationManager', 'organizationmanager']))
            //             ->where(fn($q) => $q
            //                 ->where('organization_id', $_updateAdminOrgId)
            //                 ->orWhereHas('organizations', fn($pq) => $pq->where('organizations.id', $_updateAdminOrgId))
            //                 ->orWhereHas('roles', fn($rq) => $rq->where('default_for', 'admin')))
            //             ->when(!empty($_updateAdminSkip), fn($q) => $q->whereNotIn('id', $_updateAdminSkip))
            //             ->where(fn($q) => $q->whereNull('company_id')->orWhereNotIn('company_id', $updateAssigneesCompany))
            //             ->get();
            //         if ($_updateAdmins->isNotEmpty()) {
            //             \Illuminate\Support\Facades\Notification::send($_updateAdmins->all(), new \App\Notifications\TaskNotification($updateNotifData));
            //         }
            //     }
            // }

            return response()->json([
                'success' => true,
                'message' => app()->getLocale() == 'ar'
                    ? 'تم تحديث المهمة بنجاح'
                    : 'Task updated successfully.',
                'data' => $task->load('program', 'assignees', 'creator.roles')
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Error updating task: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => app()->getLocale() == 'ar'
                    ? 'حدث خطأ أثناء تحديث المهمة'
                    : 'An error occurred while updating the task.'
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $task = ProgramTask::find($id);
            
            if (!$task) {
                return response()->json([
                    'success' => false,
                    'message' => app()->getLocale() == 'ar'
                        ? 'المهمة غير موجودة'
                        : 'Task not found.'
                ], 404);
            }

            $fileManager = app(\App\Services\FileManagerService::class);

            // Delete task-level attachments
            foreach ($task->attachments ?? [] as $attachment) {
                if (isset($attachment['path'])) {
                    $fileManager->delete($attachment['path'], 'public');
                }
            }

            // Delete assignee-level attachments (submissions)
            ProgramTaskAssignee::where('program_task_id', $task->id)->each(function ($assignee) use ($fileManager) {
                foreach ($assignee->attachment ?? [] as $att) {
                    if (isset($att['path'])) {
                        $fileManager->delete($att['path'], 'public');
                    }
                }
            });

            $task->delete();

            return response()->json([
                'success' => true,
                'message' => app()->getLocale() == 'ar'
                    ? 'تم حذف المهمة بنجاح'
                    : 'Task deleted successfully.'
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Error deleting task: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => app()->getLocale() == 'ar'
                    ? 'حدث خطأ أثناء حذف المهمة'
                    : 'An error occurred while deleting the task.'
            ], 500);
        }
    }

    /**
     * Get companies/assignees by program ID.
     */
    public function getAssignees($programId)
    {
        try {
            $companies = Company::where('cycle', $programId)
                ->get()
                ->map(function($company) {
                    return [
                        'id' => $company->id,
                        'name' => $company->name,
                        'logo' => $company->logo ? url($company->logo) : null,
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $companies
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Get unique task titles for suggestions.
     */
    public function getTaskTitles()
    {
        try {
            $titles = ProgramTask::select('title')
                ->distinct()
                ->pluck('title');

            return response()->json([
                'success' => true,
                'data' => $titles
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Delete a single attachment from a task.
     */
    public function uploadAttachment(Request $request, $taskId)
    {
        try {
            $task = ProgramTask::find($taskId);

            if (!$task) {
                return response()->json(['success' => false, 'message' => 'Task not found.'], 404);
            }

            $request->validate([
                'attachment' => 'required|file|max:5120|mimes:jpeg,jpg,png,gif,webp,pdf,doc,docx,xls,xlsx,ppt,pptx,zip,rar,txt,csv',
            ]);

            /** @var \App\User $uploader */
            $uploader = auth()->user();

            if ($task->status === 'completed' && $uploader->hasRole('registered')) {
                return response()->json(['success' => false, 'message' => 'Task is already completed.'], 422);
            }
            $file = $request->file('attachment');
            $fileManager = app(\App\Services\FileManagerService::class);
            $path = $fileManager->upload($file, 'tasks/attachments', 'public', 'attachment');

            $newAtt = [
                'name'       => $file->getClientOriginalName(),
                'path'       => $path,
                'size'       => $file->getSize(),
                'type'       => $file->getMimeType(),
                'note'       => $request->input('note', ''),
                'batch_id'   => $request->input('batch_id', ''),
                'company_id' => $uploader->company_id,
                'user_id'    => $uploader->id,
                'created_at' => now()->toDateTimeString(),
            ];

            $activityData = null;

            if ($uploader->hasRole('registered')) {
                $assignee = ProgramTaskAssignee::where('program_task_id', $taskId)
                    ->where('company_id', $uploader->company_id)
                    ->first();

                if (!$assignee) {
                    return response()->json(['success' => false, 'message' => 'Not assigned to this task.'], 403);
                }

                $assigneeAtts   = $assignee->attachment ?? [];
                $assigneeAtts[] = $newAtt;
                $assignee->update(['attachment' => $assigneeAtts]);

                $activity = ProgramTaskActivity::create([
                    'program_task_id' => $taskId,
                    'user_id'         => $uploader->id,
                    'company_id'      => $uploader->company_id,
                    'action'          => 'submission',
                ]);

                $activity->load('user');
                $activityData = [
                    'user'       => $activity->user->name ?? 'N/A',
                    'user_id'    => $activity->user_id,
                    'img_url'    => $activity->user ? \App\User::image_handler($activity->user->img_url) : null,
                    'action'     => 'submitted a file',
                    'time'       => $activity->created_at->diffForHumans(),
                    'created_at' => $activity->created_at->format('Y-m-d H:i'),
                ];
            } else {
                $attachments   = $task->attachments ?? [];
                $attachments[] = array_merge($newAtt, ['status' => 'attachment']);
                $task->update(['attachments' => $attachments]);
            }

            return response()->json([
                'success'    => true,
                'attachment' => array_merge($newAtt, ['url' => $fileManager->getUrl($path, 'public')]),
                'activity'   => $activityData,
            ]);

        } catch (\Exception $e) {
            \Log::error('Error uploading attachment: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'An error occurred.'], 500);
        }
    }

    public function deleteAttachment($taskId, $filename)
    {
        try {
            $task = ProgramTask::find($taskId);

            if (!$task) {
                return response()->json(['success' => false, 'message' => 'Task not found.'], 404);
            }

            $fileManager = app(\App\Services\FileManagerService::class);

            // Search task-level attachments first
            $taskAtts  = $task->attachments ?? [];
            $target    = null;
            $remaining = [];
            foreach ($taskAtts as $att) {
                if ($att['name'] === $filename) {
                    $target = $att;
                } else {
                    $remaining[] = $att;
                }
            }
            if ($target) {
                $fileManager->delete($target['path'], 'public');
                $task->update(['attachments' => $remaining]);
                return response()->json([
                    'success'     => true,
                    'message'     => app()->getLocale() == 'ar' ? 'تم حذف المرفق بنجاح' : 'Attachment deleted successfully.',
                    'attachments' => $remaining,
                ]);
            }

            // Search assignee-level attachments (submissions)
            $assignees = ProgramTaskAssignee::where('program_task_id', $taskId)->get();
            foreach ($assignees as $assignee) {
                $atts      = $assignee->attachment ?? [];
                $found     = null;
                $remaining = [];
                foreach ($atts as $att) {
                    if ($att['name'] === $filename) {
                        $found = $att;
                    } else {
                        $remaining[] = $att;
                    }
                }
                if ($found) {
                    $fileManager->delete($found['path'], 'public');
                    $assignee->update(['attachment' => $remaining]);
                    return response()->json([
                        'success'     => true,
                        'message'     => app()->getLocale() == 'ar' ? 'تم حذف المرفق بنجاح' : 'Attachment deleted successfully.',
                        'attachments' => $remaining,
                    ]);
                }
            }

            return response()->json(['success' => false, 'message' => 'Attachment not found.'], 404);

        } catch (\Exception $e) {
            \Log::error('Error deleting attachment: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'An error occurred.'], 500);
        }
    }

    /**
     * Download task attachment.
     */
    public function downloadAttachment($taskId, $filename)
    {
        try {
            $task = ProgramTask::find($taskId);
            
            if (!$task) {
                return response()->json([
                    'success' => false,
                    'message' => app()->getLocale() == 'ar'
                        ? 'المهمة غير موجودة'
                        : 'Task not found.'
                ], 404);
            }

            // Search task-level attachments
            $attachment = null;
            foreach ($task->attachments ?? [] as $att) {
                if ($att['name'] === $filename) {
                    $attachment = $att;
                    break;
                }
            }

            // If not found, search assignee-level attachments (submissions)
            if (!$attachment) {
                $assignees = ProgramTaskAssignee::where('program_task_id', $taskId)->get();
                foreach ($assignees as $assignee) {
                    foreach ($assignee->attachment ?? [] as $att) {
                        if ($att['name'] === $filename) {
                            $attachment = $att;
                            break 2;
                        }
                    }
                }
            }

            if (!$attachment) {
                return response()->json([
                    'success' => false,
                    'message' => app()->getLocale() == 'ar'
                        ? 'المرفق غير موجود'
                        : 'Attachment not found.'
                ], 404);
            }

            $filePath = $attachment['path'];
            $fileManager = app(\App\Services\FileManagerService::class);
            $relativePath = $fileManager->getRelativePath($filePath, 'public');
            
            // Check if file exists in storage
            if (!Storage::disk('public')->exists($relativePath)) {
                return response()->json([
                    'success' => false,
                    'message' => app()->getLocale() == 'ar'
                        ? 'الملف غير موجود على الخادم'
                        : 'File not found on server.'
                ], 404);
            }

            // Get file information
            $mimeType = $attachment['type'] ?? Storage::disk('public')->mimeType($relativePath);
            $originalName = $attachment['name'];

            // Return file download response
            return Storage::disk('public')->download($relativePath, $originalName, [
                'Content-Type' => $mimeType,
                'Content-Disposition' => 'attachment; filename="' . $originalName . '"'
            ]);

        } catch (\Exception $e) {
            \Log::error('Error downloading attachment: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => app()->getLocale() == 'ar'
                    ? 'حدث خطأ أثناء تحميل المرفق'
                    : 'An error occurred while downloading the attachment.'
            ], 500);
        }
    }

    public function addComment(Request $request, $taskId)
    {
        try {
            $task = ProgramTask::find($taskId);

            if (!$task) {
                return response()->json([
                    'success' => false,
                    'message' => app()->getLocale() == 'ar'
                        ? 'المهمة غير موجودة'
                        : 'Task not found.'
                ], 404);
            }

            $comment = $task->comments()->create([
                'user_id' => auth()->id(),
                'comment' => $request->input('comment'),
            ]);

            ProgramTaskActivity::create([
                'program_task_id' => $task->id,
                'user_id'         => auth()->id(),
                'action'          => 'comment',
            ]);

            $user = auth()->user();

            return response()->json([
                'success' => true,
                'message' => __('admin.comment_success'),
                'comment' => [
                    'id'      => $comment->id,
                    'user'    => $user->name,
                    'user_id' => $user->id,
                    'img_url' => User::image_handler($user->img_url),
                    'text'    => $comment->comment,
                    'time'    => $comment->created_at->diffForHumans(),
                ],
            ]);

        } catch (\Exception $e) {
            \Log::error('Error adding comment: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => __('admin.comment_failed')
            ], 500);
        }
    }
    
    public function moveTask($taskId, $status)
    {
        try {
            $user = auth()->user();
            $task = ProgramTask::find($taskId) ?? MentorshipTask::find($taskId);

            if (!$task) {
                return response()->json([
                    'success' => false,
                    'message' => app()->getLocale() == 'ar'
                        ? 'المهمة غير موجودة'
                        : 'Task not found.'
                ], 404);
            }
            if($user->hasRole('registered')) {
                if($task->status == 'completed') {
                    return response()->json([
                        'success' => false,
                        'message' => app()->getLocale() == 'ar'
                            ? 'لا يمكنك نقل مهمة لانها مكتملة'
                            : 'You cannot move a task that is already completed.'
                    ], 400);
                }
            }
            if($status == 'completed') {
                return response()->json([
                    'success' => false,
                    'message' => app()->getLocale() == 'ar'
                        ? 'لا يمكنك وضع المهمة كمكتملة. يجب أن يقوم مُسند المهمة بإكمالها.'
                        : 'You can’t mark this task as completed. It must be completed by the assigner.'
                ], 400);
            }

            $assigneeRow = ProgramTaskAssignee::where('program_task_id', $task->id)
                ->where('company_id', $user->company_id)
                ->first();
            $oldStatus = $assigneeRow ? $assigneeRow->status : $task->status;

            ProgramTaskAssignee::where('program_task_id', $task->id)
                ->where('company_id', $user->company_id)
                ->update(['status' => $status, 'last_updated' => now()]);
           
            if ($task instanceof ProgramTask) {
                $createdActivity = ProgramTaskActivity::create([
                    'program_task_id' => $task->id,
                    'user_id'         => auth()->id(),
                    'action'          => 'move',
                    'column'          => 'status',
                    'was'             => $oldStatus,
                    'new'             => $status,
                    'company_id'      => $user->company_id ?: null,
                ]);

                $moveNotifAction = null;
                if ($status === 'archived') {
                    $moveNotifAction = 'archive';
                } elseif ($oldStatus === 'archived') {
                    $moveNotifAction = 'reopen';
                }

                if ($moveNotifAction) {
                    $task->load('assignees');
                    $moveAssigneesCompany = $task->assignees()->pluck('company_id')->toArray();
                    $moveAssignees        = User::whereIn('company_id', $moveAssigneesCompany)->get();
                    $moveMentor           = User::find($task->created_by);
                    $moveStartupNames     = $task->assignees->pluck('name')->filter()->values()->toArray();

                    $moveNotifData = [
                        'task'         => $task,
                        'message'      => ($moveNotifAction === 'archive' ? "Task Archived: " : "Task Reopened: ") . $task->title,
                        'action'       => $moveNotifAction,
                        'mentor'       => $moveMentor,
                        'startupNames' => $moveStartupNames,
                        'program_name' => $task->program ? $task->program->title : null,
                    ];
                    $moveNotifiedIds = [auth()->id()];
                    if ($moveMentor && $moveMentor->id !== auth()->id()) {
                        \Illuminate\Support\Facades\Notification::send([$moveMentor], new \App\Notifications\TaskNotification($moveNotifData));
                        $moveNotifiedIds[] = $moveMentor->id;
                    }
                    foreach ($moveAssignees as $assignee) {
                        if ($assignee->id !== auth()->id()) {
                            \Illuminate\Support\Facades\Notification::send([$assignee], new \App\Notifications\TaskNotification($moveNotifData));
                            $moveNotifiedIds[] = $assignee->id;
                        }
                    }
                    if ($moveMentor) {
                        $_moveAdminOrgId = $moveMentor->organization_id ?? $task->program?->organization_id;
                        if ($_moveAdminOrgId) {
                            $_moveAdminSkip = array_values(array_filter(array_unique($moveNotifiedIds)));
                            $_moveAdmins = User::whereHas('roles', fn($q) => $q->whereIn('default_for', ['admin', 'subadmin', 'organizationManager', 'organizationmanager']))
                                ->where(fn($q) => $q
                                    ->where('organization_id', $_moveAdminOrgId)
                                    ->orWhereHas('organizations', fn($pq) => $pq->where('organizations.id', $_moveAdminOrgId))
                                    ->orWhereHas('roles', fn($rq) => $rq->where('default_for', 'admin')))
                                ->when(!empty($_moveAdminSkip), fn($q) => $q->whereNotIn('id', $_moveAdminSkip))
                                ->where(fn($q) => $q->whereNull('company_id')->orWhereNotIn('company_id', $moveAssigneesCompany))
                                ->get();
                            if ($_moveAdmins->isNotEmpty()) {
                                \Illuminate\Support\Facades\Notification::send($_moveAdmins->all(), new \App\Notifications\TaskNotification($moveNotifData));
                            }
                        }
                    }
                }
            }

            $activityData = null;
            if (!empty($createdActivity)) {
                $createdActivity->load('user');
                $actLabel = 'moved task' . ($createdActivity->was && $createdActivity->new
                    ? ' from "' . $createdActivity->was . '" to "' . $createdActivity->new . '"'
                    : '');
                $activityData = [
                    'user'       => $createdActivity->user->name ?? 'N/A',
                    'user_id'    => $createdActivity->user_id,
                    'img_url'    => $createdActivity->user ? \App\User::image_handler($createdActivity->user->img_url) : null,
                    'action'     => $actLabel,
                    'time'       => $createdActivity->created_at->diffForHumans(),
                    'created_at' => $createdActivity->created_at->format('Y-m-d H:i'),
                ];
            }

            $message = $status === 'review'
                ? (app()->getLocale() == 'ar'
                    ? 'تم إرسالها للمراجعة. سيتم مراجعة هذه المهمة بواسطة مُسند المهمة.'
                    : 'Sent for review. This task will be reviewed by the assigner.')
                : __('admin.Task_status_updated_successfully');

            return response()->json([
                'success'  => true,
                'message'  => $message,
                'activity' => $activityData,
            ]);

        } catch (\Exception $e) {
            \Log::error('Error moving task: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => __('admin.move_task_failed')
            ], 500);
        }
    }
    
    public function addFeedback(Request $request, $taskId)
    {
        
        try {
            $task = ProgramTask::find($taskId);

            if (!$task) {
                return response()->json([
                    'success' => false,
                    'message' => app()->getLocale() == 'ar'
                        ? 'المهمة غير موجودة'
                        : 'Task not found.'
                ], 404);
            }


            $companyIdForCheck = $request->input('company_id');
            if (empty($companyIdForCheck) && $task->assignees()->count() === 1) {
                $companyIdForCheck = $task->assignees()->first()->id;
            }

            $assigneeStatus = ProgramTaskAssignee::where('program_task_id', $task->id)
                ->when($companyIdForCheck, fn($q) => $q->where('company_id', $companyIdForCheck))
                ->value('status');

            if ($assigneeStatus !== 'review') {
                return response()->json([
                    'success' => false,
                    'message' => app()->getLocale() == 'ar'
                        ? 'لا يمكن تقديم ملاحظات إلا على المهام التي في حالة مراجعة'
                        : 'Feedback can only be submitted for tasks in review status.'
                ], 400);
            }

            $validator = Validator::make($request->all(), [
                'decision'    => 'required|in:pass,revision',
                'note'        => 'required|string',
                'attachments' => 'nullable|array',
                'attachments.*' => 'file|max:10240|mimes:jpeg,jpg,png,pdf,zip,rar,doc,docx,xls,xlsx,ppt,pptx',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first(),
                    'errors'  => $validator->errors()
                ], 422);
            }

            $feedback = $task->feedbacks()->create([
                'user_id'    => auth()->id(),
                'company_id' => $companyIdForCheck ?: null,
                'decision'   => $request->input('decision'),
                'note'       => $request->input('note'),
            ]);
            $uploadedAttachments = [];
            if ($request->hasFile('attachments')) {
                $existingAttachments = $task->attachments ?? [];
                $files = $request->file('attachments');
                
                if (count($files) > 5) {
                    return response()->json([
                        'success' => false,
                        'message' => app()->getLocale() == 'ar'
                            ? 'الحد الأقصى للمرفقات هو 5 ملفات.'
                            : 'Maximum 5 attachments allowed.',
                    ], 422);
                }
                foreach ($files as $file) {
                    $path = app(\App\Services\FileManagerService::class)->upload($file, 'tasks/attachments', 'public', 'attachment');
                    $newAtt = [
                        'name'       => $file->getClientOriginalName(),
                        'path'       => $path,
                        'size'       => $file->getSize(),
                        'type'       => $file->getMimeType(),
                        'status'     => 'feedback',
                        'created_at' => now()->toDateTimeString(),
                    ];
                    $existingAttachments[] = $newAtt;
                    $uploadedAttachments[] = $newAtt;
                }
                $task->update(['attachments' => $existingAttachments]);
            }

            $task->status = $request->input('decision') === 'pass' ? 'completed' : 'pending';
            $task->save();

            $user      = auth()->user();
            $decision  = $request->input('decision');
            $companyId = $request->input('company_id');

            if ($companyId) {
                ProgramTaskAssignee::where('program_task_id', $task->id)
                    ->where('company_id', $companyId)
                    ->update(['status' => $task->status, 'last_updated' => now()]);
            }

            ProgramTaskActivity::create([
                'program_task_id' => $task->id,
                'user_id'         => auth()->id(),
                'action'          => 'feedback',
                'company_id'      => $companyId ?: null,
            ]);

            $targetCompanyIds = $companyId
                ? [$companyId]
                : $task->assignees()->pluck('company_id')->toArray();

            $assignees    = User::whereIn('company_id', $targetCompanyIds)->get();
            $startupNames = $task->assignees
                ->when($companyId, fn($col) => $col->where('id', $companyId))
                ->pluck('name')->filter()->values()->toArray();

            $notifData = [
                'task'         => $task,
                'message'      => $decision === 'pass'
                    ? ($task->title . ' passed.')
                    : ('Revisions requested for ' . $task->title . '.'),
                'action'       => 'feedback',
                'decision'     => $decision,
                'feedbackNote' => $feedback->note,
                'mentor'       => $user,
                'startupNames' => $startupNames,
                'program_name' => $task->program ? $task->program->title : null,
            ];
            
            foreach ($assignees as $assignee) {
                \Illuminate\Support\Facades\Notification::send(
                    [$assignee],
                    new \App\Notifications\TaskNotification($notifData)
                );
            }

            return response()->json([
                'success'     => true,
                'message'     => __('admin.feedback_added_success'),
                'feedback'    => [
                    'id'         => $feedback->id,
                    'user'       => $user->name,
                    'user_id'    => $user->id,
                    'company_id' => $feedback->company_id,
                    'img_url'    => User::image_handler($user->img_url),
                    'decision'   => $feedback->decision,
                    'text'       => $feedback->note,
                    'time'       => $feedback->created_at->format('d-m-Y H:i'),
                ],
                'attachments' => $uploadedAttachments,
            ]);

        } catch (\Exception $e) {
            \Log::error('Error adding feedback: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => __('admin.feedback_failed')
            ], 500);
        }
    }
}
