<?php declare(strict_types=1);

namespace Fazland\SkebbyRestClient\DataStructure;

use Fazland\SkebbyRestClient\Constant\SendMethods;
use Fazland\SkebbyRestClient\Exception\EmptyResponseException;
use Fazland\SkebbyRestClient\Exception\UnknownErrorResponseException;
use Fazland\SkebbyRestClient\Exception\XmlLoadException;

/**
 * Represents a Skebby Response.
 *
 * @author Massimiliano Braglia <massimiliano.braglia@fazland.com>
 */
class Response
{
    /**
     * @var string
     */
    private $status;

    /**
     * @var string
     */
    private $code;

    /**
     * @var string
     */
    private $errorMessage;

    /**
     * @var string
     */
    private $messageId;

    /**
     * @param string $rawResponse
     *
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

            if (false === $doc) {
                throw new XmlLoadException($rawResponse, libxml_get_errors());
            }
        } catch (\Throwable $e) {
            throw new UnknownErrorResponseException($e->getMessage(), $rawResponse);
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
     *
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * Whether the response is successful or not.
     *
     * @return bool
     */
    public function isSuccessful(): bool
    {
        return 'success' === $this->status;
    }

    /**
     * Gets the code.
     *
     * @return string|null
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Gets the error message.
     *
     * @return string|null
     */
    public function getErrorMessage()
    {
        return $this->errorMessage;
    }

    /**
     * Gets the message id.
     *
     * @return string|null
     */
    public function getMessageId()
    {
        return $this->messageId;
    }

    public function __toString(): string
    {
        return "Response status: $this->status, code: $this->code, error_message: $this->errorMessage, message_id: $this->messageId";
    }
}
