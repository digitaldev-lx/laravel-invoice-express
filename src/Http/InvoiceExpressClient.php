<?php

declare(strict_types=1);

namespace DigitaldevLx\LaravelInvoiceExpress\Http;

use DigitaldevLx\LaravelInvoiceExpress\Exceptions\AuthenticationException;
use DigitaldevLx\LaravelInvoiceExpress\Exceptions\BadRequestException;
use DigitaldevLx\LaravelInvoiceExpress\Exceptions\ConnectionFailedException;
use DigitaldevLx\LaravelInvoiceExpress\Exceptions\InvoiceExpressException;
use DigitaldevLx\LaravelInvoiceExpress\Exceptions\NotFoundException;
use DigitaldevLx\LaravelInvoiceExpress\Exceptions\PdfDownloadException;
use DigitaldevLx\LaravelInvoiceExpress\Exceptions\RateLimitException;
use DigitaldevLx\LaravelInvoiceExpress\Exceptions\ServerException;
use DigitaldevLx\LaravelInvoiceExpress\Exceptions\ValidationException;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Throwable;

final class InvoiceExpressClient
{
    private const string BASE_URL_TEMPLATE = 'https://%s.app.invoicexpress.com/';

    public function __construct(
        private readonly string $accountName,
        private readonly string $apiKey,
        private readonly int $timeout = 15,
        private readonly int $retryTimes = 3,
        private readonly int $retryBackoffMs = 1000,
        private readonly int $rateLimitPerMinute = 780,
        private readonly ?CacheRepository $cache = null,
        private readonly ?LoggerInterface $logger = null,
        private readonly bool $retryWrites = false,
    ) {}

    public function accountName(): string
    {
        return $this->accountName;
    }

    /**
     * Issue a request to the InvoiceXpress API.
     *
     * @param  array<string, mixed>  $params  Body for POST/PUT, query for GET/DELETE.
     * @param  array<string, scalar>  $pathParameters  Replacements for `{key}` placeholders in `$endpoint`.
     * @return array<string, mixed>|string Decoded JSON, or raw body when `$expectsBinary` is true.
     */
    public function request(
        string $method,
        string $endpoint,
        array $params = [],
        array $pathParameters = [],
        bool $expectsBinary = false,
    ): array|string {
        $this->throttle();

        $resolvedEndpoint = $this->buildEndpoint($endpoint, $pathParameters);
        $upperMethod = strtoupper($method);

        $request = $this->httpClient($upperMethod);

        try {
            $response = match ($upperMethod) {
                'GET' => $request->get($resolvedEndpoint, $params),
                'POST' => $request->post($resolvedEndpoint, $params),
                'PUT' => $request->put($resolvedEndpoint, $params),
                'DELETE' => $request->delete($resolvedEndpoint, $params),
                default => throw new InvoiceExpressException("Unsupported HTTP method: {$method}"),
            };
        } catch (InvoiceExpressException $e) {
            throw $e;
        } catch (Throwable $e) {
            throw $this->sanitizedFailure($e, $resolvedEndpoint);
        }

        $this->logger?->debug('InvoiceXpress request', [
            'method' => $upperMethod,
            'endpoint' => $resolvedEndpoint,
            'status' => $response->status(),
        ]);

        return $this->handleResponse($response, $resolvedEndpoint, $expectsBinary);
    }

    public function useAccount(string $accountName, string $apiKey): self
    {
        return new self(
            accountName: $accountName,
            apiKey: $apiKey,
            timeout: $this->timeout,
            retryTimes: $this->retryTimes,
            retryBackoffMs: $this->retryBackoffMs,
            rateLimitPerMinute: $this->rateLimitPerMinute,
            cache: $this->cache,
            logger: $this->logger,
            retryWrites: $this->retryWrites,
        );
    }

    /**
     * Download a PDF from the (temporary, absolute) URL returned by the
     * `api/pdf/{id}.json` endpoint, honouring the configured timeout.
     *
     * The URL is never included in exception messages: it is a bearer link.
     *
     * @throws PdfDownloadException
     */
    public function download(string $url): string
    {
        if (! preg_match('#^https?://#i', $url)) {
            throw new PdfDownloadException('InvoiceXpress returned an invalid PDF URL.');
        }

        $this->throttle();

        try {
            $request = Http::timeout($this->timeout);

            if ($this->retryTimes > 0) {
                $backoff = $this->retryBackoffMs;
                $request = $request->retry(
                    times: $this->retryTimes,
                    sleepMilliseconds: static fn (int $attempt): int => $backoff * (2 ** ($attempt - 1)),
                    when: static fn (Throwable $e): bool => $e instanceof ConnectionException,
                    throw: false,
                );
            }

            $response = $request->get($url);
        } catch (Throwable $e) {
            $reason = $e instanceof ConnectionException ? 'connection error or timeout' : $e::class;

            throw new PdfDownloadException(
                'InvoiceXpress PDF download failed ('.$reason.').',
                code: 0,
                previous: new RuntimeException('PDF download failed (details withheld).'),
            );
        }

        if ($response->failed()) {
            throw new PdfDownloadException(
                'InvoiceXpress PDF download failed (HTTP '.$response->status().').',
                code: $response->status(),
            );
        }

        return $response->body();
    }

