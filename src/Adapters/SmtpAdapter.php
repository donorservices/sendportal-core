<?php

namespace Sendportal\Base\Adapters;

use Illuminate\Support\Arr;
use Sendportal\Base\Services\Messages\MessageTrackingOptions;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\Dsn;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransportFactory;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

class SmtpAdapter extends BaseMailAdapter
{
    /** @var EsmtpTransport */
    protected $transport;

    /**
     * Sends straight through the transport rather than Symfony's Mailer, whose
     * send() returns void: the transport hands back the SentMessage, so we can
     * store a real message ID instead of a placeholder.
     *
     * A failed send throws (TransportExceptionInterface) instead of being
     * swallowed, so the message is not marked as sent and the queued job fails
     * and can be retried, like the API-based adapters.
     *
     * @throws \Symfony\Component\Mailer\Exception\TransportExceptionInterface
     */
    public function send(string $fromEmail, string $fromName, string $toEmail, string $subject, MessageTrackingOptions $trackingOptions, string $content): string
    {
        $result = $this->resolveTransport()->send($this->resolveMessage($subject, $content, $fromEmail, $fromName, $toEmail));

        return $this->resolveMessageId($result);
    }

    protected function resolveTransport(): EsmtpTransport
    {
        if ($this->transport) {
            return $this->transport;
        }

        $factory = new EsmtpTransportFactory();

        $encryption = Arr::get($this->config, 'encryption');

        // Derive the scheme from the port so it is never empty (Symfony rejects
        // an empty scheme). Implicit TLS on 465 (or ssl) -> smtps; everything
        // else -> smtp, which negotiates STARTTLS opportunistically (e.g. Resend
        // on 587). Previously any encryption value other than exactly 'tls'
        // produced an empty scheme and a 500.
        $scheme = ((int) Arr::get($this->config, 'port') === 465 || $encryption === 'ssl')
            ? 'smtps'
            : 'smtp';

        $dsn = new Dsn(
            $scheme,
            Arr::get($this->config, 'host'),
            Arr::get($this->config, 'username'),
            Arr::get($this->config, 'password'),
            Arr::get($this->config, 'port')
        );

        $this->transport = $factory->create($dsn);

        return $this->transport;
    }

    protected function resolveMessage(string $subject, string $content, string $fromEmail, string $fromName, string $toEmail): Email
    {
        $msg = (new Email())
            ->from(new Address($fromEmail, $fromName))
            ->to($toEmail)
            ->subject($subject)
            ->html($content);

        return $msg;
    }

    protected function resolveMessageId(?SentMessage $result): string
    {
        if (! $result) {
            // Only possible if a transport listener rejected the message.
            throw new \RuntimeException('The SMTP transport did not send the message.');
        }

        return $result->getMessageId();
    }
}
