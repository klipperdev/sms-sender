<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\SmsSender;

use Klipper\Component\SmsSender\Exception\InvalidArgumentException;
use Klipper\Component\SmsSender\Exception\LogicException;
use Klipper\Component\SmsSender\Mime\Phone;
use Klipper\Component\SmsSender\Mime\Sms;
use Symfony\Component\Mime\Header\Headers;
use Symfony\Component\Mime\Header\MailboxListHeader;
use Symfony\Component\Mime\Message;
use Symfony\Component\Mime\RawMessage;

/**
 * Delayed Sms envelope.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class DelayedEnvelope extends Envelope
{
    /**
     * @var bool
     */
    private $senderSet = false;

    /**
     * @var bool
     */
    private $recipientsSet = false;

    /**
     * @var Message
     */
    private $message;

    /**
     * Constructor.
     *
     * @param Message|RawMessage $message The message
     */
    public function __construct(RawMessage $message)
    {
        parent::__construct(new Phone('+100'), []);

        if (!$message instanceof Message) {
            throw new InvalidArgumentException(sprintf(
                'A delayed SMS envelope requires an instance of %s ("%s" given).',
                Message::class,
                \get_class($message)
            ));
        }

        $this->message = $message;
    }

    /**
     * {@inheritdoc}
     */
    public function setFrom(Phone $from): void
    {
        parent::setFrom($from);

        $this->senderSet = true;
    }

    /**
     * {@inheritdoc}
     */
    public function getFrom(): Phone
    {
        if ($this->senderSet) {
            return parent::getFrom();
        }

        if ($this->message instanceof Sms && null !== $from = $this->message->getFrom()) {
            return $from;
        }

        throw new LogicException('Unable to determine the sender of the message.');
    }

    /**
     * {@inheritdoc}
     */
    public function setRecipients(array $recipients): void
    {
        parent::setRecipients($recipients);

        $this->recipientsSet = \count(parent::getRecipients()) > 0;
    }

    /**
     * {@inheritdoc}
     */
    public function getRecipients(): array
    {
        if ($this->recipientsSet) {
            return parent::getRecipients();
        }

        return self::getRecipientsFromHeaders($this->message->getHeaders());
    }

    /**
     * Get the recipient phones from the message header.
     *
     * @param Headers $headers The message headers
     *
     * @return Phone[]
     */
    private static function getRecipientsFromHeaders(Headers $headers): array
    {
        $recipients = [];

        /** @var MailboxListHeader $header */
        foreach ($headers->all('to') as $header) {
            foreach ($header->getAddresses() as $phone) {
                $recipients[] = Phone::create($phone);
            }
        }

        return $recipients;
    }
}
