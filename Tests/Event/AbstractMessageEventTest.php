<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\SmsSender\Tests\Event;

use Klipper\Component\SmsSender\Event\AbstractMessageEvent;
use Klipper\Component\SmsSender\Mime\Phone;
use Klipper\Component\SmsSender\SmsEnvelope;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Mime\RawMessage;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 *
 * @internal
 */
final class AbstractMessageEventTest extends TestCase
{
    /**
     * @throws
     */
    public function testGettersAndSetters(): void
    {
        $message = new RawMessage('');
        $envelope = new SmsEnvelope(new Phone('+100'), [new Phone('+2000')]);

        $event = $this->getMockForAbstractClass(AbstractMessageEvent::class, [
            $message,
            $envelope,
        ]);

        static::assertSame($message, $event->getMessage());
        static::assertSame($envelope, $event->getEnvelope());

        $event->setMessage(clone $message);
        $event->setEnvelope(clone $envelope);

        static::assertNotSame($message, $event->getMessage());
        static::assertNotSame($envelope, $event->getEnvelope());
    }
}
