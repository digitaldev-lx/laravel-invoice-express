<?php

declare(strict_types=1);

namespace DigitaldevLx\LaravelInvoiceExpress\DataTransferObjects;

use DigitaldevLx\LaravelInvoiceExpress\DataTransferObjects\Contracts\DataTransferObject;
use DigitaldevLx\LaravelInvoiceExpress\Enums\Country;

final readonly class Address implements DataTransferObject
{
    public function __construct(
        public ?string $street = null,
        public ?string $postalCode = null,
        public ?string $city = null,
        public Country|string|null $country = null,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $country = $this->country instanceof Country ? $this->country->value : $this->country;

        return array_filter([
            'address' => $this->street,
            'postal_code' => $this->postalCode,
            'city' => $this->city,
            'country' => $country,
        ], static fn (mixed $value): bool => $value !== null);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): static
    {
        $country = isset($data['country']) && is_string($data['country'])
            ? (Country::tryFrom($data['country']) ?? $data['country'])
            : null;

        return new self(
            street: isset($data['address']) && is_string($data['address']) ? $data['address'] : null,
            postalCode: isset($data['postal_code']) && is_string($data['postal_code']) ? $data['postal_code'] : null,
            city: isset($data['city']) && is_string($data['city']) ? $data['city'] : null,
            country: $country,
        );
    }
}
