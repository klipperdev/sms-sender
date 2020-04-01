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

use Klipper\Component\SmsSender\Exception\TransportExceptionInterface;
use Klipper\Component\SmsSender\SentMessage;
use Klipper\Component\SmsSender\SmsEnvelope;
use Symfony\Component\Mime\RawMessage;

/**
 * Interface for the transport.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
interface TransportInterface
{
    /**
     * Get the name.
     */
    public function getName(): string;

    /**
     * Send the message.
     *
     * @param RawMessage       $message  The message
     * @param null|SmsEnvelope $envelope The envelope
     *
     * @throws TransportExceptionInterface
     */
    public function send(RawMessage $message, SmsEnvelope $envelope = null): ?SentMessage;

    /**
     * Check if the from phone is required.
     */
    public function hasRequiredFrom(): bool;
}
