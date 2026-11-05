<?php

namespace App\Services\Trace;

use RuntimeException;

/** A refusal the API returns as {message, errors} with its own status. */
final class TraceException extends RuntimeException
{
    public function __construct(string $message, public readonly int $status = 422, public readonly array $errors = [])
    {
        parent::__construct($message);
    }

    public static function field(string $field, string $message, int $status = 422): self
    {
        return new self($message, $status, [$field => [$message]]);
    }
}
