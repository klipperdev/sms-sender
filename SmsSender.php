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

use Klipper\Component\SmsSender\Exception\TransportException;
use Klipper\Component\SmsSender\Messenger\SendSmsMessage;
use Klipper\Component\SmsSender\Transport\TransportInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Mime\Message;
use Symfony\Component\Mime\RawMessage;

/**
 * Sms sender.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class SmsSender implements SmsSenderInterface
{
    /**
     * @var TransportInterface
     */
    private $transport;

    /**
     * @var null|MessageBusInterface
     */
    private $bus;

    /**
     * Constructor.
     *
     * @param TransportInterface       $transport The transport
     * @param null|MessageBusInterface $bus       The message bus
     */
    public function __construct(TransportInterface $transport, MessageBusInterface $bus = null)
    {
        $this->transport = $transport;
        $this->bus = $bus;
    }

    /**
     * {@inheritdoc}
     */
    public function send(RawMessage $message, Envelope $envelope = null): void
    {
        if ($message instanceof Message && $this->hasRequiredFrom() && !$message->getHeaders()->has('From')) {
            throw new TransportException('The transport required the "From" information');
        }

        if (null === $this->bus) {
            $this->transport->send($message, $envelope);

            return;
        }

        $this->bus->dispatch(new SendSmsMessage($message, $envelope));
    }

    /**
     * {@inheritdoc}
     */
    public function hasRequiredFrom(): bool
    {
        return $this->transport->hasRequiredFrom();
    }
}
