<?php

namespace Fazland\SkebbyRestClient\DataStructure;

/**
 * @author Massimiliano Braglia <massimiliano.braglia@fazland.com>
 */
class Sms
{
    /**
     * @var string[]
     */
    private $recipients;

    /**
     * @var string
     */
    private $text;

    /**
     * @var string
     */
    private $recipientVariables;

    /**
     * Sms constructor.
     */
    public function __construct()
    {
        $this->recipients = [];
        $this->recipientVariables = [];
    }

    /**
     * @return static
     */
    public static function create()
    {
        return new static();
    }

    /**
     * @return string[]
     */
    public function getRecipients()
    {
        return $this->recipients;
    }

    /**
     * @param string[] $recipients
     *
     * @return $this
     */
    public function setRecipients(array $recipients)
    {
        $this->recipients = $recipients;

        return $this;
    }

    /**
     * @param string $recipient
     *
     * @return $this
     */
    public function addRecipient($recipient)
    {
        $this->recipients[] = $recipient;

        return $this;
    }

    /**
     * @param string $recipient
     *
     * @return $this
     */
    public function removeTo($recipient)
    {
        $itemPosition = array_search($recipient, $this->recipients);

        if ($itemPosition) {
            unset($this->recipients[$itemPosition]);
        }

        return $this;
    }

    /**
     * @return bool
     */
    public function hasRecipients()
    {
        return ! empty($this->recipients);
    }

    /**
     * @return string[]
     */
    public function getRecipientVariables()
    {
        return $this->recipientVariables;
    }

    /**
     * @param string $recipient
     * @param string[] $recipientVariables
     *
     * @return $this
     */
    public function setRecipientVariables($recipient, array $recipientVariables)
    {
        $this->recipientVariables[$recipient] = $recipientVariables;

        return $this;
    }

    /**
     * @param string $recipient
     * @param string $recipientVariable
     * @param string $recipientVariableValue
     *
     * @return $this
     */
    public function addRecipientVariable($recipient, $recipientVariable, $recipientVariableValue)
    {
        if (! isset($this->recipientVariables[$recipient])) {
            $this->recipientVariables[$recipient] = [];
        }

        $this->recipientVariables[$recipient][$recipientVariable] = $recipientVariableValue;

        return $this;
    }

    /**
     * @param string $recipient
     * @param string $recipientVariable
     *
     * @return $this
     */
    public function removeRecipientVariable($recipient, $recipientVariable)
    {
        $itemPosition = array_search($recipient, $this->recipientVariables);
        if ($itemPosition) {
            $variablePosition = array_search($recipientVariable, $this->recipientVariables[$itemPosition]);

            if ($variablePosition) {
                unset($this->recipientVariables[$itemPosition][$variablePosition]);
            }
        }

        return $this;
    }

    /**
     * @return bool
     */
    public function hasRecipientVariables()
    {
        return ! empty($this->recipientVariables);
    }

    /**
     * @return $this
     */
    public function clearRecipientVariables()
    {
        $this->recipientVariables = [];

        return $this;
    }

    /**
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * @param string $text
     *
     * @return $this
     */
    public function setText($text)
    {
        $this->text = $text;

        return $this;
    }
}
