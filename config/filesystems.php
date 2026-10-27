<?php

// Base directory of the private disks for confidential uploads (see config/protected_files.php).
$protectedFilesRoot = rtrim(env('PROTECTED_FILES_ROOT', storage_path('protected')), '/');

return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default filesystem disk that should be used
    | by the framework. The "local" disk, as well as a variety of cloud
    | based disks are available to your application. Just store away!
    |
    */

    'default' => env('FILESYSTEM_DRIVER', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Default Cloud Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Many applications store files both locally and in the cloud. For this
    | reason, you may specify a default "cloud" driver here. This driver
    | will be bound as the Cloud disk implementation in the container.
    |
    */

    'cloud' => env('FILESYSTEM_CLOUD', 's3'),

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Here you may configure as many filesystem "disks" as you wish, and you
    | may even configure multiple disks of the same driver. Defaults have
    | been setup for each driver as an example of the required options.
    |
    | Supported Drivers: "local", "ftp", "sftp", "s3", "rackspace"
    |
    */

    'disks' => [

        'local' => [
            'driver' => 'local',
            'root' => storage_path('app'),
        ],

        //this will save the files in the public directory
        'public_uploads' => [
            'driver' => 'local',
            'root'   => public_path('191014'),
            'url' => env('APP_URL') . '/191014',
            'visibility' => 'public',
        ],
        'pitch_deck' => [
            'driver' => 'local',
            'root'   => public_path('pitch_decks'),
            'url' => env('APP_URL') . '/pitch_decks',
            'visibility' => 'public',
        ],
        'programTeams' => [
            'driver' => 'local',
            'root'   => storage_path('app/public/programs/teams'),
            'url' => env('APP_URL') . '/programs/teams',
            'visibility' => 'public',
        ],
        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => env('APP_URL') . '/storage',
            'visibility' => 'public',
        ],
        'logo_uploads' => [
            'driver' => 'local',
            'root' => public_path('images/logo'), // المسار المطلوب
            'url' => env('APP_URL') . '/images/logo',
            'visibility' => 'public',
        ],
        'Home_upload' => [
            'driver' => 'local',
            'root' => public_path('images/resource'), // المسار المطلوب
            'url' => env('APP_URL') . '/images/resource',
            'visibility' => 'public',
        ],
        'serviceProvider' => [
            'driver' => 'local',
            'root' => storage_path('app/uploads/service_provider'),
            'url' => env('APP_URL') . '/dashboard/uploads/service_provider',
            'visibility' => 'public',
        ],
        'UsersDisk' => [
            'driver' => 'local',
            'root' => storage_path('app/uploads'),
            'url' => env('APP_URL') . '/dashboard/uploads',
            'visibility' => 'private',
        ],
        'UsersDiskInviteMentor' => [
            'driver' => 'local',
            'root' => storage_path('app/uploads/InviteMentor'),
            'url' => env('APP_URL') . '/dashboard/uploads/InviteMentor',
            'visibility' => 'public',
            'throw' => true,
            'permissions' => [
                'file' => [
                    'public' => 0644,
                ],
            ],
        ],
        'TrainingDisk' => [
            'driver' => 'local',
            'root' => storage_path('app/training'),
            'url' => env('APP_URL') . '/dashboard/training',
            'visibility' => 'private',
        ],
        'PrivateDisk' => [
            'driver' => 'local',
            'root' => storage_path('app/users'),
            'url' => env('APP_URL') . '/dashboard/private',
            'visibility' => 'private',
        ],
        's3' => [
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => env('AWS_BUCKET'),
            'url' => env('AWS_URL'),
        ],
        'UsersLFM' => [
            'driver' => 'local',
            'root' => storage_path('app/uploads/files'),
            'url' => env('APP_URL') . '/dashboard/uploads',
            'visibility' => 'private',
        ],
        'UsersLFMPublic' => [
            'driver' => 'local',
            'root' => public_path('orgnizations'),
            'url' => env('APP_URL') . '/orgnizations',
            'visibility' => 'public',
        ],
        'mentorship_tasks' => [
            'driver' => 'local',
            'root' => storage_path('app/mentorship_tasks'),
            'url' => env('APP_URL') . '/dashboard/uploads',
            'visibility' => 'private',
        ],
        'cycle_custom_pages' => [
            'driver' => 'local',
            'root' => storage_path('app/cycle_custom_pages'),
            'url' => env('APP_URL') . '/cycle-custom-pages',
            'visibility' => 'private',
        ],
        'screening_guidelines' => [
            'driver' => 'local',
            'root' => storage_path('app/screening_guidelines'),
            'url' => env('APP_URL') . '/screening-guidelines',
            'visibility' => 'private',
        ],
        'companies' => [
            'driver' => 'local',
            'root' => storage_path('app/uploads/companies'),
            'url' => env('APP_URL') . 'api/uploads/companies',
            'visibility' => 'private',
        ],
        'QrCodePublic' => [
            'driver' => 'local',
            'root' => public_path('qrcodes'),
            'url' => env('APP_URL') . '/qrcodes',
            'visibility' => 'public',
        ],
        'document' => [
            'driver' => 'local',
            'root'   => public_path('documents'),
            'url' => env('APP_URL') . '/documents',
            'visibility' => 'public',
        ],

        'organizations' => [
            'driver' => 'local',
            'root' => storage_path('app/organizations'),
            'url' => env('APP_URL') . '/organizations/logo',
            'visibility' => 'private',
        ],

        'organization_landing' => [
            'driver' => 'local',
            'root' => storage_path('app/organization_landing'),
            'url' => env('APP_URL') . '/organization-wizard',
            'visibility' => 'public',
        ],

        'service_messages' => [
            'driver' => 'local',
            'root' => storage_path('app/service_messages'),
            'visibility' => 'private',
        ],

        'hackathon_shared' => [
            'driver'     => 'local',
            'root'       => storage_path('app/hackathon'),
            'visibility' => 'private',
        ],

        'importer' => [
            'driver' => 'local',
            'root' => storage_path('app/private/importer'),
            'throw' => false,
        ],

        /*
         * Confidential uploads. Private on purpose: no `url`, never web-served.
         * Files are reached only through ProtectedFileController, which
         * authorizes each request against the file's protected_files record.
         */
        'protected_legal' => [           // commercial register, tax card
            'driver' => 'local',
            'root' => $protectedFilesRoot . '/documents',
            'visibility' => 'private',
            'throw' => false,
        ],
        'protected_pitch' => [           // pitch deck, financial/business plan, supporting documents
            'driver' => 'local',
            'root' => $protectedFilesRoot . '/pitch_decks',
            'visibility' => 'private',
            'throw' => false,
        ],
        'protected_submissions' => [     // files attached to program applications
            'driver' => 'local',
            'root' => $protectedFilesRoot . '/formsubmissions',
            'visibility' => 'private',
            'throw' => false,
        ],
        'protected_files' => [           // milestones/, employees/, tasks/attachments/
            'driver' => 'local',
            'root' => $protectedFilesRoot . '/files',
            'visibility' => 'private',
            'throw' => false,
        ],
        'protected_guidelines' => [      // program guideline, policy and delegation PDFs
            'driver' => 'local',
            'root' => $protectedFilesRoot . '/guidelines',
            'visibility' => 'private',
            'throw' => false,
        ],
        'protected_trash' => [           // replaced or deleted files are moved here, never unlinked
            'driver' => 'local',
            'root' => $protectedFilesRoot . '/_trash',
            'visibility' => 'private',
            'throw' => false,
        ],
    ],

];
