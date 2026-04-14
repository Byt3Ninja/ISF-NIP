<?php

namespace App\Http\Controllers\Mentor;

use App\Company;
use App\Cycle;
use App\Http\Controllers\Controller;
use App\MentorshipRequest;
use App\ProgramTask;
use App\SurveyResponses;
use App\User;
use Illuminate\Http\Request;
use Spatie\Permission\Traits\HasRoles;

class MentorshipController extends Controller
{
    public function index(){
        
        $user = auth()->user();
        $mentorRequests =  MentorshipRequest::all();
        $available_surveys = SurveyResponses::whereIn('user_id', $mentorRequests->pluck('mentor_id'))->whereIn('triggered_at', $mentorRequests->pluck('id'))->where('triggered_by', 'session')->get();

        $sessions = collect([]);
        $cycles = collect([]);
        if(auth()->user() && auth()->user()->hasRole('Mentor')) {
            $sessions = auth()->user()->mentorSessions()->whereDate('zoom_date','<',now()->format('Y-m-d H:i'))->where('approved','Yes')->orderBy('zoom_date','DESC')->get();
            $cycles = auth()->user()->mentorCycles()->get();
        }
        if(auth()->user() && auth()->user()->hasRole('Registered')) {
            $company_id = auth()->user()->company_id;
            if(auth()->user()->company) {
                $usersCompany = auth()->user()->company->users()->where('company_id', $company_id)->get();
                $sessions = MentorshipRequest::whereIn('user_id', $usersCompany->pluck('id'))->whereDate('zoom_date','<',now()->format('Y-m-d H:i'))->where('approved','Yes')->orderBy('zoom_date','DESC')->get();
                $cycles = collect([auth()->user()->company->cycledata])->filter();
            } else {
                $sessions = collect(); // Empty collection if no company
            }
        }
        if(auth()->user() && auth()->user()->hasRole('Admin')) {
            $company_id = Company::all();
            $usersCompany = User::whereIn('company_id', $company_id->pluck('id'))->get();
            $sessions = MentorshipRequest::whereIn('user_id', $usersCompany->pluck('id'))->whereDate('zoom_date','<',now()->format('Y-m-d H:i'))->where('approved','Yes')->orderBy('zoom_date','DESC')->get();
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
            $baseQuery = fn() => ProgramTask::where('program_id', $cycle->id)
                ->where('status', '!=', 'archived')
                ->when($isRegistered,
                    fn($q) => $q->whereHas('assignees', fn($q2) => $q2->where('companies.id', $userCompanyId)),
                    fn($q) => $q->where('created_by', $user->id)
                );

            $completed = (clone $baseQuery())->where('status', 'completed')->count();
            $pending   = (clone $baseQuery())->where('status', '!=', 'completed')->count();

            return [
                'id'        => $cycle->id,
                'title'     => $cycle->title,
                'completed' => $completed,
                'pending'   => $pending,
            ];
        });

