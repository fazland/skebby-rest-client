<?php

namespace Fazland\SkebbyRestClient\Tests\Util;

/**
 * @author Massimiliano Braglia <massimiliano.braglia@fazland.com>
 */
class MockedFunctionResult
{
    /**
     * @var bool
     */
    private $success;

    /**
     * @var string
     */
    private $message;

    /**
     * @param bool $success
     * @param string $message
     */
    public function __construct($success, $message)
    {
        $this->success = $success;
        $this->message = $message;
    }

    /**
     * @return bool
     */
    public function isSuccessful()
    {
        return $this->success;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }
}
