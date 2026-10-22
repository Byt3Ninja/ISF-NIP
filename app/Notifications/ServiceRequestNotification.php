<?php

namespace App\Notifications;

use App\Traits\ConfiguresSmtp;
use App\User;
use App\ServicesRequests;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Throwable;

class ServiceRequestNotification extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels, ConfiguresSmtp;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     *
     * @var int
     */
    public $backoff = 60;

    /**
     * Service request instance.
     *
     * @var ServicesRequests
     */
    protected $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    public function build()
    {
        $service = $this->request->service;
        $requester = $this->request->user;
        $org = $requester->organization;
        $orgData = $this->emailBrandingData();

        $adminEmail = User::role('Admin')->orderBy('id')->value('email');
        $orgManagerEmail = User::role('organizationManager')->orderBy('id')->value('email');
        $ccEmails = array_filter([$requester->email, $adminEmail, $orgManagerEmail]);
        Log::info('CC Emails: ' . implode(', ', $ccEmails).
            ' | Requester Email: ' . $requester->email .
            ' | Admin Email: ' . $adminEmail .
            ' | Org Manager Email: ' . $orgManagerEmail);
        return $this->subject("[Service Request] New Request for {$service->name_en} from {$requester->first_name_en}")
                    ->cc($ccEmails)
                    ->markdown('emails.service_request', array_merge([
                        'service' => $service,
                        'requester' => $requester,
                        'request' => $this->request,
                    ], $orgData));
    }

    /**
     * Handle a job failure.
     *
     * @param Throwable $exception
     * @return void
     */
    public function failed(Throwable $exception): void
    {
        Log::error('ServiceRequestNotification failed after all retries', [
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
            'request_id' => $this->request->id ?? null,
        ]);
    }
}
