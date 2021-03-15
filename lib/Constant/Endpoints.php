<?php
declare(strict_types=1);

namespace Fazland\SkebbyRestClient\Constant;

/**
 * Represents the available Endpoints.
 *
 * @author Massimiliano Braglia <massimiliano.braglia@fazland.com>
 */
class Endpoints
{
    /**
     * Represents the Skebby SMS Gateway HTTP REST endpoint.
     *
     * @const
     */
    public const REST_HTTP = 'http://gateway.skebby.it/api/send/smseasy/advanced/rest.php';

    /**
     * Represents the Skebby SMS Gateway HTTPS REST endpoint.
     *
     * @const
     */
    public const REST_HTTPS = 'https://gateway.skebby.it/api/send/smseasy/advanced/rest.php';
}
