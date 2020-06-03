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

use Klipper\Component\SmsSender\SentMessage;

/**
 * Pretends messages have been sent, but just ignores them.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
final class NullTransport extends AbstractTransport
{
    public function getName(): string
    {
        return 'sms://null';
    }

    protected function doSend(SentMessage $message): void
    {
    }
}
