<?php

namespace Fazland\SkebbyRestClient\Client;

use Fazland\SkebbyRestClient\DataStructure\Response;
use Fazland\SkebbyRestClient\DataStructure\Sms;
use Fazland\SkebbyRestClient\Exception\NoRecipientsSpecifiedException;
use Fazland\SkebbyRestClient\Constant\Charsets;
use Fazland\SkebbyRestClient\Constant\EncodingSchemas;
use Fazland\SkebbyRestClient\Constant\Endpoints;
use Fazland\SkebbyRestClient\Constant\Recipients;
use Fazland\SkebbyRestClient\Constant\SendMethods;
use Fazland\SkebbyRestClient\Constant\ValidityPeriods;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author Massimiliano Braglia <massimiliano.braglia@fazland.com>
 */
class Client
{
    /**
     * @var array
     */
    private $config;

    /**
     * @param array $options
     */
    public function __construct(array $options)
    {
        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);
        $this->config = $resolver->resolve($options);
    }

    /**
     * @param Sms $sms
     *
     * @return Response[]
     */
    public function send(Sms $sms)
    {
        $messages = [];

        $recipients = $sms->getRecipients();
        if (count($recipients) > Recipients::MAX) {
            foreach (array_chunk($recipients, Recipients::MAX) as $chunk) {
                $message = clone $sms;
                $message
                    ->setRecipients($chunk)
                    ->clearRecipientVariables()
                ;

                foreach ($chunk as $recipient) {
                    foreach ($sms->getRecipientVariables()[$recipient] as $variable => $value) {
                        $message->addRecipientVariable($recipient, $variable, $value);
                    }
                }

                $messages[] = $message;
            }
        } else {
            $messages[] = $sms;
        }

        $responses = [];
        foreach ($messages as $message) {
            $request = $this->prepareRequest($message);

            $responses[] = $this->executeRequest($request);
        }

        return $responses;
    }

    /**
     * @param OptionsResolver $resolver
     */
    private function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setRequired([
                'username',
                'password',
                'sender_number',
                'method',
            ])
            ->setDefined([
                'delivery_start',
                'validity_period',
                'encoding_schema',
                'charset',
                'endpoint_uri',
            ])
            ->setAllowedTypes('username', 'string')
            ->setAllowedTypes('password', 'string')
            ->setAllowedTypes('sender_number', 'string')
            ->setAllowedTypes('method', 'string')
            ->setAllowedTypes('delivery_start', 'string')
            ->setAllowedTypes('validity_period', 'int')
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
            ->setAllowedValues('delivery_start', function ($value) {
                $d = \DateTime::createFromFormat(\DateTime::RFC2822, $value);
                return $d && $d->format('Y-m-d') === $value;
            })
            ->setAllowedValues('validity_period', function ($value) {
                return $value >= ValidityPeriods::MIN && $value <= ValidityPeriods::MAX;
            })
            ->setAllowedValues('encoding_schema', [
                EncodingSchemas::NORMAL,
                EncodingSchemas::UCS2,
            ])
            ->setAllowedValues('charset', [
                Charsets::ISO_8859_1,
                Charsets::UTF8,
            ])
            ->setDefaults([
                'charset' => Charsets::UTF8,
                'validity_period' => ValidityPeriods::MAX,
                'encoding_schema' => EncodingSchemas::NORMAL,
                'endpoint_uri' => Endpoints::REST_HTTPS
            ])
        ;
    }

    /**
     * @param Sms $sms
     *
     * @return array
     *
     * @throws NoRecipientsSpecifiedException
     */
    private function prepareRequest(Sms $sms)
    {
        if (! $sms->hasRecipients()) {
            throw new NoRecipientsSpecifiedException();
        }

        $request = [
            'username' => $this->config['username'],
            'password' => $this->config['password'],
            'method' => $this->config['method'],
            'sender_number' => '"' . $this->normalizePhoneNumber($this->config['sender_number']) . '"',
            'recipients' => $this->prepareRecipients($sms),
            'text' => str_replace(' ', '+', $sms->getText()),
            'user_reference' => $sms->getUserReference(),
            'delivery_start' => isset($this->config['delivery_start']) ? $this->config['delivery_start'] : null,
            'validity_period' => isset($this->config['validity_period']) ? $this->config['validity_period'] : null,
            'encoding_scheme' => isset($this->config['encoding_schema']) ? $this->config['encoding_schema'] : null,
            'charset' => isset($this->config['charset']) ? $this->config['charset'] : null,
        ];

        $serializedRequest = "";
        foreach ($request as $key => $value) {
            $serializedRequest .= $key . '=' . $value . '&';
        }

        return rtrim($serializedRequest, '&');
    }

    /**
     * @param Sms $sms
     *
     * @return string
     */
    private function prepareRecipients(Sms $sms)
    {
        $recipients = $sms->getRecipients();

        $recipientVariables = $sms->getRecipientVariables();

        if (0 === count($recipientVariables)) {
            $recipients = array_map([$this, 'normalizePhoneNumber'], $recipients);
            return json_encode($recipients);
        }

        return json_encode(array_map(function ($recipient) use ($recipientVariables) {
            $targetVariables = $recipientVariables[$recipient];

            return array_merge(['recipient' => $this->normalizePhoneNumber($recipient)], $targetVariables);
        }, $recipients));
    }

    /**
     * @param string $phoneNumber
     *
     * @return string
     */
    private function normalizePhoneNumber($phoneNumber)
    {
        if ("+" === $phoneNumber[0]) {
            $phoneNumber = substr($phoneNumber, 1);
        } elseif ("00" === substr($phoneNumber, 0, 2)) {
            $phoneNumber = substr($phoneNumber, 2);
        }

        return $phoneNumber;
    }

    /**
     * @param string $request
     *
     * @return Response
     */
    private function executeRequest($request)
    {
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 60);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $request);
        curl_setopt($curl, CURLOPT_URL, $this->config['endpoint_uri']);

        $response = curl_exec($curl);

        curl_close($curl);

        return new Response($response);
    }
}
