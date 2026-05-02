<?php

declare(strict_types=1);

namespace DigitaldevLx\LaravelInvoiceExpress\Concerns\Resource;

trait ListsResources
{
    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function all(array $filters = []): array
    {
        /** @var array<string, mixed> $result */
        $result = $this->call('all', $filters);

        return $result;
    }
}
