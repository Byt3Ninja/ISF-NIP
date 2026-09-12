<?php

namespace App\Mail;

use App\Traits\RoutesTenantMail;
use App\Traits\ConfiguresSmtp;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Password;

class CustomEmail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels, ConfiguresSmtp, RoutesTenantMail;

    public $user;
    public $resetUrl;

    public function __construct($user)
    {
        $this->user = $user;

        // توليد التوكن
        $token = Password::broker()->createToken($this->user);

        // رابط إعادة التعيين
        $this->resetUrl = url(route('password.reset', [
            'token' => $token,
            'email' => $this->user->email,
        ], false));
    }

    public function build()
    {
        $org = $this->user->organization ?? null;
        $orgData = $this->buildEmailOrganizationData($org);

        return $this->subject('بيانات الدخول لمنصة نمو السياحة')
                    ->view('emails.custom-mail')
                    ->with(array_merge([
                        'user' => $this->user,
                        'resetUrl' => $this->resetUrl,
                    ], $orgData));
    }
}
