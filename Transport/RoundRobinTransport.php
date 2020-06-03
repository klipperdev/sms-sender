<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\SmsSender\Transport;

use Klipper\Component\SmsSender\Envelope;
use Klipper\Component\SmsSender\Exception\TransportException;
use Klipper\Component\SmsSender\Exception\TransportExceptionInterface;
use Klipper\Component\SmsSender\SentMessage;
use Symfony\Component\Mime\RawMessage;

/**
 * Pretends messages have been sent, but just ignores them.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class RoundRobinTransport implements TransportInterface
{
    private \SplObjectStorage $deadTransports;

    /**
     * @var TransportInterface[]
     */
    private array $transports;

    private int $retryPeriod;

    private int $cursor = 0;

    /**
     * @param TransportInterface[] $transports  The transports
     * @param int                  $retryPeriod The retry period
     */
    public function __construct(array $transports, int $retryPeriod = 60)
    {
        if (empty($transports)) {
            throw new TransportException(static::class.' must have at least one transport configured.');
        }

        $this->transports = $transports;
        $this->deadTransports = new \SplObjectStorage();
        $this->retryPeriod = $retryPeriod;
    }

    public function getName(): string
    {
        return implode(' '.$this->getNameSymbol().' ', array_map(static function (TransportInterface $transport) {
            return $transport->getName();
        }, $this->transports));
    }

    public function send(RawMessage $message, Envelope $envelope = null): ?SentMessage
    {
        while ($transport = $this->getNextTransport()) {
            try {
                return $transport->send($message, $envelope);
            } catch (TransportExceptionInterface $e) {
                $this->deadTransports[$transport] = microtime(true);
            }
        }

        throw new TransportException('All transports failed.');
    }

    public function hasRequiredFrom(): bool
    {
        foreach ($this->transports as $transport) {
            if ($transport->hasRequiredFrom()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get the name symbol.
     */
    protected function getNameSymbol(): string
    {
        return '&&';
    }

    /**
     * Rotates the transport list around and returns the first instance.
     */
    protected function getNextTransport(): ?TransportInterface
    {
        $cursor = $this->cursor;
        $transport = null;

        while (true) {
            $transport = $this->transports[$cursor];

            if (!$this->isTransportDead($transport)) {
                break;
            }

            if ((microtime(true) - $this->deadTransports[$transport]) > $this->retryPeriod) {
                $this->deadTransports->detach($transport);

                break;
            }

            if ($this->cursor === $cursor = $this->moveCursor($cursor)) {
                return null;
            }
        }

        $this->cursor = $this->moveCursor($cursor);

        return $transport;
    }

    /**
     * Check if the transport is dead.
     *
     * @param TransportInterface $transport The transport
     */
    protected function isTransportDead(TransportInterface $transport): bool
    {
        return $this->deadTransports->contains($transport);
    }

    /**
     * Move the cursor on the next transport, or the first transport if all transports are tested.
     *
     * @param int $cursor The cursor position
     */
    private function moveCursor(int $cursor): int
    {
        return ++$cursor >= \count($this->transports) ? 0 : $cursor;
    }
}
