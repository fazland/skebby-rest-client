<?php

declare(strict_types=1);

namespace Fazland\SkebbyRestClient\Exception;

use Throwable;

/**
 * Represents an unknown error response exception.
 */
class UnknownErrorResponseException extends Exception
{
    private ?string $response = null;

    public function __construct(string $message = '', ?string $response = null, ?Throwable $previous = null)
    {
        $this->response = $response;

        parent::__construct($message, 0, $previous);
    }

    public function __toString(): string
    {
        return '[' . static::class . '] ' . $this->message . "\n" .
            'Response: ' . "\n" .
            $this->response;
    }

    /**
     * Gets the Unknown error response.
     */
    public function getResponse(): ?string
    {
        return $this->response;
    }
}
