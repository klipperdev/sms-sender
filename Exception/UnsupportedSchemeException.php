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

use Klipper\Component\SmsSender\Transport\Dsn;

/**
 * Unsupported Scheme Exception for the SmsSender component.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class UnsupportedSchemeException extends LogicException
{
    /**
     * Constructor.
     *
     * @param Dsn           $dsn       The dsn instance
     * @param null|string[] $supported The supported schemes
     */
    public function __construct(Dsn $dsn, array $supported = null)
    {
        $message = sprintf('The "%s" scheme is not supported for SMS Sender "%s".', $dsn->getScheme(), $dsn->getHost());

        if ($supported) {
            $message .= sprintf(' Supported schemes are: "%s".', implode('", "', $supported));
        }

        parent::__construct($message);
    }
}
