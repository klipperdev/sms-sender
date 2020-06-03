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

use Klipper\Component\SmsSender\Transport\Result;
use Symfony\Component\Mime\Message;
use Symfony\Component\Mime\RawMessage;

/**
 * Sent Message.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class SentMessage
{
    private RawMessage $original;

    private RawMessage $raw;

    private Envelope $envelope;

    private Result $result;

    /**
     * @param RawMessage $message  The message
     * @param Envelope   $envelope The envelope
     * @param Result     $result   The result wrapper for transport
     */
    public function __construct(RawMessage $message, Envelope $envelope, Result $result)
    {
        $this->raw = $message instanceof Message ? new RawMessage($message->toIterable()) : $message;
        $this->original = $message;
        $this->envelope = $envelope;
        $this->result = $result;
    }

    /**
     * Get the message.
     */
    public function getMessage(): RawMessage
    {
        return $this->raw;
    }

    /**
     * Get the original message.
     */
    public function getOriginalMessage(): RawMessage
    {
        return $this->original;
    }

    /**
     * Get the envelope.
     */
    public function getEnvelope(): Envelope
    {
        return $this->envelope;
    }

    /**
     * Convert the message into a string.
     */
    public function toString(): string
    {
        return $this->raw->toString();
    }

    /**
     * Convert the message to iterable parts.
     */
    public function toIterable(): iterable
    {
        return $this->raw->toIterable();
    }

    /**
     * Get the transport result.
     */
    public function getResult(): Result
    {
        return $this->result;
    }
}
