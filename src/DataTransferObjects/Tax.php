<?php

declare(strict_types=1);

namespace DigitaldevLx\LaravelInvoiceExpress\DataTransferObjects;

use DigitaldevLx\LaravelInvoiceExpress\DataTransferObjects\Contracts\DataTransferObject;
use DigitaldevLx\LaravelInvoiceExpress\Support\Decimals;

final readonly class Tax implements DataTransferObject
{
    public function __construct(
        public ?string $name = null,
        public ?string $value = null,
        public ?string $region = null,
        public ?string $code = null,
        public ?bool $defaultTax = null,
        public ?string $exemptionCode = null,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'name' => $this->name,
            'value' => $this->value,
            'region' => $this->region,
            'code' => $this->code,
            'default_tax' => $this->defaultTax,
            'exemption_code' => $this->exemptionCode,
        ], static fn (mixed $value): bool => $value !== null);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): static
    {
        return new self(
            name: isset($data['name']) && is_string($data['name']) ? $data['name'] : null,
            value: Decimals::toString($data['value'] ?? null),
            region: isset($data['region']) && is_string($data['region']) ? $data['region'] : null,
            code: isset($data['code']) && is_string($data['code']) ? $data['code'] : null,
            defaultTax: isset($data['default_tax']) ? (bool) $data['default_tax'] : null,
            exemptionCode: isset($data['exemption_code']) && is_string($data['exemption_code']) ? $data['exemption_code'] : null,
        );
    }
}
