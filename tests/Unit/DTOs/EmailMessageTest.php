<?php

declare(strict_types=1);

use DigitaldevLx\LaravelInvoiceExpress\DataTransferObjects\EmailMessage;
use DigitaldevLx\LaravelInvoiceExpress\DataTransferObjects\EmailRecipient;

it('wraps the message inside the client envelope expected by the API', function (): void {
    $message = new EmailMessage(
        to: new EmailRecipient(email: 'cliente@example.com'),
        subject: 'A sua fatura',
        body: 'Em anexo.',
        cc: new EmailRecipient(email: 'contabilidade@example.com'),
        logo: true,
    );

    expect($message->toArray())->toBe([
        'client' => [
            'email' => 'cliente@example.com',
            'subject' => 'A sua fatura',
            'body' => 'Em anexo.',
            'cc' => 'contabilidade@example.com',
            'logo' => true,
        ],
    ]);
});

it('hydrates from a flat or wrapped structure', function (): void {
    $flat = EmailMessage::fromArray(['email' => 'a@b.pt', 'subject' => 'S']);
    expect($flat->to->email)->toBe('a@b.pt');
    expect($flat->subject)->toBe('S');

    $wrapped = EmailMessage::fromArray(['client' => ['email' => 'a@b.pt']]);
    expect($wrapped->to->email)->toBe('a@b.pt');
});
