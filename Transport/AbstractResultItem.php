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

use Klipper\Component\SmsSender\Mime\Phone;

/**
 * Abstract result item of the transport.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
abstract class AbstractResultItem
{
    protected Phone $recipient;

    protected array $data;

    /**
     * @param Phone $recipient The recipient
     * @param array $data      The data
     */
    public function __construct(Phone $recipient, array $data)
    {
        $this->recipient = $recipient;
        $this->data = $data;
    }

    /**
     * Get the recipient.
     */
    public function getRecipient(): Phone
    {
        return $this->recipient;
    }

    /**
     * Get the data.
     */
    public function getData(): array
    {
        return $this->data;
    }
}
