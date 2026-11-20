<?php

declare(strict_types=1);

namespace Tests\Feature\Subscriptions;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SubscriptionsControllerTest extends TestCase
{
    use RefreshDatabase;

    private const UNKNOWN_HASH = '00000000-0000-4000-8000-000000000000';

    #[Test]
    public function the_unsubscribe_page_returns_404_for_an_unknown_message_hash()
    {
        $this->get(route('sendportal.subscriptions.unsubscribe', self::UNKNOWN_HASH))
            ->assertNotFound();
    }

    #[Test]
    public function the_subscribe_page_returns_404_for_an_unknown_message_hash()
    {
        $this->get(route('sendportal.subscriptions.subscribe', self::UNKNOWN_HASH))
            ->assertNotFound();
    }

    #[Test]
    public function updating_a_subscription_returns_404_for_an_unknown_message_hash()
    {
        $this->put(route('sendportal.subscriptions.update', self::UNKNOWN_HASH), ['unsubscribed' => 1])
            ->assertNotFound();
    }
}
