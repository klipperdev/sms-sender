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
use Klipper\Component\SmsSender\Transport\FailoverTransport;
use Klipper\Component\SmsSender\Transport\RoundRobinTransport;
use Klipper\Component\SmsSender\Transport\TransportInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Mime\RawMessage;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 *
 * @internal
 */
final class FailoverTransportTest extends TestCase
{
    public function testGetName(): void
    {
        $t1 = $this->createMock(TransportInterface::class);
        $t1->expects(static::once())->method('getName')->willReturn('t1://local');

        $t2 = $this->createMock(TransportInterface::class);
        $t2->expects(static::once())->method('getName')->willReturn('t2://local');

        $t = new FailoverTransport([$t1, $t2]);
        static::assertEquals('t1://local || t2://local', $t->getName());
    }

    public function testSendNoTransports(): void
    {
        $this->expectException(TransportException::class);
        new FailoverTransport([]);
    }

    public function testSendFirstWork(): void
    {
        $transport1 = $this->createMock(TransportInterface::class);
        $transport1->expects(static::exactly(3))->method('send');

        $transport2 = $this->createMock(TransportInterface::class);
        $transport2->expects(static::never())->method('send');

        $transport = new FailoverTransport([$transport1, $transport2]);

        $transport->send(new RawMessage(''));
        $this->assertTransports($transport, 1, []);

        $transport->send(new RawMessage(''));
        $this->assertTransports($transport, 1, []);

        $transport->send(new RawMessage(''));
        $this->assertTransports($transport, 1, []);
    }

    public function testSendAllDead(): void
    {
        $transport1 = $this->createMock(TransportInterface::class);
        $transport1->expects(static::once())->method('send')->will(static::throwException(new TransportException()));

        $transport2 = $this->createMock(TransportInterface::class);
        $transport2->expects(static::once())->method('send')->will(static::throwException(new TransportException()));

        $transport = new FailoverTransport([$transport1, $transport2]);

        $this->expectException(TransportException::class);
        $this->expectExceptionMessage('All transports failed.');

        $transport->send(new RawMessage(''));
        $this->assertTransports($transport, 0, [$transport1, $transport2]);
    }

    public function testSendOneDead(): void
    {
        $transport1 = $this->createMock(TransportInterface::class);
        $transport1->expects(static::once())->method('send')->will(static::throwException(new TransportException()));

        $transport2 = $this->createMock(TransportInterface::class);
        $transport2->expects(static::exactly(3))->method('send');

        $transport = new FailoverTransport([$transport1, $transport2]);

        $transport->send(new RawMessage(''));
        $this->assertTransports($transport, 0, [$transport1]);

        $transport->send(new RawMessage(''));
        $this->assertTransports($transport, 0, [$transport1]);

        $transport->send(new RawMessage(''));
        $this->assertTransports($transport, 0, [$transport1]);
    }

    public function testSendOneDeadAndRecoveryWithinRetryPeriod(): void
    {
        $sendPosTransport1 = 0;
        $transport1 = $this->createMock(TransportInterface::class);
        $transport1->expects(static::exactly(3))
            ->method('send')
            ->willReturnCallback(static function () use (&$sendPosTransport1): void {
                ++$sendPosTransport1;

                if (1 === $sendPosTransport1) {
                    throw new TransportException();
                }
            })
        ;

        $sendPosTransport2 = 0;
        $transport2 = $this->createMock(TransportInterface::class);
        $transport2->expects(static::exactly(4))
            ->method('send')
            ->willReturnCallback(static function () use (&$sendPosTransport2): void {
                ++$sendPosTransport2;

                if (4 === $sendPosTransport2) {
                    throw new TransportException();
                }
            })
        ;

        $transport = new FailoverTransport([$transport1, $transport2], 6);

        $transport->send(new RawMessage('')); // transport1 > fail - transport2>sent
        $this->assertTransports($transport, 0, [$transport1]);

        sleep(4);
        $transport->send(new RawMessage('')); // transport2 > sent
        $this->assertTransports($transport, 0, [$transport1]);

        sleep(4);
        $transport->send(new RawMessage('')); // transport2 > sent
        $this->assertTransports($transport, 0, [$transport1]);

        sleep(4);
        $transport->send(new RawMessage('')); // transport2 > fail - transport1>sent
        $this->assertTransports($transport, 1, [$transport2]);

        sleep(4);
        $transport->send(new RawMessage('')); // transport1 > sent
        $this->assertTransports($transport, 1, [$transport2]);
    }

    public function testSendAllDeadWithinRetryPeriod(): void
    {
        $sendPosTransport1 = 0;
        $transport1 = $this->createMock(TransportInterface::class);
        $transport1->expects(static::exactly(1))
            ->method('send')
            ->willReturnCallback(static function () use (&$sendPosTransport1): void {
                ++$sendPosTransport1;

                if (1 === $sendPosTransport1) {
                    throw new TransportException();
                }
            })
        ;

        $sendPosTransport2 = 0;
        $transport2 = $this->createMock(TransportInterface::class);
        $transport2->expects(static::exactly(3))
            ->method('send')
            ->willReturnCallback(static function () use (&$sendPosTransport2): void {
                ++$sendPosTransport2;

                if (3 === $sendPosTransport2) {
                    throw new TransportException();
                }
            })
        ;

        $transport = new FailoverTransport([$transport1, $transport2], 40);

        $transport->send(new RawMessage(''));

        sleep(4);
        $transport->send(new RawMessage(''));

        $this->expectException(TransportException::class);
        $this->expectExceptionMessage('All transports failed.');

        sleep(4);
        $transport->send(new RawMessage(''));
    }

    public function testSendOneDeadButRecover(): void
    {
        $sendPosTransport1 = 0;
        $transport1 = $this->createMock(TransportInterface::class);
        $transport1->expects(static::exactly(2))
            ->method('send')
            ->willReturnCallback(static function () use (&$sendPosTransport1): void {
                ++$sendPosTransport1;

                if (1 === $sendPosTransport1) {
                    throw new TransportException();
                }
            })
        ;

        $sendPosTransport2 = 0;
        $transport2 = $this->createMock(TransportInterface::class);
        $transport2->expects(static::exactly(3))
            ->method('send')
            ->willReturnCallback(static function () use (&$sendPosTransport2): void {
                ++$sendPosTransport2;

                if (3 === $sendPosTransport2) {
                    throw new TransportException();
                }
            })
        ;

        $transport = new FailoverTransport([$transport1, $transport2], 1);

        $transport->send(new RawMessage(''));

        sleep(1);
        $transport->send(new RawMessage(''));

        sleep(1);
        $transport->send(new RawMessage(''));
    }

    private function assertTransports(RoundRobinTransport $transport, int $cursor, array $deadTransports): void
    {
        $prop = new \ReflectionProperty(RoundRobinTransport::class, 'cursor');
        $prop->setAccessible(true);
        static::assertSame($cursor, $prop->getValue($transport));

        $prop = new \ReflectionProperty(RoundRobinTransport::class, 'deadTransports');
        $prop->setAccessible(true);
        static::assertSame($deadTransports, iterator_to_array($prop->getValue($transport)));
    }
}
