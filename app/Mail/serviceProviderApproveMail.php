<?php

namespace App\Mail;

use App\Traits\RoutesTenantMail;
use Illuminate\Mail\Mailable;
use Illuminate\Bus\Queueable;
use App\Traits\ConfiguresSmtp;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use App\Services\GlobalNotificationSettingsService;
use Illuminate\Queue\SerializesModels;
use Throwable;

class serviceProviderApproveMail extends Mailable implements ShouldQueue
{
	use Queueable, SerializesModels, ConfiguresSmtp, RoutesTenantMail;

	public $contact_name;
	public $login_url;
	public $locale;

	/**
	 * Create a new message instance.
	 */
	public function __construct($contact_name, $login_url, $locale = 'en')
	{
		$this->contact_name = $contact_name;
		$this->login_url = $login_url;
		$this->locale = $locale;
	}

	/**
	 * Platform-branded email: the platform logo and name, never an
	 * organization's (EmailBranding).
	 */
	public function emailBranding()
	{
		return null;
	}

	public function build()
	{
		$orgData = $this->emailBrandingData();
		$subject = $this->locale === 'ar' ? 'تحديث بخصوص طلبكم' : 'Update on Your Request';

		return $this->subject($subject)->view(
			'emails.service-providers.approve', array_merge([
				'contact_name' => $this->contact_name,
				'login_url' => $this->login_url,
				'locale' => $this->locale,
			], $orgData)
		);
	}
}
