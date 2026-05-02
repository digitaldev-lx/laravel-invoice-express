<?php

declare(strict_types=1);

namespace DigitaldevLx\LaravelInvoiceExpress\Enums;

enum DocumentType: string
{
    case Invoice = 'Invoice';
    case SimplifiedInvoice = 'SimplifiedInvoice';
    case InvoiceReceipt = 'InvoiceReceipt';
    case CreditNote = 'CreditNote';
    case DebitNote = 'DebitNote';
    case Receipt = 'Receipt';
    case CashInvoice = 'CashInvoice';
    case VatMossInvoice = 'VatMossInvoice';

    case Quote = 'Quote';
    case Proforma = 'Proforma';
    case FeesNote = 'FeesNote';
    case Estimate = 'Estimate';

    case TransportGuide = 'TransportGuide';
    case ShippingGuide = 'ShippingGuide';
    case DevolutionGuide = 'DevolutionGuide';
    case GlobalGuide = 'GlobalGuide';

    case PurchaseOrder = 'PurchaseOrder';

    public function endpointRoot(): string
    {
        return match ($this) {
            self::Invoice => 'invoices',
            self::SimplifiedInvoice => 'simplified_invoices',
            self::InvoiceReceipt => 'invoice_receipts',
            self::CreditNote => 'credit_notes',
            self::DebitNote => 'debit_notes',
            self::Receipt => 'receipts',
            self::CashInvoice => 'cash_invoices',
            self::VatMossInvoice => 'vat_moss_invoices',

            self::Quote, self::Proforma, self::FeesNote, self::Estimate => 'estimates',

            self::TransportGuide,
            self::ShippingGuide,
            self::DevolutionGuide,
            self::GlobalGuide => 'guides',

            self::PurchaseOrder => 'purchase_orders',
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::Invoice => 'Fatura',
            self::SimplifiedInvoice => 'Fatura Simplificada',
            self::InvoiceReceipt => 'Fatura-Recibo',
            self::CreditNote => 'Nota de Crédito',
            self::DebitNote => 'Nota de Débito',
            self::Receipt => 'Recibo',
            self::CashInvoice => 'Fatura à Pronto',
            self::VatMossInvoice => 'Fatura VAT MOSS',
            self::Quote => 'Orçamento',
            self::Proforma => 'Pró-forma',
            self::FeesNote => 'Nota de Honorários',
            self::Estimate => 'Estimativa',
            self::TransportGuide => 'Guia de Transporte',
            self::ShippingGuide => 'Guia de Remessa',
            self::DevolutionGuide => 'Guia de Devolução',
            self::GlobalGuide => 'Guia Global',
            self::PurchaseOrder => 'Encomenda',
        };
    }

    public function isInvoiceLike(): bool
    {
        return in_array($this, [
            self::Invoice,
            self::SimplifiedInvoice,
            self::InvoiceReceipt,
            self::CreditNote,
            self::DebitNote,
            self::CashInvoice,
            self::VatMossInvoice,
        ], true);
    }

    public function isEstimate(): bool
    {
        return in_array($this, [
            self::Quote,
            self::Proforma,
            self::FeesNote,
            self::Estimate,
        ], true);
    }

    public function isGuide(): bool
    {
        return in_array($this, [
            self::TransportGuide,
            self::ShippingGuide,
            self::DevolutionGuide,
            self::GlobalGuide,
        ], true);
    }

    public function supportsQrCode(): bool
    {
        return $this->isInvoiceLike() || $this->isGuide();
    }

    public function supportsPayment(): bool
    {
        return $this->isInvoiceLike();
    }
}
