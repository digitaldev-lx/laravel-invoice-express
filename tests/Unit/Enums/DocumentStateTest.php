<?php

declare(strict_types=1);

use DigitaldevLx\LaravelInvoiceExpress\Enums\DocumentState;

it('maps each state to its API action verb', function (): void {
    expect(DocumentState::Draft->apiAction())->toBe('draft');
    expect(DocumentState::Final->apiAction())->toBe('finalized');
    expect(DocumentState::Settled->apiAction())->toBe('settled');
    expect(DocumentState::Canceled->apiAction())->toBe('canceled');
    expect(DocumentState::Deleted->apiAction())->toBe('deleted');
    expect(DocumentState::SecondCopy->apiAction())->toBe('second_copy');
});

it('flags terminal states', function (): void {
    expect(DocumentState::Draft->isTerminal())->toBeFalse();
    expect(DocumentState::Final->isTerminal())->toBeFalse();
    expect(DocumentState::Settled->isTerminal())->toBeTrue();
    expect(DocumentState::Canceled->isTerminal())->toBeTrue();
    expect(DocumentState::Deleted->isTerminal())->toBeTrue();
});

it('returns Portuguese labels', function (): void {
    expect(DocumentState::Final->label())->toBe('Finalizado');
    expect(DocumentState::Settled->label())->toBe('Liquidado');
    expect(DocumentState::Canceled->label())->toBe('Anulado');
});
