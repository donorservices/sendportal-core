<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use Carbon\Carbon;
use PHPUnit\Framework\Attributes\Test;
use Sendportal\Base\Services\Helper;
use Tests\TestCase;

class HelperTest extends TestCase
{
    #[Test]
    public function display_date_returns_a_carbon_instance_without_a_timezone()
    {
        $result = app(Helper::class)->displayDate('2026-01-15 12:34:56');

        self::assertInstanceOf(Carbon::class, $result);
        self::assertSame('15 Jan 2026 12:34', $result->format('d M Y H:i'));
    }

    #[Test]
    public function display_date_converts_to_the_given_timezone()
    {
        $result = app(Helper::class)->displayDate('2026-01-15 12:00:00', 'America/New_York');

        self::assertSame('2026-01-15 07:00', $result->format('Y-m-d H:i'));
    }

    #[Test]
    public function display_date_returns_null_for_empty_dates()
    {
        self::assertNull(app(Helper::class)->displayDate(null));
    }
}
