<?php

namespace Fazland\SkebbyRestClient\Exception;

/**
 * @author Massimiliano Braglia <massimiliano.braglia@fazland.com>
 */
class UnknownErrorResponseException extends Exception
{
    /**
     * @var string
     */
    private $response;

    /**
     * UnknownErrorResponseException constructor.
     * @param string $message
     * @param string $response
     */
    public function __construct($message = '', $response = null)
    {
        $this->response = $response;

        parent::__construct($message);
    }

    /**
     * @return string
     */
    public function getResponse()
    {
        return $this->response;
    }
}