        $tasks = [
            [
                'id' => 1,
                'title' => 'Build Landing Page',
                'description' => 'Design and develop the landing page for the startup pitch event.',
                'program' => [
                    'id' => 3,
                    'title' => 'Spring Accelerator 2025',
                ],
                'assignees' => [
                    [
                        'id' => 101,
                        'name' => 'TechNova',
                        'status' => 'progress',
                        'lastUpdated' => '2025-03-10',
                        'logo' => 'logos/technova.png',
                        'profile_url' => 'https://example.com/dashboard/companyProfile/101',
                    ],
                    [
                        'id' => 102,
                        'name' => 'GreenSpark',
                        'status' => 'pending',
                        'lastUpdated' => '2025-03-08',
                        'logo' => null,
                        'profile_url' => 'https://example.com/dashboard/companyProfile/102',
                    ],
                ],
                'priority' => 'High',
                'overallStatus' => 'progress',
                'dueDate' => '2025-04-01',
                'tags' => ['Design', 'Frontend'],
                'attachments' => [
                    [
                        'id' => md5('tasks/attachments/wireframe_v1.pdf'),
                        'name' => 'wireframe_v1.pdf',
                        'url' => 'https://example.com/storage/tasks/attachments/wireframe_v1.pdf',
                        'size' => 204800,
                    ],
                ],
                'submissions' => [],
                'creator' => [
                    'id' => 5,
                    'name' => 'Sara Ahmed',
                    'role' => 'admin',
                    'avatar' => 'https://example.com/storage/avatars/sara.jpg',
                    'profile_url' => 'https://example.com/dashboard/users/5',
                ],
                'feedbacks' => [],
                'activities' => [
                    [
                        'user' => 'Sara Ahmed',
                        'user_id' => 5,
                        'img_url' => 'https://example.com/storage/avatars/sara.jpg',
                        'action' => 'created this task',
                        'time' => '3 days ago',
                    ],
                    [
                        'user' => 'Omar Khaled',
                        'user_id' => 8,
                        'img_url' => null,
                        'action' => 'updated status from "pending" to "progress"',
                        'time' => '1 day ago',
                    ],
                ],
                'activities_count' => 2,
                'feedbacks_count' => 0,
                'createdAt' => 1741737600,
            ],
            [
                'id' => 2,
                'title' => 'Submit Financial Report',
                'description' => 'Prepare and submit Q1 financial projections for review.',
                'program' => [
                    'id' => 3,
                    'title' => 'Spring Accelerator 2025',
                ],
                'assignees' => [
                    [
                        'id' => 103,
                        'name' => 'FinPath',
                        'status' => 'completed',
                        'lastUpdated' => '2025-03-15',
                        'logo' => 'logos/finpath.png',
                        'profile_url' => 'https://example.com/dashboard/companyProfile/103',
                    ],
                ],
                'priority' => 'Medium',
                'overallStatus' => 'review',
                'dueDate' => '2025-03-20',
                'tags' => ['Finance', 'Reporting'],
                'attachments' => [],
                'submissions' => [
                    [
                        'user' => 'Karim Mansour',
                        'user_id' => 12,
                        'img_url' => null,
                        'status' => 'pending_review',
                        'time' => '2 days ago',
                        'note' => null,
                        'files' => [
                            [
                                'name' => 'q1_report.xlsx',
                                'url' => 'https://example.com/storage/tasks/attachments/q1_report.xlsx',
                                'size_bytes' => 512000,
                            ],
                        ],
                    ],
                ],
                'creator' => [
                    'id' => 5,
                    'name' => 'Sara Ahmed',
                    'role' => 'admin',
                    'avatar' => 'https://example.com/storage/avatars/sara.jpg',
                    'profile_url' => 'https://example.com/dashboard/users/5',
                ],
                'feedbacks' => [
                    [
                        'id' => 1,
                        'user' => 'Sara Ahmed',
                        'user_id' => 5,
                        'img_url' => 'https://example.com/storage/avatars/sara.jpg',
                        'decision' => 'revision',
                        'text' => 'Please include last year\'s comparison figures.',
                        'time' => '2 days ago',
                    ],
                ],
                'activities' => [
                    [
                        'user' => 'Sara Ahmed',
                        'user_id' => 5,
                        'img_url' => 'https://example.com/storage/avatars/sara.jpg',
                        'action' => 'created this task',
                        'time' => '5 days ago',
                    ],
                    [
                        'user' => 'Karim Mansour',
                        'user_id' => 12,
                        'img_url' => null,
                        'action' => 'added a comment',
                        'time' => '3 days ago',
                    ],
                    [
                        'user' => 'Sara Ahmed',
                        'user_id' => 5,
                        'img_url' => 'https://example.com/storage/avatars/sara.jpg',
                        'action' => 'submitted feedback',
                        'time' => '2 days ago',
                    ],
                ],
                'activities_count' => 3,
                'feedbacks_count' => 1,
                'createdAt' => 1741564800,
            ],
            [
                'id' => 3,
                'title' => 'Pitch Deck Preparation',
                'description' => 'Create a compelling 10-slide pitch deck for demo day.',
                'program' => [
                    'id' => 4,
                    'title' => 'Winter Cohort 2024',
                ],
                'assignees' => [
                    [
                        'id' => 104,
                        'name' => 'EduBridge',
                        'status' => 'pending',
                        'lastUpdated' => '2025-03-05',
                        'logo' => null,
                        'profile_url' => 'https://example.com/dashboard/companyProfile/104',
                    ],
                    [
                        'id' => 105,
                        'name' => 'AgroSmart',
                        'status' => 'pending',
                        'lastUpdated' => '2025-03-05',
                        'logo' => 'logos/agrosmart.png',
                        'profile_url' => 'https://example.com/dashboard/companyProfile/105',
                    ],
                ],
                'priority' => 'High',
                'overallStatus' => 'pending',
                'dueDate' => '2025-04-10',
                'tags' => ['Pitch', 'Presentation'],
                'attachments' => [],
                'submissions' => [],
                'creator' => [
                    'id' => 7,
                    'name' => 'Lina Haddad',
                    'role' => 'mentor',
                    'avatar' => null,
                    'profile_url' => 'https://example.com/dashboard/users/7',
                ],
                'feedbacks' => [],
                'activities' => [
                    [
                        'user' => 'Lina Haddad',
                        'user_id' => 7,
                        'img_url' => null,
                        'action' => 'created this task',
                        'time' => '1 week ago',
                    ],
                ],
                'activities_count' => 1,
                'feedbacks_count' => 0,
                'createdAt' => 1741132800,
            ],
            [
                'id' => 4,
                'title' => 'Market Research Survey',
                'description' => 'Conduct a 50-response survey to validate product-market fit.',
                'program' => [
                    'id' => 4,
                    'title' => 'Winter Cohort 2024',
                ],
                'assignees' => [
                    [
                        'id' => 106,
                        'name' => 'MediLink',
                        'status' => 'completed',
                        'lastUpdated' => '2025-02-28',
                        'logo' => 'logos/medilink.png',
                        'profile_url' => 'https://example.com/dashboard/companyProfile/106',
                    ],
                ],
                'priority' => 'Low',
                'overallStatus' => 'completed',
                'dueDate' => '2025-02-28',
                'tags' => ['Research', 'Marketing'],
                'attachments' => [
                    [
                        'id' => md5('tasks/attachments/survey_template.pdf'),
                        'name' => 'survey_template.pdf',
                        'url' => 'https://example.com/storage/tasks/attachments/survey_template.pdf',
                        'size' => 102400,
                    ],
                ],
                'submissions' => [
                    [
                        'user' => 'Nour El-Din',
                        'user_id' => 15,
                        'img_url' => null,
                        'status' => 'pending_review',
                        'time' => '6 days ago',
                        'note' => null,
                        'files' => [
                            [
                                'name' => 'survey_results.pdf',
                                'url' => 'https://example.com/storage/tasks/attachments/survey_results.pdf',
                                'size_bytes' => 307200,
                            ],
                        ],
                    ],
                    [
                        'user' => 'Hana Youssef',
                        'user_id' => 16,
                        'img_url' => null,
                        'status' => 'pending_review',
                        'time' => '5 days ago',
                        'note' => null,
                        'files' => [
                            [
                                'name' => 'survey_analysis.xlsx',
                                'url' => 'https://example.com/storage/tasks/attachments/survey_analysis.xlsx',
                                'size_bytes' => 204800,
                            ],
                        ],
                    ],
                ],
                'creator' => [
                    'id' => 5,
                    'name' => 'Sara Ahmed',
                    'role' => 'admin',
                    'avatar' => 'https://example.com/storage/avatars/sara.jpg',
                    'profile_url' => 'https://example.com/dashboard/users/5',
                ],
                'feedbacks' => [
                    [
                        'id' => 2,
                        'user' => 'Sara Ahmed',
                        'user_id' => 5,
                        'img_url' => 'https://example.com/storage/avatars/sara.jpg',
                        'decision' => 'revision',
                        'text' => 'Please expand on the demographic breakdown section.',
                        'time' => '6 days ago',
                    ],
                    [
                        'id' => 3,
                        'user' => 'Sara Ahmed',
                        'user_id' => 5,
                        'img_url' => 'https://example.com/storage/avatars/sara.jpg',
                        'decision' => 'pass',
                        'text' => 'Great work! Survey data is thorough and well-presented.',
                        'time' => '5 days ago',
                    ],
                ],
                'activities' => [
                    [
                        'user' => 'Sara Ahmed',
                        'user_id' => 5,
                        'img_url' => 'https://example.com/storage/avatars/sara.jpg',
                        'action' => 'created this task',
                        'time' => '2 weeks ago',
                    ],
                    [
                        'user' => 'Nour El-Din',
                        'user_id' => 15,
                        'img_url' => null,
                        'action' => 'moved task from "pending" to "review"',
                        'time' => '6 days ago',
                    ],
                    [
                        'user' => 'Sara Ahmed',
                        'user_id' => 5,
                        'img_url' => 'https://example.com/storage/avatars/sara.jpg',
                        'action' => 'submitted feedback',
                        'time' => '6 days ago',
                    ],
                    [
                        'user' => 'Hana Youssef',
                        'user_id' => 16,
                        'img_url' => null,
                        'action' => 'added a comment',
                        'time' => '5 days ago',
                    ],
                    [
                        'user' => 'Sara Ahmed',
                        'user_id' => 5,
                        'img_url' => 'https://example.com/storage/avatars/sara.jpg',
                        'action' => 'submitted feedback',
                        'time' => '5 days ago',
                    ],
                    [
                        'user' => 'Sara Ahmed',
                        'user_id' => 5,
                        'img_url' => 'https://example.com/storage/avatars/sara.jpg',
                        'action' => 'updated status from "review" to "completed"',
                        'time' => '5 days ago',
                    ],
                ],
                'activities_count' => 6,
                'feedbacks_count' => 2,
                'createdAt' => 1740528000,
            ],
            [
                'id' => 5,
                'title' => 'Legal Documentation Review',
                'description' => 'Review and sign the incorporation documents and IP assignment agreements.',
                'program' => [
                    'id' => 3,
                    'title' => 'Spring Accelerator 2025',
                ],
                'assignees' => [
                    [
                        'id' => 107,
                        'name' => 'LawTech',
                        'status' => 'pending',
                        'lastUpdated' => '2025-03-18',
                        'logo' => null,
                        'profile_url' => 'https://example.com/dashboard/companyProfile/107',
                    ],
                ],
                'priority' => 'High',
                'overallStatus' => 'archived',
                'dueDate' => 'N/A',
                'tags' => ['Legal'],
                'attachments' => [],
                'submissions' => [],
                'creator' => [
                    'id' => 7,
                    'name' => 'Lina Haddad',
                    'role' => 'mentor',
                    'avatar' => null,
                    'profile_url' => 'https://example.com/dashboard/users/7',
                ],
                'feedbacks' => [],
                'activities' => [
                    [
                        'user' => 'Lina Haddad',
                        'user_id' => 7,
                        'img_url' => null,
                        'action' => 'created this task',
                        'time' => '3 weeks ago',
                    ],
                    [
                        'user' => 'Omar Khaled',
                        'user_id' => 8,
                        'img_url' => null,
                        'action' => 'moved task from "pending" to "archived"',
                        'time' => '1 week ago',
                    ],
                ],
                'activities_count' => 2,
                'feedbacks_count' => 0,
                'createdAt' => 1740182400,
            ],
        ];

