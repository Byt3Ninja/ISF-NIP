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
