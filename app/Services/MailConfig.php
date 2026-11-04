<?php
namespace App\Services;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;

class MailConfig
{
    /**
    * Update the mail configuration at runtime.
    * @param array $smtp
    * @return void
    */
    public static function set(array $smtp)
    {
        if (($smtp['transport'] ?? 'smtp') === 'resend') {
            Config::set('mail.driver', 'resend');
            Config::set('mail.default', 'resend');
            Config::set('resend.api_key', $smtp['api_key']);
            Config::set('mail.from.address', $smtp['from_address'] ?? config('mail.from.address'));
            Config::set('mail.from.name', $smtp['from_name'] ?? config('mail.from.name'));
        } else {
            // The smtp mailer keeps its name; only an internal relay swaps the
            // transport. With mail.driver set, Laravel builds the mailer from the
            // top-level mail.* keys and takes the transport from mail.driver.
            $transport = ($smtp['transport'] ?? 'smtp') === 'relay' ? 'relay' : 'smtp';
            Config::set('mail.driver', $transport);
            Config::set('mail.default', 'smtp');
            Config::set('mail.mailers.smtp.transport', $transport);
            Config::set('mail.host', $smtp['host']);
            Config::set('mail.port', (int) ($smtp['port'] ?? 587));
            Config::set('mail.username', $smtp['username']);
            Config::set('mail.password', $smtp['password']);
            Config::set('mail.encryption', $smtp['encryption']);
            Config::set('mail.mailers.smtp.host', $smtp['host']);
            Config::set('mail.mailers.smtp.port', (int) ($smtp['port'] ?? 587));
            Config::set('mail.mailers.smtp.username', $smtp['username']);
            Config::set('mail.mailers.smtp.password', $smtp['password']);
            Config::set('mail.mailers.smtp.encryption', $smtp['encryption']);

            Config::set('mail.from.address', $smtp['from_address']);
            Config::set('mail.from.name', $smtp['from_name']);
        }

        // Purge cached mailer instances so the next send() creates a fresh
        // mailer with the updated transport configuration.
        Mail::forgetMailers();
    }
}
