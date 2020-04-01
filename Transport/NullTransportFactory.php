<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\SmsSender\Transport;

use Klipper\Component\SmsSender\Exception\UnsupportedSchemeException;

/**
 * Pretends messages have been sent, but just ignores them.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class NullTransportFactory extends AbstractTransportFactory
{
    /**
     * {@inheritdoc}
     */
    public function create(Dsn $dsn): TransportInterface
    {
        if ('sms' === $dsn->getScheme()) {
            return new NullTransport($this->dispatcher, $this->logger);
        }

        throw new UnsupportedSchemeException($dsn, ['sms']);
    }

    /**
     * {@inheritdoc}
     */
    public function supports(Dsn $dsn): bool
    {
        return 'null' === $dsn->getHost();
    }
}
