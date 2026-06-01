<?php

declare(strict_types=1);

namespace DigitaldevLx\LaravelInvoiceExpress\DataTransferObjects;

use DigitaldevLx\LaravelInvoiceExpress\DataTransferObjects\Contracts\DataTransferObject;
use DigitaldevLx\LaravelInvoiceExpress\Enums\Country;
use DigitaldevLx\LaravelInvoiceExpress\Enums\Language;

final readonly class Client implements DataTransferObject
{
    public function __construct(
        public string $name,
        public ?string $code = null,
        public ?string $email = null,
        public ?string $address = null,
        public ?string $city = null,
        public ?string $postalCode = null,
        public Country|string|null $country = null,
        public ?string $fiscalId = null,
        public ?string $website = null,
        public ?string $phone = null,
        public ?string $fax = null,
        public Language|string|null $language = null,
        public ?string $observations = null,
        public ?string $sendOptions = null,
        public ?string $preferredContactName = null,
        public ?float $discount = null,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $country = $this->country instanceof Country ? $this->country->value : $this->country;
        $language = $this->language instanceof Language ? $this->language->value : $this->language;

        return array_filter([
            'name' => $this->name,
            'code' => $this->code,
            'email' => $this->email,
            'address' => $this->address,
            'city' => $this->city,
            'postal_code' => $this->postalCode,
            'country' => $country,
            'fiscal_id' => $this->fiscalId,
            'website' => $this->website,
            'phone' => $this->phone,
            'fax' => $this->fax,
            'language' => $language,
            'observations' => $this->observations,
            'send_options' => $this->sendOptions,
            'preferred_contact_name' => $this->preferredContactName,
            'discount' => $this->discount,
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

        $language = isset($data['language']) && is_string($data['language'])
            ? (Language::tryFrom($data['language']) ?? $data['language'])
            : null;

        return new self(
            name: isset($data['name']) && is_string($data['name']) ? $data['name'] : '',
            code: isset($data['code']) && is_string($data['code']) ? $data['code'] : null,
            email: isset($data['email']) && is_string($data['email']) ? $data['email'] : null,
            address: isset($data['address']) && is_string($data['address']) ? $data['address'] : null,
            city: isset($data['city']) && is_string($data['city']) ? $data['city'] : null,
            postalCode: isset($data['postal_code']) && is_string($data['postal_code']) ? $data['postal_code'] : null,
            country: $country,
            fiscalId: isset($data['fiscal_id']) && is_string($data['fiscal_id']) ? $data['fiscal_id'] : null,
            website: isset($data['website']) && is_string($data['website']) ? $data['website'] : null,
            phone: isset($data['phone']) && is_string($data['phone']) ? $data['phone'] : null,
            fax: isset($data['fax']) && is_string($data['fax']) ? $data['fax'] : null,
            language: $language,
            observations: isset($data['observations']) && is_string($data['observations']) ? $data['observations'] : null,
            sendOptions: isset($data['send_options']) && is_string($data['send_options']) ? $data['send_options'] : null,
            preferredContactName: isset($data['preferred_contact_name']) && is_string($data['preferred_contact_name']) ? $data['preferred_contact_name'] : null,
            discount: isset($data['discount']) ? (float) $data['discount'] : null,
        );
    }
}