        $programs = [
            (object)['id' => 3, 'title' => 'Spring Accelerator 2025'],
            (object)['id' => 4, 'title' => 'Winter Cohort 2024'],
            (object)['id' => 5, 'title' => 'Summer Bootcamp 2024'],
        ];

        $startups = [
            ['id' => 101, 'name' => 'TechNova',   'cycle' => 3, 'logo' => 'logos/technova.png'],
            ['id' => 102, 'name' => 'GreenSpark', 'cycle' => 3, 'logo' => null],
            ['id' => 103, 'name' => 'FinPath',    'cycle' => 3, 'logo' => 'logos/finpath.png'],
            ['id' => 107, 'name' => 'LawTech',    'cycle' => 3, 'logo' => null],
            ['id' => 104, 'name' => 'EduBridge',  'cycle' => 4, 'logo' => null],
            ['id' => 105, 'name' => 'AgroSmart',  'cycle' => 4, 'logo' => 'logos/agrosmart.png'],
            ['id' => 106, 'name' => 'MediLink',   'cycle' => 4, 'logo' => 'logos/medilink.png'],
            ['id' => 108, 'name' => 'HealthSync', 'cycle' => 5, 'logo' => 'logos/healthsync.png'],
            ['id' => 109, 'name' => 'CloudNine',  'cycle' => 5, 'logo' => null],
            ['id' => 110, 'name' => 'PayFlow',    'cycle' => 5, 'logo' => 'logos/payflow.png'],
        ];

        if (auth()->user() && auth()->user()->hasRole('Mentor')) {
            return view('admin.tasks.index',compact('tasks', 'programs', 'startups'));
        }
        
        return view('mentor.mentorshipSessions.index',compact('sessions','available_surveys','cycles'));
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
