<?php

use App\Enums\ProtectedFileSet;

/*
|--------------------------------------------------------------------------
| Confidential uploads
|--------------------------------------------------------------------------
|
| Files in these sets live outside the web root and are only reachable through
| App\Http\Controllers\ProtectedFileController, which authorizes every request
| against the file's own record (protected_files) and logs it.
|
*/

return [

    // Base directory for every private disk below. It must be outside any web
    // root, kept across deploys, included in backups and writable by the web user.
    'root' => env('PROTECTED_FILES_ROOT', storage_path('protected')),

    // While existing files are still being moved, a stored path that has no
    // file record yet keeps its old public link. Switch off at cutover: from then
    // on a value without a record gets no link at all.
    'legacy_fallback' => env('PROTECTED_FILES_LEGACY_FALLBACK', true),

    // Web root the hand-moved upload folders (formsubmissions/) used to live in.
    'legacy_public_root' => env('PROTECTED_FILES_LEGACY_PUBLIC_ROOT', public_path()),

    // How the file body is sent: "stream" (PHP) or "x-accel" (nginx internal
    // redirect to `accel_location`, which must alias `root`).
    'delivery' => env('PROTECTED_FILES_DELIVERY', 'stream'),
    'accel_location' => env('PROTECTED_FILES_ACCEL_LOCATION', '/_protected'),

    // Shown in the browser only when the extension AND the sniffed content type
    // agree; everything else is sent as a download.
    'inline_types' => [
        'pdf'  => 'application/pdf',
        'jpg'  => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png'  => 'image/png',
        'gif'  => 'image/gif',
        'webp' => 'image/webp',
    ],

    /*
     * Per set: the private disk, where its files used to be served from
     * (`legacy_disk`, or `legacy_public_dir` for files moved into public/ by hand)
     * and the folder prefix old values sometimes carry.
     */
    'sets' => [
        ProtectedFileSet::COMPANY_LEGAL => [
            'disk' => 'protected_legal',
            'legacy_disk' => 'document',
            'legacy_prefixes' => ['documents/'],
        ],
        ProtectedFileSet::COMPANY_PITCH => [
            'disk' => 'protected_pitch',
            'legacy_disk' => 'pitch_deck',
            'legacy_prefixes' => ['pitch_decks/'],
        ],
        ProtectedFileSet::FORM_ANSWER => [
            'disk' => 'protected_submissions',
            'legacy_public_dir' => 'formsubmissions',
            'legacy_prefixes' => ['formsubmissions/'],
        ],
        ProtectedFileSet::MILESTONE_DOCUMENT => [
            'disk' => 'protected_files',
            'legacy_disk' => 'public',
            'legacy_prefixes' => ['storage/'],
        ],
        ProtectedFileSet::EMPLOYEE_DOCUMENT => [
            'disk' => 'protected_files',
            'legacy_disk' => 'public',
            'legacy_prefixes' => ['storage/'],
        ],
        ProtectedFileSet::TASK_ATTACHMENT => [
            'disk' => 'protected_files',
            'legacy_disk' => 'public',
            'legacy_prefixes' => ['storage/'],
        ],
        ProtectedFileSet::PROGRAM_GUIDELINE => [
            'disk' => 'protected_guidelines',
            'legacy_disk' => 'public_uploads',
            'legacy_prefixes' => ['191014/'],
        ],
    ],
];
