<?php

namespace App\Http\Controllers;

use App\Cycle;
use App\Company;
use App\Tags;
use App\ProgramTask;
use Illuminate\Http\Request;
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
        $tasksData = ProgramTask::with(['program', 'assignees', 'creator'])
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
                    'profile_url' => url('/dashboard/users/' . $task->creator->id),
                ] : null,
            ];
        });

        // Calculate real stats
        $stats = [
            'total_tasks' => $tasksData->count(),
            'pending' => $tasksData->where('status', 'Pending')->count(),
            'active' => [
                'in_progress_count' => $tasksData->where('status', 'In Progress')->count(),
                'in_review_count' => $tasksData->where('status', 'Review')->count(),
            ],
            'completed' => $tasksData->where('status', 'Completed')->count(),
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
            'assignees' => 'required|array|min:1',
            'assignees.*' => 'exists:companies,id',
            'priority' => 'required|in:Low,Medium,High',
            'status' => 'required|in:Pending,In Progress,Review,Completed',
            'due_date' => 'nullable|date|after_or_equal:today',
            'tags' => 'nullable|array',
            'attachments' => 'nullable|array|max:10',
            'attachments.*' => 'file|max:10240|mimes:pdf,docx,xlsx,png,jpg,jpeg',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Handle file attachments
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

                    // Validate file type
                    $allowedExtensions = ['pdf', 'docx', 'xlsx', 'png', 'jpg', 'jpeg'];
                    $extension = strtolower($file->getClientOriginalExtension());
                    
                    if (!in_array($extension, $allowedExtensions)) {
                        return response()->json([
                            'success' => false,
                            'message' => app()->getLocale() == 'ar'
                                ? 'نوع الملف غير مدعوم. يرجى تحميل ملفات بصيغة PDF أو DOCX أو XLSX أو PNG أو JPG أو JPEG.'
                                : 'Unsupported file type. Please upload files in PDF, DOCX, XLSX, PNG, JPG, or JPEG format.'
                        ], 422);
                    }

                    // Store the file
                    $path = $file->store('tasks/attachments', 'public');
                    $attachments[] = [
                        'name' => $file->getClientOriginalName(),
                        'path' => $path,
                        'size' => $file->getSize(),
                        'type' => $file->getMimeType()
                    ];
                }
            }


            // Process tags - create new ones if they don't exist
            $processedTags = [];
            if ($request->has('tags') && is_array($request->tags)) {
                foreach ($request->tags as $tagName) {
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

            // Attach assignees (companies/startups)
            $assigneeData = [];
            foreach ($request->assignees as $companyId) {
                $assigneeData[$companyId] = [
                    'status' => 'Pending',
                    'last_updated' => now(),
                ];
            }
            $task->assignees()->attach($assigneeData);

            return response()->json([
                'success' => true,
                'message' => app()->getLocale() == 'ar' 
                    ? 'تم إنشاء المهمة بنجاح' 
                    : 'Task created successfully.',
                'data' => $task->load('program', 'assignees')
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
            'status' => 'sometimes|required|in:Pending,In Progress,Review,Completed',
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

            // Update assignees if provided
            if ($request->has('assignees')) {
                $assigneeData = [];
                foreach ($request->assignees as $companyId) {
                    $assigneeData[$companyId] = [
                        'status' => 'Pending',
                        'last_updated' => now(),
                    ];
                }
                $task->assignees()->sync($assigneeData);
            }

            return response()->json([
                'success' => true,
                'message' => app()->getLocale() == 'ar'
                    ? 'تم تحديث المهمة بنجاح'
                    : 'Task updated successfully.',
                'data' => $task->load('program', 'assignees')
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
}
