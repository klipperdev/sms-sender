<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\SmsSender\Tests\Mime;

use Klipper\Component\SmsSender\Exception\E164ComplianceException;
use Klipper\Component\SmsSender\Exception\InvalidArgumentException;
use Klipper\Component\SmsSender\Exception\LogicException;
use Klipper\Component\SmsSender\Mime\Phone;
use libphonenumber\PhoneNumberUtil;
use PHPUnit\Framework\TestCase;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 *
 * @internal
 */
final class PhoneTest extends TestCase
{
    protected function tearDown(): void
    {
        Phone::$encoderClass = PhoneNumberUtil::class;
    }

    public function testConstructorWithoutPhoneNumberUtilLoaded(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('The "Klipper\Component\SmsSender\Mime\Phone" class cannot be used as it needs "InvalidClass"; try running "composer require giggsey/libphonenumber-for-php".');

        Phone::$encoderClass = 'InvalidClass';
        new Phone('+100');
    }

    public function testConstructorWithInvalidNumber(): void
    {
        $this->expectException(E164ComplianceException::class);
        $this->expectExceptionMessage('Phone "42" does not comply with number-spec of E164.');

        new Phone('42');
    }

    public function testConstructor(): void
    {
        $phone = new Phone('+1 00');

        static::assertSame('+1 00', $phone->getPhone());
        static::assertSame('+100', $phone->toString());
        static::assertSame('+100', $phone->getEncodedPhone());
        static::assertSame('+100@carrier', $phone->getAddress());
        static::assertSame('+100@carrier', $phone->getEncodedAddress());
    }

    public function testCreateWithString(): void
    {
        $phone = Phone::create('+1 00');
        static::assertSame('+1 00', $phone->getPhone());
    }

    public function testCreateWithPhoneInstance(): void
    {
        $expectedPhone = new Phone('+100');
        $phone = Phone::create($expectedPhone);

        static::assertSame($expectedPhone, $phone);
    }

    public function testCreateWithInvalidType(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('A phone can be an instance of Klipper\Component\SmsSender\Mime\Phone or a string ("stdClass" given).');

        Phone::create(new \stdClass());
    }

    public function testCreateArrayWithString(): void
    {
        $phones = Phone::createArray(['+1 00']);

        static::assertCount(1, $phones);
        static::assertSame('+1 00', $phones[0]->getPhone());
    }

    public function testCreateArrayWithPhoneInstance(): void
    {
        $expectedPhones = [new Phone('+100')];
        $phones = Phone::createArray($expectedPhones);

        static::assertSame($expectedPhones, $phones);
    }
}
