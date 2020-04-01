<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\SmsSender\Event;

use Klipper\Component\SmsSender\SmsEnvelope;
use Klipper\Component\SmsSender\Transport\Result;
use Symfony\Component\Mime\RawMessage;

/**
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class MessageResultEvent extends AbstractMessageEvent
{
    /**
     * @var Result
     */
    private $result;

    /**
     * Constructor.
     *
     * @param RawMessage  $message  The message
     * @param SmsEnvelope $envelope The envelope
     * @param Result      $result   The result
     */
    public function __construct(RawMessage $message, SmsEnvelope $envelope, Result $result)
    {
        parent::__construct($message, $envelope);

        $this->result = $result;
    }

    /**
     * Get the result.
     */
    public function getResult(): Result
    {
        return $this->result;
    }
}
