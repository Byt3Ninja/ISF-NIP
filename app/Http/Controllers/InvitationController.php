<?php

namespace App\Http\Controllers;

use App\Settings;
use App\Invitation;
use App\InviteMentor;
use App\Organization;
use App\Services\RichTextSanitizer;
use Illuminate\Support\Str;
use App\Support\NotificationClaim;
use App\Mail\InviteMentorMail;
use Illuminate\Http\UploadedFile;
use App\Jobs\SendInviteMentorEmail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Request;

class InvitationController extends Controller
{
    public function index(RichTextSanitizer $sanitizer)
    {
        $user = auth()->user();
        if($user->hasRole('admin')) {
            $inviteMentors = InviteMentor::all();
            $allOrganizations = Organization::all();
        }elseif($user->hasAnyRole(['organizationManager', 'subadmin'])) {
            $orgIds = $user->organizations_ids;
           
            $inviteMentors = InviteMentor::where(function ($q) use ($orgIds) {
                $q->where('scope', 'Public');
                $q->orWhere(function ($q2) use ($orgIds) {
                    foreach ($orgIds as $id) {
                        $q2->orWhereJsonContains('organization_ids', (string) $id);
                    }
                });
            })->get();
            $allOrganizations = Organization::whereIn('id', $orgIds)->get();
        }

        $organizationMap = collect($allOrganizations)->keyBy('id');

        $invitations = $inviteMentors->map(function ($invite) use ($organizationMap, $sanitizer) {
            // $orgIds = is_string($invite->organization_ids) ? json_decode($invite->organization_ids, true) : ($invite->organization_ids ?? []);
            $orgIds = [];

            if (is_string($invite->organization_ids)) {
                $orgIds = json_decode($invite->organization_ids, true) ?? [];
            } elseif (is_array($invite->organization_ids)) {
                $orgIds = $invite->organization_ids;
            }
            
            // جلب name لكل ID من الـ organizationMap
            $organizations = collect($orgIds)->map(function ($id) use ($organizationMap) {
                return $organizationMap->has($id) ? [
                    'id' => $id,
                    'name' => $organizationMap[$id]['name_en']
                ] : null;
            })->filter()->values()->toArray(); 
            
            return [
                'inviter' => [
                    'id'   => $invite->user->id,
                    'name' => $invite->user->name,
                    'img_url' => $invite->user->img_url,
                ],
                'invitee_name'     => $invite->name,
                'invitee_email'    => $invite->email,
                'subject'          => $invite->subject,
                'scope'            => $invite->scope,
                'status'           => $invite->status,
                'message_content'  => $sanitizer->clean($invite->message_content),
                'response_message' => '',
                'created_at'       => $invite->created_at->format('Y-m-d H:i:s'),
                'accepted_at'      => $invite->updated_at == $invite->created_at ? 'N/A' : $invite->updated_at->format('Y-m-d H:i:s'),
                'attachments'      => is_string($invite->attachments) ? json_decode($invite->attachments, true) : ($invite->attachments ?? []),
                'organizations'    => $organizations,
            ];
        })->values()->toArray();
       
        // dd($invitations);
        // $invitations = [
        //     [
        //         'invitee_name'      => 'Ahmed Hassan',
        //         'invitee_email'     => 'ahmed.hassan@example.com',
        //         'subject'             => 'Annual Tech Conference',
        //         'scope'             => 'Public',
        //         'status'            => 'Pending',
        //         'message_content'   => 'You are invited to join our annual tech conference.',
        //         'response_message'  => 'Hello Ahmed, I am excited to join the tech conference.',
        //         'created_at'    => '2025-01-10 10:00:00',
        //         'attachments'       => [],
        //     ],
        //     [
        //         'invitee_name'      => 'Sara Mohamed',
        //         'invitee_email'     => 'sara.mohamed@example.com',
        //         'subject'             => 'Company Strategy Meeting',
        //         'scope'             => 'Organization',
        //         'status'            => 'Accepted',
        //         'message_content'   => 'Please attend the company strategy meeting.',
        //         'response_message'  => 'I will be attending the strategy meeting.',
        //         'created_at'    => '2025-01-15 14:30:00',
        //         'attachments'       => ['agenda.pdf'],
        //     ],
        //     [
        //         'invitee_name'      => 'Omar Khaled',
        //         'invitee_email'     => 'omar.khaled@example.com',
        //         'subject'             => 'Product Launch Event',
        //         'scope'             => 'Public',
        //         'status'            => 'Declined',
        //         'message_content'   => 'Join us for the exciting product launch event.',
        //         'response_message'  => 'Sorry, I can\'t make it.',
        //         'created_at'    => '2025-01-18 09:00:00',
        //         'attachments'       => ['product_brochure.pdf', 'invitation_card.jpg'],
        //     ],
        //     [
        //         'invitee_name'      => 'Laila Mahmoud',
        //         'invitee_email'     => 'laila.mahmoud@example.com',
        //         'subject'             => 'HR Policy Workshop',
        //         'scope'             => 'Organization',
        //         'status'            => 'Pending',
        //         'message_content'   => 'Workshop on new HR policies.',
        //         'response_message'  => 'I will be attending the workshop.',
        //         'created_at'    => '2025-01-22 11:00:00',
        //         'attachments'       => [],
        //     ],
        //     [
        //         'invitee_name'      => 'Youssef Adel',
        //         'invitee_email'     => 'youssef.adel@example.com',
        //         'subject'             => 'Networking Breakfast',
        //         'scope'             => 'Public',
        //         'status'            => 'Accepted',
        //         'message_content'   => 'Network with industry leaders over breakfast.',
        //         'response_message'  => 'I will be attending the networking event.',
        //         'created_at'    => '2025-01-25 08:00:00',
        //         'attachments'       => ['networking_guide.pdf'],
        //     ],
        // ];

        $organizations = $allOrganizations->map(function($org) {
                            return [
                                'id' => $org->id,
                                'name' => $org->name_en, // لو عندك حقل name_en
                            ];
                        })->toArray();
        
                    //  dd($invitations);
        return view('mentors.invitations.index', compact('invitations', 'organizations'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    public function store(Request $request, RichTextSanitizer $sanitizer)
    {
           
        $setting = Settings::first();
                        if($setting->enable_email == 0){
                            return response()->json(['success' => 'false', 'message' => 'admin.Email_Not_Enabled'], 203);
                        }
                        if($setting->host == null){
                            return response()->json(['success' => 'false', 'message' => 'admin.Email_Not_Configured'], 203);
                        }
        
        
        // Retrieve the data from the request
        $data = $request->all();
        //    dd($data);
        $request->validate([
            'invitee_name' => 'required|string',
            'invitee_email' => 'required|email',
            'invitation_scope' => 'required',
            'organizations' => 'nullable|array',
            'invitation_subject' => 'nullable|string',
            'invitation_message' => 'nullable|string',
            'attachments' => 'nullable',
        ]);

        // Store the data in the database
        $name = strstr($data['invitee_email'], '@', true);

        $invitation = new InviteMentor;
        $invitation->email = $data['invitee_email'];
        $invitation->name = $data['invitee_name'] ?? $name;
        $invitation->user_id = auth()->user()->id;
        $invitation->status = 'Sent';
        $invitation->scope = $data['invitation_scope'];
        $invitation->organization_ids = $data['organizations'] ?? [];
        // Custom subject/message are optional -- when left blank, the mail
        // falls back to the platform's default "invited as a mentor" template.
        $invitation->subject = $data['invitation_subject'] ?: null;
        // Keep the editor's formatting (bold, colors, lists...) for the email,
        // minus anything unsafe and the uploaded-file links (sent as attachments).
        $invitation->message_content = $sanitizer->cleanWithoutAttachmentLinks($data['invitation_message'] ?? null);
        $invitation->attachments = $data['attachments'] ?? [];
        $invitation->save();

        // Claim the "send invitation email" step so a retry/duplicate call for this
        // invitation can never send it twice; a failed send gives the claim back.
        NotificationClaim::on('invite_mentor', ['id' => $invitation->id], 'email_sent_at')
            ->run(fn (NotificationClaim $claim) => dispatch((new SendInviteMentorEmail($invitation, auth()->user()->email))->withClaim($claim)));
        if($data['invitation_scope'] == 'Organization'){
            $allOrganizations = Organization::whereIn('id', $data['organizations'])->get();
            $organizationMap = collect($allOrganizations)->keyBy('id');
        }else{
            $organizationMap = [];
        }
       
        $invitation = InviteMentor::where('id', $invitation->id)->get()->map(function ($invite) use ($organizationMap, $sanitizer) {

         $orgIds = [];

            if (is_string($invite->organization_ids)) {
                $orgIds = json_decode($invite->organization_ids, true) ?? [];
            } elseif (is_array($invite->organization_ids)) {
                $orgIds = $invite->organization_ids;
            }
            
            // جلب name لكل ID من الـ organizationMap
            $organizations = collect($orgIds)->map(function ($id) use ($organizationMap) {
                return $organizationMap->has($id) ? [
                    'id' => $id,
                    'name' => $organizationMap[$id]['name_en']
                ] : null;
            })->filter()->values()->toArray(); 

            return [
                'invitee_name'     => $invite->name,
                'invitee_email'    => $invite->email,
                'subject'          => $invite->subject,
                'scope'            => $invite->scope,
                'status'           => $invite->status,
                'message_content'  => $sanitizer->clean($invite->message_content),
                'response_message' => $invite->response_message ?? null,
                'created_at'       => $invite->created_at->format('Y-m-d H:i:s'),
                'accepted_at'      => $invite->updated_at == $invite->created_at ? 'N/A' : $invite->updated_at->format('Y-m-d H:i:s'),
                'attachments'      => $invite->attachments ?? [],
                'organizations'    => $organizations,
            ];
        });

        return response()->json(['success' => true,
         'message' => 'Invitation sent successfully.',
        'data' => $invitation], 200);
    }

    private function uploadToUsersDisk(UploadedFile $file): array
    {
         $extension = $file->guessExtension();
         $fileManager = app(\App\Services\FileManagerService::class);
         $fileName = $fileManager->upload($file, '', 'UsersDiskInviteMentor', 'common');
         return [
             'file_name' => $fileName,
             'file_url'  => $fileManager->getUrl($fileName, 'UsersDiskInviteMentor'),
             'extension' => $extension,
             'size'      => $file->getSize(),
         ];
    }

    /**
     * دالة الكونترولر
     */
    public function updateFile(Request $request)
    {
        $request->validate([
            'file' => 'required|file|bail|mimes:pdf,jpg,jpeg,png,doc,docx,xls,xlsx,ppt,pptx|max:10240',
        ]);

        // استدعاء دالة الرفع
        $fileData = $this->uploadToUsersDisk($request->file('file'));
        
        return response()->json([
            'success' => true,
            'data' => $fileData
        ]);
    }

    private function deleteFromUsersDisk(string $fileName): bool
    {
        return app(\App\Services\FileManagerService::class)->delete($fileName, 'UsersDiskInviteMentor');
    }

    public function deleteFile(Request $request)
    {
        $request->validate([
            'file_name' => 'required|string',
        ]);

        $deleted = $this->deleteFromUsersDisk($request->file_name);

        if (!$deleted) {
            return response()->json([
                'success' => false,
                'message' => 'File not found or already deleted'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'File deleted successfully'
        ]);
    }

    public function showAttachment($path)
    {
        $safeName = basename($path);
        $basePath = storage_path('app/uploads/InviteMentor');
        $filePath = $basePath . DIRECTORY_SEPARATOR . $safeName;

        $realBase = realpath($basePath);
        $realFile = realpath($filePath);

        if (!$realBase || !$realFile || !str_starts_with($realFile, $realBase)) {
            abort(404);
        }

        return response()->file($realFile);
    }
}
