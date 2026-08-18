<?php

declare(strict_types=1);

namespace DigitaldevLx\LaravelInvoiceExpress\Exceptions;

final class ValidationException extends InvoiceExpressException
{
    /**
     * @param  array<string, array<int, string>>  $errors  Map of field => messages. Empty when the
     *                                                     API reported the failure without field names.
     * @param  array<int, string>  $messages  Every human-readable message, whatever shape was used.
     */
    public function __construct(
        string $message,
        public readonly array $errors = [],
        int $code = 422,
        ?\Throwable $previous = null,
        public readonly array $messages = [],
    ) {
        parent::__construct($message, $code, $previous);
    }

    /**
     * Parse an InvoiceXpress validation response.
     *
     * InvoiceXpress is not consistent about how it reports a 422. Shapes seen in the wild:
     *
     *   { "errors": [ { "error": "Razão de isenção deve ter uma opção selecionada" } ] }
     *   { "errors": [ "Some message" ] }
     *   { "errors": { "field": ["message1", "message2"] } }
     *   { "field": ["message"] }
     *
     * Only the keyed shapes carry a field name, and the unkeyed list is what the document
     * write endpoints actually return — parsing just the keyed shapes left every failed
     * create/update surfacing as a bare "validation error" with nothing actionable in it.
     *
     * @param  array<string, mixed>  $response
     */
    public static function fromResponse(array $response, int $statusCode = 422): self
    {
        $rawErrors = isset($response['errors']) && is_array($response['errors'])
            ? $response['errors']
            : $response;

        $errors = [];
        $messages = [];

        foreach ($rawErrors as $field => $entry) {
            // Unkeyed list: `[ {"error": "..."}, "..." ]`
            if (! is_string($field)) {
                self::pushMessage($entry, $messages);

                continue;
            }

            // A bare `{"error": "..."}` / `{"message": "..."}` is a message, not a field.
            if (($field === 'error' || $field === 'message') && is_string($entry)) {
                self::pushMessage($entry, $messages);

                continue;
            }

            $collected = [];

            if (is_string($entry)) {
                self::pushMessage($entry, $collected);
            } elseif (is_array($entry)) {
                foreach ($entry as $msg) {
                    self::pushMessage($msg, $collected);
                }
            }

            if ($collected !== []) {
                $errors[$field] = $collected;

                foreach ($collected as $text) {
                    $messages[] = "{$field}: {$text}";
                }
            }
        }

        $message = $messages !== []
            ? 'InvoiceXpress validation error: '.implode('; ', $messages)
            : 'InvoiceXpress validation error';

        return new self(
            message: $message,
            errors: $errors,
            code: $statusCode,
            messages: $messages,
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

    /**
     * Accept a plain string or an `{error}` / `{message}` wrapper.
     *
     * @param  array<int, string>  $into
     */
    private static function pushMessage(mixed $entry, array &$into): void
    {
        if (is_string($entry)) {
            $text = trim($entry);

            if ($text !== '') {
                $into[] = $text;
            }

            return;
        }

        if (is_array($entry)) {
            $text = $entry['error'] ?? $entry['message'] ?? null;

            if (is_string($text) && trim($text) !== '') {
                $into[] = trim($text);
            }
        }
    }
}
