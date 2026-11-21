<?php

declare(strict_types=1);

namespace Tests\Feature\Messages;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Sendportal\Base\Events\MessageDispatchEvent;
use Sendportal\Base\Facades\Sendportal;
use Sendportal\Base\Listeners\MessageDispatchHandler;
use Sendportal\Base\Models\Campaign;
use Sendportal\Base\Models\EmailService;
use Sendportal\Base\Models\EmailServiceType;
use Sendportal\Base\Models\Message;
use Sendportal\Base\Services\Messages\RelayMessage;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Tests\TestCase;

/**
 * A message the mail server refused must not be recorded as sent. These run
 * the real relay and SMTP adapter against a port nothing listens on.
 */
class SendFailureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // TestCase stubs the relay; use the real one.
        $this->app->forgetInstance(RelayMessage::class);
    }

    #[Test]
    public function a_queued_message_that_the_smtp_server_refuses_stays_unsent_and_the_job_fails(): void
    {
        $message = $this->messageViaUnreachableSmtp();

        try {
            app(MessageDispatchHandler::class)->handle(new MessageDispatchEvent($message));
            self::fail('The dispatch job should have failed.');
        } catch (TransportExceptionInterface $e) {
            // expected: the queue records the failure and can retry it
        }

        $message->refresh();
        self::assertNull($message->sent_at);
        self::assertNull($message->message_id);
    }

    #[Test]
    public function sending_a_draft_message_manually_reports_the_failure_instead_of_a_500(): void
    {
        $message = $this->messageViaUnreachableSmtp();

        $this->from(route('sendportal.messages.draft'))
            ->post(route('sendportal.messages.send'), ['id' => $message->id])
            ->assertRedirect(route('sendportal.messages.draft'))
            ->assertSessionHasErrors();

        self::assertNull($message->refresh()->sent_at);
    }

    private function messageViaUnreachableSmtp(): Message
    {
        $socket = stream_socket_server('tcp://127.0.0.1:0');
        $port = (int) parse_url('tcp://' . stream_socket_get_name($socket, false), PHP_URL_PORT);
        fclose($socket);

        $workspaceId = Sendportal::currentWorkspaceId();

        $service = EmailService::factory()->create([
            'workspace_id' => $workspaceId,
            'type_id' => EmailServiceType::SMTP,
            'settings' => ['host' => '127.0.0.1', 'port' => $port, 'username' => null, 'password' => null, 'encryption' => null],
        ]);

        $campaign = Campaign::factory()->withContent()->create([
            'workspace_id' => $workspaceId,
            'email_service_id' => $service->id,
        ]);

        return Message::factory()->create([
            'workspace_id' => $workspaceId,
            'source_id' => $campaign->id,
            'queued_at' => now(),
        ]);
    }
}
