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

use Klipper\Component\SmsSender\Envelope;
use Klipper\Component\SmsSender\Event\MessageEvent;
use Klipper\Component\SmsSender\EventListener\MessageListener;
use Klipper\Component\SmsSender\Mime\Phone;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Mime\BodyRendererInterface;
use Symfony\Component\Mime\Header\Headers;
use Symfony\Component\Mime\Header\UnstructuredHeader;
use Symfony\Component\Mime\Message;
use Symfony\Component\Mime\RawMessage;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 *
 * @internal
 */
final class MessageListenerTest extends TestCase
{
    /**
     * @var Envelope
     */
    private $envelope;

    /**
     * @var MessageListener
     */
    private $listener;

    protected function setUp(): void
    {
        $this->envelope = new Envelope(new Phone('+100'), [new Phone('+2000')]);
        $this->listener = new MessageListener();
    }

    protected function tearDown(): void
    {
        $this->envelope = null;
        $this->listener = null;
    }

    public function testGetSubscribedEvents(): void
    {
        $expected = [
            MessageEvent::class => 'onMessage',
        ];

        static::assertSame($expected, MessageListener::getSubscribedEvents());
    }

    public function testOnMessageWithoutHeadersAndBodyRenderer(): void
    {
        $message = new Message();
        $event = new MessageEvent($message, $this->envelope);

        $this->listener->onMessage($event);

        static::assertSame($message, $event->getMessage());
        static::assertSame([], $message->getHeaders()->toArray());
    }

    public function testOnMessageWithRawMessageInstance(): void
    {
        $message = new RawMessage('');
        $event = new MessageEvent($message, $this->envelope);

        /** @var BodyRendererInterface|MockObject $bodyRenderer */
        $bodyRenderer = $this->getMockBuilder(BodyRendererInterface::class)->getMock();
        $bodyRenderer->expects(static::never())->method('render');

        $this->listener = new MessageListener(null, $bodyRenderer);

        $this->listener->onMessage($event);
    }

    public function testOnMessageWithBodyRenderer(): void
    {
        $message = new Message();
        $event = new MessageEvent($message, $this->envelope);

        /** @var BodyRendererInterface|MockObject $bodyRenderer */
        $bodyRenderer = $this->getMockBuilder(BodyRendererInterface::class)->getMock();
        $bodyRenderer->expects(static::once())->method('render')->with($message);

        $this->listener = new MessageListener(null, $bodyRenderer);

        $this->listener->onMessage($event);
    }

    public function testOnMessageWithHeaders(): void
    {
        $message = new Message();
        $event = new MessageEvent($message, $this->envelope);

        $headers = new Headers();
        $this->listener = new MessageListener($headers);

        $header1 = new UnstructuredHeader('Test', 'value 1');
        $header2 = new UnstructuredHeader('Test', 'value 2');

        static::assertSame([], $headers->toArray());

        $headers->add($header1);
        $headers->add($header2);

        $this->listener->onMessage($event);

        $expectedHeaders = [
            'Test: value 1',
            'Test: value 2',
        ];

        static::assertSame($expectedHeaders, $headers->toArray());
    }
}
