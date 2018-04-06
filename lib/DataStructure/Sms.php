<?php declare(strict_types=1);

namespace Fazland\SkebbyRestClient\DataStructure;

use Fazland\SkebbyRestClient\Constant\ValidityPeriods;
use Fazland\SkebbyRestClient\Exception\InvalidDeliveryStartException;
use Fazland\SkebbyRestClient\Exception\InvalidValidityPeriodException;

/**
 * Represents an SMS.
 *
 * @author Massimiliano Braglia <massimiliano.braglia@fazland.com>
 */
class Sms
{
    /**
     * @var string
     */
    private $sender;

    /**
     * @var string[]
     */
    private $recipients;

    /**
     * @var string[][]
     */
    private $recipientVariables;

    /**
     * @var string
     */
    private $text;

    /**
     * @var string
     */
    private $userReference;

    /**
     * @var \DateTime
     */
    private $deliveryStart;

    /**
     * @var \DateInterval
     */
    private $validityPeriod;

    /**
     * Sms constructor.
     */
    public function __construct()
    {
        $this->recipients = [];
        $this->recipientVariables = [];
    }

    /**
     * Creates a new instance of SMS.
     *
     * @return static
     */
    public static function create(): self
    {
        return new static();
    }

    /**
     * Gets the sender.
     *
     * @return null|string
     */
    public function getSender()
    {
        return $this->sender;
    }

    /**
     * Sets the sender.
     *
     * @param string $sender
     *
     * @return $this
     */
    public function setSender(string $sender): self
    {
        $this->sender = $sender;

        return $this;
    }

    /**
     * Gets the recipients.
     *
     * @return string[]
     */
    public function getRecipients(): array
    {
        return $this->recipients;
    }

    /**
     * Sets the recipients.
     *
     * @param string[] $recipients
     *
     * @return $this
     */
    public function setRecipients(array $recipients): self
    {
        $this->recipients = $recipients;

        return $this;
    }

    /**
     * Adds a single recipient.
     *
     * @param string $recipient
     *
     * @return $this
     */
    public function addRecipient(string $recipient): self
    {
        $this->recipients[] = $recipient;

        return $this;
    }

    /**
     * Removes a single recipient.
     *
     * @param string $recipient
     *
     * @return $this
     */
    public function removeRecipient(string $recipient): self
    {
        $itemPosition = array_search($recipient, $this->recipients);

        if (false !== $itemPosition) {
            unset($this->recipients[$itemPosition]);
        }

        unset($this->recipientVariables[$recipient]);

        return $this;
    }

    /**
     * Whether the current sms has or not recipients.
     *
     * @return bool
     */
    public function hasRecipients(): bool
    {
        return ! empty($this->recipients);
    }

    /**
     * Gets the recipient variables.
     *
     * @return string[][]
     */
    public function getRecipientVariables(): array
    {
        return $this->recipientVariables;
    }

    /**
     * Sets the recipient variables for the recipient specified.
     *
     * @param string   $recipient
     * @param string[] $recipientVariables
     *
     * @return $this
     */
    public function setRecipientVariables(string $recipient, array $recipientVariables): self
    {
        $this->recipientVariables[$recipient] = $recipientVariables;

        return $this;
    }

    /**
     * Adds a single recipient variable for the specified recipient.
     *
     * @param string $recipient
     * @param string $recipientVariable
     * @param string $recipientVariableValue
     *
     * @return $this
     */
    public function addRecipientVariable(
        string $recipient,
        string $recipientVariable,
        string $recipientVariableValue
    ): self {
        if (! isset($this->recipientVariables[$recipient])) {
            $this->recipientVariables[$recipient] = [];
        }

        $this->recipientVariables[$recipient][$recipientVariable] = $recipientVariableValue;

        return $this;
    }

    /**
     * Removes the recipient variable for the recipient specified.
     *
     * @param string $recipient
     * @param string $recipientVariable
     *
     * @return $this
     */
    public function removeRecipientVariable(string $recipient, string $recipientVariable): self
    {
        unset($this->recipientVariables[$recipient][$recipientVariable]);

        return $this;
    }

    /**
     * Whether the current sms has or not recipient variables.
     *
     * @return bool
     */
    public function hasRecipientVariables(): bool
    {
        return ! empty($this->recipientVariables);
    }

    /**
     * Clears the recipient variables.
     *
     * @return $this
     */
    public function clearRecipientVariables(): self
    {
        $this->recipientVariables = [];

        return $this;
    }

    /**
     * Gets the text.
     *
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * Sets the text.
     *
     * @param string $text
     *
     * @return $this
     */
    public function setText(string $text): self
    {
        $this->text = $text;

        return $this;
    }

    /**
     * Gets the user reference.
     *
     * @return string
     */
    public function getUserReference()
    {
        return $this->userReference;
    }

    /**
     * Sets the user reference.
     *
     * @param string $userReference
     *
     * @return $this
     */
    public function setUserReference(string $userReference): self
    {
        $this->userReference = $userReference;

        return $this;
    }

    /**
     * Gets the delivery start.
     *
     * @return \DateTimeInterface
     */
    public function getDeliveryStart()
    {
        return $this->deliveryStart;
    }

    /**
     * @param \DateTimeInterface|null $deliveryStart
     *
     * @return $this
     *
     * @throws InvalidDeliveryStartException
     */
    public function setDeliveryStart(\DateTimeInterface $deliveryStart = null)
    {
        if (null !== $deliveryStart && $deliveryStart < date_create_from_format('U', (string) time())) {
            throw new InvalidDeliveryStartException();
        }

        $this->deliveryStart = $deliveryStart;

        return $this;
    }

    /**
     * Gets the validity period.
     *
     * @return \DateInterval
     */
    public function getValidityPeriod()
    {
        return $this->validityPeriod;
    }

    /**
     * Sets the validity period.
     *
     * @param \DateInterval|null $validityPeriod
     *
     * @return $this
     *
     * @throws InvalidValidityPeriodException
     */
    public function setValidityPeriod(\DateInterval $validityPeriod = null): self
    {
        if (null !== $validityPeriod &&
            ($validityPeriod->i < ValidityPeriods::MIN || $validityPeriod->i > ValidityPeriods::MAX)
        ) {
            throw new InvalidValidityPeriodException();
        }

        $this->validityPeriod = $validityPeriod;

        return $this;
    }
}
