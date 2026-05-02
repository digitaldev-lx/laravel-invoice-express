<?php

declare(strict_types=1);

namespace DigitaldevLx\LaravelInvoiceExpress\Enums;

enum PaymentMethod: string
{
    case Cash = 'NU';
    case Cheque = 'CH';
    case BankTransfer = 'TB';
    case DirectDebit = 'CD';
    case MultibancoReference = 'MB';
    case MBWay = 'MW';
    case CreditCard = 'CC';
    case PayPal = 'PP';
    case PromissoryNote = 'LC';
    case Compensation = 'CS';
    case Other = 'OU';

    public function code(): string
    {
        return $this->value;
    }

    public function label(): string
    {
        return match ($this) {
            self::Cash => 'Numerário',
            self::Cheque => 'Cheque',
            self::BankTransfer => 'Transferência Bancária',
            self::DirectDebit => 'Débito Directo',
            self::MultibancoReference => 'Referência Multibanco',
            self::MBWay => 'MB Way',
            self::CreditCard => 'Cartão de Crédito',
            self::PayPal => 'PayPal',
            self::PromissoryNote => 'Letra/Livrança',
            self::Compensation => 'Compensação',
            self::Other => 'Outro',
        };
    }
}
