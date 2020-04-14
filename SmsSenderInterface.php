<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\SmsSender;

use Klipper\Component\SmsSender\Exception\TransportExceptionInterface;
use Symfony\Component\Mime\RawMessage;

/**
 * Interface for the sms sender.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
interface SmsSenderInterface
{
    /**
     * Send the message.
     *
     * @param RawMessage    $message  The message
     * @param null|Envelope $envelope The envelope
     *
     * @throws TransportExceptionInterface
     */
    public function send(RawMessage $message, Envelope $envelope = null): void;

    /**
     * Check if the transport has a required from phone.
     */
    public function hasRequiredFrom(): bool;
}
