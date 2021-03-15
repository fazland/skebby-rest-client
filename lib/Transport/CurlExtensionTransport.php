<?php

declare(strict_types=1);

namespace Fazland\SkebbyRestClient\Transport;

use function curl_close;
use function curl_exec;
use function curl_init;
use function curl_setopt;
use function is_bool;

use const CURLOPT_CONNECTTIMEOUT;
use const CURLOPT_POST;
use const CURLOPT_POSTFIELDS;
use const CURLOPT_RETURNTRANSFER;
use const CURLOPT_TIMEOUT;
use const CURLOPT_URL;

/**
 * Curl extension transport.
 */
class CurlExtensionTransport implements TransportInterface
{
    public function executeRequest(string $uri, string $body): string
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

        return is_bool($response) ? '' : $response;
    }
}
