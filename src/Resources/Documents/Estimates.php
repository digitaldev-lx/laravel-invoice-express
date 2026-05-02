<?php

declare(strict_types=1);

namespace DigitaldevLx\LaravelInvoiceExpress\Resources\Documents;

use DigitaldevLx\LaravelInvoiceExpress\Concerns\Resource\ChangesState;
use DigitaldevLx\LaravelInvoiceExpress\Concerns\Resource\GeneratesPdf;
use DigitaldevLx\LaravelInvoiceExpress\Concerns\Resource\HandlesRelatedDocuments;
use DigitaldevLx\LaravelInvoiceExpress\Concerns\Resource\SendsByEmail;
use DigitaldevLx\LaravelInvoiceExpress\DataTransferObjects\Estimate as EstimateDto;
use DigitaldevLx\LaravelInvoiceExpress\Enums\DocumentType;
use DigitaldevLx\LaravelInvoiceExpress\Enums\EstimateType;
use DigitaldevLx\LaravelInvoiceExpress\Events\DocumentCreated;

class Estimates extends Document
{
    use ChangesState;
    use GeneratesPdf;
    use HandlesRelatedDocuments;
    use SendsByEmail;

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function all(EstimateType $type = EstimateType::Quote, array $filters = []): array
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
    public function find(int $id, EstimateType $type = EstimateType::Quote): array
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
     * @param  EstimateDto|array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function create(EstimateDto|array $data, ?EstimateType $type = null): array
    {
        $estimate = $data instanceof EstimateDto ? $data : EstimateDto::fromArray($data);
        $effectiveType = $type ?? $estimate->type;

        $rootKey = $effectiveType->payloadKey();
        $params = [$rootKey => $data instanceof EstimateDto ? $data->toArray() : $data];

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
            EstimateType::Quote => DocumentType::Quote,
            EstimateType::Proforma => DocumentType::Proforma,
            EstimateType::FeesNote => DocumentType::FeesNote,
            EstimateType::Estimate => DocumentType::Estimate,
        };

        DocumentCreated::dispatch($unwrappedArray, $documentType);

        return $unwrappedArray;
    }

    /**
     * @param  EstimateDto|array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function update(int $id, EstimateDto|array $data, EstimateType $type = EstimateType::Quote): array
    {
        $rootKey = $type->payloadKey();
        $params = [$rootKey => $data instanceof EstimateDto ? $data->toArray() : $data];

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
        return 'estimates';
    }

    protected function documentType(): DocumentType
    {
        return DocumentType::Estimate;
    }

    protected function statePayloadRootKey(): string
    {
        return 'estimate';
    }
}
