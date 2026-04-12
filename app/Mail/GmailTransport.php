<?php

namespace App\Mail;

use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;
use Symfony\Component\Mailer\Transport\Smtp\Stream\SocketStream;

class GmailTransport extends EsmtpTransport
{
    public function __construct(string $username, string $password)
    {
        parent::__construct('smtp.gmail.com', 587, false);

        /** @var SocketStream $stream */
        $stream = $this->getStream();
        $stream->setStreamOptions([
            'ssl' => [
                'verify_peer'       => true,
                'verify_peer_name'  => true,
                'allow_self_signed' => false,
                'cafile'            => '/etc/ssl/certs/ca-certificates.crt',
                'crypto_method'     => STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT
                                     | STREAM_CRYPTO_METHOD_TLSv1_3_CLIENT,
            ],
        ]);

        $this->setUsername($username);
        $this->setPassword($password);
    }
}
