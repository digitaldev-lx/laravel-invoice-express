<?php

declare(strict_types=1);

namespace DigitaldevLx\LaravelInvoiceExpress\Enums;

enum GuideType: string
{
    case Transport = 'TransportGuide';
    case Shipping = 'ShippingGuide';
    case Devolution = 'DevolutionGuide';
    case Global = 'GlobalGuide';

    public function payloadKey(): string
    {
        return match ($this) {
            self::Transport => 'transport',
            self::Shipping => 'shipping',
            self::Devolution => 'devolution',
            self::Global => 'globals',
        };
    }

    public function endpointPath(): string
    {
        return match ($this) {
            self::Transport => 'transports',
            self::Shipping => 'shippings',
            self::Devolution => 'devolutions',
            self::Global => 'globals',
        };
    }
}
