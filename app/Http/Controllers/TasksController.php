<?php

namespace App\Http\Controllers;

use App\Cycle;
use App\Company;
use App\Tags;
use Illuminate\Http\Request;

class TasksController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {            
        $organization_id = auth()->user()->organization_id;

        $programs = Cycle::select('id', 'title')
        ->when(!is_null($organization_id), function ($q) use ($organization_id) {
            $q->where('organization_id', $organization_id);
        })
        ->get();

        $startups = Company::whereHas('cycledata', function ($q) use ($organization_id) {
            $q->when(!is_null($organization_id), function ($q) use ($organization_id) {
                $q->where('organization_id', $organization_id);
            });
        })
        ->get();

        $tags = Tags::select('id', 'name')->orderBy("id", "desc")->get();

        $stats = [
            'total_tasks' => 45,
            'pending' => 12,
            'active' => [
                'in_progress_count' => 10,
                'in_review_count' => 5,
            ],
            'completed' => 15,
        ];

        // Get real startups for sample data
        $sampleStartups = $startups->take(4)->values();

        $tasks = [
            [
                'id' => 1,
                'title' => 'Implement user authentication',
                'description' => 'Add login and registration functionality',
                'program' => [
                    'id' => 38,
                    'title' => 'Incubator program',
                ],
                'assignees' => [
                    [
                        'id' => $sampleStartups->get(0)?->id ?? 1,
                        'name' => $sampleStartups->get(0)?->name ?? 'Sample Company 1',
                        'status' => 'In Progress',
                        'lastUpdated' => '2025-12-15',
                    ],
                    [
                        'id' => $sampleStartups->get(1)?->id ?? 2,
                        'name' => $sampleStartups->get(1)?->name ?? 'Sample Company 2',
                        'status' => 'Pending',
                        'lastUpdated' => '2025-12-14',
                    ],
                ],
                'priority' => 'High',
                'overallStatus' => 'In Progress',
                'dueDate' => '2025-12-20',
                'tags' => ['urgent', 'feature'],
            ],
            [
                'id' => 2,
                'title' => 'Fix responsive layout issues',
                'description' => 'Resolve mobile display problems',
                'program' => [
                    'id' => 48,
                    'title' => 'hackathon program',
                ],
                'assignees' => [
                    [
                        'id' => $sampleStartups->get(2)?->id ?? 3,
                        'name' => $sampleStartups->get(2)?->name ?? 'Sample Company 3',
                        'status' => 'Pending',
                        'lastUpdated' => '2025-12-13',
                    ],
                ],
                'priority' => 'Medium',
                'overallStatus' => 'Pending',
                'dueDate' => '2025-12-25',
                'tags' => ['bug'],
            ],
            [
                'id' => 3,
                'title' => 'Update documentation',
                'description' => 'Add new API endpoints to docs',
                'program' => [
                    'id' => 38,
                    'title' => 'Web App',
                ],
                'assignees' => [
                    [
                        'id' => $sampleStartups->get(3)?->id ?? 4,
                        'name' => $sampleStartups->get(3)?->name ?? 'Sample Company 4',
                        'status' => 'Completed',
                        'lastUpdated' => '2025-12-15',
                    ],
                ],
                'priority' => 'Low',
                'overallStatus' => 'Completed',
                'dueDate' => '2025-12-15',
                'tags' => ['important'],
            ],
            [
                'id' => 4,
                'title' => 'Optimize database queries',
                'description' => 'Improve performance of slow queries',
                'program' => [
                    'id' => 44,
                    'title' => 'growhub program',
                ],
                'assignees' => [
                    [
                        'id' => $sampleStartups->get(0)?->id ?? 1,
                        'name' => $sampleStartups->get(0)?->name ?? 'Sample Company 1',
                        'status' => 'In Review',
                        'lastUpdated' => '2025-12-16',
                    ],
                    [
                        'id' => $sampleStartups->get(1)?->id ?? 2,
                        'name' => $sampleStartups->get(1)?->name ?? 'Sample Company 2',
                        'status' => 'In Progress',
                        'lastUpdated' => '2025-12-15',
                    ],
                ],
                'priority' => 'High',
                'overallStatus' => 'In Review',
                'dueDate' => '2025-12-18',
                'tags' => ['urgent', 'bug'],
            ],
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

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {

    }
}
