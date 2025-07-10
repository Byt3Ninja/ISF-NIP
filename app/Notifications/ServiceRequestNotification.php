<?php

namespace App\Notifications;

use App\User;
use App\ServicesRequests;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class ServiceRequestNotification extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    protected $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    public function build()
    {
        $service = $this->request->service;
        $requester = $this->request->user;

        $adminEmail = User::role('Admin')->orderBy('id')->value('email'); 
        $orgManagerEmail = User::role('organizationManager')->orderBy('id')->value('email'); 
        $ccEmails = array_filter([$requester->email, $adminEmail, $orgManagerEmail]);
        Log::info('CC Emails: ' . implode(', ', $ccEmails). 
            ' | Requester Email: ' . $requester->email . 
            ' | Admin Email: ' . $adminEmail . 
            ' | Org Manager Email: ' . $orgManagerEmail);
        return $this->subject("[Service Request] New Request for {$service->name_en} from {$requester->first_name_en}")
                    ->cc($ccEmails)
                    ->markdown('emails.service_request', [
                        'service' => $service,
                        'requester' => $requester,
                        'request' => $this->request,
                    ]);
    }
}
