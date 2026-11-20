<?php

declare(strict_types=1);

namespace Tests\Unit\Adapters;

use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\Attributes\Test;
use Postal\ApiException;
use Postal\Client;
use Postal\HttpClientFactory;
use Sendportal\Base\Adapters\PostalAdapter;
use Sendportal\Base\Services\Messages\MessageTrackingOptions;
use Tests\TestCase;

class PostalAdapterTest extends TestCase
{
    /** @var array<int, array> */
    private array $history = [];

    private function adapter(Response $response): PostalAdapter
    {
        $stack = HandlerStack::create(new MockHandler([$response]));
        $stack->push(Middleware::history($this->history));
        $client = new Client('https://postal.example.test', 'secret-key', HttpClientFactory::create('https://postal.example.test', 'secret-key', $stack));

        return new class(['postal_host' => 'postal.example.test', 'key' => 'secret-key'], $client) extends PostalAdapter {
            public function __construct(array $config, private Client $client)
            {
                parent::__construct($config);
            }

            protected function resolveClient(): Client
            {
                return $this->client;
            }
        };
    }

    #[Test]
    public function it_sends_the_message_and_returns_the_recipient_message_id(): void
    {
        $adapter = $this->adapter(new Response(200, [], json_encode([
            'status' => 'success',
            'data' => [
                'message_id' => 'abc@postal.example.test',
                'messages' => ['Jane@Example.com' => ['id' => 4321, 'token' => 'tok']],
            ],
        ])));

        $id = $adapter->send('news@example.com', 'News', 'Jane@Example.com', 'Hello', new MessageTrackingOptions(), '<p>Hi</p>');

        self::assertSame('4321', $id);

        $request = $this->history[0]['request'];
        self::assertSame('https://postal.example.test/api/v1/send/message', (string) $request->getUri());
        self::assertSame('secret-key', $request->getHeaderLine('X-Server-API-Key'));

        $body = json_decode((string) $request->getBody(), true);
        self::assertSame(['Jane@Example.com'], $body['to']);
        self::assertSame('News <news@example.com>', $body['from']);
        self::assertSame('Hello', $body['subject']);
        self::assertSame('<p>Hi</p>', $body['html_body']);
    }

    #[Test]
    public function it_throws_when_postal_rejects_the_message(): void
    {
        $adapter = $this->adapter(new Response(200, [], json_encode([
            'status' => 'error',
            'data' => ['code' => 'InvalidServerAPIKey', 'message' => 'The API key is invalid'],
        ])));

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('InvalidServerAPIKey');

        $adapter->send('news@example.com', 'News', 'jane@example.com', 'Hello', new MessageTrackingOptions(), '<p>Hi</p>');
    }
}
