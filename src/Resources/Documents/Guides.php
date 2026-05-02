<?php

declare(strict_types=1);

namespace DigitaldevLx\LaravelInvoiceExpress\Resources\Documents;

use DigitaldevLx\LaravelInvoiceExpress\Concerns\Resource\ChangesState;
use DigitaldevLx\LaravelInvoiceExpress\Concerns\Resource\GeneratesPdf;
use DigitaldevLx\LaravelInvoiceExpress\Concerns\Resource\GetsQrCode;
use DigitaldevLx\LaravelInvoiceExpress\Concerns\Resource\HandlesRelatedDocuments;
use DigitaldevLx\LaravelInvoiceExpress\Concerns\Resource\SendsByEmail;
use DigitaldevLx\LaravelInvoiceExpress\DataTransferObjects\Guide as GuideDto;
use DigitaldevLx\LaravelInvoiceExpress\Enums\DocumentType;
use DigitaldevLx\LaravelInvoiceExpress\Enums\GuideType;
use DigitaldevLx\LaravelInvoiceExpress\Events\DocumentCreated;

class Guides extends Document
{
    use ChangesState;
    use GeneratesPdf;
    use GetsQrCode;
    use HandlesRelatedDocuments;
    use SendsByEmail;

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function all(GuideType $type = GuideType::Transport, array $filters = []): array
    {
        $result = $this->client->request(
            method: 'GET',
            endpoint: sprintf('%s.json', $type->endpointPath()),
            params: $filters,
        );

        return is_array($result) ? $result : [];
    }

    /**
     * @return array<string, mixed>
     */
    public function find(int $id, GuideType $type = GuideType::Transport): array
    {
        $result = $this->client->request(
            method: 'GET',
            endpoint: sprintf('%s/{id}.json', $type->endpointPath()),
            pathParameters: ['id' => $id],
        );

        if (is_array($result) && isset($result[$type->payloadKey()]) && is_array($result[$type->payloadKey()])) {
            /** @var array<string, mixed> $unwrapped */
            $unwrapped = $result[$type->payloadKey()];

            return $unwrapped;
        }

        return is_array($result) ? $result : [];
    }

    /**
     * @param  GuideDto|array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function create(GuideDto|array $data, ?GuideType $type = null): array
    {
        $guide = $data instanceof GuideDto ? $data : GuideDto::fromArray($data);
        $effectiveType = $type ?? $guide->type;

        $rootKey = $effectiveType->payloadKey();
        $params = [$rootKey => $data instanceof GuideDto ? $data->toArray() : $data];

        $result = $this->client->request(
            method: 'POST',
            endpoint: sprintf('%s.json', $effectiveType->endpointPath()),
            params: $params,
        );

        $resultArray = is_array($result) ? $result : [];
        $unwrapped = $resultArray[$rootKey] ?? $resultArray;
        /** @var array<string, mixed> $unwrappedArray */
        $unwrappedArray = is_array($unwrapped) ? $unwrapped : [];

        $documentType = match ($effectiveType) {
            GuideType::Transport => DocumentType::TransportGuide,
            GuideType::Shipping => DocumentType::ShippingGuide,
            GuideType::Devolution => DocumentType::DevolutionGuide,
            GuideType::Global => DocumentType::GlobalGuide,
        };

        DocumentCreated::dispatch($unwrappedArray, $documentType);

        return $unwrappedArray;
    }

    /**
     * @param  GuideDto|array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function update(int $id, GuideDto|array $data, GuideType $type = GuideType::Transport): array
    {
        $rootKey = $type->payloadKey();
        $params = [$rootKey => $data instanceof GuideDto ? $data->toArray() : $data];

        $result = $this->client->request(
            method: 'PUT',
            endpoint: sprintf('%s/{id}.json', $type->endpointPath()),
            params: $params,
            pathParameters: ['id' => $id],
        );

        return is_array($result) ? $result : [];
    }

    protected function endpointRoot(): string
    {
        return 'guides';
    }

    protected function documentType(): DocumentType
    {
        return DocumentType::TransportGuide;
    }

    protected function statePayloadRootKey(): string
    {
        return 'guide';
    }
}