    private function httpClient(string $method = 'GET'): PendingRequest
    {
        $request = Http::baseUrl(sprintf(self::BASE_URL_TEMPLATE, $this->accountName))
            ->withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json; charset=utf-8',
            ])
            ->withQueryParameters(['api_key' => $this->apiKey])
            ->timeout($this->timeout)
            ->acceptJson();

        if ($this->retryTimes > 0) {
            $backoff = $this->retryBackoffMs;
            $request = $request->retry(
                times: $this->retryTimes,
                sleepMilliseconds: static fn (int $attempt): int => $backoff * (2 ** ($attempt - 1)),
                when: fn (Throwable $e): bool => $this->shouldRetry($e, $method),
                throw: false,
            );
        }

        return $request;
    }

    /**
     * @param  array<string, scalar>  $pathParameters
     */
    private function buildEndpoint(string $endpoint, array $pathParameters): string
    {
        $resolved = ltrim($endpoint, '/');

        foreach ($pathParameters as $key => $value) {
            $resolved = str_replace('{'.$key.'}', urlencode((string) $value), $resolved);
        }

        return $resolved;
    }

    private function throttle(): void
    {
        if ($this->cache === null) {
            return;
        }

        $minuteBucket = (int) (time() / 60);
        $key = sprintf(
            'invoiceexpress:rate:%s:%d',
            $this->accountName,
            $minuteBucket,
        );

        $count = $this->cache->increment($key);
        if (! is_int($count)) {
            $count = 1;
        }

        if ($count === 1) {
            $this->cache->put($key, 1, 70);
        }

        $threshold = (int) ($this->rateLimitPerMinute * 0.95);

        if ($count >= $threshold) {
            throw new RateLimitException(
                'Local InvoiceXpress rate limit reached (95% of '.$this->rateLimitPerMinute.' req/min).',
                retryAfter: 60,
            );
        }
    }

    /**
     * Only idempotent methods are retried on connection errors / 5xx: a write
     * that timed out or returned 5xx may already have been committed upstream,
     * and repeating it could issue a duplicate fiscal document. A 429 is safe
     * to repeat for any method because the request was rejected, not executed.
     * `retry.writes = true` restores the pre-3.3 behaviour.
     */
    private function shouldRetry(Throwable $e, string $method): bool
    {
        $idempotent = in_array($method, ['GET', 'HEAD'], true) || $this->retryWrites;

        if ($e instanceof ConnectionException) {
            return $idempotent;
        }

        if ($e instanceof RequestException) {
            $status = $e->response->status();

            if ($status === 429) {
                return true;
            }

            return $idempotent && in_array($status, [500, 502, 503, 504], true);
        }

        return false;
    }

    /**
     * Wrap a transport failure without leaking the api_key. Guzzle embeds the
     * full request URL (including the `api_key` query string) in its messages,
     * so neither the message nor the previous exception may carry it.
     */
    private function sanitizedFailure(Throwable $e, string $endpoint): InvoiceExpressException
    {
        $message = $this->scrub($e->getMessage());

        return new ConnectionFailedException(
            "InvoiceXpress connection error on {$endpoint}: ".$message,
            code: 0,
            previous: new RuntimeException($message),
        );
    }

    /**
     * Remove the api_key (raw, url-encoded and as a query parameter) from text.
     */
    private function scrub(string $text): string
    {
        if ($this->apiKey !== '') {
            $text = str_replace([$this->apiKey, urlencode($this->apiKey), rawurlencode($this->apiKey)], '[redacted]', $text);
        }

        return (string) preg_replace('/(api_key=)[^&\s"\']*/i', '$1[redacted]', $text);
    }

    /**
     * @return array<string, mixed>|string
     */
    private function handleResponse(Response $response, string $endpoint, bool $expectsBinary): array|string
    {
        $status = $response->status();

        if ($status === 429) {
            $retryAfter = (int) $response->header('Retry-After');
            throw new RateLimitException(
                "InvoiceXpress rate limit exceeded on {$endpoint}.",
                retryAfter: $retryAfter > 0 ? $retryAfter : 60,
            );
        }

        if ($status === 401) {
            throw new AuthenticationException(
                "Authentication failed for InvoiceXpress on {$endpoint}.",
                accountName: $this->accountName,
            );
        }

        if ($status === 404) {
            throw new NotFoundException("Resource not found on {$endpoint}.");
        }

        if ($status === 422) {
            $body = (array) $response->json();
            throw ValidationException::fromResponse($body, statusCode: $status);
        }

        if ($status === 400) {
            throw new BadRequestException(
                "InvoiceXpress bad request on {$endpoint}: ".$this->bodySnippet($response),
                code: 400,
            );
        }

        if ($status >= 500) {
            throw new ServerException(
                "InvoiceXpress server error on {$endpoint} (HTTP {$status}): ".$this->bodySnippet($response),
                code: $status,
            );
        }

        if ($response->failed()) {
            throw new InvoiceExpressException(
                "InvoiceXpress error on {$endpoint} (HTTP {$status}): ".$this->bodySnippet($response),
                code: $status,
            );
        }

        if ($expectsBinary) {
            return $response->body();
        }

        return (array) ($response->json() ?? []);
    }

    /**
     * Bounded slice of an upstream response body for use in exception
     * messages — avoids dumping large/sensitive upstream payloads into logs
     * or error pages when APP_DEBUG is on.
     */
    private function bodySnippet(Response $response): string
    {
        return Str::limit($this->scrub($response->body()), 500);
    }
}
