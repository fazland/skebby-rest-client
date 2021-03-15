<?php
declare(strict_types=1);

namespace Fazland\SkebbyRestClient\Client;

use Fazland\SkebbyRestClient\Constant\Charsets;
use Fazland\SkebbyRestClient\Constant\EncodingSchemas;
use Fazland\SkebbyRestClient\Constant\Endpoints;
use Fazland\SkebbyRestClient\Constant\Recipients;
use Fazland\SkebbyRestClient\Constant\SendMethods;
use Fazland\SkebbyRestClient\Constant\ValidityPeriods;
use Fazland\SkebbyRestClient\DataStructure\Response;
use Fazland\SkebbyRestClient\DataStructure\Sms;
use Fazland\SkebbyRestClient\Event\SmsMessageSent;
use Fazland\SkebbyRestClient\Exception\EmptyResponseException;
use Fazland\SkebbyRestClient\Exception\NoRecipientsSpecifiedException;
use Fazland\SkebbyRestClient\Exception\UnknownErrorResponseException;
use Fazland\SkebbyRestClient\Transport\Factory;
use Fazland\SkebbyRestClient\Transport\TransportInterface;
use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Skebby REST client.
 *
 * @author Massimiliano Braglia <massimiliano.braglia@fazland.com>
 */
class Client
{
    private array $config;

    private TransportInterface $transport;

    private ?EventDispatcherInterface $dispatcher;

    /**
     * Client constructor.
     *
     * @throws \Fazland\SkebbyRestClient\Exception\RuntimeException
     */
    public function __construct(array $options, ?TransportInterface $transport = null, ?EventDispatcherInterface $dispatcher = null)
    {
        $resolver = new OptionsResolver();

        $this->configureOptions($resolver);
        $this->config = $resolver->resolve($options);

        $this->transport = $transport ?? Factory::createTransport();
        $this->dispatcher = $dispatcher;
    }

    /**
     * Sends an SMS.
     *
     * @return Response[]
     *
     * @throws EmptyResponseException
     * @throws NoRecipientsSpecifiedException
     * @throws UnknownErrorResponseException
     */
    public function send(Sms $sms): array
    {
        if (! $sms->hasRecipients()) {
            throw new NoRecipientsSpecifiedException();
        }

        $messages = [];

        $recipients = $sms->getRecipients();
        foreach (array_chunk($recipients, Recipients::MAX) as $chunk) {
            $message = clone $sms;
            $message
                ->setRecipients($chunk)
                ->clearRecipientVariables()
            ;

            foreach ($chunk as $recipient) {
                if (! isset($sms->getRecipientVariables()[$recipient])) {
                    continue;
                }

                foreach ($sms->getRecipientVariables()[$recipient] as $variable => $value) {
                    $message->addRecipientVariable($recipient, $variable, $value);
                }
            }

            $messages[] = $message;
        }

        $responses = [];
        foreach ($messages as $message) {
            $request = $this->prepareRequest($message);

            $responses[] = $this->executeRequest($request);

            if (null !== $this->dispatcher) {
                $this->dispatcher->dispatch(new SmsMessageSent($message));
            }
        }

        return $responses;
    }

