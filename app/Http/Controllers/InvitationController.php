<?php

namespace App\Http\Controllers;

use App\Settings;
use App\Invitation;
use App\InviteMentor;
use App\Organization;
use Illuminate\Support\Str;
use App\Mail\InviteMentorMail;
use Illuminate\Http\UploadedFile;
use App\Jobs\SendInviteMentorEmail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Request;

class InvitationController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        if($user->hasRole('admin')) {
            $inviteMentors = InviteMentor::all();
            $allOrganizations = Organization::all();
        }elseif($user->hasAnyRole(['organizationManager', 'subadmin'])) {
            $orgIds = $user->organizations_ids;
            $inviteMentors = InviteMentor::where('user_id', $user->id)->get();
            $allOrganizations = Organization::whereIn('id', $orgIds)->get();
        }

        $organizationMap = collect($allOrganizations)->keyBy('id');
        $invitations = $inviteMentors->map(function ($invite) use ($organizationMap) {
            $orgIds = is_string($invite->organization_ids) ? json_decode($invite->organization_ids, true) : ($invite->organization_ids ?? []);

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
                'message_content'  => strip_tags($invite->message_content),
                'response_message' => '',
                'created_at'       => $invite->created_at->format('Y-m-d H:i:s'),
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
        
                        
        return view('mentors.invitations.index', compact('invitations', 'organizations'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    public function store(Request $request)
    {
            
        // هنعمل ان لو حاجة فى الsmtp مش موجودة فيرجع للادمن عشان يراجع على 
        $setting = Settings::first();
                        if($setting->enable_email == 0){
                            return response()->json(['success' => 'false', 'message' => 'admin.Email_Not_Enabled'], 203);
                        }
                        if($setting->host == null){
                            return response()->json(['success' => 'false', 'message' => 'admin.Email_Not_Configured'], 203);
                        }
        
        
        // Retrieve the data from the request
        $data = $request->all();
       // dd($data);
        $request->validate([
            'invitee_name' => 'nullable|string',
            'invitee_email' => 'required|email',
            'invitation_scope' => 'required',
            'organizations' => 'nullable|array',
            'invitation_subject' => 'required',
            'invitation_message' => 'required',
            'attachments' => 'nullable|array',
        ]);

        // Store the data in the database
        $name = strstr($data['invitee_email'], '@', true);

        $invitation = new InviteMentor;
        $invitation->email = $data['invitee_email'];
        $invitation->name = $data['invitee_name'] ?? $name;
        $invitation->user_id = auth()->user()->id;
        $invitation->status = 'Sended';
        $invitation->scope = $data['invitation_scope'];
        $invitation->organization_ids = $data['organizations'] ?? [];
        $invitation->subject = $data['invitation_subject'];
        $invitation->message_content = $data['invitation_message'];
        $invitation->attachments = $data['attachments'] ?? [];
        $invitation->save();

        dispatch(new SendInviteMentorEmail($invitation));
        
        return response()->json(['success' => true, 'message' => 'Invitation sent successfully.']);
    }

    private function uploadToUsersDisk(UploadedFile $file): array
    {
         $extension = $file->guessExtension();
        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeName = Str::slug($originalName);
        $date = now()->format('Y-m-d_H-i-s');
        $fileName = $safeName . '_' . $date . '.' . $extension;
        Storage::disk('UsersDiskInviteMentor')->putFileAs('', $file, $fileName);
        return [
            'file_name' => $fileName,
            'file_url'  => Storage::disk('UsersDiskInviteMentor')->url($fileName),
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
            'file' => 'required|file|bail|mimes:pdf,jpg,jpeg,png,doc,docx,xls,xlsx|max:10240',
        ]);

        // استدعاء دالة الرفع
        $fileData = $this->uploadToUsersDisk($request->file('file'));
        
        return response()->json([
            'success' => true,
            'data' => $fileData
        ]);
    }
}
