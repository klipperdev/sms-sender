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

use Klipper\Component\SmsSender\Messenger\SendSmsMessage;
use Klipper\Component\SmsSender\Mime\Phone;
use Klipper\Component\SmsSender\SmsEnvelope;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Mime\RawMessage;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 *
 * @internal
 */
final class SendSmsMessageTest extends TestCase
{
    public function testGetters(): void
    {
        $message = new RawMessage('');
        $envelope = new SmsEnvelope(new Phone('+100'), [new Phone('+2000')]);

        $sentMessage = new SendSmsMessage($message, $envelope);

        static::assertSame($message, $sentMessage->getMessage());
        static::assertSame($envelope, $sentMessage->getEnvelope());
    }
}
