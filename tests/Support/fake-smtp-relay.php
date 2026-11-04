<?php

/*
 * Minimal SMTP server for RelayMailTransportTest. It offers STARTTLS and AUTH
 * in its EHLO reply (like the ITIDA relay) but never upgrades, and appends
 * every command line it receives to the log file. Message bodies are not
 * logged.
 *
 * Usage: php fake-smtp-relay.php <logfile>
 * Prints the port it listens on, then serves connections until it has been
 * idle for 10 seconds.
 */

$log = $argv[1];
$server = stream_socket_server('tcp://127.0.0.1:0', $errno, $errstr);

if (!$server) {
    fwrite(STDERR, "fake-smtp-relay: {$errstr}\n");
    exit(1);
}

echo parse_url('tcp://' . stream_socket_get_name($server, false), PHP_URL_PORT), "\n";
fflush(STDOUT);

while ($conn = @stream_socket_accept($server, 10)) {
    fwrite($conn, "220 fake-relay ESMTP\r\n");
    $inData = false;

    while (($line = fgets($conn)) !== false) {
        if ($inData) {
            if (rtrim($line, "\r\n") === '.') {
                $inData = false;
                fwrite($conn, "250 queued\r\n");
            }
            continue;
        }

        file_put_contents($log, $line, FILE_APPEND);
        $command = strtoupper(strtok(trim($line), ' :'));

        $reply = match ($command) {
            'EHLO'     => "250-fake-relay\r\n250-STARTTLS\r\n250 AUTH PLAIN LOGIN\r\n",
            'STARTTLS' => "454 TLS not available\r\n",
            'DATA'     => "354 go ahead\r\n",
            'QUIT'     => "221 bye\r\n",
            default    => "250 ok\r\n",
        };

        $inData = $command === 'DATA';
        fwrite($conn, $reply);

        if ($command === 'QUIT') {
            break;
        }
    }

    fclose($conn);
}
