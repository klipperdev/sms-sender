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
use Klipper\Component\SmsSender\SentMessage;
use Klipper\Component\SmsSender\Transport\Result;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Mime\Message;
use Symfony\Component\Mime\RawMessage;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 *
 * @internal
 */
final class SentMessageTest extends TestCase
{
    public function testGettersAndSetters(): void
    {
        $message = new RawMessage('CONTENT');

        /** @var Envelope $envelope */
        $envelope = $this->getMockBuilder(Envelope::class)->disableOriginalConstructor()->getMock();

        /** @var Result $result */
        $result = $this->getMockBuilder(Result::class)->disableOriginalConstructor()->getMock();

        $sentMessage = new SentMessage($message, $envelope, $result);

        static::assertSame($message, $sentMessage->getMessage());

        static::assertSame($message, $sentMessage->getOriginalMessage());

        static::assertSame($envelope, $sentMessage->getEnvelope());

        static::assertSame($result, $sentMessage->getResult());

        static::assertSame($message->toString(), $sentMessage->toString());

        static::assertCount(1, $sentMessage->toIterable());
    }

    public function testWithMessageClass(): void
    {
        $message = new Message();

        /** @var Envelope $envelope */
        $envelope = $this->getMockBuilder(Envelope::class)->disableOriginalConstructor()->getMock();

        /** @var Result $result */
        $result = $this->getMockBuilder(Result::class)->disableOriginalConstructor()->getMock();

        $sentMessage = new SentMessage($message, $envelope, $result);

        static::assertNotSame($message, $sentMessage->getMessage());
        static::assertSame(RawMessage::class, \get_class($sentMessage->getMessage()));

        static::assertSame($message, $sentMessage->getOriginalMessage());
    }
}
