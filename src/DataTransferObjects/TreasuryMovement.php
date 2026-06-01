<?php

declare(strict_types=1);

namespace DigitaldevLx\LaravelInvoiceExpress\DataTransferObjects;

use DigitaldevLx\LaravelInvoiceExpress\DataTransferObjects\Contracts\DataTransferObject;
use DigitaldevLx\LaravelInvoiceExpress\Support\Decimals;

final readonly class TreasuryMovement implements DataTransferObject
{
    public function __construct(
        public int $accountId,
        public string $amount,
        public string $date,
        public ?string $description = null,
        public ?string $movementType = null,
        public ?int $categoryId = null,
        public ?string $observations = null,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'account_id' => $this->accountId,
            'amount' => $this->amount,
            'date' => $this->date,
            'description' => $this->description,
            'movement_type' => $this->movementType,
            'category_id' => $this->categoryId,
            'observations' => $this->observations,
        ], static fn (mixed $value): bool => $value !== null);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): static
    {
        return new self(
            accountId: isset($data['account_id']) ? (int) $data['account_id'] : 0,
            amount: Decimals::toString($data['amount'] ?? null) ?? '0',
            date: isset($data['date']) && is_string($data['date']) ? $data['date'] : '',
            description: isset($data['description']) && is_string($data['description']) ? $data['description'] : null,
            movementType: isset($data['movement_type']) && is_string($data['movement_type']) ? $data['movement_type'] : null,
            categoryId: isset($data['category_id']) ? (int) $data['category_id'] : null,
            observations: isset($data['observations']) && is_string($data['observations']) ? $data['observations'] : null,
        );
    }
}
