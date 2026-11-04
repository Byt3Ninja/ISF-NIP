@extends('layouts.admin')

@section('vendorstyle')
<link rel="stylesheet" href="/vuexy/assets/vendor/libs/form-validation/form-validation.css" />
<link rel="stylesheet" href="/vuexy/assets/vendor/libs/quill/typography.css" />
<link rel="stylesheet" href="/vuexy/assets/vendor/libs/quill/katex.css" />
<link rel="stylesheet" href="/vuexy/assets/vendor/libs/quill/editor.css" />
@stop

@php
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
      'profile_url' => '/show/UserProfile/1',
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
        'profile_url' => '/show/UserProfile/2',
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
      'profile_url' => '/show/UserProfile/3',
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
        'profile_url' => '/show/UserProfile/4',
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
      'profile_url' => '/show/UserProfile/5',
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
        'profile_url' => '/show/UserProfile/6',
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
      'profile_url' => '/show/UserProfile/7',
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
        'profile_url' => '/show/UserProfile/8',
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
      'profile_url' => '/show/UserProfile/9',
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
        'profile_url' => '/show/UserProfile/10',
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
      'profile_url' => '/show/UserProfile/11',
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
        'profile_url' => '/show/UserProfile/12',
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
      'profile_url' => '/show/UserProfile/3',
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
      'profile_url' => '/show/UserProfile/11',
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
        'profile_url' => '/show/UserProfile/14',
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
    'show'   => $_user->ableTo('tasks.update', $permissions),
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
@endphp

@section('style')
<style>
  /* Minimal custom CSS. Relying on Bootstrap 5 utilities for layout */
  .cursor-grab { cursor: grab; }
  .cursor-grab:active { cursor: grabbing; }
  .sortable-ghost { opacity: 0.4; background-color: var(--bs-secondary-bg) !important; border: 1px dashed var(--bs-secondary); }

  .ql-editor {
    min-height: 150px !important;
    max-height: 150px !important;
  }

  #vt-comment-editor .ql-editor {
    min-height: 80px !important;
    max-height: 120px !important;
  }

  #vt-dropzone.dz-dragover {
    border-color: var(--bs-primary) !important;
    background-color: rgba(var(--bs-primary-rgb), .05);
  }
  #vt-dropzone-queue .dz-queue-item .dz-progress-bar {
    height: 3px;
    border-radius: 2px;
    background: var(--bs-primary);
    transition: width .3s;
  }
</style>
@stop

