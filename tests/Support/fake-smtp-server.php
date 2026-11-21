<?php

/*
 * Minimal scripted SMTP server for adapter tests.
 *
 * Usage: php fake-smtp-server.php <mode> <transcript-file>
 *   mode "accept"      - accepts the message and replies "250 Ok: queued as FAKE123"
 *   mode "reject-rcpt" - rejects the recipient with "550 mailbox unavailable"
 *
 * Writes the port it listens on to "<transcript-file>.port" (not stdout:
 * auto-prepended tooling such as Herd's dumper can capture CLI output), serves
 * one connection, then writes the client's commands and data to the
 * transcript file and exits.
 */

[, $mode, $transcriptFile] = $argv;

$server = stream_socket_server('tcp://127.0.0.1:0', $errno, $errstr);
if (! $server) {
    fwrite(STDERR, "listen failed: {$errstr}\n");
    exit(1);
}

file_put_contents($transcriptFile . '.port', (string) parse_url('tcp://' . stream_socket_get_name($server, false), PHP_URL_PORT));

$conn = @stream_socket_accept($server, 10);
if (! $conn) {
    exit(1);
}

$transcript = '';
$reply = static function (string $line) use ($conn): void {
    fwrite($conn, $line . "\r\n");
};

$reply('220 fake.smtp.test ESMTP');
$inData = false;

while (($line = fgets($conn)) !== false) {
    $transcript .= $line;

    if ($inData) {
        if (rtrim($line, "\r\n") === '.') {
            $inData = false;
            $reply('250 Ok: queued as FAKE123');
        }
        continue;
    }

    $command = strtoupper(substr(trim($line), 0, 4));

    match ($command) {
        'EHLO' => $reply("250-fake.smtp.test\r\n250 8BITMIME"),
        'MAIL', 'RSET', 'NOOP' => $reply('250 Ok'),
        'RCPT' => $reply($mode === 'reject-rcpt' ? '550 5.1.1 mailbox unavailable' : '250 Ok'),
        'DATA' => [$reply('354 End data with <CR><LF>.<CR><LF>'), $inData = true],
        'QUIT' => $reply('221 Bye'),
        default => $reply('502 Command not implemented'),
    };

    if ($command === 'QUIT') {
        break;
    }
}

file_put_contents($transcriptFile, $transcript);