    /**
     * Configure default options for client.
     *
     * It takes required options username, password, sender and method.
     * validity_period MUST be a \DateInterval object if set
     * delivery_start MUST be a \DateTime object if set
     */
    private function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired([
                'username',
                'password',
                'sender',
                'method',
            ])
            ->setDefaults([
                'delivery_start' => null,
                'charset' => Charsets::UTF8,
                'validity_period' => \DateInterval::createFromDateString('2800 minutes'),
                'encoding_schema' => EncodingSchemas::NORMAL,
                'endpoint_uri' => Endpoints::REST_HTTPS,
            ])
            ->setAllowedTypes('username', 'string')
            ->setAllowedTypes('password', 'string')
            ->setAllowedTypes('sender', 'string')
            ->setAllowedTypes('method', 'string')
            ->setAllowedTypes('delivery_start', ['null', 'DateTime'])
            ->setAllowedTypes('validity_period', ['null', 'DateInterval'])
            ->setAllowedTypes('encoding_schema', 'string')
            ->setAllowedTypes('charset', 'string')
            ->setAllowedTypes('endpoint_uri', 'string')
            ->setAllowedValues('method', [
                SendMethods::CLASSIC,
                SendMethods::CLASSIC_PLUS,
                SendMethods::BASIC,
                SendMethods::TEST_CLASSIC,
                SendMethods::TEST_CLASSIC_PLUS,
                SendMethods::TEST_BASIC,
            ])
            ->setAllowedValues('validity_period', function (\DateInterval $value) {
                return $value->i >= ValidityPeriods::MIN && $value->i <= ValidityPeriods::MAX;
            })
            ->setAllowedValues('encoding_schema', [
                EncodingSchemas::NORMAL,
                EncodingSchemas::UCS2,
            ])
            ->setAllowedValues('charset', [
                Charsets::ISO_8859_1,
                Charsets::UTF8,
            ])
        ;
    }

    /**
     * Converts the {@see Sms} to an array request.
     */
    private function prepareRequest(Sms $sms): string
    {
        [$senderString, $senderNumber] = $this->getSenderParams($sms);

        $deliveryStart = $sms->getDeliveryStart() ?: $this->config['delivery_start'];
        $validityPeriod = $sms->getValidityPeriod() ?: $this->config['validity_period'];

        $request = [
            'username' => $this->config['username'],
            'password' => $this->config['password'],
            'method' => $this->config['method'],
            'sender_number' => $senderNumber,
            'sender_string' => $senderString,
            'recipients' => $this->prepareRecipients($sms),
            'text' => str_replace(' ', '+', $sms->getText()),
            'user_reference' => $sms->getUserReference(),
            'delivery_start' => $deliveryStart ? urlencode($deliveryStart->format(\DateTime::RFC2822)) : null,
            'validity_period' => $validityPeriod ? $validityPeriod->i : null,
            'encoding_scheme' => $this->config['encoding_schema'],
            'charset' => urlencode($this->config['charset']),
        ];

        /*
         * if sender_string is passed and is empty, it's impossible to use sender_number as sender,
         * Skebby will use the default sender set in Skebby Administration Panel.
         */
        if ('' === trim($request['sender_string'])) {
            unset($request['sender_string']);
        }

        $serializedRequest = [];
        foreach ($request as $key => $value) {
            $serializedRequest[] = "$key=$value";
        }

        return implode('&', $serializedRequest);
    }

    /**
     * Converts the {@see Sms} recipients into an array.
     */
    private function prepareRecipients(Sms $sms): string
    {
        $recipients = $sms->getRecipients();

        if (! $sms->hasRecipientVariables()) {
            $recipients = array_map([$this, 'normalizePhoneNumber'], $recipients);

            return json_encode($recipients);
        }

        $recipientVariables = $sms->getRecipientVariables();

        return json_encode(array_map(function ($recipient) use ($recipientVariables) {
            $targetVariables = [];
            if (isset($recipientVariables[$recipient])) {
                $targetVariables = $recipientVariables[$recipient];
            }

            return array_merge(['recipient' => $this->normalizePhoneNumber($recipient)], $targetVariables);
        }, $recipients), JSON_THROW_ON_ERROR);
    }

    /**
     * Normalizes the phoneNumber.
     *
     * @throws NumberParseException
     */
    private function normalizePhoneNumber(string $phoneNumber): string
    {
        $utils = PhoneNumberUtil::getInstance();
        $parsed = $utils->parse(preg_replace('/^00/', '+', $phoneNumber));

        $phoneNumber = $utils->format($parsed, PhoneNumberFormat::E164);

        return substr($phoneNumber, 1);
    }

    /**
     * Executes the request.
     *
     * @throws EmptyResponseException
     * @throws UnknownErrorResponseException
     */
    private function executeRequest(string $request): Response
    {
        $response = $this->transport->executeRequest($this->config['endpoint_uri'], $request);

        return new Response($response);
    }

    /**
     * Gets sender parameters (alphanumeric sender or phone number).
     *
     * @return string[]
     */
    private function getSenderParams(Sms $sms): array
    {
        $sender = $sms->getSender() ?: $this->config['sender'];

        $senderString = '';
        $senderNumber = '';

        try {
            $senderNumber = $this->normalizePhoneNumber($sender);
        } catch (NumberParseException $e) {
            $senderString = substr($sender, 0, 11);
        }

        return [$senderString, $senderNumber];
    }
}
