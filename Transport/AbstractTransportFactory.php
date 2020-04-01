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

use Klipper\Component\SmsSender\Exception\IncompleteDsnException;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Abstract class for the transport.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
abstract class AbstractTransportFactory implements TransportFactoryInterface
{
    /**
     * @var EventDispatcherInterface
     */
    protected $dispatcher;

    /**
     * @var HttpClientInterface
     */
    protected $client;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Constructor.
     *
     * @param null|EventDispatcherInterface $dispatcher The event dispatcher
     * @param null|HttpClientInterface      $client     The http client
     * @param null|LoggerInterface          $logger     The logger
     */
    public function __construct(
        EventDispatcherInterface $dispatcher = null,
        HttpClientInterface $client = null,
        LoggerInterface $logger = null
    ) {
        $this->dispatcher = $dispatcher;
        $this->client = $client;
        $this->logger = $logger;
    }

    /**
     * Get the user.
     *
     * @param Dsn $dsn The dsn instance
     *
     * @throws IncompleteDsnException When user is not set
     *
     * @return string
     */
    protected function getUser(Dsn $dsn): string
    {
        if (null === $user = $dsn->getUser()) {
            throw new IncompleteDsnException('User is not set');
        }

        return $user;
    }

    /**
     * Get the password.
     *
     * @param Dsn $dsn The dsn instance
     *
     * @throws IncompleteDsnException When password is not set
     *
     * @return string
     */
    protected function getPassword(Dsn $dsn): string
    {
        if (null === $password = $dsn->getPassword()) {
            throw new IncompleteDsnException('Password is not set');
        }

        return $password;
    }
}
