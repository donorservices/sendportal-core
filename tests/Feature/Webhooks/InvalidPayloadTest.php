<?php

declare(strict_types=1);

namespace Tests\Feature\Webhooks;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Sendportal\Base\Events\Webhooks\MailgunWebhookReceived;
use Sendportal\Base\Events\Webhooks\MailjetWebhookReceived;
use Sendportal\Base\Events\Webhooks\PostalWebhookReceived;
use Sendportal\Base\Events\Webhooks\PostmarkWebhookReceived;
use Sendportal\Base\Events\Webhooks\SendgridWebhookReceived;
use Sendportal\Base\Events\Webhooks\SesWebhookReceived;
use Tests\TestCase;

class InvalidPayloadTest extends TestCase
{
    use RefreshDatabase;

    private const WEBHOOK_EVENTS = [
        SesWebhookReceived::class,
        MailgunWebhookReceived::class,
        PostmarkWebhookReceived::class,
        SendgridWebhookReceived::class,
        MailjetWebhookReceived::class,
        PostalWebhookReceived::class,
    ];

    public static function invalidBodies(): array
    {
        $cases = [];

        foreach (['aws', 'mailgun', 'postmark', 'sendgrid', 'mailjet', 'postal'] as $provider) {
            $cases["{$provider}: not JSON"] = [$provider, 'this is not json'];
            $cases["{$provider}: empty body"] = [$provider, ''];
            $cases["{$provider}: JSON string"] = [$provider, '"hello"'];
            $cases["{$provider}: JSON number"] = [$provider, '42'];
        }

        return $cases;
    }

    #[Test]
    #[DataProvider('invalidBodies')]
    public function a_body_that_is_not_a_json_object_is_rejected_with_a_400(string $provider, string $body): void
    {
        Event::fake(self::WEBHOOK_EVENTS);

        $this->call('POST', route("sendportal.api.webhooks.{$provider}"), [], [], [], ['CONTENT_TYPE' => 'application/json'], $body)
            ->assertStatus(400);

        foreach (self::WEBHOOK_EVENTS as $event) {
            Event::assertNotDispatched($event);
        }
    }

    #[Test]
    public function sendgrid_events_that_are_not_objects_are_skipped(): void
    {
        Event::fake(self::WEBHOOK_EVENTS);

        $this->call('POST', route('sendportal.api.webhooks.sendgrid'), [], [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'junk',
            ['event' => 'delivered', 'sg_message_id' => 'abc.123', 'timestamp' => time()],
        ]))->assertOk();

        Event::assertDispatchedTimes(SendgridWebhookReceived::class, 1);
    }
}
