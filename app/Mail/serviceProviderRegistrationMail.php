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

class serviceProviderRegistrationMail extends Mailable implements ShouldQueue
{
	use Queueable, SerializesModels, ConfiguresSmtp, RoutesTenantMail;

	public $contact_name;
	public $email_address;
	public $temp_password;
	public $login_url;
	public $locale;

	/**
	 * Create a new message instance.
	 */
	public function __construct($contact_name, $email_address, $temp_password, $login_url, $locale = 'en')
	{
		$this->contact_name = $contact_name;
		$this->email_address = $email_address;
		$this->temp_password = $temp_password;
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
		$subject = $this->locale === 'ar' ? 'تم استقبال طلب التسجيل' : 'Registration request received';

		return $this->subject($subject)->view('emails.service-providers.registration', array_merge([
			'contact_name' => $this->contact_name,
			'email' => $this->email_address,
			'password' => $this->temp_password,
			'login_url' => $this->login_url,
			'locale' => $this->locale,
		], $orgData));
	}
}
