<?php declare(strict_types=1);

namespace Fazland\SkebbyRestClient\Exception;

/**
 * Represents an unknown error response exception.
 *
 * @author Massimiliano Braglia <massimiliano.braglia@fazland.com>
 */
class UnknownErrorResponseException extends Exception
{
    /**
     * @var null|string
     */
    private $response;

    /**
     * UnknownErrorResponseException constructor.
     *
     * @param string $message
     * @param null|string $response
     */
    public function __construct(string $message = '', string $response = null)
    {
        $this->response = $response;

        parent::__construct($message);
    }

    /**
     * {@inheritdoc}
     */
    public function __toString(): string
    {
        return '['.get_class($this).'] '.$this->message."\n".
            'Response: '."\n".
            $this->response
        ;
    }

    /**
     * Gets the Unknown error response.
     *
     * @return null|string
     */
    public function getResponse()
    {
        return $this->response;
    }
}
