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

use Klipper\Component\SmsSender\Exception\InvalidArgumentException;
use Klipper\Component\SmsSender\Exception\LogicException;
use Klipper\Component\SmsSender\Transport;
use Klipper\Component\SmsSender\Transport\AbstractTransport;
use Klipper\Component\SmsSender\Transport\FailoverTransport;
use Klipper\Component\SmsSender\Transport\NullTransport;
use Klipper\Component\SmsSender\Transport\RoundRobinTransport;
use Klipper\Component\SmsSender\Transport\TransportInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 *
 * @internal
 */
final class TransportTest extends TestCase
{
    /**
     * @var EventDispatcherInterface|MockObject
     */
    private $dispatcher;

    /**
     * @var HttpClientInterface|MockObject
     */
    private $httpClient;

    /**
     * @var LoggerInterface|MockObject
     */
    private $logger;

    protected function setUp(): void
    {
        $this->dispatcher = $this->getMockBuilder(EventDispatcherInterface::class)->getMock();
        $this->httpClient = $this->getMockBuilder(HttpClientInterface::class)->getMock();
        $this->logger = $this->getMockBuilder(LoggerInterface::class)->getMock();
    }

    protected function tearDown(): void
    {
        $this->dispatcher = null;
        $this->httpClient = null;
        $this->logger = null;
    }

    public function testFromDsnNull(): void
    {
        $transport = Transport::fromDsn('sms://null', $this->dispatcher, $this->httpClient, $this->logger);

        static::assertInstanceOf(NullTransport::class, $transport);
        $this->validateDispatcher($transport);
    }

    public function testFromDsnNullWithInvalidScheme(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('The "api" scheme is not supported for SMS Sender "null". Supported schemes are: "sms".');

        Transport::fromDsn('api://null', $this->dispatcher, $this->httpClient, $this->logger);
    }

    public function testFromDsnFailover(): void
    {
        $transport = Transport::fromDsn('sms://null || sms://null', $this->dispatcher, $this->httpClient, $this->logger);
        static::assertInstanceOf(FailoverTransport::class, $transport);
    }

    public function testFromDsnRoundRobin(): void
    {
        $transport = Transport::fromDsn('sms://null && sms://null', $this->dispatcher, $this->httpClient, $this->logger);
        static::assertInstanceOf(RoundRobinTransport::class, $transport);
    }

    public function testFromInvalidDsn(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The "sms://" SMS Sender DSN is invalid.');

        Transport::fromDsn('sms://');
    }

    public function testFromInvalidDsnNoHost(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The "?!" SMS Sender DSN must contain a transport scheme.');

        Transport::fromDsn('?!');
    }

    public function testFromInvalidTransportName(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('The "foobar" SMS Sender is not supported.');

        Transport::fromDsn('sms://foobar');
    }

    private function validateDispatcher(TransportInterface $transport): void
    {
        $p = new \ReflectionProperty(AbstractTransport::class, 'dispatcher');
        $p->setAccessible(true);
        static::assertSame($this->dispatcher, $p->getValue($transport));
    }
}
