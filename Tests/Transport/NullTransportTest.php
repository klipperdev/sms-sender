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

use Klipper\Component\SmsSender\Envelope;
use Klipper\Component\SmsSender\Mime\Phone;
use Klipper\Component\SmsSender\Transport\NullTransport;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Mime\Message;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 *
 * @internal
 */
final class NullTransportTest extends TestCase
{
    public function testGetName(): void
    {
        $t = new NullTransport();
        static::assertEquals('sms://null', $t->getName());
    }

    public function testSend(): void
    {
        $transport = new NullTransport();

        $transport->send(new Message(), new Envelope(new Phone('+100'), [new Phone('+2000')]));
        static::assertTrue(true);
    }
}
