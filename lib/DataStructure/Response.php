<?php

namespace Fazland\SkebbyRestClient\DataStructure;

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

        parse_str($rawResponse, $response);

        if (! isset($response['status'])) {
            throw new UnknownErrorResponseException("Missing response status value from Skebby");
        }

        $this->status = $response['status'];
        $this->code = isset($response['code']) ? $response['code'] : null;
        $this->errorMessage = isset($response['message']) ? $response['message'] : "Unknown error";
        $this->messageId = isset($response['id']) ? $response['id'] : null;
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
        return "success" === $this->status;
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