@section('content')
  <div>
    {{-- TOOLBAR --}}
    <div class="card shadow-xs mb-6">
      <div class="card-body">
        <div class="d-flex flex-wrap justify-content-between align-items-md-center gap-3">
          <div class="d-flex align-items-center gap-2">
            <div class="avatar">
              <div class="avatar-initial rounded-circle">
                <i class="ti ti-rocket ti-md text-primary"></i>
              </div>
            </div>
            <h5 class="card-title mb-0">{{ $cycle['title'] }}</h5>
          </div>
          <div class="d-flex align-items-center gap-3">
            <div class="input-group input-group-merge">
              <span class="input-group-text">
                <i class="ti ti-search text-muted"></i>
              </span>
              <input type="text" id="kanbanSearch" class="form-control" placeholder="{{ __('admin.Search') }}..." />
            </div>
            @if ($_user->ableTo('tasks.store', $permissions))
            <button class="btn btn-primary text-nowrap px-10" type="button" data-bs-toggle="offcanvas" data-bs-target="#addTaskOffcanvas">
              <i class="ti ti-plus me-2"></i> {{ __('admin.Add_Task') }}
            </button>
            @endif
          </div>  
        </div>
      </div>
    </div>

    {{-- KANBAN BOARDS --}}
    <div class="d-flex align-items-start gap-4 overflow-x-auto pb-3" id="kanbanWrapper">
      @foreach ($boards as $statusKey => $board)
        @php $boardTasks = array_values(array_filter($tasks, fn($t) => $t['status'] === $statusKey)); @endphp
        <div class="bg-body border rounded p-2 flex-shrink-0 kanban-board" data-board="{{ $statusKey }}" style="width: 300px;">
          <header class="d-flex align-items-center gap-2 px-2 py-2 mb-2">
            <span class="badge {{ $board['color'] }} rounded-pill board-count">{{ count($boardTasks) }}</span>
            <h6 class="mb-0 fw-semibold">{{ $board['label'] }}</h6>
          </header>

          <main class="kanban-drag d-flex flex-column gap-2" style="min-height: 60px;">
            @forelse ($boardTasks as $task)
              @php
                $p = $priorityConfig[$task['priority']] ?? $priorityConfig['Medium'];
                $r = $task['creator'] ?? ['name' => 'User'];
                $ri = strtoupper(substr($r['name'], 0, 2));
                $attCount = count($task['attachments'] ?? []);
                $commentCount = count($task['comments'] ?? []);
              @endphp
              <div class="card shadow-sm border-0 cursor-grab kanban-item" data-task-id="{{ $task['id'] }}" data-search-text="{{ strtolower($task['title']) }}">
                <div class="card-body p-3">
                  <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="badge {{ $p['class'] }} rounded-pill" style="font-size: 0.7rem;">
                      <span class="d-inline-block rounded-circle me-1 {{ $p['dot'] }}" style="width: 6px; height: 6px;"></span>{{ $p['label'] }}
                    </span>
                    <div class="d-flex align-items-center gap-1">
                      <button class="btn btn-sm btn-icon btn-text-secondary rounded-circle view-task-btn" type="button" data-task-id="{{ $task['id'] }}" title="{{ __('admin.View') }}"><i class="ti ti-eye ti-md text-dark"></i></button>
                      @if ($_user->ableTo('tasks.update', $permissions))
                      <button class="btn btn-sm btn-icon btn-text-secondary rounded-circle edit-task-btn" type="button" data-task-id="{{ $task['id'] }}" title="{{ __('admin.Edit') }}"><i class="ti ti-edit ti-md text-dark"></i></button>
                      @endif
                      <button class="btn btn-sm btn-icon btn-text-secondary rounded-circle archive-task-btn" type="button" data-task-id="{{ $task['id'] }}" title="{{ __('admin.archive_task') }}"><i class="ti ti-archive ti-md text-dark"></i></button>
                    </div>
                  </div>
                  <h6 class="mb-1 text-truncate">{{ $task['title'] }}</h6>
                  <div class="text-muted small mb-2 line-clamp-2">{!! strip_tags($task['description']) !!}</div>
                  <div class="d-flex align-items-center justify-content-between small text-muted mt-3">
                    <span class="d-flex align-items-center gap-2">
                      @if($attCount > 0)
                        <span class="d-flex align-items-center" title="{{ $attCount }} Attachments">
                          <i class="ti ti-paperclip ti-xs me-1"></i>{{ $attCount }}
                        </span>
                      @endif
                      @if($commentCount > 0)
                        <span class="d-flex align-items-center" title="{{ $commentCount }} Comments">
                          <i class="ti ti-message ti-xs me-1"></i>{{ $commentCount }}
                        </span>
                      @endif
                      <span class="d-flex align-items-center">
                        <i class="ti ti-calendar ti-xs me-1"></i> {{ $task['due_date'] }}
                      </span>
                    </span>
                    <div data-bs-toggle="tooltip" data-bs-placement="top" title="{{ $r['name'] }}">
                      @if (!empty($r['img_url']))
                        <img src="{{ $r['img_url'] }}" alt="avatar" class="rounded-circle object-fit-cover" style="width: 24px; height: 24px;" />
                      @else
                        <span class="badge bg-label-primary rounded-circle d-flex align-items-center justify-content-center p-0" style="width: 24px; height: 24px;">{{ $ri }}</span>
                      @endif
                    </div>
                  </div>
                </div>
              </div>
            @empty
              <p class="text-muted small text-center py-3 m-0" data-empty-board>{{ __('admin.No_Tasks') }}</p>
            @endforelse
          </main>
        </div>
      @endforeach
    </div>

    {{-- VIEW TASK OFFCANVAS --}}
    <div class="offcanvas offcanvas-end w-px-{{ $_user->ableTo('tasks.update', $permissions) ? '700' : '600' }}" tabindex="-1" id="viewTaskOffcanvas">
      <div class="offcanvas-header border-bottom">
        <h5 class="offcanvas-title"><i class="ti ti-eye me-1 text-primary"></i> {{ __('admin.View_Task') }}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
      </div>
      <div class="offcanvas-body pt-0 d-flex flex-column">
        
        {{-- TAB NAVIGATION GENERATED DYNAMICALLY --}}
        <ul class="nav nav-tabs pb-6 mb-4 rounded-0 mt-3" role="tablist">
          @foreach($offcanvasTabs as $tab)
            @if($tab['show'])
              <li class="nav-item">
                <button class="nav-link {{ $tab['active'] ? 'active' : '' }}" data-bs-toggle="tab" data-bs-target="{{ $tab['target'] }}" role="tab">
                  <i class="ti {{ $tab['icon'] }} ti-xs me-1"></i> {{ $tab['label'] }}
                  @if(isset($tab['badge']))
                    <span class="badge badge-center {{ $tab['badge']['class'] }} rounded-pill ms-1" id="{{ $tab['badge']['id'] }}">0</span>
                  @endif
                </button>
              </li>
            @endif
          @endforeach
        </ul>

        <div class="tab-content p-0 flex-grow-1 overflow-auto">
          {{-- DETAILS TAB --}}
          <div class="tab-pane fade show active" id="vt-details" role="tabpanel">
            <div class="mb-3">
              <span class="text-muted d-block mb-1">{{ __('admin.Title') }}</span>
              <h6 id="vt-title" class="mb-0 text-dark"></h6>
            </div>
            <div class="mb-3">
              <span class="text-muted d-block mb-1">{{ __('admin.Description') }}</span>
              <div id="vt-description" class="text-dark bg-body border rounded p-3" style="min-height: 80px;"></div>
            </div>
            <div class="row g-3 mb-4">
              <div class="col-6">
                <span class="text-muted d-block mb-1">{{ __('admin.Due_Date') }}</span>
                <p id="vt-due-date" class="mb-0 text-dark"></p>
              </div>
              <div class="col-6">
                <span class="text-muted d-block mb-1">{{ __('admin.Priority') }}</span>
                <div id="vt-priority"></div>
              </div>
              <div class="col-6">
                <span class="text-muted d-block mb-1">{{ __('admin.Status') }}</span>
                <div id="vt-status"></div>
              </div>
            </div>

            @role('Admin')
            <div class="mb-4 d-none" id="vt-assignee-wrapper">
              <span class="text-muted small d-block mb-2">{{ __('admin.Assignee') ?? 'Assignee' }}</span>
              <div class="border rounded p-3 bg-body d-flex flex-column gap-3">
                <div class="d-flex align-items-center gap-3" id="vt-assignee-startup">
                  <div id="vt-assignee-startup-avatar"></div>
                  <div>
                    <span class="small">{{ __('admin.Startup') ?? 'Startup' }}</span>
                    <div id="vt-assignee-startup-name" class="fw-semibold text-dark"></div>
                  </div>
                </div>
                <div class="d-flex align-items-center gap-3 border-top pt-3" id="vt-assignee-founder">
                  <div id="vt-assignee-founder-avatar"></div>
                  <div>
                    <span class="small">{{ __('admin.Founder') ?? 'Founder' }}</span>
                    <div id="vt-assignee-founder-name" class="fw-semibold text-dark"></div>
                  </div>
                </div>
              </div>
            </div>
            @endrole

            <div class="mb-4">
              <span class="text-muted small d-block mb-1">{{ $_user->ableTo('tasks.update', $permissions) ? __('admin.provided_resources') : __('admin.Attachments') }}</span>
              <div id="vt-attachments-list" class="d-flex flex-column gap-2 small mb-3"></div>

              {{-- Dropzone Upload --}}
              <div class="border-top pt-3">
                <label class="form-label small mb-2">
                  {{ $_user->ableTo('tasks.update', $permissions) ? __('admin.upload_attachments') : __('admin.your_submission') }}
                  <span class="text-muted">({{ __('admin.max') }} 5 {{ __('admin.files') ?? 'files' }}, 10MB {{ __('admin.each') }})</span>
                </label>

                <div id="vt-dropzone"
                  class="bg-light-subtle border border-dashed rounded p-4 text-center position-relative"
                  style="cursor:pointer; border-color: var(--bs-border-color) !important; transition: border-color .2s, background .2s;">
                  <input type="file" id="vt-dropzone-input" class="position-absolute top-0 start-0 w-100 h-100 opacity-0" style="cursor:pointer;"
                    accept="image/*,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-powerpoint,application/vnd.openxmlformats-officedocument.presentationml.presentation,application/zip,application/x-zip-compressed,text/plain,text/csv"
                    multiple />
                  <i class="ti ti-cloud-upload ti-lg text-primary mb-2 d-block"></i>
                  <p class="mb-0 small">{{ __('admin.dropzone') }}</p>
                  <p class="mb-0 text-muted small">{{ __('admin.max_5_files') }}</p>
                </div>

                <div id="vt-dropzone-queue" class="d-flex flex-column gap-2 mt-2"></div>

                <div class="d-flex justify-content-between align-items-center mt-2">
                  <div class="invalid-feedback d-block" id="vt-upload-error"></div>
                  <button type="button" class="btn btn-sm btn-primary text-nowrap ms-auto d-none" id="vt-dropzone-upload-btn">
                    <i class="ti ti-upload ti-xs me-1"></i>{{ __('admin.upload_all') }}
                  </button>
                </div>
                <input type="hidden" id="vt-upload-btn-task-id" value="" />
              </div>
            </div>
          </div>

          {{-- SUBMISSIONS TAB --}}
          <div class="tab-pane fade" id="vt-submissions" role="tabpanel">
            <div id="vt-submissions-list" class="small pe-2"></div>
          </div>

           {{-- FEEDBACK TAB --}}
          <div class="tab-pane fade" id="vt-feedback" role="tabpanel">
            <div id="vt-feedback-list" class="small pe-2"></div>

            @if ($_user->ableTo('tasks.update', $permissions))
            <div class="mt-3" id="vt-feedback-form-wrapper">
              <h5 class="fw-semibold small mb-3">{{ __('admin.add_feedback') }}</h5>

              <div class="mb-3">
                <label class="form-label small">{{ __('admin.feedback_decision') }} <span class="text-danger">*</span></label>
                <div class="row g-3">
                  <div class="col-6">
                    <div class="form-check custom-option custom-option-basic checked" id="vt-fb-pass-wrapper">
                      <label class="form-check-label custom-option-content" for="vt-fb-pass">
                        <input name="vt_feedback_decision" class="form-check-input" type="radio" value="pass" id="vt-fb-pass" checked>
                        <span class="custom-option-header"><span class="h6 mb-0">{{ __('admin.pass_task') }}</span></span>
                        <span class="custom-option-body"><small>{{ __('admin.mark_as_completed') }}</small></span>
                      </label>
                    </div>
                  </div>
                  <div class="col-6">
                    <div class="form-check custom-option custom-option-basic" id="vt-fb-revision-wrapper">
                      <label class="form-check-label custom-option-content" for="vt-fb-revision">
                        <input name="vt_feedback_decision" class="form-check-input" type="radio" value="revision" id="vt-fb-revision">
                        <span class="custom-option-header"><span class="h6 mb-0">{{ __('admin.needs_revision') }}</span></span>
                        <span class="custom-option-body"><small>{{ __('admin.request_changes') }}</small></span>
                      </label>
                    </div>
                  </div>
                </div>
              </div>

              <div class="mb-3">
                <label class="form-label small" for="vt-fb-note">{{ __('admin.feedback_note') }}</label>
                <textarea id="vt-fb-note" class="form-control" rows="3" placeholder="{{ __('admin.feedback_note_placeholder') }}"></textarea>
              </div>

              <button type="button" class="btn btn-primary" id="vt-submit-feedback-btn">
                <i class="ti ti-send ti-xs me-1"></i> {{ __('admin.submit_feedback') }}
              </button>
            </div>
            @endif
          </div>
          
          {{-- COMMENTS TAB --}}
          <div class="tab-pane fade" id="vt-comments" role="tabpanel">
            <div id="vt-comments-list" class="small mb-4 pe-2"></div>
            
            <div class="border-top pt-3 mt-auto">
              <label class="form-label small fw-medium">{{ __('admin.Add comment') }}</label>
              <div id="vt-comment-editor" class="border rounded-top"></div>
              <div class="border border-top-0 rounded-bottom p-1 d-flex justify-content-between align-items-center">
                <div id="vt-comment-toolbar">
                  <span class="ql-formats m-0">
                    <button class="ql-bold"></button>
                    <button class="ql-italic"></button>
                    <button class="ql-underline"></button>
                  </span>
                </div>
                <button class="btn btn-sm btn-primary" type="button" id="vt-send-comment-btn">{{ __('admin.send') }}</button>
              </div>
            </div>
          </div>

          {{-- ACTIVITY TAB --}}
          <div class="tab-pane fade" id="vt-activity" role="tabpanel">
            <div id="vt-activities-list" class="small pe-2"></div>
          </div>
        </div>

        <div class="d-flex gap-2 pt-3 border-top mt-3">
          @if ($_user->ableTo('tasks.update', $permissions))
          <button type="button" class="btn btn-outline-primary" id="vt-edit-trigger-btn">
            <i class="ti ti-edit ti-xs me-1"></i> {{ __('admin.Edit_Task') }}
          </button>
          @endif
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="offcanvas">{{ __('admin.Close') }}</button>
        </div>
      </div>
    </div>

    {{-- ADD TASK OFFCANVAS --}}
    <div class="offcanvas offcanvas-end" tabindex="-1" id="addTaskOffcanvas" style="width: 480px;">
      <div class="offcanvas-header border-bottom">
        <h5 class="offcanvas-title"><i class="ti ti-plus me-1 text-primary"></i> {{ __('admin.add_new_task') }}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
      </div>
      <div class="offcanvas-body">
        <form class="d-flex flex-column h-100" id="addTaskForm" novalidate enctype="multipart/form-data">
          <div class="flex-grow-1 overflow-auto">
            <div class="mb-3">
              <label class="form-label">{{ __('admin.Title') }} <span class="text-danger">*</span></label>
              <input type="text" name="title" class="form-control" placeholder="{{ __('admin.Title') }}" />
            </div>
            <div class="mb-3">
              <label class="form-label">{{ __('admin.Description') }}</label>
              <div class="border border-bottom-0 rounded-top p-1"><div id="add-desc-toolbar"><span class="ql-formats"><button class="ql-bold"></button><button class="ql-italic"></button><button class="ql-list" value="ordered"></button><button class="ql-list" value="bullet"></button></span></div></div>
              <div id="add-desc-editor" class="border rounded-top"></div>
            </div>
            <div class="mb-3">
              <label class="form-label">{{ __('admin.Due_Date') }}</label>
              <input type="text" id="add-due-date" name="due_date" class="form-control" placeholder="YYYY-MM-DD" />
            </div>
            <div class="row g-3 mb-3">
              <div class="col-6">
                <label class="form-label">{{ __('admin.Priority') }} <span class="text-danger">*</span></label>
                <select name="priority" id="add-priority" class="select2 form-select">
                  <option value="">{{ __('admin.Select') }}</option>
                  @foreach($priorityConfig as $k => $p)
                    <option value="{{ $k }}">{{ $p['label'] }}</option>
                  @endforeach
                </select>
              </div>
              <div class="col-6">
                <label class="form-label">{{ __('admin.Status') }} <span class="text-danger">*</span></label>
                <select name="status" id="add-status" class="select2 form-select">
                  <option value="">{{ __('admin.Select') }}</option>
                  @foreach($boards as $k => $b)
                    <option value="{{ $k }}">{{ $b['label'] }}</option>
                  @endforeach
                </select>
              </div>
            </div>

            <div class="mb-4">
              <label class="form-label d-flex justify-content-between">
                <span>{{ __('admin.Attachments') }}</span>
                <span class="text-muted small">{{ __('admin.Max_Each_5_Image_Pdf') }}</span>
              </label>
              <input type="file" name="attachments[]" id="add-attachments" class="form-control" accept="image/jpeg,image/png,image/gif,application/pdf" multiple />
              <div class="invalid-feedback" id="add-file-error"></div>
            </div>

          </div>
          <div class="d-flex gap-2 mt-auto">
            <button type="submit" class="btn btn-primary" id="add-submit-btn">{{ __('admin.Create') }}</button>
            <button type="button" class="btn btn-label-secondary" data-bs-dismiss="offcanvas">{{ __('admin.Cancel') }}</button>
          </div>
        </form>
      </div>
    </div>

    {{-- EDIT TASK OFFCANVAS --}}
    <div class="offcanvas offcanvas-end" tabindex="-1" id="editTaskOffcanvas" style="width: 480px;">
      <div class="offcanvas-header border-bottom">
        <h5 class="offcanvas-title"><i class="ti ti-edit me-1 text-warning"></i> {{ __('admin.Edit_Task') }}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
      </div>
      <div class="offcanvas-body">
        <form id="editTaskForm" class="d-flex flex-column h-100" novalidate enctype="multipart/form-data">
          <input type="hidden" id="et-id" name="id" />
          <input type="hidden" name="_method" value="PUT" />

          <div class="flex-grow-1 overflow-auto">
            <div class="mb-3">
              <label class="form-label">{{ __('admin.Title') }} <span class="text-danger">*</span></label>
              <input type="text" id="et-title" name="title" class="form-control" />
            </div>
            <div class="mb-3">
              <label class="form-label">{{ __('admin.Description') }}</label>
              <div class="border border-bottom-0 rounded-top p-1"><div id="et-desc-toolbar"><span class="ql-formats"><button class="ql-bold"></button><button class="ql-italic"></button><button class="ql-list" value="ordered"></button><button class="ql-list" value="bullet"></button></span></div></div>
              <div id="et-desc-editor" class="border rounded-top"></div>
            </div>
            <div class="mb-3">
              <label class="form-label">{{ __('admin.Due_Date') }}</label>
              <input type="text" id="et-due-date" name="due_date" class="form-control" />
            </div>
            <div class="row g-3 mb-3">
              <div class="col-6">
                <label class="form-label">{{ __('admin.Priority') }} <span class="text-danger">*</span></label>
                <select name="priority" id="et-priority" class="select2 form-select">
                  @foreach($priorityConfig as $k => $p)
                    <option value="{{ $k }}">{{ $p['label'] }}</option>
                  @endforeach
                </select>
              </div>
              <div class="col-6">
                <label class="form-label">{{ __('admin.Status') }} <span class="text-danger">*</span></label>
                <select name="status" id="et-status" class="select2 form-select">
                  @foreach($boards as $k => $b)
                    <option value="{{ $k }}">{{ $b['label'] }}</option>
                  @endforeach
                </select>
              </div>
            </div>

            <div class="mb-4">
              <label class="form-label">{{ __('admin.Attachments') }}</label>

              <div id="et-current-attachments-wrapper" class="mb-3 d-none">
                <span class="d-block small text-muted mb-2">{{ __('admin.current_files') }}</span>
                <div id="et-current-attachments-list" class="d-flex flex-column gap-2 small"></div>
              </div>

              <label class="form-label d-flex justify-content-between">
                <span>{{ __('admin.Upload') }}</span>
                <span class="text-muted small">{{ __('admin.max') }} 5MB</span>
              </label>
              <input type="file" name="attachments[]" id="et-attachments" class="form-control" accept="image/jpeg,image/png,image/gif,application/pdf" multiple />
              <div class="invalid-feedback" id="et-file-error"></div>
            </div>
          </div>

          <div class="d-flex gap-2 mt-auto">
            <button type="submit" class="btn btn-primary" id="et-submit-btn">{{ __('admin.Update') }}</button>
            <button type="button" class="btn btn-label-secondary" data-bs-dismiss="offcanvas">{{ __('admin.Cancel') }}</button>
          </div>
        </form>
      </div>
    </div>

  </div>
