<?php

namespace App\Http\Controllers\Mentor;

use App\User;
use App\Label;
use Carbon\Carbon;
use App\MentorshipTask;
use Exception, Log, URL;
use App\MentorshipRequest;
use App\MentorshipTaskLog;
use App\MentorshipTaskFile;
use Illuminate\Http\Request;
use App\MentorshipTaskComment;
use App\Services\WhatsAppService;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Jobs\SendTaskNotificationJob;
use App\Notifications\TaskNotification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Notification;
use App\Http\Requests\Mentor\MentorshipTask\TaskRequest;

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

		$cycle = [
			'id' => 84,
			'title' => 'Alumni Program',
		];

		$tasks = [
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
						'profile_url' => '/dashboard/companyProfile/10',
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

			// Task 2: Your Original Design Task (Enhanced)
			[
				'id' => 2,
				'title' => 'Mobile App Onboarding UX',
				'description' => '<p>Create a seamless onboarding experience with illustrations and animations.</p><p><strong>Requirements:</strong></p><ul><li>Max 3 screens</li><li>Include "Skip" option</li><li>Use existing brand colors</li></ul>',
				'due_date' => '2025-04-20',
				'priority' => 'Medium',
				'status' => 'in_progress',
				'created_at' => '2025-04-05T08:00:00Z',
				'updated_at' => '2025-04-06T12:30:00Z',

				'creator' => [
					'id' => 3,
					'name' => 'Sarah Lee',
					'img_url' => '/vuexy/assets/img/avatars/3.png',
					'profile_url' => '/dashboard/show/UserProfile/3',
				],

				'assignee' => [
					'startup' => [
						'id' => 11,
						'name' => 'AppFlow Labs',
						'img_url' => '/vuexy/assets/img/icons/brands/chrome.png',
						'profile_url' => '/dashboard/companyProfile/11',
					],
					'founder' => [
						'id' => 4,
						'name' => 'David Kim',
						'img_url' => '/vuexy/assets/img/avatars/4.png',
						'profile_url' => '/dashboard/show/UserProfile/4',
					],
				],

				'attachments' => [
					[
						'id' => 110,
						'name' => 'onboarding_wireframes.fig',
						'url' => '/storage/designs/onboarding_wireframes.fig',
						'mime_type' => 'application/octet-stream',
						'size_bytes' => 409600,
						'uploaded_at' => '2025-04-05T09:00:00Z',
					],
				],

				'activities' => [
					[
						'user' => 'Sarah Lee',
						'user_id' => 3,
						'img_url' => '/vuexy/assets/img/avatars/3.png',
						'action' => 'created this task',
						'type' => 'created',
						'time' => 'Apr 5, 8:00 AM',
						'created_at' => '2025-04-05T08:00:00Z',
					],
					[
						'user' => 'David Kim',
						'user_id' => 4,
						'img_url' => '/vuexy/assets/img/avatars/4.png',
						'action' => 'moved task to In Progress',
						'type' => 'moved',
						'time' => 'Apr 5, 10:00 AM',
						'created_at' => '2025-04-05T10:00:00Z',
					],
				],

				'comments' => [
					[
						'id' => 11,
						'user' => 'David Kim',
						'user_id' => 4,
						'img_url' => '/vuexy/assets/img/avatars/4.png',
						'text' => '<p>Let’s simplify step one. I think users might get overwhelmed by the text.</p>',
						'time' => 'Apr 5, 10:30 AM',
						'created_at' => '2025-04-05T10:30:00Z',
						'updated_at' => null,
					],
				],

				'feedbacks' => [
					[
						'id' => 31,
						'user' => 'Sarah Lee',
						'user_id' => 3,
						'img_url' => '/vuexy/assets/img/avatars/3.png',
						'decision' => 'revision',
						'text' => 'First screen is too dense, reduce text.',
						'time' => 'Apr 6, 11:00 AM',
						'created_at' => '2025-04-06T11:00:00Z',
					],
					[
						'id' => 32,
						'user' => 'David Kim',
						'user_id' => 4,
						'img_url' => '/vuexy/assets/img/avatars/4.png',
						'decision' => 'approved',
						'text' => 'Animations look great. Will proceed with building the assets.',
						'time' => 'Apr 6, 12:30 PM',
						'created_at' => '2025-04-06T12:30:00Z',
					],
				],

				'submissions' => [
					[
						'id' => 21,
						'user' => 'David Kim',
						'user_id' => 4,
						'img_url' => '/vuexy/assets/img/avatars/4.png',
						'note' => 'Initial onboarding screens. Need feedback on the Lottie animations.',
						'status' => 'pending_review',
						'time' => 'Apr 6, 9:00 AM',
						'created_at' => '2025-04-06T09:00:00Z',
						'files' => [
							[
								'id' => 301,
								'name' => 'onboarding_v1.fig',
								'url' => '/storage/designs/onboarding_v1.fig',
								'mime_type' => 'application/octet-stream',
								'size_bytes' => 500000,
							],
						],
					],
				],

				'comments_count' => 1,
				'feedbacks_count' => 2,
				'submissions_count' => 1,
				'attachments_count' => 1,
			],

			// Task 3: Your Original Tech Task (Enhanced)
			[
				'id' => 3,
				'title' => 'API Performance Optimization',
				'description' => '<p>Analyze slow endpoints and reduce response time below 200ms.</p><p>Specifically look at the <code>/api/v1/reports</code> endpoint. We are seeing spikes during peak hours.</p>',
				'due_date' => '2025-04-10',
				'priority' => 'High',
				'status' => 'review',
				'created_at' => '2025-04-02T12:00:00Z',
				'updated_at' => '2025-04-06T16:00:00Z',

				'creator' => [
					'id' => 5,
					'name' => 'Michael Brown',
					'img_url' => '/vuexy/assets/img/avatars/5.png',
					'profile_url' => '/dashboard/show/UserProfile/5',
				],

				'assignee' => [
					'startup' => [
						'id' => 12,
						'name' => 'ScaleTech',
						'img_url' => '/vuexy/assets/img/icons/brands/firefox.png',
						'profile_url' => '/dashboard/companyProfile/12',
					],
					'founder' => [
						'id' => 6,
						'name' => 'Emma Wilson',
						'img_url' => '/vuexy/assets/img/avatars/6.png',
						'profile_url' => '/dashboard/show/UserProfile/6',
					],
				],

				'attachments' => [
					[
						'id' => 120,
						'name' => 'performance_log.txt',
						'url' => '/storage/logs/performance_log.txt',
						'mime_type' => 'text/plain',
						'size_bytes' => 20480,
						'uploaded_at' => '2025-04-02T12:10:00Z',
					],
				],

				'activities' => [
					[
						'user' => 'Michael Brown',
						'user_id' => 5,
						'img_url' => '/vuexy/assets/img/avatars/5.png',
						'action' => 'uploaded logs',
						'type' => 'upload',
						'time' => 'Apr 2, 12:10 PM',
						'created_at' => '2025-04-02T12:10:00Z',
					],
				],

				'comments' => [
					[
						'id' => 12,
						'user' => 'Emma Wilson',
						'user_id' => 6,
						'img_url' => '/vuexy/assets/img/avatars/6.png',
						'text' => '<p>Consider Redis caching. It seems like we are hitting the DB on every single request for static lookup tables.</p>',
						'time' => 'Apr 6, 2:00 PM',
						'created_at' => '2025-04-06T14:00:00Z',
						'updated_at' => null,
					],
				],

				'feedbacks' => [
					[
						'id' => 33,
						'user' => 'Emma Wilson',
						'user_id' => 6,
						'img_url' => '/vuexy/assets/img/avatars/6.png',
						'decision' => 'approved',
						'text' => 'Performance verified on staging. Averaging 140ms response time now.',
						'time' => 'Apr 6, 3:30 PM',
						'created_at' => '2025-04-06T15:30:00Z',
					],
				],

				'submissions' => [
					[
						'id' => 22,
						'user' => 'Michael Brown',
						'user_id' => 5,
						'img_url' => '/vuexy/assets/img/avatars/5.png',
						'note' => 'Optimized queries and implemented Redis layer for static data.',
						'status' => 'pending_review',
						'time' => 'Apr 6, 3:00 PM',
						'created_at' => '2025-04-06T15:00:00Z',
						'files' => [],
					],
				],

				'comments_count' => 1,
				'feedbacks_count' => 1,
				'submissions_count' => 1,
				'attachments_count' => 1,
			],

			// Task 4: Original Marketing Task (Filled out missing variables)
			[
				'id' => 4,
				'title' => 'Marketing Landing Page Copy',
				'description' => '<p>Write persuasive copy for the new Spring Campaign landing page.</p><ul><li>Focus on "time-saving" benefits</li><li>Include 2 variations for A/B testing the H1 header</li></ul>',
				'due_date' => '2025-04-18',
				'priority' => 'Low',
				'status' => 'completed',
				'created_at' => '2025-03-30T10:00:00Z',
				'updated_at' => '2025-04-04T09:00:00Z',

				'creator' => [
					'id' => 7,
					'name' => 'Olivia Martinez',
					'img_url' => '/vuexy/assets/img/avatars/7.png',
					'profile_url' => '/dashboard/show/UserProfile/7',
				],

				'assignee' => [
					'startup' => [
						'id' => 13,
						'name' => 'CopyCrafters',
						'img_url' => '/vuexy/assets/img/icons/brands/mailchimp.png',
						'profile_url' => '/dashboard/companyProfile/13',
					],
					'founder' => [
						'id' => 8,
						'name' => 'James Anderson',
						'img_url' => '/vuexy/assets/img/avatars/8.png',
						'profile_url' => '/dashboard/show/UserProfile/8',
					],
				],

				'attachments' => [],
				'activities' => [
					[
						'user' => 'Olivia Martinez',
						'user_id' => 7,
						'img_url' => '/vuexy/assets/img/avatars/7.png',
						'action' => 'marked task as Completed',
						'type' => 'completed',
						'time' => 'Apr 4, 9:00 AM',
						'created_at' => '2025-04-04T09:00:00Z',
					],
				],
				'comments' => [],
				'feedbacks' => [
					[
						'id' => 35,
						'user' => 'James Anderson',
						'user_id' => 8,
						'img_url' => '/vuexy/assets/img/avatars/8.png',
						'decision' => 'approved',
						'text' => 'Ready to publish. Excellent tone of voice.',
						'time' => 'Apr 3, 2:15 PM',
						'created_at' => '2025-04-03T14:15:00Z',
					],
				],
				'submissions' => [
					[
						'id' => 23,
						'user' => 'James Anderson',
						'user_id' => 8,
						'img_url' => '/vuexy/assets/img/avatars/8.png',
						'note' => 'Attached the final word doc with 2 H1 variations.',
						'status' => 'approved',
						'time' => 'Apr 3, 11:00 AM',
						'created_at' => '2025-04-03T11:00:00Z',
						'files' => [
							[
								'id' => 302,
								'name' => 'landing_page_copy_vFinal.docx',
								'url' => '/storage/docs/landing_page_copy_vFinal.docx',
								'mime_type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
								'size_bytes' => 153600,
							],
						],
					],
				],

				'comments_count' => 0,
				'feedbacks_count' => 1,
				'submissions_count' => 1,
				'attachments_count' => 0,
			],

			// Task 5: Original DB Task (Filled out missing variables)
			[
				'id' => 5,
				'title' => 'Database Schema Refactor',
				'description' => '<p>Normalize tables for the user management module.</p><p>Extract user preferences into a separate <code>user_settings</code> table to prevent row bloat in the main <code>users</code> table.</p>',
				'due_date' => '2025-04-25',
				'priority' => 'High',
				'status' => 'pending',
				'created_at' => '2025-04-06T07:30:00Z',
				'updated_at' => '2025-04-07T10:00:00Z',

				'creator' => [
					'id' => 9,
					'name' => 'Daniel White',
					'img_url' => '/vuexy/assets/img/avatars/9.png',
					'profile_url' => '/dashboard/show/UserProfile/9',
				],

				'assignee' => [
					'startup' => [
						'id' => 14,
						'name' => 'DataFlow Inc.',
						'img_url' => '/vuexy/assets/img/icons/brands/asana.png',
						'profile_url' => '/dashboard/companyProfile/14',
					],
					'founder' => [
						'id' => 10,
						'name' => 'Sophia Taylor',
						'img_url' => '/vuexy/assets/img/avatars/10.png',
						'profile_url' => '/dashboard/show/UserProfile/10',
					],
				],

				'attachments' => [
					[
						'id' => 125,
						'name' => 'current_schema.png',
						'url' => '/storage/designs/current_schema.png',
						'mime_type' => 'image/png',
						'size_bytes' => 204800,
						'uploaded_at' => '2025-04-06T07:35:00Z',
					],
				],
				'activities' => [],
				'comments' => [],
				'feedbacks' => [
					[
						'id' => 37,
						'user' => 'Sophia Taylor',
						'user_id' => 10,
						'img_url' => '/vuexy/assets/img/avatars/10.png',
						'decision' => 'rejected',
						'text' => 'Needs redesign. The proposed ERD breaks existing joins in the billing service.',
						'time' => 'Apr 7, 9:00 AM',
						'created_at' => '2025-04-07T09:00:00Z',
					],
				],
				'submissions' => [
					[
						'id' => 24,
						'user' => 'Sophia Taylor',
						'user_id' => 10,
						'img_url' => '/vuexy/assets/img/avatars/10.png',
						'note' => 'Here is my initial schema proposal.',
						'status' => 'rejected',
						'time' => 'Apr 6, 4:00 PM',
						'created_at' => '2025-04-06T16:00:00Z',
						'files' => [
							[
								'id' => 303,
								'name' => 'proposed_erd_v1.pdf',
								'url' => '/storage/docs/proposed_erd_v1.pdf',
								'mime_type' => 'application/pdf',
								'size_bytes' => 1024000,
							],
						],
					],
				],

				'comments_count' => 0,
				'feedbacks_count' => 1,
				'submissions_count' => 1,
				'attachments_count' => 1,
			],

			// Task 6: New - DevOps / Urgent Task
			[
				'id' => 6,
				'title' => 'Migrate to AWS RDS',
				'description' => '<p>Migrate our primary PostgreSQL database from the self-hosted EC2 instance to <strong>AWS RDS</strong> for automated backups and Multi-AZ support.</p><p><em>Note: Zero downtime is expected. Ensure replica synchronization before flipping the DNS switch.</em></p>',
				'due_date' => '2025-04-08',
				'priority' => 'Urgent',
				'status' => 'in_progress',
				'created_at' => '2025-04-04T08:00:00Z',
				'updated_at' => '2025-04-07T11:00:00Z',

				'creator' => [
					'id' => 11,
					'name' => 'Chris Martin',
					'img_url' => '/vuexy/assets/img/avatars/11.png',
					'profile_url' => '/dashboard/show/UserProfile/11',
				],

				'assignee' => [
					'startup' => [
						'id' => 15,
						'name' => 'CloudOps Solutions',
						'img_url' => '/vuexy/assets/img/icons/brands/aws.png',
						'profile_url' => '/dashboard/companyProfile/15',
					],
					'founder' => [
						'id' => 12,
						'name' => 'Kevin Hart',
						'img_url' => '/vuexy/assets/img/avatars/12.png',
						'profile_url' => '/dashboard/show/UserProfile/12',
					],
				],

				'attachments' => [],

				'activities' => [
					[
						'user' => 'Kevin Hart',
						'user_id' => 12,
						'img_url' => '/vuexy/assets/img/avatars/12.png',
						'action' => 'started the migration process',
						'type' => 'progress',
						'time' => 'Apr 7, 10:00 AM',
						'created_at' => '2025-04-07T10:00:00Z',
					],
				],

				'comments' => [
					[
						'id' => 13,
						'user' => 'Chris Martin',
						'user_id' => 11,
						'img_url' => '/vuexy/assets/img/avatars/11.png',
						'text' => '<p>Please confirm once the initial data dump is restored on RDS.</p>',
						'time' => 'Apr 7, 10:15 AM',
						'created_at' => '2025-04-07T10:15:00Z',
						'updated_at' => null,
					],
					[
						'id' => 14,
						'user' => 'Kevin Hart',
						'user_id' => 12,
						'img_url' => '/vuexy/assets/img/avatars/12.png',
						'text' => '<p>Dump restored. Setting up logical replication now.</p>',
						'time' => 'Apr 7, 11:00 AM',
						'created_at' => '2025-04-07T11:00:00Z',
						'updated_at' => null,
					]
				],

				'feedbacks' => [],
				'submissions' => [],

				'comments_count' => 2,
				'feedbacks_count' => 0,
				'submissions_count' => 0,
				'attachments_count' => 0,
			],

			// Task 7: New - Frontend Bug Fix
			[
				'id' => 7,
				'title' => 'Fix Mobile Navigation Glitch',
				'description' => '<p>The hamburger menu overlaps with the company logo on devices smaller than 375px width (e.g., iPhone SE).</p><p>Please adjust the flexbox wrapping to ensure it behaves correctly.</p>',
				'due_date' => '2025-04-12',
				'priority' => 'Medium',
				'status' => 'progress',
				'created_at' => '2025-04-07T09:00:00Z',
				'updated_at' => '2025-04-07T09:00:00Z',

				'creator' => [
					'id' => 3,
					'name' => 'Sarah Lee',
					'img_url' => '/vuexy/assets/img/avatars/3.png',
					'profile_url' => '/dashboard/show/UserProfile/3',
				],

				'assignee' => [],

				'attachments' => [
					[
						'id' => 130,
						'name' => 'nav_bug_screenshot.png',
						'url' => '/storage/issues/nav_bug_screenshot.png',
						'mime_type' => 'image/png',
						'size_bytes' => 150000,
						'uploaded_at' => '2025-04-07T09:05:00Z',
					],
				],

				'activities' => [
					[
						'user' => 'Sarah Lee',
						'user_id' => 3,
						'img_url' => '/vuexy/assets/img/avatars/3.png',
						'action' => 'created this bug ticket',
						'type' => 'created',
						'time' => 'Apr 7, 9:00 AM',
						'created_at' => '2025-04-07T09:00:00Z',
					],
				],

				'comments' => [],
				'feedbacks' => [],
				'submissions' => [],

				'comments_count' => 0,
				'feedbacks_count' => 0,
				'submissions_count' => 0,
				'attachments_count' => 1,
			],

			// Task 8: New - Security Audit
			[
				'id' => 8,
				'title' => 'Annual Penetration Testing',
				'description' => '<p>Conduct a gray-box penetration test on the primary web application.</p><ul><li>Check for OWASP Top 10 vulnerabilities</li><li>Audit 3rd-party dependencies</li><li>Provide a risk mitigation report</li></ul>',
				'due_date' => '2025-05-01',
				'priority' => 'High',
				'status' => 'review',
				'created_at' => '2025-03-20T08:00:00Z',
				'updated_at' => '2025-04-07T14:00:00Z',

				'creator' => [
					'id' => 11,
					'name' => 'Chris Martin',
					'img_url' => '/vuexy/assets/img/avatars/11.png',
					'profile_url' => '/dashboard/show/UserProfile/11',
				],

				'assignee' => [
					'startup' => [
						'id' => 16,
						'name' => 'CyberSec Shield',
						'img_url' => '/vuexy/assets/img/icons/brands/github.png',
						'profile_url' => '/dashboard/companyProfile/16',
					],
					'founder' => [
						'id' => 14,
						'name' => 'Nina Dobrev',
						'img_url' => '/vuexy/assets/img/avatars/14.png',
						'profile_url' => '/dashboard/show/UserProfile/14',
					],
				],

				'attachments' => [
					[
						'id' => 140,
						'name' => 'scoping_document.pdf',
						'url' => '/storage/security/scoping_document.pdf',
						'mime_type' => 'application/pdf',
						'size_bytes' => 850000,
						'uploaded_at' => '2025-03-20T08:30:00Z',
					]
				],

				'activities' => [
					[
						'user' => 'Nina Dobrev',
						'user_id' => 14,
						'img_url' => '/vuexy/assets/img/avatars/14.png',
						'action' => 'submitted the final vulnerability report',
						'type' => 'submit',
						'time' => 'Apr 7, 1:45 PM',
						'created_at' => '2025-04-07T13:45:00Z',
					]
				],

				'comments' => [
					[
						'id' => 15,
						'user' => 'Nina Dobrev',
						'user_id' => 14,
						'img_url' => '/vuexy/assets/img/avatars/14.png',
						'text' => '<p>Attached the final report. Only found a few low-severity issues related to missing security headers.</p>',
						'time' => 'Apr 7, 1:50 PM',
						'created_at' => '2025-04-07T13:50:00Z',
						'updated_at' => null,
					]
				],

				'feedbacks' => [],

				'submissions' => [
					[
						'id' => 25,
						'user' => 'Nina Dobrev',
						'user_id' => 14,
						'img_url' => '/vuexy/assets/img/avatars/14.png',
						'note' => 'Pen-Test Final Report 2025',
						'status' => 'pending_review',
						'time' => 'Apr 7, 1:45 PM',
						'created_at' => '2025-04-07T13:45:00Z',
						'files' => [
							[
								'id' => 304,
								'name' => 'Pentest_Report_April2025.pdf',
								'url' => '/storage/security/Pentest_Report_April2025.pdf',
								'mime_type' => 'application/pdf',
								'size_bytes' => 2500000,
							]
						],
					]
				],

				'comments_count' => 1,
				'feedbacks_count' => 0,
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
				'show'=> auth()->user()->hasAnyRole(['admin', 'subadmin', 'organizationManager']),
			],
			[
				'target' => '#vt-feedback',
				'icon'   => 'ti-star',
				'label'  => __('admin.feedback'),
				'badge'  => ['id' => 'vt-feedback-count', 'class' => 'bg-label-primary'],
				'active' => false,
				'show'   => true,
			],
			[
				'target' => '#vt-comments',
				'icon'   => 'ti-message',
				'label'  => __('admin.Comments'),
				'badge'  => ['id' => 'vt-comment-count', 'class' => 'bg-label-danger bg-glow'],
				'active' => false,
				'show'   => true,
			],
			[
				'target' => '#vt-activity',
				'icon'   => 'ti-activity',
				'label'  => __('admin.Activities'),
				'badge'  => ['id' => 'vt-activity-count', 'class' => 'bg-label-info bg-glow'],
				'active' => false,
				'show'   => true,
			],
		];

		return view('kanban.index', compact('cycle', 'tasks', 'boards', 'priorityConfig', 'jsPriorityMap', 'jsStatusMap', 'offcanvasTabs'));
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
				'task' => $task,
				'message' => $message,
				'action' => 'add'
			];

			SendTaskNotificationJob::dispatch($mentor, $startup, $task, $data);
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
			$data = [
				'task' => $task,
				'message' => $message,
				'action' => 'move'
			];
			Notification::send([$mentor, $startup], new TaskNotification($data));

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
    			'task' => $task,
    			'message' => $message,
    			'action' => 'update'
			];
			Notification::send([$task->mentor, $task->user], new TaskNotification($data));

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
			$instance = Storage::disk('mentorship_tasks');
			$sessionDir = (string) $session;
			$targetDir  = $sessionDir . '/' . $card;

			if( !$instance->directoryExists( $sessionDir ) ) { $instance->makeDirectory($sessionDir); }
			if( !$instance->directoryExists($targetDir) ) { $instance->makeDirectory($targetDir); }

			$filesToAdd = [];
			foreach ($request->attachments as $attachment) {
				$check_file = $instance->missing($targetDir . '/' . $attachment->getClientOriginalName());
				if (!$check_file) {
					$counter = 1;
					do {
						$file_name = str_replace('.' . $attachment->getClientOriginalExtension(), '', $attachment->getClientOriginalName()) . '-' . $counter++ . '.' . $attachment->getClientOriginalExtension();
						$check_file = $instance->missing($targetDir . '/' .  $file_name);
					} while (!$check_file);
				} else {
					$file_name = $attachment->getClientOriginalName();
				}
				$file_name = strtolower(str_replace(' ', '-', $file_name));
				$filesToAdd[] = [
					'mentorship_request_id' => $task->mentorship_request_id,
					'mentorship_task_id' => $task->id,
					'user_id' => $user->id,
					'file_name' => $attachment->getClientOriginalName(),
					'file' => $file_name,
				];
				$path = $instance->putFileAs($targetDir, $attachment, $file_name);
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
			$instance = Storage::disk('mentorship_tasks');
			$session = $file->mentorship_request_id;
			$card = $file->mentorship_task_id;
			$sessionDir = (string) $session;
			$targetDir  = $sessionDir . '/' . $card;

			$check_file = $instance->missing($targetDir . '/' . $file->file);
			if( !$check_file ) { $instance->delete($targetDir . '/' . $file->file); }
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
