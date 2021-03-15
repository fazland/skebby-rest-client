<?php

declare(strict_types=1);

namespace Fazland\SkebbyRestClient\Exception;

use Throwable;

use function sprintf;

/**
 * Represents an unknown error response exception.
 */
class UnknownErrorResponseException extends Exception
{
    private ?string $response;

    public function __construct(string $message = '', ?string $response = null, ?Throwable $previous = null)
    {
        $this->response = $response;

        parent::__construct($message, 0, $previous);
    }

    public function __toString(): string
    {
        return sprintf("[%s] %s\nResponse:\n%s", static::class, $this->message, $this->response);
    }

    /**
     * Gets the Unknown error response.
     */
    public function getResponse(): ?string
    {
        return $this->response;
    }
}
