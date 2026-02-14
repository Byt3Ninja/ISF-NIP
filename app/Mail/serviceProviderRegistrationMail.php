<?php

namespace App\Mail;

use App\Services\GlobalNotificationSettingsService;
use App\Traits\ConfiguresSmtp;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Throwable;

class serviceProviderRegistrationMail extends Mailable implements ShouldQueue
{
	use Queueable, SerializesModels, ConfiguresSmtp;

	public $contact_name;
	
	/**
	 * Create a new message instance.
	 */
	public function __construct($contact_name)
	{
		$this->contact_name = $contact_name;
	}

	public function build()
	{
		$orgData = $this->buildEmailOrganizationData(null);

		return $this->subject('Registration request received - تم استقبال طلب التسجيل')->view('emails.service-providers.registration', array_merge(['contact_name' => $this->contact_name], $orgData));
	}
}
