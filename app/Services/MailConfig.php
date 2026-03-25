<?php
namespace App\Services;

use Illuminate\Support\Facades\Config;
class MailConfig
{
       /**
        * Updfate the mail configuration at runtime.
        * @param array $smtp
        * @return void
        */
       public static function set(array $smtp)
    {
        if (($smtp['transport'] ?? 'smtp') === 'resend') {
            Config::set('mail.default', 'resend');
            Config::set('resend.api_key', $smtp['api_key']);
            Config::set('mail.from.address', $smtp['from_address'] ?? config('mail.from.address'));
            Config::set('mail.from.name', $smtp['from_name'] ?? config('mail.from.name'));
            return;
        }

        Config::set('mail.default', 'smtp');
        Config::set('mail.mailers.smtp.transport', 'smtp');
        Config::set('mail.mailers.smtp.host', $smtp['host']);
        Config::set('mail.mailers.smtp.port', $smtp['port']);
        Config::set('mail.mailers.smtp.username', $smtp['username']);
        Config::set('mail.mailers.smtp.password', $smtp['password']);
        Config::set('mail.mailers.smtp.encryption', $smtp['encryption']);

        Config::set('mail.from.address', $smtp['from_address']);
        Config::set('mail.from.name', $smtp['from_name']);
    }
}
