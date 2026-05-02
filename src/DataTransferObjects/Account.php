<?php

declare(strict_types=1);

namespace DigitaldevLx\LaravelInvoiceExpress\DataTransferObjects;

use DigitaldevLx\LaravelInvoiceExpress\DataTransferObjects\Contracts\DataTransferObject;

final readonly class Account implements DataTransferObject
{
    public function __construct(
        public string $name,
        public ?string $code = null,
        public ?string $accountType = null,
        public ?float $openingBalance = null,
        public ?string $observations = null,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'name' => $this->name,
            'code' => $this->code,
            'account_type' => $this->accountType,
            'opening_balance' => $this->openingBalance,
            'observations' => $this->observations,
        ], static fn (mixed $value): bool => $value !== null);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): static
    {
        return new self(
            name: isset($data['name']) && is_string($data['name']) ? $data['name'] : '',
            code: isset($data['code']) && is_string($data['code']) ? $data['code'] : null,
            accountType: isset($data['account_type']) && is_string($data['account_type']) ? $data['account_type'] : null,
            openingBalance: isset($data['opening_balance']) ? (float) $data['opening_balance'] : null,
            observations: isset($data['observations']) && is_string($data['observations']) ? $data['observations'] : null,
        );
    }
}
