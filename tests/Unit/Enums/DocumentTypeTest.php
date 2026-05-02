<?php

declare(strict_types=1);

use DigitaldevLx\LaravelInvoiceExpress\Enums\DocumentType;

it('maps each document type to the expected endpoint root', function (): void {
    expect(DocumentType::Invoice->endpointRoot())->toBe('invoices');
    expect(DocumentType::SimplifiedInvoice->endpointRoot())->toBe('simplified_invoices');
    expect(DocumentType::InvoiceReceipt->endpointRoot())->toBe('invoice_receipts');
    expect(DocumentType::CreditNote->endpointRoot())->toBe('credit_notes');
    expect(DocumentType::DebitNote->endpointRoot())->toBe('debit_notes');
    expect(DocumentType::Receipt->endpointRoot())->toBe('receipts');
    expect(DocumentType::CashInvoice->endpointRoot())->toBe('cash_invoices');
    expect(DocumentType::VatMossInvoice->endpointRoot())->toBe('vat_moss_invoices');
    expect(DocumentType::Quote->endpointRoot())->toBe('estimates');
    expect(DocumentType::Proforma->endpointRoot())->toBe('estimates');
    expect(DocumentType::FeesNote->endpointRoot())->toBe('estimates');
    expect(DocumentType::Estimate->endpointRoot())->toBe('estimates');
    expect(DocumentType::TransportGuide->endpointRoot())->toBe('guides');
    expect(DocumentType::ShippingGuide->endpointRoot())->toBe('guides');
    expect(DocumentType::DevolutionGuide->endpointRoot())->toBe('guides');
    expect(DocumentType::GlobalGuide->endpointRoot())->toBe('guides');
    expect(DocumentType::PurchaseOrder->endpointRoot())->toBe('purchase_orders');
});

it('classifies invoice-like documents', function (): void {
    expect(DocumentType::Invoice->isInvoiceLike())->toBeTrue();
    expect(DocumentType::CreditNote->isInvoiceLike())->toBeTrue();
    expect(DocumentType::Quote->isInvoiceLike())->toBeFalse();
    expect(DocumentType::TransportGuide->isInvoiceLike())->toBeFalse();
});

it('classifies estimates', function (): void {
    expect(DocumentType::Quote->isEstimate())->toBeTrue();
    expect(DocumentType::Proforma->isEstimate())->toBeTrue();
    expect(DocumentType::Invoice->isEstimate())->toBeFalse();
});

it('classifies guides', function (): void {
    expect(DocumentType::TransportGuide->isGuide())->toBeTrue();
    expect(DocumentType::GlobalGuide->isGuide())->toBeTrue();
    expect(DocumentType::Invoice->isGuide())->toBeFalse();
});

it('exposes QR code support for invoices and guides', function (): void {
    expect(DocumentType::Invoice->supportsQrCode())->toBeTrue();
    expect(DocumentType::TransportGuide->supportsQrCode())->toBeTrue();
    expect(DocumentType::Quote->supportsQrCode())->toBeFalse();
    expect(DocumentType::PurchaseOrder->supportsQrCode())->toBeFalse();
});

it('exposes payment support only for invoice-like documents', function (): void {
    expect(DocumentType::Invoice->supportsPayment())->toBeTrue();
    expect(DocumentType::CreditNote->supportsPayment())->toBeTrue();
    expect(DocumentType::Quote->supportsPayment())->toBeFalse();
    expect(DocumentType::TransportGuide->supportsPayment())->toBeFalse();
});

it('returns a Portuguese label for each type', function (): void {
    expect(DocumentType::Invoice->label())->toBe('Fatura');
    expect(DocumentType::CreditNote->label())->toBe('Nota de Crédito');
    expect(DocumentType::TransportGuide->label())->toBe('Guia de Transporte');
});
