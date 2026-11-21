<?php

declare(strict_types=1);

namespace Sendportal\Base\Http\Controllers\Api\Webhooks;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Sendportal\Base\Events\Webhooks\MailgunWebhookReceived;
use Sendportal\Base\Http\Controllers\Api\Webhooks\Concerns\DecodesJsonPayload;
use Sendportal\Base\Http\Controllers\Controller;

class MailgunWebhooksController extends Controller
{
    use DecodesJsonPayload;

    public function handle(): Response
    {
        if (($payload = $this->jsonPayload()) === null) {
            return $this->invalidPayloadResponse('Mailgun');
        }

        $payload = $this->stripAttachments($payload);

        Log::info('Mailgun webhook received');

        if (\Arr::get($payload, 'event-data.event')) {
            event(new MailgunWebhookReceived($payload));

            return response('OK');
        }

        return response('OK (not processed');
    }

    /**
     * Remove attachments from the webhook.
     *
     * This is needed to ensure that the payload can be correctly serialized for the queue.
     */
    protected function stripAttachments(array $payload): array
    {
        unset($payload['event-data.message.attachments']);

        return $payload;
    }
}
