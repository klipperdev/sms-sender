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
    /**
     * @var RawMessage
     */
    private $original;

    /**
     * @var RawMessage
     */
    private $raw;

    /**
     * @var SmsEnvelope
     */
    private $envelope;

    /**
     * @var Result
     */
    private $result;

    /**
     * Constructor.
     *
     * @param RawMessage  $message  The message
     * @param SmsEnvelope $envelope The envelope
     * @param Result      $result   The result wrapper for transport
     */
    public function __construct(RawMessage $message, SmsEnvelope $envelope, Result $result)
    {
        $this->raw = $message instanceof Message ? new RawMessage($message->toIterable()) : $message;
        $this->original = $message;
        $this->envelope = $envelope;
        $this->result = $result;
    }

    /**
     * Get the message.
     *
     * @return RawMessage
     */
    public function getMessage(): RawMessage
    {
        return $this->raw;
    }

    /**
     * Get the original message.
     *
     * @return RawMessage
     */
    public function getOriginalMessage(): RawMessage
    {
        return $this->original;
    }

    /**
     * Get the envelope.
     *
     * @return SmsEnvelope
     */
    public function getEnvelope(): SmsEnvelope
    {
        return $this->envelope;
    }

    /**
     * Convert the message into a string.
     *
     * @return string
     */
    public function toString(): string
    {
        return $this->raw->toString();
    }

    /**
     * Convert the message to iterable parts.
     *
     * @return iterable
     */
    public function toIterable(): iterable
    {
        return $this->raw->toIterable();
    }

    /**
     * Get the transport result.
     *
     * @return Result
     */
    public function getResult(): Result
    {
        return $this->result;
    }
}
