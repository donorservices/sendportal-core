<?php

declare(strict_types=1);

namespace Sendportal\Base\Http\Controllers\Api\Webhooks;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Sendportal\Base\Events\Webhooks\PostmarkWebhookReceived;
use Sendportal\Base\Http\Controllers\Api\Webhooks\Concerns\DecodesJsonPayload;
use Sendportal\Base\Http\Controllers\Controller;

class PostmarkWebhooksController extends Controller
{
    use DecodesJsonPayload;

    public function handle(): Response
    {
        if (($payload = $this->jsonPayload()) === null) {
            return $this->invalidPayloadResponse('Postmark');
        }

        Log::info('Postmark webhook received');

        event(new PostmarkWebhookReceived($payload));

        return response('OK');
    }
}
