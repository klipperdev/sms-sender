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

/**
 * Pretends messages have been sent, but just ignores them.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class FailoverTransport extends RoundRobinTransport
{
    /**
     * @var null|TransportInterface
     */
    private $currentTransport;

    /**
     * Get the name symbol.
     */
    protected function getNameSymbol(): string
    {
        return '||';
    }

    /**
     * {@inheritdoc}
     */
    protected function getNextTransport(): ?TransportInterface
    {
        if (null === $this->currentTransport || $this->isTransportDead($this->currentTransport)) {
            $this->currentTransport = parent::getNextTransport();
        }

        return $this->currentTransport;
    }
}
