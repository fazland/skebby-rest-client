<?php

declare(strict_types=1);

namespace Fazland\SkebbyRestClient\DataStructure;

use Fazland\SkebbyRestClient\Constant\SendMethods;
use Fazland\SkebbyRestClient\Exception\EmptyResponseException;
use Fazland\SkebbyRestClient\Exception\UnknownErrorResponseException;
use Fazland\SkebbyRestClient\Exception\XmlLoadException;
use Throwable;

use function libxml_get_errors;
use function libxml_use_internal_errors;
use function simplexml_load_string;
use function sprintf;

/**
 * Represents a Skebby Response.
 */
class Response
{
    private string $status;
    private ?string $code = null;
    private ?string $errorMessage = null;
    private ?string $messageId = null;

    /**
     * @throws EmptyResponseException
     * @throws UnknownErrorResponseException
     */
    public function __construct(string $rawResponse)
    {
        if (empty($rawResponse)) {
            throw new EmptyResponseException();
        }

        $doc = null;

        $useErrors = libxml_use_internal_errors(true);
        try {
            $doc = @simplexml_load_string($rawResponse);

            if ($doc === false) {
                throw new XmlLoadException($rawResponse, libxml_get_errors());
            }
        } catch (Throwable $e) {
            throw new UnknownErrorResponseException($e->getMessage(), $rawResponse, $e);
        } finally {
            libxml_use_internal_errors($useErrors);
        }

        foreach (SendMethods::all() as $method) {
            if (! isset($doc->$method)) {
                continue;
            }

            $element = $doc->$method;

            if (! isset($element->status)) {
                throw new UnknownErrorResponseException('Missing response status value from Skebby', $rawResponse);
            }

            $this->status = (string) $element->status;
            $this->messageId = isset($element->id) ? (string) $element->id : null;

            if (! $this->isSuccessful()) {
                $this->code = isset($element->code) ? (string) $element->code : null;
                $this->errorMessage = isset($element->message) ? (string) $element->message : 'Unknown error';
            }

            return;
        }

        throw new UnknownErrorResponseException('Missing response status value from Skebby', $rawResponse);
    }

    /**
     * Gets the status.
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * Whether the response is successful or not.
     */
    public function isSuccessful(): bool
    {
        return $this->status === 'success';
    }

    /**
     * Gets the code.
     */
    public function getCode(): ?string
    {
        return $this->code;
    }

    /**
     * Gets the error message.
     */
    public function getErrorMessage(): ?string
    {
        return $this->errorMessage;
    }

    /**
     * Gets the message id.
     */
    public function getMessageId(): ?string
    {
        return $this->messageId;
    }

    public function __toString(): string
    {
        return sprintf('Response status: %s, code: %s, error_message: %s, message_id: %s', $this->status, $this->status ?? '', $this->errorMessage ?? '', $this->messageId ?? '');
    }
}
