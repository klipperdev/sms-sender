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

use Klipper\Component\SmsSender\Envelope;
use Klipper\Component\SmsSender\Exception\InvalidArgumentException;
use Klipper\Component\SmsSender\Exception\TransportException;
use Klipper\Component\SmsSender\Exception\TransportResultException;
use Klipper\Component\SmsSender\Mime\Sms;
use Klipper\Component\SmsSender\SentMessage;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Abstract class for the api transport.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
abstract class AbstractApiTransport extends AbstractTransport
{
    /**
     * @var null|HttpClientInterface
     */
    protected $client;

    /**
     * Constructor.
     *
     * @param null|HttpClientInterface      $client     The http client
     * @param null|EventDispatcherInterface $dispatcher The event dispatcher
     * @param null|LoggerInterface          $logger     The logger
     */
    public function __construct(
        HttpClientInterface $client = null,
        EventDispatcherInterface $dispatcher = null,
        LoggerInterface $logger = null
    ) {
        parent::__construct($dispatcher, $logger);

        $this->client = $client;
    }

    /**
     * {@inheritdoc}
     */
    protected function doSend(SentMessage $message): void
    {
        try {
            $sms = $message->getOriginalMessage();

            if (!$sms instanceof Sms) {
                throw new InvalidArgumentException(sprintf('The message must be an instance %s ("%s" given).', Sms::class, \get_class($sms)));
            }
        } catch (\Exception $e) {
            throw new TransportException(sprintf('Unable to send message with the "%s" transport: %s', static::class, $e->getMessage()), 0, $e);
        }

        $this->doSendSms($sms, $message->getEnvelope(), $message->getResult());

        if ($message->getResult()->hasErrors()) {
            throw new TransportResultException($message->getResult());
        }
    }

    /**
     * Action to send the SMS.
     *
     * @param Sms      $sms      The SMS message
     * @param Envelope $envelope The SMS envelope
     * @param Result   $result   The result wrapper
     *
     * @throws
     */
    abstract protected function doSendSms(Sms $sms, Envelope $envelope, Result $result): void;
}
