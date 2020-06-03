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
 * Success result of the transport.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class ErrorResult extends AbstractResultItem
{
    private string $message;

    private string $code;

    private ?\Throwable $throwable;

    /**
     * @param Phone           $recipient The recipient
     * @param string          $message   The error message
     * @param string          $code      The error code
     * @param array           $data      The error data
     * @param null|\Throwable $throwable The exception
     */
    public function __construct(
        Phone $recipient,
        string $message,
        string $code,
        array $data = [],
        ?\Throwable $throwable = null
    ) {
        parent::__construct($recipient, $data);

        $this->message = $message;
        $this->code = $code;
        $this->throwable = $throwable;
    }

    /**
     * Get the error message.
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * Get the error data.
     */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * Get the exception.
     */
    public function getThrowable(): ?\Throwable
    {
        return $this->throwable;
    }
}
