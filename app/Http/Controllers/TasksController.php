<?php

namespace App\Http\Controllers;

use App\Cycle;
use App\Company;
use App\Tags;
use App\ProgramTask;
use App\ProgramTaskComment;
use App\ProgramTaskActivity;
use App\MentorshipTask;
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
        $tasksData = ProgramTask::with(['program', 'assignees', 'creator', 'feedbacks.user', 'activities.user'])
            ->when(!$user->hasRole('admin') && !empty($orgIds), function ($q) use ($orgIds) {
                $q->whereHas('program', function($pq) use ($orgIds) {
                    $pq->whereIn('organization_id', $orgIds);
                });
            })
            ->orderBy('created_at', 'desc')
            ->get();
        
        $tasks = $tasksData->map(function($task) {
            return [
                'id' => $task->id,
                'title' => $task->title,
                'description' => $task->description,
                'program' => [
                    'id' => $task->program_id,
                    'title' => $task->program->title ?? 'N/A',
                ],
                'assignees' => $task->assignees->map(function($assignee) {
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
                })->toArray(),
                'priority' => $task->priority,
                'overallStatus' => $task->status,
                'dueDate' => $task->due_date ? $task->due_date->format('Y-m-d') : 'N/A',
                'tags' => $task->tags ?? [],
                'attachments' => $task->attachments ?? [],
                'creator' => $task->creator ? [
                    'id' => $task->creator->id,
                    'name' => $task->creator->name,
                    'role' => $task->creator->roles->first()->name ?? 'N/A',
                    'avatar' => $task->creator->image_url,
                    'profile_url' => url('/dashboard/users/' . $task->creator->id),
                ] : null,
                'feedbacks' => $task->feedbacks->map(function($fb) {
                    return [
                        'id'       => $fb->id,
                        'user'     => $fb->user->name ?? 'N/A',
                        'user_id'  => $fb->user_id,
                        'img_url'  => $fb->user ? User::image_handler($fb->user->img_url) : null,
                        'decision' => $fb->decision,
                        'text'     => $fb->note,
                        'time'     => $fb->created_at->diffForHumans(),
                    ];
                })->toArray(),
                'activities' => $task->activities->map(function($act) {
                    $action = match($act->action) {
                        'create' => 'created this task',
                        'update' => 'updated ' . $act->column . ($act->was && $act->new ? ' from "' . $act->was . '" to "' . $act->new . '"' : ''),
                        'move'   => 'moved task' . ($act->was && $act->new ? ' from "' . $act->was . '" to "' . $act->new . '"' : ''),
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

        // Calculate real stats
        $stats = [
            'total_tasks' => $tasksData->count(),
            'pending' => $tasksData->where('status', 'pending')->count(),
            'active' => [
                'in_progress_count' => $tasksData->where('status', 'progress')->count(),
                'in_review_count' => $tasksData->where('status', 'review')->count(),
            ],
            'completed' => $tasksData->where('status', 'completed')->count(),
            'archived' => $tasksData->where('status', 'archived')->count(),
        ];

        return view("admin.tasks.index", compact("programs", "startups", "tags", "stats", "tasks"));
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
            'attachments.*' => 'file|max:10240',
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
                    $path = $file->store('tasks/attachments', 'public');
                    $attachments[] = [
                        'name'   => $file->getClientOriginalName(),
                        'path'   => $path,
                        'size'   => $file->getSize(),
                        'type'   => $file->getMimeType(),
                        'status' => $uploader->hasRole('registered') ? 'submission' : 'attachment',
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

            ProgramTaskActivity::create([
                'program_task_id' => $task->id,
                'user_id'         => auth()->id(),
                'action'          => 'create',
            ]);

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
    public function show(Tags $tags)
    {
        //
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

            $trackable = ['title', 'description', 'priority', 'status', 'due_date'];
            foreach ($trackable as $col) {
                $old = $oldValues[$col] ?? null;
                $new = $task->$col ?? null;
                if ((string) $old !== (string) $new) {
                    ProgramTaskActivity::create([
                        'program_task_id' => $task->id,
                        'user_id'         => auth()->id(),
                        'action'          => 'update',
                        'column'          => $col,
                        'was'             => $old,
                        'new'             => $new,
                    ]);
                }
            }

            // Update assignees if provided — preserve existing statuses
            if ($request->has('assignees')) {
                $existingStatuses = $task->assignees()->pluck('status', 'company_id')->toArray();
                $assigneeData = [];
                foreach ($request->assignees as $companyId) {
                    $assigneeData[$companyId] = [
                        'status' => $existingStatuses[$companyId] ?? 'pending',
                        'last_updated' => now(),
                    ];
                }
                $task->assignees()->sync($assigneeData);
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
                    $path = $file->store('tasks/attachments', 'public');
                    $attachments[] = [
                        'name'   => $file->getClientOriginalName(),
                        'path'   => $path,
                        'size'   => $file->getSize(),
                        'type'   => $file->getMimeType(),
                        'status' => $uploader->hasRole('registered') ? 'submission' : 'attachment',
                    ];
                }
                $task->update(['attachments' => $attachments]);
            }

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

            // Delete attachments if any
            if ($task->attachments) {
                foreach ($task->attachments as $attachment) {
                    if (isset($attachment['path'])) {
                        Storage::disk('public')->delete($attachment['path']);
                    }
                }
            }

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
                'attachment' => 'required|file|max:5120|mimes:jpeg,png,gif,pdf',
            ]);

            /** @var \App\User $uploader */
            $uploader = auth()->user();
            $file = $request->file('attachment');
            $path = $file->store('tasks/attachments', 'public');

            $newAtt = [
                'name'   => $file->getClientOriginalName(),
                'path'   => $path,
                'size'   => $file->getSize(),
                'type'   => $file->getMimeType(),
                'status' => $uploader->hasRole('registered') ? 'submission' : 'attachment',
            ];

            $attachments = $task->attachments ?? [];
            $attachments[] = $newAtt;
            $task->update(['attachments' => $attachments]);

            return response()->json([
                'success'    => true,
                'attachment' => array_merge($newAtt, ['url' => Storage::disk('public')->url($path)]),
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

            $attachments = $task->attachments ?? [];
            $target = null;
            $remaining = [];

            foreach ($attachments as $att) {
                if ($att['name'] === $filename) {
                    $target = $att;
                } else {
                    $remaining[] = $att;
                }
            }

            if (!$target) {
                return response()->json(['success' => false, 'message' => 'Attachment not found.'], 404);
            }

            // Delete physical file
            if (Storage::disk('public')->exists($target['path'])) {
                Storage::disk('public')->delete($target['path']);
            }

            $task->update(['attachments' => $remaining]);

            return response()->json([
                'success' => true,
                'message' => app()->getLocale() == 'ar' ? 'تم حذف المرفق بنجاح' : 'Attachment deleted successfully.',
                'attachments' => $remaining,
            ]);

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

            // Find the attachment in the task's attachments array
            $attachment = null;
            if ($task->attachments) {
                foreach ($task->attachments as $att) {
                    if ($att['name'] === $filename) {
                        $attachment = $att;
                        break;
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
            
            // Check if file exists in storage
            if (!Storage::disk('public')->exists($filePath)) {
                return response()->json([
                    'success' => false,
                    'message' => app()->getLocale() == 'ar'
                        ? 'الملف غير موجود على الخادم'
                        : 'File not found on server.'
                ], 404);
            }

            // Get file information
            $mimeType = $attachment['type'] ?? Storage::disk('public')->mimeType($filePath);
            $originalName = $attachment['name'];

            // Return file download response
            return Storage::disk('public')->download($filePath, $originalName, [
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
            $task = ProgramTask::find($taskId) ?? MentorshipTask::find($taskId);

            if (!$task) {
                return response()->json([
                    'success' => false,
                    'message' => app()->getLocale() == 'ar'
                        ? 'المهمة غير موجودة'
                        : 'Task not found.'
                ], 404);
            }

            $oldStatus = $task->status;
            $task->status = $status;
            $task->save();
           
            if ($task instanceof ProgramTask) {
                
                ProgramTaskActivity::create([
                    'program_task_id' => $task->id,
                    'user_id'         => auth()->id(),
                    'action'          => 'move',
                    'column'          => 'status',
                    'was'             => $oldStatus,
                    'new'             => $status,
                ]);
                
            }

            return response()->json([
                'success' => true,
                'message' => __('admin.Task_status_updated_successfully')
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

            $validator = Validator::make($request->all(), [
                'decision' => 'required|in:pass,revision',
                'note'     => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first(),
                    'errors'  => $validator->errors()
                ], 422);
            }

            $feedback = $task->feedbacks()->create([
                'user_id'  => auth()->id(),
                'decision' => $request->input('decision'),
                'note'     => $request->input('note'),
            ]);

            $task->status = $request->input('decision') === 'pass' ? 'completed' : 'pending';
            $task->save();

            $user = auth()->user();

            ProgramTaskActivity::create([
                'program_task_id' => $task->id,
                'user_id'         => auth()->id(),
                'action'          => 'feedback',
            ]);

            return response()->json([
                'success'  => true,
                'message'  => __('admin.feedback_added_success'),
                'feedback' => [
                    'id'       => $feedback->id,
                    'user'     => $user->name,
                    'user_id'  => $user->id,
                    'img_url'  => User::image_handler($user->img_url),
                    'decision' => $feedback->decision,
                    'text'     => $feedback->note,
                    'time'     => $feedback->created_at->diffForHumans(),
                ],
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
