<?php

declare(strict_types=1);

namespace Sendportal\Base\Adapters;

use DomainException;
use Illuminate\Support\Arr;
use Postal\Client;
use Postal\Send\Message;
use Postal\Send\Result;
use Sendportal\Base\Services\Messages\MessageTrackingOptions;

class PostalAdapter extends BaseMailAdapter
{
    /**
     * @throws \Postal\ApiException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function send(string $fromEmail, string $fromName, string $toEmail, string $subject, MessageTrackingOptions $trackingOptions, string $content): string
    {
        $message = (new Message())
            ->to($toEmail)
            ->from($fromName . ' <' . $fromEmail . '>')
            ->subject($subject)
            ->htmlBody($content);

        $result = $this->resolveClient()->send->message($message);

        return $this->resolveMessageId($result, $toEmail);
    }

    protected function resolveClient(): Client
    {
        return new Client('https://' . Arr::get($this->config, 'postal_host'), (string) Arr::get($this->config, 'key'));
    }

    /**
     * Postal webhooks identify messages by the per-recipient numeric ID
     * (payload.message.id), so that is what we store.
     */
    protected function resolveMessageId(Result $result, string $toEmail): string
    {
        $recipients = $result->recipients();
        $message = $recipients[strtolower($toEmail)] ?? reset($recipients);

        if (! $message) {
            throw new DomainException('Unable to resolve message ID');
        }

        return (string) $message->id;
    }
}
