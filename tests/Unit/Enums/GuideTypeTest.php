<?php

declare(strict_types=1);

use DigitaldevLx\LaravelInvoiceExpress\Enums\GuideType;

it('exposes the endpoint path per guide type', function (): void {
    expect(GuideType::Transport->endpointPath())->toBe('transports');
    expect(GuideType::Shipping->endpointPath())->toBe('shippings');
    expect(GuideType::Devolution->endpointPath())->toBe('devolutions');
    expect(GuideType::Global->endpointPath())->toBe('globals');
});
