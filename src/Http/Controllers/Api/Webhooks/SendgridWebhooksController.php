<?php

declare(strict_types=1);

namespace Sendportal\Base\Http\Controllers\Api\Webhooks;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Sendportal\Base\Events\Webhooks\SendgridWebhookReceived;
use Sendportal\Base\Http\Controllers\Api\Webhooks\Concerns\DecodesJsonPayload;
use Sendportal\Base\Http\Controllers\Controller;

class SendgridWebhooksController extends Controller
{
    use DecodesJsonPayload;

    public function handle(): Response
    {
        if (($events = $this->jsonPayload()) === null) {
            return $this->invalidPayloadResponse('SendGrid');
        }

        // SendGrid posts a JSON array of event objects.
        $payload = collect($events)->filter(fn ($event) => is_array($event));

        Log::info('SendGrid webhook received');

        if ($payload->isEmpty()) {
            return response('OK (not processed');
        }

        foreach ($payload as $event) {
            event(new SendgridWebhookReceived($event));
        }

        return response('OK');
    }
}
