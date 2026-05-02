<?php

declare(strict_types=1);

namespace DigitaldevLx\LaravelInvoiceExpress\Enums;

enum DocumentState: string
{
    case Draft = 'draft';
    case Final = 'final';
    case Settled = 'settled';
    case Canceled = 'canceled';
    case Deleted = 'deleted';
    case SecondCopy = 'second_copy';

    public function apiAction(): string
    {
        return match ($this) {
            self::Draft => 'draft',
            self::Final => 'finalized',
            self::Settled => 'settled',
            self::Canceled => 'canceled',
            self::Deleted => 'deleted',
            self::SecondCopy => 'second_copy',
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Rascunho',
            self::Final => 'Finalizado',
            self::Settled => 'Liquidado',
            self::Canceled => 'Anulado',
            self::Deleted => 'Apagado',
            self::SecondCopy => 'Segunda Via',
        };
    }

    public function isTerminal(): bool
    {
        return in_array($this, [self::Settled, self::Canceled, self::Deleted], true);
    }
}
