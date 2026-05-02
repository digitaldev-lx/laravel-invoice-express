<?php

declare(strict_types=1);

namespace DigitaldevLx\LaravelInvoiceExpress\Concerns\Resource;

trait GetsSingleResource
{
    /**
     * @return array<string, mixed>
     */
    public function find(int $id): array
    {
        /** @var array<string, mixed> $result */
        $result = $this->call('find', [], ['id' => $id]);

        return $result;
    }
}
