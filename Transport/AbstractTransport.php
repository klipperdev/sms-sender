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

use Klipper\Component\SmsSender\DelayedEnvelope;
use Klipper\Component\SmsSender\Envelope;
use Klipper\Component\SmsSender\Event\MessageEvent;
use Klipper\Component\SmsSender\Event\MessageResultEvent;
use Klipper\Component\SmsSender\Exception\TransportException;
use Klipper\Component\SmsSender\SentMessage;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Mime\Message;
use Symfony\Component\Mime\RawMessage;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * Abstract class for the transport.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
abstract class AbstractTransport implements TransportInterface
{
    private EventDispatcherInterface $dispatcher;

    private LoggerInterface $logger;

    private float $rate = 0.0;

    private float $lastSent = 0.0;

    /**
     * @param null|EventDispatcherInterface $dispatcher The event dispatcher
     * @param null|LoggerInterface          $logger     The logger
     */
    public function __construct(
        ?EventDispatcherInterface $dispatcher = null,
        ?LoggerInterface $logger = null
    ) {
        $this->dispatcher = $dispatcher ?? new EventDispatcher();
        $this->logger = $logger ?? new NullLogger();
    }

    /**
     * Sets the maximum number of messages to send per second (0 to disable).
     *
     * @return static
     */
    public function setMaxPerSecond(float $rate): self
    {
        if (0 >= $rate) {
            $rate = 0.0;
        }

        $this->rate = $rate;
        $this->lastSent = 0.0;

        return $this;
    }

    public function send(RawMessage $message, ?Envelope $envelope = null): ?SentMessage
    {
        $message = clone $message;
        $sentMessage = null;

        if (null !== $envelope) {
            $envelope = clone $envelope;
        } else {
            try {
                /** @var Message $message */
                $envelope = new DelayedEnvelope($message);
            } catch (\Throwable $e) {
                throw new TransportException('Cannot send message without a valid envelope.', 0, $e);
            }
        }

        $event = new MessageEvent($message, $envelope);
        $this->dispatcher->dispatch($event);
        $envelope = $event->getEnvelope();

        if (!$envelope->getRecipients()) {
            return $sentMessage;
        }

        $sentMessage = new SentMessage($event->getMessage(), $envelope, new Result(static::class));

        $this->doSend($sentMessage);
        $this->dispatcher->dispatch(new MessageResultEvent(
            $sentMessage->getMessage(),
            $sentMessage->getEnvelope(),
            $sentMessage->getResult()
        ));

        $this->checkThrottling();

        return $sentMessage;
    }

    public function hasRequiredFrom(): bool
    {
        return true;
    }

    /**
     * Action to send the message.
     *
     * @param SentMessage $message The message
     */
    abstract protected function doSend(SentMessage $message): void;

    /**
     * Get the logger.
     */
    protected function getLogger(): LoggerInterface
    {
        return $this->logger;
    }

    /**
     * Check the throttling.
     */
    private function checkThrottling(): void
    {
        if (0.0 === $this->rate) {
            return;
        }

        $sleep = (1 / $this->rate) - (microtime(true) - $this->lastSent);

        if (0 < $sleep) {
            $this->getLogger()->debug(sprintf('SMS transport "%s" sleeps for %.2f seconds', static::class, $sleep));
            usleep((int) ($sleep * 1000000));
        }

        $this->lastSent = microtime(true);
    }
}
