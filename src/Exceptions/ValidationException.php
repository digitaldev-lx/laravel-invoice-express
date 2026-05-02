<?php

declare(strict_types=1);

namespace DigitaldevLx\LaravelInvoiceExpress\Exceptions;

final class ValidationException extends InvoiceExpressException
{
    /**
     * @param  array<string, array<int, string>>  $errors  Map of field => messages
     */
    public function __construct(
        string $message,
        public readonly array $errors = [],
        int $code = 422,
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }

    /**
     * Parse an InvoiceXpress validation response.
     *
     * The expected format is `{ "errors": { "field": ["message1", "message2"] } }`
     * or, for some endpoints, a flat `{ "field": ["message"] }`.
     *
     * @param  array<string, mixed>  $response
     */
    public static function fromResponse(array $response, int $statusCode = 422): self
    {
        $rawErrors = isset($response['errors']) && is_array($response['errors'])
            ? $response['errors']
            : $response;

        $errors = [];

        foreach ($rawErrors as $field => $messages) {
            if (! is_string($field)) {
                continue;
            }

            if (is_string($messages)) {
                $errors[$field] = [$messages];

                continue;
            }

            if (is_array($messages)) {
                $errors[$field] = array_values(array_filter(
                    $messages,
                    static fn (mixed $msg): bool => is_string($msg),
                ));
            }
        }

        $fieldNames = array_keys($errors);
        $message = $fieldNames !== []
            ? 'InvoiceXpress validation error on fields: '.implode(', ', $fieldNames)
            : 'InvoiceXpress validation error';

        return new self(
            message: $message,
            errors: $errors,
            code: $statusCode,
        );
    }

    /**
     * @return array<string, string> field => first error message
     */
    public function getFieldErrors(): array
    {
        $result = [];

        foreach ($this->errors as $field => $messages) {
            $result[$field] = $messages[0] ?? '';
        }

        return $result;
    }

    public function hasFieldError(string $field): bool
    {
        return array_key_exists($field, $this->errors);
    }

    public function getFirstError(?string $field = null): ?string
    {
        if ($field !== null) {
            return $this->errors[$field][0] ?? null;
        }

        foreach ($this->errors as $messages) {
            if (isset($messages[0])) {
                return $messages[0];
            }
        }

        return null;
    }
}
