<?php

namespace App\Http\Controllers;

use App\Cycle;
use App\Project;
use Carbon\Carbon;
use App\Team_member;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Project::with(['programs', 'teamMembers']);
    
        if ($request->filled('program_name')) {
            $query->whereHas('programs', function ($q) use ($request) {
                $q->where('title', $request->program_name);
            });
        }
    
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $startDate = Carbon::parse($request->start_date)->startOfDay();
            $endDate = Carbon::parse($request->end_date)->endOfDay();
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }
    
        $projects = $query->paginate(10);
        $programs = Cycle::all();
    
        return view('admin.projects.index', compact('projects', 'programs'));
    }
    

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.projects.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:projects,name|max:255',
            'description' => 'nullable'
        ]);

        $project = Project::create($request->only('name', 'description'));

        return redirect()->route('projects.show', $project)->with('message', 'StoredSuccessfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(Project $project)
    {
        $linkedPrograms = $project->programs()->get();
        $availablePrograms = Cycle::whereNotIn('id', $project->programs()->pluck('cycles.id'))->get();
        $availableTeamMembers = Team_member::whereNotIn('id', $project->teamMembers()->pluck('team_member_id'))->get();

        $distinctRoles = Team_member::distinct()->pluck('TeamMember');
        return view('admin.projects.show', compact('project', 'linkedPrograms', 'availablePrograms','availableTeamMembers','distinctRoles'));
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Project $project)
    {
        return view('admin.projects.edit', compact('project'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Project $project)
    {
        $request->validate([
            'name' => 'required|unique:projects,name,' . $project->id,
            'description' => 'nullable'
        ]);

        $project->update($request->only('name', 'description'));

        return redirect()->route('projects.show', $project)->with('message', 'StoredSuccessfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project)
    {
        if ($project->programs()->exists() || $project->teamMembers()->exists()) {
            return redirect()->back()->with('error', 'ProjectCannotBeDeleted');
        }
        $project->delete();
        return redirect()->back()->with('message', 'ProjectDeleted');
    }

    public function linkPrograms(Request $request, Project $project)
    {
       $request->validate(['programs' => 'required|array']);

       $programsData = [];
        foreach ($request->programs as $programId) {
           $programsData[$programId] = [
            'status' => 'Active',
            'created_at' => now(),
            'updated_at' => now(),
           ];
      }

        $project->programs()->attach($programsData);

        return back()->with('message', 'ProgramsLinked');
    }


    public function unlinkProgram(Project $project, Cycle $cycle)
    {
        $project->programs()->detach($cycle->id);
        if (request()->ajax()) {
          return response()->json(['message' => __('admin.ProgramUnlinked')]);
        }
    }

    public function addTeamMembers(Request $request, Project $project) 
  {
      $request->validate([
        'team_members' => 'required|array',
        'team_members.*' => 'exists:team_members,id',
        'roles' => 'required|array',
      ]);

    foreach ($request->team_members as $teamMemberId) {
        $role = $request->roles[$teamMemberId] ?? null; 

        if ($role && !$project->teamMembers()->where('team_member_id', $teamMemberId)->exists()) {
            $project->teamMembers()->attach($teamMemberId, ['role' => $role]);
        }
    }

     return redirect()->back()->with('message', 'Team_member_added');
  }
    public function removeTeamMember(Project $project, Team_member $teamMember) 
    {
        $project->teamMembers()->detach($teamMember->id);
        if (request()->ajax()) {
            return response()->json(['message' => __('admin.Team_member_removed')], 200);
        }
    
    }
}
