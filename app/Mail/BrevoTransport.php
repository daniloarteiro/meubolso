<?php

namespace App\Mail;

use Illuminate\Support\Facades\Http;
use RuntimeException;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\MessageConverter;

class BrevoTransport extends AbstractTransport
{
    public function __construct(
        private readonly string $apiKey,
    ) {
        parent::__construct();
    }

    protected function doSend(SentMessage $message): void
    {
        if ($this->apiKey === '') {
            throw new RuntimeException('BREVO_API_KEY não configurada.');
        }

        $email = MessageConverter::toEmail($message->getOriginalMessage());

        $from = $email->getFrom()[0] ?? null;
        $sender = $from instanceof Address
            ? array_filter(['email' => $from->getAddress(), 'name' => $from->getName() ?: null])
            : ['email' => (string) config('mail.from.address'), 'name' => (string) config('mail.from.name')];

        $to = array_map(
            fn (Address $addr) => array_filter(['email' => $addr->getAddress(), 'name' => $addr->getName() ?: null]),
            $email->getTo()
        );

        $payload = array_filter([
            'sender' => $sender,
            'to' => array_values($to),
            'subject' => $email->getSubject() ?? '',
            'htmlContent' => $email->getHtmlBody(),
            'textContent' => $email->getTextBody(),
        ], fn ($valor) => $valor !== null && $valor !== '');

        Http::withHeaders([
            'api-key' => $this->apiKey,
            'accept' => 'application/json',
        ])
            ->timeout(15)
            ->post('https://api.brevo.com/v3/smtp/email', $payload)
            ->throw();
    }

    public function __toString(): string
    {
        return 'brevo';
    }
}
