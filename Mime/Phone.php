<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\SmsSender\Mime;

use Klipper\Component\SmsSender\Exception\E164ComplianceException;
use Klipper\Component\SmsSender\Exception\InvalidArgumentException;
use Klipper\Component\SmsSender\Exception\LogicException;
use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Encoder\IdnAddressEncoder;

/**
 * Phone.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
final class Phone
{
    /**
     * @var string
     */
    public static $encoderClass = PhoneNumberUtil::class;

    /**
     * @var PhoneNumberUtil
     */
    private static $phoneEncoder;

    /**
     * @var null|IdnAddressEncoder
     */
    private static $addressEncoder;

    /**
     * @var string
     */
    private $phone;

    /**
     * Constructor.
     *
     * @param string $phone The phone
     */
    public function __construct(string $phone)
    {
        if (!class_exists(static::$encoderClass)) {
            throw new LogicException(sprintf('The "%s" class cannot be used as it needs "%s"; try running "composer require giggsey/libphonenumber-for-php".', \get_class($this), static::$encoderClass));
        }

        if (null === self::$phoneEncoder) {
            self::$phoneEncoder = PhoneNumberUtil::getInstance();
        }

        $phone = str_replace('@carrier', '', $phone);

        try {
            self::$phoneEncoder->parse($phone);
        } catch (NumberParseException $e) {
            throw new E164ComplianceException(sprintf('Phone "%s" does not comply with number-spec of E164.', $phone));
        }

        $this->phone = $phone;
    }

    /**
     * Get the phone.
     */
    public function getPhone(): string
    {
        return $this->phone;
    }

    /**
     * Convert the phone into a string.
     */
    public function toString(): string
    {
        return $this->getEncodedPhone();
    }

    /**
     * Get the encoded phone.
     *
     * @throws
     */
    public function getEncodedPhone(): string
    {
        return self::$phoneEncoder->format(self::$phoneEncoder->parse($this->phone), PhoneNumberFormat::E164);
    }

    public function getAddress(): string
    {
        return $this->getEncodedPhone().'@carrier';
    }

    public function getEncodedAddress(): string
    {
        if (null === self::$addressEncoder) {
            self::$addressEncoder = new IdnAddressEncoder();
        }

        return self::$addressEncoder->encodeString($this->getAddress());
    }

    /**
     * Create the phone instance.
     *
     * @param Address|Phone|string $phone The phone
     *
     * @return static
     */
    public static function create($phone): Phone
    {
        if ($phone instanceof Address) {
            $phone = $phone->getAddress();
        }

        if ($phone instanceof self) {
            return $phone;
        }

        if (\is_string($phone)) {
            return new self($phone);
        }

        throw new InvalidArgumentException(sprintf(
            'A phone can be an instance of %s or a string ("%s" given).',
            static::class,
            \is_object($phone) ? \get_class($phone) : \gettype($phone)
        ));
    }

    /**
     * Create the phone instances.
     *
     * @param Address[]|Phone[]|string[] $phones The phones
     *
     * @return static[]
     */
    public static function createArray(array $phones): array
    {
        $res = [];

        foreach ($phones as $phone) {
            $res[] = self::create($phone);
        }

        return $res;
    }

    /**
     * Create and convert the phone instance into address instance.
     *
     * @param Address|Phone|string $phone The phone
     */
    public static function createAddress($phone): Address
    {
        return Address::create(static::create($phone)->getAddress());
    }

    /**
     * Create and convert the phone instances into address instances.
     *
     * @param Address[]|Phone[]|string[] $phones The phones
     *
     * @return Address[]
     */
    public static function createAddressArray(array $phones): array
    {
        $res = [];

        foreach ($phones as $phone) {
            $res[] = static::createAddress($phone);
        }

        return $res;
    }

    public static function isAddressPhone(Address $address): bool
    {
        return '@carrier' === substr($address->getAddress(), -8);
    }
}
