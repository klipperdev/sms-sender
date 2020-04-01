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

use Klipper\Component\SmsSender\DelayedSmsEnvelope;
use Klipper\Component\SmsSender\Exception\InvalidArgumentException;
use Klipper\Component\SmsSender\Exception\LogicException;
use Klipper\Component\SmsSender\Mime\Phone;
use Klipper\Component\SmsSender\Mime\Sms;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Mime\RawMessage;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 *
 * @internal
 */
final class DelayedSmsEnvelopeTest extends TestCase
{
    public function testConstructorWithInvalidMessage(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('A delayed SMS envelope requires an instance of Symfony\Component\Mime\Message ("Symfony\Component\Mime\RawMessage" given).');

        new DelayedSmsEnvelope(new RawMessage(''));
    }

    public function testGetFromFromMessage(): void
    {
        $message = new Sms();
        $message->from('+100');

        $envelope = new DelayedSmsEnvelope($message);

        static::assertNotNull($message->getFrom());
        static::assertSame($message->getFrom(), $envelope->getFrom());
    }

    public function testGetFromWithoutValue(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Unable to determine the sender of the message.');

        $message = new Sms();
        $envelope = new DelayedSmsEnvelope($message);

        $envelope->getFrom();
    }

    public function testGetFromFromEnvelope(): void
    {
        $from = new Phone('+100');

        $message = new Sms();
        $message->from('+100');

        $envelope = new DelayedSmsEnvelope($message);
        $envelope->setFrom($from);

        static::assertNotSame($message->getFrom(), $envelope->getFrom());
        static::assertSame($from, $envelope->getFrom());
    }

    public function testGetRecipientsFromMessage(): void
    {
        $message = new Sms();
        $message->to('+100');

        $envelope = new DelayedSmsEnvelope($message);

        static::assertNotEmpty($message->getTo());
        static::assertNotEmpty($envelope->getRecipients());

        static::assertSame($message->getTo()[0]->toString(), $envelope->getRecipients()[0]->toString());
    }

    public function testGetRecipientsFromEnvelope(): void
    {
        $to = [new Phone('+100')];

        $message = new Sms();
        $message->to('+100');

        $envelope = new DelayedSmsEnvelope($message);
        $envelope->setRecipients($to);

        static::assertSame($to, $envelope->getRecipients());
    }
}
