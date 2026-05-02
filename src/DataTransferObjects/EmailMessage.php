<?php

declare(strict_types=1);

namespace DigitaldevLx\LaravelInvoiceExpress\DataTransferObjects;

use DigitaldevLx\LaravelInvoiceExpress\DataTransferObjects\Contracts\DataTransferObject;

final readonly class EmailMessage implements DataTransferObject
{
    public function __construct(
        public EmailRecipient $to,
        public ?string $subject = null,
        public ?string $body = null,
        public ?EmailRecipient $cc = null,
        public ?EmailRecipient $bcc = null,
        public ?bool $logo = null,
        public ?bool $saveToSent = null,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $client = array_filter([
            'email' => $this->to->email,
            'save_to_sent_items' => $this->saveToSent,
            'subject' => $this->subject,
            'body' => $this->body,
            'cc' => $this->cc?->email,
            'bcc' => $this->bcc?->email,
            'logo' => $this->logo,
        ], static fn (mixed $value): bool => $value !== null);

        return ['client' => $client];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): static
    {
        $client = isset($data['client']) && is_array($data['client']) ? $data['client'] : $data;

        return new self(
            to: new EmailRecipient(
                email: isset($client['email']) && is_string($client['email']) ? $client['email'] : '',
            ),
            subject: isset($client['subject']) && is_string($client['subject']) ? $client['subject'] : null,
            body: isset($client['body']) && is_string($client['body']) ? $client['body'] : null,
            cc: isset($client['cc']) && is_string($client['cc']) ? new EmailRecipient($client['cc']) : null,
            bcc: isset($client['bcc']) && is_string($client['bcc']) ? new EmailRecipient($client['bcc']) : null,
            logo: isset($client['logo']) ? (bool) $client['logo'] : null,
            saveToSent: isset($client['save_to_sent_items']) ? (bool) $client['save_to_sent_items'] : null,
        );
    }
}
