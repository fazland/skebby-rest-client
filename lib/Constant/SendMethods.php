<?php
declare(strict_types=1);

namespace Fazland\SkebbyRestClient\Constant;

/**
 * Represents the Skebby SendMethods.
 *
 * @author Massimiliano Braglia <massimiliano.braglia@fazland.com>
 */
class SendMethods
{
    /**
     * Represents send method classic (simple SMS without delivery report).
     *
     * @const
     */
    public const CLASSIC = 'send_sms_classic';

    /**
     * Represents send method classic plus (simple SMS with delivery report).
     *
     * @const
     */
    public const CLASSIC_PLUS = 'send_sms_classic_report';

    /**
     * Represents send method basic (simple SMS without delivery warranty and delivery report).
     *
     * @const
     */
    public const BASIC = 'send_sms_basic';

    /**
     * IT WON'T SEND SMS.
     * Represents send method classic (simple SMS without delivery report).
     *
     * @const
     */
    public const TEST_CLASSIC = 'test_send_sms_classic';

    /**
     * IT WON'T SEND SMS.
     * Represents send method classic plus (simple SMS with delivery report).
     *
     * @const
     */
    public const TEST_CLASSIC_PLUS = 'test_send_sms_classic_report';

    /**
     * IT WON'T SEND SMS.
     * Represents send method basic (simple SMS without delivery warranty and delivery report).
     *
     * @const
     */
    public const TEST_BASIC = 'test_send_sms_basic';

    public static function all()
    {
        $reflectedClass = new \ReflectionClass(__CLASS__);

        return $reflectedClass->getConstants();
    }
}
