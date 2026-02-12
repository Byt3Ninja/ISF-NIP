<?php

namespace App\Mail;

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
	use Queueable, SerializesModels, ConfiguresSmtp;

	public $contact_name;
	public $email_address;
	public $temp_password;
	public $login_url;
	
	/**
	 * Create a new message instance.
	 */
	public function __construct($contact_name, $email_address, $temp_password, $login_url)
	{
		$this->contact_name = $contact_name;
		$this->email_address = $email_address;
		$this->temp_password = $temp_password;
		$this->login_url = $login_url;
	}

	public function build()
	{
		$orgData = $this->buildEmailOrganizationData(null);

		return $this->subject('Update on Your Request - تحديث بخصوص طلبكم')->view(
			'emails.service-providers.approve', array_merge([
				'contact_name' => $this->contact_name,
				'email' => $this->email_address,
				'password' => $this->temp_password,
				'login_url' => $this->login_url
			], $orgData)
		);
	}
}
