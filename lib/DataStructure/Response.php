<?php

namespace Fazland\SkebbyRestClient\DataStructure;

use Fazland\SkebbyRestClient\Constant\SendMethods;
use Fazland\SkebbyRestClient\Exception\EmptyResponseException;
use Fazland\SkebbyRestClient\Exception\UnknownErrorResponseException;

/**
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
    public function __construct($rawResponse)
    {
        if (empty($rawResponse)) {
            throw new EmptyResponseException();
        }

        $doc = simplexml_load_string($rawResponse);
        foreach (SendMethods::all() as $method) {
            if (! isset($doc->$method)) {
                continue;
            }

            $element = $doc->$method;

            if (! isset($element->status)) {
                throw new UnknownErrorResponseException('Missing response status value from Skebby');
            }

            $this->status = (string)$element->status;
            $this->messageId = isset($element->id) ? (string)$element->id : null;

            if (! $this->isSuccessful()) {
                $this->code = isset($element->code) ? (string)$element->code : null;
                $this->errorMessage = isset($element->message) ? (string)$element->message : 'Unknown error';
            }

            return;
        }

        throw new UnknownErrorResponseException('Missing response status value from Skebby');
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    public function isSuccessful()
    {
        return 'success' === $this->status;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @return string
     */
    public function getErrorMessage()
    {
        return $this->errorMessage;
    }

    /**
     * @return string
     */
    public function getMessageId()
    {
        return $this->messageId;
    }
}
