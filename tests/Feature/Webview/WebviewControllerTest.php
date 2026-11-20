<?php

declare(strict_types=1);

namespace Tests\Feature\Webview;

use PHPUnit\Framework\Attributes\Test;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Sendportal\Base\Facades\Sendportal;
use Sendportal\Base\Models\Campaign;
use Sendportal\Base\Models\Message;
use Tests\TestCase;

class WebviewControllerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function a_message_can_be_seen_in_the_webview()
    {
        // given
        $campaign = Campaign::factory()->withContent()->create(['workspace_id' => Sendportal::currentWorkspaceId()]);
        $message = Message::factory()->create(['source_id' => $campaign->id, 'workspace_id' => Sendportal::currentWorkspaceId()]);

        // when
        $response = $this->get(route('sendportal.webview.show', $message->hash));

        // then
        $response->assertOk();
    }

    #[Test]
    public function an_unknown_message_hash_returns_404_in_the_webview()
    {
        $this->get(route('sendportal.webview.show', '00000000-0000-4000-8000-000000000000'))
            ->assertNotFound();
    }
}
