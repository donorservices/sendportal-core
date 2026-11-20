<?php

namespace Sendportal\Base\Services;

use Carbon\Carbon;

class Helper
{
    /**
     * Display a given date in the active user's timezone.
     *
     * @param mixed $date
     * @param string|null $timezone
     * @return mixed
     */
    public function displayDate($date, ?string $timezone = null)
    {
        if (! $date) {
            return null;
        }

        $carbon = Carbon::parse($date)->copy();

        // Carbon 3's tz(null) is a getter that returns the timezone *name*,
        // so only convert when a timezone is actually given.
        return $timezone !== null ? $carbon->tz($timezone) : $carbon;
    }

    public function isPro(): bool
    {
        return class_exists(\Sendportal\Pro\SendportalProServiceProvider::class);
    }
}
