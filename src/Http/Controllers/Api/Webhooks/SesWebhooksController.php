<?php

declare(strict_types=1);

namespace Sendportal\Base\Http\Controllers\Api\Webhooks;

use Exception;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Sendportal\Base\Events\Webhooks\SesWebhookReceived;
use Sendportal\Base\Http\Controllers\Api\Webhooks\Concerns\DecodesJsonPayload;
use Sendportal\Base\Http\Controllers\Controller;

class SesWebhooksController extends Controller
{
    use DecodesJsonPayload;

    /**
     * @throws Exception
     */
    public function handle(): Response
    {
        if (($payload = $this->jsonPayload()) === null) {
            return $this->invalidPayloadResponse('SES');
        }

        Log::info('SES webhook received', ['payload' => $payload]);

        $payloadType = $payload['Type'] ?? null;

        if (! in_array($payloadType, ['SubscriptionConfirmation', 'Notification'], true)) {
            return response('OK (not processed).');
        }

        event(new SesWebhookReceived($payload, $payloadType));

        return response('OK');
    }
}
