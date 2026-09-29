<?php

namespace App\Mail;

use App\Traits\RoutesTenantMail;
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

class serviceProviderRejectMail extends Mailable implements ShouldQueue
{
	use Queueable, SerializesModels, ConfiguresSmtp, RoutesTenantMail;

	public $reason;
	public $contact_name;
	public $locale;

	/**
	 * Create a new message instance.
	 */
	public function __construct($contact_name, $reason, $locale = 'en')
	{
		$this->reason = $reason;
		$this->contact_name = $contact_name;
		$this->locale = $locale;
	}

	public function build()
	{
		$orgData = $this->buildEmailOrganizationData(null);
		$subject = $this->locale === 'ar' ? 'تحديث بخصوص طلبكم' : 'Update on Your Request';

		return $this->subject($subject)->view('emails.service-providers.reject', array_merge([
			'contact_name' => $this->contact_name,
			'reason' => $this->reason,
			'locale' => $this->locale,
		], $orgData));
	}
}
