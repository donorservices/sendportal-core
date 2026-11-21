<?php

declare(strict_types=1);

namespace Tests\Feature\Campaigns;

use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Sendportal\Base\Facades\Sendportal;
use Sendportal\Base\Models\Campaign;
use Sendportal\Base\Services\Messages\DispatchTestMessage;
use Tests\TestCase;

class CampaignTestControllerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function a_test_email_can_be_sent(): void
    {
        $campaign = $this->dispatchReturns(fn ($mock) => $mock->andReturn('msg-1'));

        $this->post(route('sendportal.campaigns.test', $campaign->id), ['recipient_email' => 'me@example.com'])
            ->assertRedirect(route('sendportal.campaigns.preview', $campaign->id))
            ->assertSessionHas('success', 'The test email has been dispatched.');
    }

    #[Test]
    public function a_failed_test_email_shows_the_reason_instead_of_a_500(): void
    {
        $campaign = $this->dispatchReturns(fn ($mock) => $mock->andThrow(new Exception('550 mailbox unavailable')));

        $this->post(route('sendportal.campaigns.test', $campaign->id), ['recipient_email' => 'me@example.com'])
            ->assertRedirect(route('sendportal.campaigns.preview', $campaign->id))
            ->assertSessionHas('error', 'Failed to dispatch test email. 550 mailbox unavailable');
    }

    #[Test]
    public function a_test_email_that_could_not_be_dispatched_flashes_an_error(): void
    {
        $campaign = $this->dispatchReturns(fn ($mock) => $mock->andReturnNull());

        $this->post(route('sendportal.campaigns.test', $campaign->id), ['recipient_email' => 'me@example.com'])
            ->assertRedirect(route('sendportal.campaigns.preview', $campaign->id))
            ->assertSessionHas('error', 'Failed to dispatch test email.');
    }

    private function dispatchReturns(callable $outcome): Campaign
    {
        $campaign = Campaign::factory()->withContent()->create(['workspace_id' => Sendportal::currentWorkspaceId()]);

        $this->instance(DispatchTestMessage::class, Mockery::mock(DispatchTestMessage::class, function ($mock) use ($outcome, $campaign) {
            $outcome($mock->shouldReceive('handle')->once()->with(Sendportal::currentWorkspaceId(), $campaign->id, 'me@example.com'));
        }));

        return $campaign;
    }
}