@stop

@section('scripts')
<script src="/vuexy/assets/vendor/libs/sortablejs/sortable.js"></script>
<script src="/vuexy/assets/vendor/libs/quill/quill.js"></script>
<script src="/vuexy/assets/vendor/libs/form-validation/popular.js"></script>
<script src="/vuexy/assets/vendor/libs/form-validation/bootstrap5.js"></script>
<script src="/vuexy/assets/vendor/libs/form-validation/auto-focus.js"></script>
<script src="/js/kanban/kanban-utils.js"></script>
<script src="/js/kanban/kanban-renderers.js"></script>
<script src="/js/kanban/kanban-board.js"></script>
<script src="/js/kanban/kanban-dropzone.js"></script>
<script src="/js/kanban/kanban-tasks.js"></script>
@stop

@section('pagescripts')
<script>
$(document).ready(function () {
  'use strict';

  // ─── Server-side config injected into JS ────────────────────────────────
  window.KanbanConfig = {
    isAbleToEdit:    @json($_user->ableTo('tasks.store', $permissions)),
    isAdmin:         @json(auth()->user()->hasRole('Admin')),
    currentUserName: @json(auth()->user()->name ?? 'You'),
    profileBaseUrl:  @json(url("/show/UserProfile")),

    priorityMap: @json($jsPriorityMap),
    statusMap:   @json($jsStatusMap),

    routes: {
      storeTask:        '{{ route("tasks.store") }}',
      updateTask:       '{{ route("tasks.update", ":id") }}',
      moveTask:         '{{ route("tasks.move", ["taskId" => ":id", "status" => ":status"]) }}',
      uploadAttachment: '{{ route("tasks.upload_attachment", ":taskId") }}',
      deleteAttachment: '{{ route("tasks.delete_attachment", ["taskId" => ":taskId", "filename" => ":filename"]) }}',
      addComment:       '{{ route("tasks.add_comment", ":id") }}',
      addFeedback:      '{{ route("tasks.add_feedback", ":id") }}',
    },

    labels: {
      // General
      processing:         @json(__('admin.processing')),
      storedSuccessfully: @json(__('admin.StoredSuccessfully')),
      saveFailed:         @json(__('admin.save_failed')),
      updateFailed:       @json(__('admin.update_failed')),
      deleteFailed:       @json(__('admin.delete_failed')),
      requiredField:      @json(__('admin.Required_Field')),
      noTasks:            @json(__('admin.No_Tasks')),
      you:                @json(__('admin.you')),
      justNow:            @json(__('admin.just_now')),
      // Buttons
      view:               @json(__('admin.View')),
      edit:               @json(__('admin.Edit')),
      create:             @json(__('admin.Create')),
      update:             @json(__('admin.Update')),
      send:               @json(__('admin.send')),
      remove:             @json(__('admin.Remove')),
      download:           @json(__('admin.Download')),
      yes:                @json(__('admin.DYes')),
      no:                 @json(__('admin.DNo')),
      // Tasks
      archiveTask:        @json(__('admin.archive_task')),
      archiveTaskConfirm: @json(__('admin.archive_task_confirm')),
      reopenTask:         @json(__('admin.reopen_task')),
      reopenTaskConfirm:  @json(__('admin.reopen_task_confirm')),
      taskArchived:       @json(__('admin.task_archived')),
      taskReopened:       @json(__('admin.task_reopened')),
      moveTaskFailed:     @json(__('admin.move_task_failed')),
      // Attachments
      noAttachments:            @json(__('admin.no_attachments')),
      deleteAttachment:         @json(__('admin.delete_attachment')),
      recover:                  @json(__('admin.Recover')),
      attachmentDeletedSuccess: @json(__('admin.attachment_deleted_success')),
      attachmentUploadedSuccess:@json(__('admin.attachment_uploaded_success')),
      uploading:                @json(__('admin.uploading')),
      uploadAll:                @json(__('admin.upload_all')),
      uploaded:                 @json(__('admin.uploaded')),
      failed:                   @json(__('admin.Failed')),
      maxFilesReached:          @json(__('admin.max_5_files_reached')),
      invalidFileType:          @json(__('admin.Invalid_task_file_type')),
      exceedsSizeLimit:         @json(__('admin.file_exceeds_10_limit')),
      uploadFailedRetry:        @json(__('admin.upload_failed_retry')),
      // Comments
      noComments:     @json(__('admin.no_comments')),
      commentSuccess: @json(__('admin.comment_success')),
      commentFailed:  @json(__('admin.comment_failed')),
      requiredField:  @json(__('admin.Required_Field')),
      // Feedback
      noFeedback:     @json(__('admin.no_feedback')),
      feedbackSuccess:@json(__('admin.feedback_success')),
      feedbackFailed: @json(__('admin.feedback_failed')),
      submitFeedback: @json(__('admin.submit_feedback')),
      passTask:       @json(__('admin.pass_task')),
      needsRevision:  @json(__('admin.needs_revision')),
      // Submissions
      noSubmissions:  @json(__('admin.no_submissions')),
      pendingReview:  @json(__('admin.pending_review')),
      approved:       @json(__('admin.Approved')),
      rejected:       @json(__('admin.Rejected')),
      // Activities
      noActivities:   @json(__('admin.no_recent_activities')),
    },
  };

  // ─── Bootstrap ──────────────────────────────────────────────────────────
  $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

  let tasks     = @json($tasks);
  const program = @json($cycle);

  // Plugins
  const addDescQ   = new Quill('#add-desc-editor',   { theme: 'snow', modules: { toolbar: '#add-desc-toolbar' } });
  const etDescQ    = new Quill('#et-desc-editor',    { theme: 'snow', modules: { toolbar: '#et-desc-toolbar' } });
  const vtCommentQ = new Quill('#vt-comment-editor', { theme: 'snow', modules: { toolbar: '#vt-comment-toolbar' } });

  $('#add-due-date, #et-due-date').flatpickr({ dateFormat: 'Y-m-d' });
  $('#add-priority, #add-status').select2({ dropdownParent: $('#addTaskOffcanvas') });
  $('#et-priority, #et-status').select2({ dropdownParent: $('#editTaskOffcanvas') });

  // Form validation
  const fvConfig = {
    fields: {
      title:    { validators: { notEmpty: { message: @json(__('admin.Required_Field')) } } },
      priority: { validators: { notEmpty: { message: @json(__('admin.Required_Field')) } } },
      status:   { validators: { notEmpty: { message: @json(__('admin.Required_Field')) } } },
    },
    plugins: {
      trigger:    new FormValidation.plugins.Trigger(),
      bootstrap5: new FormValidation.plugins.Bootstrap5({ rowSelector: '.mb-3, .col-6' }),
    },
  };
  const addFv = FormValidation.formValidation(document.getElementById('addTaskForm'), fvConfig);
  const etFv  = FormValidation.formValidation(document.getElementById('editTaskForm'), fvConfig);

  // Bind change event listeners compactly
  ['add', 'et'].forEach(prefix => {
    [`#${prefix}-priority`, `#${prefix}-status`].forEach(sel => {
      const field = sel.split('-')[1];
      const fv = prefix === 'add' ? addFv : etFv;
      $(sel).on('change', () => fv.revalidateField(field));
    });
    
    $(`#${prefix}-attachments`).on('change', function () {
      validateFiles($(this), `#${prefix}-file-error`);
    });
  });

  // Init modules
  initKanbanDragDrop(tasks);
  initKanbanSearch();
  initDropzone(tasks);
  initKanbanTasks(tasks, program, addDescQ, etDescQ, vtCommentQ, addFv, etFv);
});
</script>
@stop
