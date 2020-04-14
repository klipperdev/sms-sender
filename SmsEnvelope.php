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
use Klipper\Component\SmsSender\Mime\Phone;

/**
 * Sms envelope.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class SmsEnvelope
{
    /**
     * @var Phone
     */
    private $from;

    /**
     * @var Phone[]
     */
    private $recipients = [];

    /**
     * Constructor.
     *
     * @param Phone   $from       The from phone
     * @param Phone[] $recipients The recipient phones
     */
    public function __construct(Phone $from, array $recipients)
    {
        $this->from = $from;
        $this->setRecipients($recipients);
    }

    /**
     * Set the from phone.
     *
     * @param Phone $from The from phone
     */
    public function setFrom(Phone $from): void
    {
        $this->from = $from;
    }

    /**
     * Get the from phone.
     */
    public function getFrom(): Phone
    {
        return $this->from;
    }

    /**
     * Set the recipient phones.
     *
     * @param array $recipients The recipient phones
     */
    public function setRecipients(array $recipients): void
    {
        $this->recipients = [];

        foreach ($recipients as $recipient) {
            if (!$recipient instanceof Phone) {
                throw new InvalidArgumentException(sprintf('A recipient must be an instance of "%s" (got "%s").', Phone::class, \is_object($recipient) ? \get_class($recipient) : \gettype($recipient)));
            }

            $this->recipients[] = $recipient;
        }
    }

    /**
     * Get the recipient phones.
     *
     * @return Phone[]
     */
    public function getRecipients(): array
    {
        return $this->recipients;
    }
}
