<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\SmsSender\Exception;

use Klipper\Bridge\SmsSender\Amazon\Transport\SnsTransportFactory;
use Klipper\Bridge\SmsSender\Twilio\Transport\TwilioTransportFactory;
use Klipper\Component\SmsSender\Transport\Dsn;

/**
 * Unsupported Host Exception for the SmsSender component.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class UnsupportedHostException extends LogicException
{
    private const HOST_TO_PACKAGE_MAP = [
        'sns' => [
            'class' => SnsTransportFactory::class,
            'package' => 'klipper/amazon-sms-sender',
        ],
        'twilio' => [
            'class' => TwilioTransportFactory::class,
            'package' => 'klipper/twilio-sms-sender',
        ],
    ];

    /**
     * Constructor.
     *
     * @param Dsn $dsn The dsn instance
     */
    public function __construct(Dsn $dsn)
    {
        parent::__construct(static::buildMessage($dsn->getHost(), self::HOST_TO_PACKAGE_MAP));
    }

    /**
     * Build the error message.
     *
     * @param string $host The host
     * @param array  $map  The map
     */
    public static function buildMessage(string $host, array $map): string
    {
        $package = $map[$host] ?? null;

        if (isset($package['class'], $package['package']) && !class_exists($package['class'])) {
            return sprintf(
                'Unable to send sms via "%s" as the bridge is not installed. Try running "composer require %s".',
                $host,
                $package['package']
            );
        }

        return sprintf('The "%s" SMS Sender is not supported.', $host);
    }
}
