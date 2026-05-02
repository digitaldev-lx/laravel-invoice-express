<?php

declare(strict_types=1);

namespace DigitaldevLx\LaravelInvoiceExpress\Concerns\Resource;

trait DeletesResource
{
    /**
     * @return array<string, mixed>
     */
    public function delete(int $id): array
    {
        /** @var array<string, mixed> $result */
        $result = $this->call('delete', [], ['id' => $id]);

        return $result;
    }
}
