<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\SmsSender\Tests\Transport;

use Klipper\Component\SmsSender\Exception\TransportException;
use Klipper\Component\SmsSender\Mime\Phone;
use Klipper\Component\SmsSender\SmsEnvelope;
use Klipper\Component\SmsSender\Transport\AbstractTransport;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Mime\Message;
use Symfony\Component\Mime\RawMessage;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 *
 * @internal
 */
final class AbstractTransportTest extends TestCase
{
    /**
     * @throws
     */
    public function testSend(): void
    {
        $message = new RawMessage('');
        $envelope = new SmsEnvelope(new Phone('+100'), [new Phone('+2000')]);

        $transport = $this->getMockForAbstractClass(AbstractTransport::class);
        $transport->setMaxPerSecond(2 / 10);

        $transport->expects(static::atLeastOnce())
            ->method('doSend')
        ;

        $start = time();
        $transport->send($message, $envelope);
        static::assertEqualsWithDelta(0, time() - $start, 1);

        $transport->send($message, $envelope);
        static::assertEqualsWithDelta(5, time() - $start, 1);

        $transport->send($message, $envelope);
        static::assertEqualsWithDelta(10, time() - $start, 1);

        $transport->send($message, $envelope);
        static::assertEqualsWithDelta(15, time() - $start, 1);

        $start = time();
        $transport->setMaxPerSecond(-3);

        $transport->send($message, $envelope);
        static::assertEqualsWithDelta(0, time() - $start, 1);

        $transport->send($message, $envelope);
        static::assertEqualsWithDelta(0, time() - $start, 1);
    }

    /**
     * @throws
     */
    public function testSendWithEmptyRecipients(): void
    {
        $message = new RawMessage('');
        $envelope = new SmsEnvelope(new Phone('+100'), []);

        $transport = $this->getMockForAbstractClass(AbstractTransport::class);

        $transport->expects(static::never())
            ->method('doSend')
        ;

        $transport->send($message, $envelope);
    }

    /**
     * @throws
     */
    public function testSendWithoutEnvelope(): void
    {
        $message = new Message();
        $message->getHeaders()->addMailboxListHeader('From', [Phone::createAddress('+100')]);
        $message->getHeaders()->addMailboxListHeader('To', [Phone::createAddress('+2000')]);

        $transport = $this->getMockForAbstractClass(AbstractTransport::class);

        $transport->expects(static::once())
            ->method('doSend')
        ;

        $transport->send($message);
    }

    /**
     * @throws
     */
    public function testSendWithInvalidEnvelope(): void
    {
        $this->expectException(TransportException::class);
        $this->expectExceptionMessage('Cannot send message without a valid envelope.');

        $message = new RawMessage('');

        $transport = $this->getMockForAbstractClass(AbstractTransport::class);

        $transport->send($message);
    }

    /**
     * @throws
     */
    public function testHasRequiredFrom(): void
    {
        $transport = $this->getMockForAbstractClass(AbstractTransport::class);

        static::assertTrue($transport->hasRequiredFrom());
    }
}
