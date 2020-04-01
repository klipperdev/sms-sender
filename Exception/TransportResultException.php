<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\SmsSender\Exception;

use Klipper\Component\SmsSender\Transport\Result;

/**
 * Base TransportResultException for the SmsSender component.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class TransportResultException extends RuntimeException implements TransportExceptionInterface
{
    /**
     * @var Result
     */
    private $result;

    /**
     * Constructor.
     *
     * @param Result $result The result
     * @param int    $code   The exception code
     */
    public function __construct(Result $result, $code = 0)
    {
        parent::__construct($this->buildMessage($result), $code);

        $this->result = $result;
    }

    /**
     * Get the transport result.
     *
     * @return Result
     */
    public function getResult(): Result
    {
        return $this->result;
    }

    /**
     * Build the exception message.
     *
     * @param Result $result The transport result
     *
     * @return string
     */
    private function buildMessage(Result $result): string
    {
        $errors = [];

        foreach ($result->getErrors() as $err) {
            $errors[] = sprintf(PHP_EOL.'- %s: %s (%s)', $err->getRecipient()->toString(), $err->getMessage(), $err->getCode());
        }

        return 'Unable to send an SMS for recipients:'.implode('', $errors);
    }
}
