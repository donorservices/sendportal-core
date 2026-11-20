<?php

declare(strict_types=1);

namespace Sendportal\Base\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * A template must contain {{content}} (or {{ content }}): that is where the
 * campaign's content is merged in. Without it, campaigns using the template
 * are sent with their content silently dropped.
 */
class ContainsContentTag implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || ! preg_match('/\{\{\s?content\s?\}\}/i', $value)) {
            $fail(__('The template must contain the {{content}} tag.'));
        }
    }
}
