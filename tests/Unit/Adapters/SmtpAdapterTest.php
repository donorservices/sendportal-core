<?php

declare(strict_types=1);

namespace Tests\Unit\Adapters;

use PHPUnit\Framework\Attributes\Test;
use Sendportal\Base\Adapters\SmtpAdapter;
use Sendportal\Base\Services\Messages\MessageTrackingOptions;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Tests\TestCase;

class SmtpAdapterTest extends TestCase
{
    /** @var resource|null */
    private $server;

    private string $transcriptFile;

    protected function tearDown(): void
    {
        if (is_resource($this->server)) {
            proc_terminate($this->server);
            proc_close($this->server);
        }

        if (isset($this->transcriptFile)) {
            @unlink($this->transcriptFile);
            @unlink($this->transcriptFile . '.port');
        }

        parent::tearDown();
    }

    #[Test]
    public function it_sends_the_message_and_returns_the_message_id_reported_by_the_server(): void
    {
        $adapter = $this->adapterFor($this->startServer('accept'));

        $id = $adapter->send('news@example.com', 'News', 'jane@example.com', 'Hello', new MessageTrackingOptions(), '<p>Hi</p>');

        self::assertSame('FAKE123', $id);

        $adapter = null; // closes the connection so the server writes its transcript
        $transcript = $this->transcript();
        self::assertStringContainsString('MAIL FROM:<news@example.com>', $transcript);
        self::assertStringContainsString('RCPT TO:<jane@example.com>', $transcript);
        self::assertStringContainsString('Subject: Hello', $transcript);
        self::assertStringContainsString('<p>Hi</p>', $transcript);
    }

    #[Test]
    public function it_throws_when_the_server_rejects_the_message(): void
    {
        $adapter = $this->adapterFor($this->startServer('reject-rcpt'));

        $this->expectException(TransportExceptionInterface::class);
        $this->expectExceptionMessage('mailbox unavailable');

        $adapter->send('news@example.com', 'News', 'nobody@example.com', 'Hello', new MessageTrackingOptions(), '<p>Hi</p>');
    }

    #[Test]
    public function it_throws_when_the_server_cannot_be_reached(): void
    {
        // Grab a free port, then close it so nothing is listening there.
        $socket = stream_socket_server('tcp://127.0.0.1:0');
        $port = (int) parse_url('tcp://' . stream_socket_get_name($socket, false), PHP_URL_PORT);
        fclose($socket);

        $this->expectException(TransportExceptionInterface::class);

        $this->adapterFor($port)->send('news@example.com', 'News', 'jane@example.com', 'Hello', new MessageTrackingOptions(), '<p>Hi</p>');
    }

    private function adapterFor(int $port): SmtpAdapter
    {
        return new SmtpAdapter([
            'host' => '127.0.0.1',
            'port' => $port,
            'username' => null,
            'password' => null,
            'encryption' => null,
        ]);
    }

    private function startServer(string $mode): int
    {
        $this->transcriptFile = tempnam(sys_get_temp_dir(), 'smtp');

        $this->server = proc_open(
            [PHP_BINARY, __DIR__ . '/../../Support/fake-smtp-server.php', $mode, $this->transcriptFile],
            [1 => ['file', '/dev/null', 'w'], 2 => ['file', '/dev/null', 'w']],
            $pipes
        );

        $portFile = $this->transcriptFile . '.port';
        for ($i = 0; $i < 100 && ! is_file($portFile); $i++) {
            usleep(50_000);
            clearstatcache();
        }

        $port = is_file($portFile) ? (int) file_get_contents($portFile) : 0;
        self::assertGreaterThan(0, $port, 'fake SMTP server did not start');

        return $port;
    }

    private function transcript(): string
    {
        for ($i = 0; $i < 50 && filesize($this->transcriptFile) === 0; $i++) {
            usleep(100_000);
            clearstatcache();
        }

        return (string) file_get_contents($this->transcriptFile);
    }
}
