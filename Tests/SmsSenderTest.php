<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\SmsSender\Tests;

use Klipper\Component\SmsSender\Envelope;
use Klipper\Component\SmsSender\Exception\TransportException;
use Klipper\Component\SmsSender\Mime\Phone;
use Klipper\Component\SmsSender\SmsSender;
use Klipper\Component\SmsSender\Transport\TransportInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope as MessengerEnvelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Mime\Message;
use Symfony\Component\Mime\RawMessage;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 *
 * @internal
 */
final class SmsSenderTest extends TestCase
{
    public function testSend(): void
    {
        $message = new RawMessage('');
        $envelope = new Envelope(new Phone('+100'), [new Phone('+2000')]);

        /** @var MockObject|TransportInterface $transport */
        $transport = $this->getMockBuilder(TransportInterface::class)->getMock();

        $transport->expects(static::once())
            ->method('send')
            ->with($message, $envelope)
        ;

        $sender = new SmsSender($transport);
        $sender->send($message, $envelope);
    }

    public function testSendWithBusMessenger(): void
    {
        $message = new RawMessage('');
        $envelope = new Envelope(new Phone('+100'), [new Phone('+2000')]);

        /** @var MockObject|TransportInterface $transport */
        $transport = $this->getMockBuilder(TransportInterface::class)->getMock();

        /** @var MessageBusInterface|MockObject $bus */
        $bus = $this->getMockBuilder(MessageBusInterface::class)->getMock();

        $bus->expects(static::once())
            ->method('dispatch')
            ->willReturnCallback(static function ($message, $stamp = []) use (&$busEnvelope) {
                $busEnvelope = new MessengerEnvelope($message, $stamp);

                return $busEnvelope;
            })
        ;

        $sender = new SmsSender($transport, $bus);
        $sender->send($message, $envelope);

        static::assertInstanceOf(MessengerEnvelope::class, $busEnvelope);
    }

    public function testHasRequiredFrom(): void
    {
        /** @var MockObject|TransportInterface $transport */
        $transport = $this->getMockBuilder(TransportInterface::class)->getMock();
        $transport->expects(static::once())->method('hasRequiredFrom')->willReturn(true);

        $sender = new SmsSender($transport);

        static::assertTrue($sender->hasRequiredFrom());
    }

    public function testSendWithRequiredFromAndWithoutFromInformation(): void
    {
        $this->expectException(TransportException::class);
        $this->expectExceptionMessage('The transport required the "From" information');

        $message = new Message();
        $envelope = new Envelope(new Phone('+100'), [new Phone('+2000')]);

        /** @var MockObject|TransportInterface $transport */
        $transport = $this->getMockBuilder(TransportInterface::class)->getMock();
        $transport->expects(static::never())->method('send');
        $transport->expects(static::once())->method('hasRequiredFrom')->willReturn(true);

        $sender = new SmsSender($transport);
        $sender->send($message, $envelope);
    }
}
