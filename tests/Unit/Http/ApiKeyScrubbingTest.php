<?php

declare(strict_types=1);

use DigitaldevLx\LaravelInvoiceExpress\Exceptions\ConnectionFailedException;
use DigitaldevLx\LaravelInvoiceExpress\Exceptions\InvoiceExpressException;
use DigitaldevLx\LaravelInvoiceExpress\Exceptions\PdfDownloadException;
use DigitaldevLx\LaravelInvoiceExpress\Exceptions\ServerException;
use DigitaldevLx\LaravelInvoiceExpress\Http\InvoiceExpressClient;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

const LEAKY_KEY = 'sup3r-s3cret-key';

function leakyClient(): InvoiceExpressClient
{
    return new InvoiceExpressClient(accountName: 'co', apiKey: LEAKY_KEY, retryTimes: 0);
}

function chainToString(Throwable $e): string
{
    $out = '';
    do {
        $out .= $e->getMessage().' '.$e->getTraceAsString().' ';
    } while ($e = $e->getPrevious());

    return $out;
}

it('scrubs the api_key from connection errors, message and previous', function (): void {
    Http::fake(['*' => fn () => throw new ConnectionException(
        'cURL error 28: Operation timed out for https://co.app.invoicexpress.com/invoices.json?api_key='.LEAKY_KEY
    )]);

    try {
        leakyClient()->request('GET', 'invoices.json');
        $this->fail('Expected exception');
    } catch (InvoiceExpressException $e) {
        expect($e)->toBeInstanceOf(ConnectionFailedException::class);
        expect($e->getMessage())->not->toContain(LEAKY_KEY)->toContain('[redacted]');
        expect($e->getPrevious())->not->toBeInstanceOf(ConnectionException::class);
        expect($e->getPrevious()?->getMessage())->not->toContain(LEAKY_KEY);
        expect(chainToString($e))->not->toContain('api_key='.LEAKY_KEY);
    }
});

it('scrubs a url-encoded api_key too', function (): void {
    $key = 'a+b/c=d';
    Http::fake(['*' => fn () => throw new ConnectionException('failed ?api_key='.urlencode($key).'&x=1')]);

    $client = new InvoiceExpressClient(accountName: 'co', apiKey: $key, retryTimes: 0);

    try {
        $client->request('GET', 'x.json');
        $this->fail('Expected exception');
    } catch (InvoiceExpressException $e) {
        expect($e->getMessage())->not->toContain(urlencode($key))->not->toContain($key);
    }
});

it('scrubs the api_key from echoed upstream error bodies', function (): void {
    Http::fake(['*' => Http::response('bad ?api_key='.LEAKY_KEY, 500)]);

    try {
        leakyClient()->request('GET', 'x.json');
        $this->fail('Expected exception');
    } catch (ServerException $e) {
        expect($e->getMessage())->not->toContain(LEAKY_KEY);
    }
});

it('still throws unsupported-method errors unchanged', function (): void {
    leakyClient()->request('PATCH', 'x.json');
})->throws(InvoiceExpressException::class, 'Unsupported HTTP method');

it('downloads a PDF through the http client', function (): void {
    Http::fake(['files.example/*' => Http::response('%PDF-bytes', 200)]);

    expect(leakyClient()->download('https://files.example/a.pdf'))->toBe('%PDF-bytes');
});

it('raises a typed exception on PDF timeout without leaking the link', function (): void {
    Http::fake(['*' => fn () => throw new ConnectionException('timeout https://files.example/a.pdf?token=SECRETLINK')]);

    try {
        leakyClient()->download('https://files.example/a.pdf?token=SECRETLINK');
        $this->fail('Expected exception');
    } catch (PdfDownloadException $e) {
        expect(chainToString($e))->not->toContain('SECRETLINK');
    }
});

it('raises a typed exception on PDF HTTP errors', function (): void {
    Http::fake(['*' => Http::response('nope', 403)]);

    leakyClient()->download('https://files.example/a.pdf');
})->throws(PdfDownloadException::class, 'HTTP 403');

it('rejects non-http PDF urls', function (): void {
    leakyClient()->download('file:///etc/passwd');
})->throws(PdfDownloadException::class, 'invalid PDF URL');
