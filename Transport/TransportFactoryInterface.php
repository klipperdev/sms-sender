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

use Klipper\Component\SmsSender\Exception\IncompleteDsnException;
use Klipper\Component\SmsSender\Exception\UnsupportedSchemeException;

/**
 * Interface for the transport factory.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
interface TransportFactoryInterface
{
    /**
     * Create the transport.
     *
     * @param Dsn $dsn The dsn instance
     *
     * @throws UnsupportedSchemeException
     * @throws IncompleteDsnException
     */
    public function create(Dsn $dsn): TransportInterface;

    /**
     * Check if the dsn is supported by the transport.
     *
     * @param Dsn $dsn The dsn instance
     */
    public function supports(Dsn $dsn): bool;
}
