<?php

declare(strict_types=1);

namespace Fazland\SkebbyRestClient\DataStructure;

use DateInterval;
use DateTimeInterface;
use Fazland\SkebbyRestClient\Clock\ClockInterface;
use Fazland\SkebbyRestClient\Clock\SystemClock;
use Fazland\SkebbyRestClient\Constant\ValidityPeriods;
use Fazland\SkebbyRestClient\Exception\InvalidDeliveryStartException;
use Fazland\SkebbyRestClient\Exception\InvalidValidityPeriodException;

use function array_search;

/**
 * Represents an SMS.
 */
class Sms
{
    private ?string $sender = null;

    /** @var string[] */
    private array $recipients = [];

    /** @var string[][] */
    private array $recipientVariables = [];

    private string $text = '';
    private ?string $userReference = null;
    private ?DateTimeInterface $deliveryStart = null;
    private ?DateInterval $validityPeriod = null;
    private ClockInterface $clock;

    final public function __construct(?ClockInterface $clock = null)
    {
        $this->clock = $clock ?? new SystemClock();
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
     */
    public function getSender(): ?string
    {
        return $this->sender;
    }

    /**
     * Sets the sender.
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
     */
    public function setRecipients(array $recipients): self
    {
        $this->recipients = $recipients;

        return $this;
    }

    /**
     * Adds a single recipient.
     */
    public function addRecipient(string $recipient): self
    {
        $this->recipients[] = $recipient;

        return $this;
    }

    /**
     * Removes a single recipient.
     */
    public function removeRecipient(string $recipient): self
    {
        $itemPosition = array_search($recipient, $this->recipients, true);
        if ($itemPosition !== false) {
            unset($this->recipients[$itemPosition]);
        }

        unset($this->recipientVariables[$recipient]);

        return $this;
    }

    /**
     * Whether the current sms has or not recipients.
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
     * @param string[] $recipientVariables
     */
    public function setRecipientVariables(string $recipient, array $recipientVariables): self
    {
        $this->recipientVariables[$recipient] = $recipientVariables;

        return $this;
    }

    /**
     * Adds a single recipient variable for the specified recipient.
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
     */
    public function removeRecipientVariable(string $recipient, string $recipientVariable): self
    {
        unset($this->recipientVariables[$recipient][$recipientVariable]);

        return $this;
    }

    /**
     * Whether the current sms has or not recipient variables.
     */
    public function hasRecipientVariables(): bool
    {
        return ! empty($this->recipientVariables);
    }

    /**
     * Clears the recipient variables.
     */
    public function clearRecipientVariables(): self
    {
        $this->recipientVariables = [];

        return $this;
    }

    /**
     * Gets the text.
     */
    public function getText(): string
    {
        return $this->text;
    }

    /**
     * Sets the text.
     */
    public function setText(string $text): self
    {
        $this->text = $text;

        return $this;
    }

    /**
     * Gets the user reference.
     */
    public function getUserReference(): ?string
    {
        return $this->userReference;
    }

    /**
     * Sets the user reference.
     */
    public function setUserReference(string $userReference): self
    {
        $this->userReference = $userReference;

        return $this;
    }

    /**
     * Gets the delivery start.
     */
    public function getDeliveryStart(): ?DateTimeInterface
    {
        return $this->deliveryStart;
    }

    /**
     * @throws InvalidDeliveryStartException
     */
    public function setDeliveryStart(?DateTimeInterface $deliveryStart = null): self
    {
        if ($deliveryStart !== null && $deliveryStart < $this->clock->now()) {
            throw new InvalidDeliveryStartException();
        }

        $this->deliveryStart = $deliveryStart;

        return $this;
    }

    /**
     * Gets the validity period.
     */
    public function getValidityPeriod(): ?DateInterval
    {
        return $this->validityPeriod;
    }

    /**
     * Sets the validity period.
     *
     * @throws InvalidValidityPeriodException
     */
    public function setValidityPeriod(?DateInterval $validityPeriod = null): self
    {
        if (
            $validityPeriod !== null &&
            ($validityPeriod->i < ValidityPeriods::MIN || $validityPeriod->i > ValidityPeriods::MAX)
        ) {
            throw new InvalidValidityPeriodException();
        }

        $this->validityPeriod = $validityPeriod;

        return $this;
    }
}
