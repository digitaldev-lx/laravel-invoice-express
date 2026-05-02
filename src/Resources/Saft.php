<?php

declare(strict_types=1);

namespace DigitaldevLx\LaravelInvoiceExpress\Resources;

use DigitaldevLx\LaravelInvoiceExpress\Attributes\InvoiceExpressEndpoint;

final class Saft extends Resource
{
    /**
     * Generate a SAF-T XML export for the requested year/month.
     *
     * Returns the raw XML body.
     */
    #[InvoiceExpressEndpoint(method: 'GET', path: 'saft.xml', binary: true)]
    public function generate(int $year, int $month): string
    {
        $result = $this->call(__FUNCTION__, [
            'year' => $year,
            'month' => $month,
        ]);

        return is_string($result) ? $result : '';
    }
}
