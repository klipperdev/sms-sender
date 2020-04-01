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

use Klipper\Component\SmsSender\Exception\IncompleteDsnException;
use Klipper\Component\SmsSender\Exception\UnsupportedSchemeException;
use Klipper\Component\SmsSender\Transport\Dsn;
use Klipper\Component\SmsSender\Transport\TransportFactoryInterface;
use Klipper\Component\SmsSender\Transport\TransportInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
abstract class TransportFactoryTestCase extends TestCase
{
    protected const USER = 'u$er';
    protected const PASSWORD = 'pa$s';

    /**
     * @var null|EventDispatcherInterface|MockObject
     */
    protected $dispatcher;

    /**
     * @var null|HttpClientInterface|MockObject
     */
    protected $client;

    /**
     * @var null|LoggerInterface|MockObject
     */
    protected $logger;

    abstract public function getFactory(): TransportFactoryInterface;

    abstract public function supportsProvider(): iterable;

    abstract public function createProvider(): iterable;

    public function unsupportedSchemeProvider(): iterable
    {
        return [];
    }

    public function incompleteDsnProvider(): iterable
    {
        return [];
    }

    /**
     * @dataProvider supportsProvider
     */
    public function testSupports(Dsn $dsn, bool $supports): void
    {
        $factory = $this->getFactory();

        static::assertSame($supports, $factory->supports($dsn));
    }

    /**
     * @dataProvider createProvider
     */
    public function testCreate(Dsn $dsn, TransportInterface $transport): void
    {
        $factory = $this->getFactory();

        static::assertEquals($transport, $factory->create($dsn));
    }

    /**
     * @dataProvider unsupportedSchemeProvider
     */
    public function testUnsupportedSchemeException(Dsn $dsn): void
    {
        $factory = $this->getFactory();

        $this->expectException(UnsupportedSchemeException::class);
        $factory->create($dsn);
    }

    /**
     * @dataProvider incompleteDsnProvider
     */
    public function testIncompleteDsnException(Dsn $dsn): void
    {
        $factory = $this->getFactory();

        $this->expectException(IncompleteDsnException::class);
        $factory->create($dsn);
    }

    protected function getDispatcher(): EventDispatcherInterface
    {
        return $this->dispatcher ?? $this->dispatcher = $this->createMock(EventDispatcherInterface::class);
    }

    protected function getClient(): HttpClientInterface
    {
        return $this->client ?? $this->client = $this->createMock(HttpClientInterface::class);
    }

    protected function getLogger(): LoggerInterface
    {
        return $this->logger ?? $this->logger = $this->createMock(LoggerInterface::class);
    }
}
