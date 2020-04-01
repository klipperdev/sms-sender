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

use Klipper\Component\SmsSender\Exception\InvalidArgumentException;
use Klipper\Component\SmsSender\Transport\Dsn;
use PHPUnit\Framework\TestCase;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 *
 * @internal
 */
final class DsnTest extends TestCase
{
    /**
     * @dataProvider fromStringProvider
     */
    public function testFromString(string $string, Dsn $dsn): void
    {
        static::assertEquals($dsn, Dsn::fromString($string));
    }

    public function testGetUser(): void
    {
        $dsn = new Dsn('sms', 'example.com', 'user');

        static::assertSame('user', $dsn->getUser());
    }

    public function testGetPassword(): void
    {
        $dsn = new Dsn('sms', 'example.com', null, 'password');

        static::assertSame('password', $dsn->getPassword());
    }

    public function testGetPort(): void
    {
        $dsn = new Dsn('sms', 'example.com', null, null, 42);

        static::assertSame(42, $dsn->getPort(50));
    }

    public function testGetPortWithDefaultValue(): void
    {
        $dsn = new Dsn('sms', 'example.com');

        static::assertSame(50, $dsn->getPort(50));
    }

    public function testGetOption(): void
    {
        $options = ['with_value' => 'some value', 'nullable' => null];
        $dsn = new Dsn('sms', 'example.com', null, null, null, $options);

        static::assertSame('some value', $dsn->getOption('with_value'));
        static::assertSame('default', $dsn->getOption('nullable', 'default'));
        static::assertSame('default', $dsn->getOption('not_existent_property', 'default'));
    }

    /**
     * @dataProvider invalidDsnProvider
     */
    public function testInvalidDsn(string $dsn, string $exceptionMessage): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($exceptionMessage);
        Dsn::fromString($dsn);
    }

    public function fromStringProvider(): iterable
    {
        yield 'simple sms without user and pass' => [
            'sms://example.com',
            new Dsn('sms', 'example.com'),
        ];

        yield 'simple sms with custom port' => [
            'sms://user1:pass2@example.com:99',
            new Dsn('sms', 'example.com', 'user1', 'pass2', 99),
        ];

        yield 'custom with urlencoded user and pass' => [
            'custom://u%24er:pa%24s@amazon',
            new Dsn('custom', 'amazon', 'u$er', 'pa$s'),
        ];

        yield 'amazon api with custom options' => [
            'api://u%24er:pa%24s@amazon?region=eu',
            new Dsn('api', 'amazon', 'u$er', 'pa$s', null, ['region' => 'eu']),
        ];
    }

    public function invalidDsnProvider(): iterable
    {
        yield [
            'some://',
            'The "some://" SMS Sender DSN is invalid.',
        ];

        yield [
            '//sendmail',
            'The "//sendmail" SMS Sender DSN must contain a transport scheme.',
        ];

        yield [
            'file:///some/path',
            'The "file:///some/path" SMS Sender DSN must contain a SMS Sender name.',
        ];
    }
}
