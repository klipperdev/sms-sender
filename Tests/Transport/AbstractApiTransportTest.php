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
use Klipper\Component\SmsSender\Exception\TransportException;
use Klipper\Component\SmsSender\Exception\TransportResultException;
use Klipper\Component\SmsSender\Mime\Phone;
use Klipper\Component\SmsSender\Mime\Sms;
use Klipper\Component\SmsSender\Transport\AbstractApiTransport;
use Klipper\Component\SmsSender\Transport\ErrorResult;
use Klipper\Component\SmsSender\Transport\Result;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Mime\Message;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 *
 * @internal
 */
final class AbstractApiTransportTest extends TestCase
{
    /**
     * @throws
     */
    public function testSend(): void
    {
        $transport = $this->getMockForAbstractClass(AbstractApiTransport::class);

        $transport->expects(static::once())->method('doSendSms');

        $message = new Sms();
        $message->to('+2000');

        $transport->send($message);
    }

    /**
     * @throws
     */
    public function testSendWithInvalidMessage(): void
    {
        $this->expectException(TransportException::class);
        $this->expectExceptionMessageRegExp('/Unable to send message with the "(\w+)" transport: The message must be an instance Klipper\\\Component\\\SmsSender\\\Mime\\\Sms \("Symfony\\\Component\\\Mime\\\Message" given\)./');

        $transport = $this->getMockForAbstractClass(AbstractApiTransport::class);

        $message = new Message();
        $message->getHeaders()->addMailboxListHeader('To', [Phone::createAddress('+2000')]);

        $transport->send($message);
    }

    /**
     * @throws
     */
    public function testSendWithResultError(): void
    {
        $this->expectException(TransportResultException::class);

        $message = new Sms();
        $recipient = new Phone('+100');
        $message->to($recipient);

        $transport = $this->getMockForAbstractClass(AbstractApiTransport::class);

        $transport->expects(static::once())
            ->method('doSendSms')
            ->willReturnCallback(static function (Sms $sms, Envelope $envelope, Result $result) use ($recipient): void {
                $result->add(new ErrorResult($recipient, 'Error message', 'error_code'));
            })
        ;

        $transport->send($message);
    }
}
