<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Auth\Notifications\ResetPassword as ResetPasswordNotification;
use Notification;

class ResetPassword extends  Notification implements ShouldQueue
{
    use Queueable;
}