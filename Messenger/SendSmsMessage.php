<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\SmsSender\Messenger;

use Klipper\Component\SmsSender\Envelope;
use Symfony\Component\Mime\RawMessage;

/**
 * Send Sms Message.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class SendSmsMessage
{
    /**
     * @var RawMessage
     */
    private $message;

    /**
     * @var null|Envelope
     */
    private $envelope;

    /**
     * Constructor.
     *
     * @param RawMessage    $message  The message
     * @param null|Envelope $envelope The envelope
     */
    public function __construct(RawMessage $message, Envelope $envelope = null)
    {
        $this->message = $message;
        $this->envelope = $envelope;
    }

    /**
     * Get the message.
     */
    public function getMessage(): RawMessage
    {
        return $this->message;
    }

    /**
     * Get the envelope.
     */
    public function getEnvelope(): ?Envelope
    {
        return $this->envelope;
    }
}
