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
use Illuminate\Support\Facades\Log;
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
                'message_content'  => trim(preg_replace('/📎[^\n]*/', '', strip_tags($invite->message_content))),
                'response_message' => '',
                'created_at'       => $invite->created_at->format('Y-m-d H:i:s'),
                'attachments'      => is_string($invite->attachments) ? json_decode($invite->attachments, true) : ($invite->attachments ?? []),
                'organizations'    => $organizations,
            ];
        })->values()->toArray();

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
            
        // Check if email settings are properly configured before sending invitation
        $setting = Settings::first();
        if (!$setting || $setting->enable_email != 1) {
            return response()->json(['success' => false, 'message' => __('admin.Email_Not_Enabled')], 203);
        }
        if (!$setting->host) {
            return response()->json(['success' => false, 'message' => __('admin.Email_Not_Configured')], 203);
        }
        
        
        // Retrieve the data from the request
        $data = $request->all();
       // dd($data);
        $request->validate([
            'invitee_name' => 'required|string|max:255',
            'invitee_email' => 'required|email',
            'invitation_scope' => 'required',
            'organizations' => 'nullable|array',
            'invitation_subject' => 'required',
            'invitation_message' => 'required',
            // 'attachments' => 'nullable|array',
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

        // Send email synchronously for immediate feedback
        try {
            Mail::to($invitation->email)->send(new InviteMentorMail($invitation));

            // Prepare invitation data for real-time table update
            $organizationMap = Organization::all()->keyBy('id');
            $orgIds = is_string($invitation->organization_ids) ? json_decode($invitation->organization_ids, true) : ($invitation->organization_ids ?? []);
            $organizations = collect($orgIds)->map(function ($id) use ($organizationMap) {
                return $organizationMap->has($id) ? [
                    'id' => $id,
                    'name' => $organizationMap[$id]['name_en']
                ] : null;
            })->filter()->values()->toArray();

            $invitationData = [
                'invitee_name'     => $invitation->name,
                'invitee_email'    => $invitation->email,
                'subject'          => $invitation->subject,
                'scope'            => $invitation->scope,
                'status'           => $invitation->status,
                'message_content'  => trim(preg_replace('/📎[^\n]*/', '', strip_tags($invitation->message_content))),
                'response_message' => '',
                'created_at'       => $invitation->created_at->format('Y-m-d H:i:s'),
                'attachments'      => is_string($invitation->attachments) ? json_decode($invitation->attachments, true) : ($invitation->attachments ?? []),
                'organizations'    => $organizations,
            ];

            return response()->json([
                'success' => true,
                'message' => 'Invitation sent successfully.',
                'invitation' => $invitationData
            ]);
        } catch (\Exception $e) {
            // Log the error and return failure response
            Log::error('Failed to send invitation email: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to send email. Please check email configuration.'], 500);
        }
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
