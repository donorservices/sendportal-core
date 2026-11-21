<?php

declare(strict_types=1);

namespace Sendportal\Base\Http\Controllers\Api\Webhooks\Concerns;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

trait DecodesJsonPayload
{
    /**
     * The request body decoded as a JSON object/array, or null when it is not
     * valid JSON or decodes to a scalar.
     */
    protected function jsonPayload(): ?array
    {
        $payload = json_decode(request()->getContent(), true);

        return is_array($payload) ? $payload : null;
    }

    /**
     * Reject a body we cannot process with a 400 rather than letting it crash
     * with a 500.
     */
    protected function invalidPayloadResponse(string $provider): Response
    {
        Log::warning("{$provider} webhook rejected: the body is not a JSON object.");

        return response('Invalid payload: expected a JSON object.', 400);
    }
}
