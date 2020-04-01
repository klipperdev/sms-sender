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

use Klipper\Component\SmsSender\Exception\InvalidArgumentException;

/**
 * DSN.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
final class Dsn
{
    /**
     * @var string
     */
    private $scheme;

    /**
     * @var string
     */
    private $host;

    /**
     * @var null|string
     */
    private $user;

    /**
     * @var null|string
     */
    private $password;

    /**
     * @var null|int
     */
    private $port;

    /**
     * @var array
     */
    private $options;

    /**
     * Constructor.
     *
     * @param string      $scheme   The scheme
     * @param string      $host     The host
     * @param null|string $user     The user
     * @param null|string $password The password
     * @param null|int    $port     The port
     * @param array       $options  The options
     */
    public function __construct(string $scheme, string $host, ?string $user = null, ?string $password = null, ?int $port = null, array $options = [])
    {
        $this->scheme = $scheme;
        $this->host = $host;
        $this->user = $user;
        $this->password = $password;
        $this->port = $port;
        $this->options = $options;
    }

    /**
     * Create a DSN instance from a string.
     *
     * @param string $dsn The string of dsn
     *
     * @return self
     */
    public static function fromString(string $dsn): self
    {
        if (false === $parsedDsn = parse_url($dsn)) {
            throw new InvalidArgumentException(sprintf('The "%s" SMS Sender DSN is invalid.', $dsn));
        }

        if (!isset($parsedDsn['scheme'])) {
            throw new InvalidArgumentException(sprintf('The "%s" SMS Sender DSN must contain a transport scheme.', $dsn));
        }

        if (!isset($parsedDsn['host'])) {
            throw new InvalidArgumentException(sprintf('The "%s" SMS Sender DSN must contain a SMS Sender name.', $dsn));
        }

        $user = isset($parsedDsn['user']) ? urldecode($parsedDsn['user']) : null;
        $password = isset($parsedDsn['pass']) ? urldecode($parsedDsn['pass']) : null;
        $port = $parsedDsn['port'] ?? null;
        parse_str($parsedDsn['query'] ?? '', $query);
        $query = $query ?? [];

        foreach ($query as $key => $value) {
            $query[$key] = urldecode($value);
        }

        return new self($parsedDsn['scheme'], $parsedDsn['host'], $user, $password, $port, $query);
    }

    /**
     * Get the scheme.
     *
     * @return string
     */
    public function getScheme(): string
    {
        return $this->scheme;
    }

    /**
     * Get the host.
     *
     * @return string
     */
    public function getHost(): string
    {
        return $this->host;
    }

    /**
     * Get the user.
     *
     * @return null|string
     */
    public function getUser(): ?string
    {
        return $this->user;
    }

    /**
     * Get the password.
     *
     * @return null|string
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    /**
     * Get the port.
     *
     * @param null|int $default The default value
     *
     * @return null|int
     */
    public function getPort(int $default = null): ?int
    {
        return $this->port ?? $default;
    }

    /**
     * Get the option.
     *
     * @param string     $key     The key option
     * @param null|mixed $default The default value
     *
     * @return null|mixed
     */
    public function getOption(string $key, $default = null)
    {
        return $this->options[$key] ?? $default;
    }
}
