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

use Klipper\Component\SmsSender\Transport\TransportInterface;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class MessageHandler
{
    /**
     * @var TransportInterface
     */
    private $transport;

    /**
     * Constructor.
     *
     * @param TransportInterface $transport The SMS transport
     */
    public function __construct(TransportInterface $transport)
    {
        $this->transport = $transport;
    }

    /**
     * Call the handler.
     *
     * @param SendSmsMessage $message The send message
     */
    public function __invoke(SendSmsMessage $message): void
    {
        $this->transport->send($message->getMessage(), $message->getEnvelope());
    }
}
