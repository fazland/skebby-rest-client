<?php

namespace Fazland\SkebbyRestClient\Transport;

class CurlExtensionTransport implements TransportInterface
{
    /**
     * {@inheritdoc}
     */
    public function executeRequest($uri, $body)
    {
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 60);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $body);
        curl_setopt($curl, CURLOPT_URL, $uri);

        $response = curl_exec($curl);

        curl_close($curl);

        return $response;
    }
}
