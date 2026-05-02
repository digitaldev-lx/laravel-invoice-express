<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;

it('runs the test-connection command successfully when credentials are valid', function (): void {
    Http::fake([
        '*invoicexpress.com/accounts.json*' => Http::response(['accounts' => []]),
    ]);

    $this->artisan('invoiceexpress:test-connection')
        ->expectsOutputToContain('Testing InvoiceXpress connection for account: test-account')
        ->expectsOutputToContain('Connection OK.')
        ->assertExitCode(0);
});

it('reports failure when the API rejects the credentials', function (): void {
    Http::fake([
        '*invoicexpress.com/accounts.json*' => Http::response([], 401),
    ]);

    $this->artisan('invoiceexpress:test-connection')
        ->expectsOutputToContain('Connection failed:')
        ->assertExitCode(1);
});

it('switches to a different account via --account/--key', function (): void {
    Http::fake([
        '*' => Http::response([]),
    ]);

    $this->artisan('invoiceexpress:test-connection', [
        '--account' => 'another',
        '--key' => 'another-key',
    ])
        ->expectsOutputToContain('Testing InvoiceXpress connection for account: another')
        ->assertExitCode(0);
});

it('lists sequences in a table', function (): void {
    Http::fake([
        '*invoicexpress.com/sequences.json*' => Http::response([
            'sequences' => [
                ['id' => 1, 'serie' => '2026', 'document_type' => 'Invoice', 'current_sequence_number' => 100, 'default_sequence' => true],
            ],
        ]),
    ]);

    $this->artisan('invoiceexpress:sync-sequences')
        ->assertExitCode(0);
});

it('writes the SAF-T XML to disk when --out is provided', function (): void {
    Http::fake([
        '*invoicexpress.com/saft.xml*' => Http::response('<?xml version="1.0"?><AuditFile></AuditFile>'),
    ]);

    $tmp = tempnam(sys_get_temp_dir(), 'saft-').'.xml';

    $this->artisan('invoiceexpress:saft', ['--year' => 2026, '--month' => 4, '--out' => $tmp])
        ->expectsOutputToContain('SAF-T XML saved to '.$tmp)
        ->assertExitCode(0);

    expect(file_get_contents($tmp))->toContain('<AuditFile>');

    @unlink($tmp);
});

it('rejects an invalid month for the SAF-T command', function (): void {
    $this->artisan('invoiceexpress:saft', ['--year' => 2026, '--month' => 13])
        ->expectsOutputToContain('Invalid month: 13')
        ->assertExitCode(1);
});
