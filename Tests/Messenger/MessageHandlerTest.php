<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\SmsSender\Tests\Messenger;

use Klipper\Component\SmsSender\Envelope;
use Klipper\Component\SmsSender\Messenger\MessageHandler;
use Klipper\Component\SmsSender\Messenger\SendSmsMessage;
use Klipper\Component\SmsSender\Mime\Phone;
use Klipper\Component\SmsSender\Transport\TransportInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Mime\RawMessage;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 *
 * @internal
 */
final class MessageHandlerTest extends TestCase
{
    public function testInvoke(): void
    {
        $message = new RawMessage('');
        $envelope = new Envelope(new Phone('+100'), [new Phone('+2000')]);
        $sendMessage = new SendSmsMessage($message, $envelope);

        /** @var MockObject|TransportInterface $transport */
        $transport = $this->getMockBuilder(TransportInterface::class)->getMock();
        $transport->expects(static::once())
            ->method('send')
            ->with($message, $envelope)
        ;

        $messageHandler = new MessageHandler($transport);
        $messageHandler($sendMessage);
    }
}
