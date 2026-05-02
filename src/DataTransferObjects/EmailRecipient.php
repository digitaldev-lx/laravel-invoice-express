<?php

declare(strict_types=1);

namespace DigitaldevLx\LaravelInvoiceExpress\DataTransferObjects;

use DigitaldevLx\LaravelInvoiceExpress\DataTransferObjects\Contracts\DataTransferObject;

final readonly class EmailRecipient implements DataTransferObject
{
    public function __construct(
        public string $email,
        public ?string $name = null,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'email' => $this->email,
            'name' => $this->name,
        ], static fn (mixed $value): bool => $value !== null);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): static
    {
        return new self(
            email: isset($data['email']) && is_string($data['email']) ? $data['email'] : '',
            name: isset($data['name']) && is_string($data['name']) ? $data['name'] : null,
        );
    }
}
