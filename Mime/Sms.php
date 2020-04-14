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

use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Header\HeaderInterface;
use Symfony\Component\Mime\Header\MailboxListHeader;
use Symfony\Component\Mime\Message;
use Symfony\Component\Mime\Part\TextPart;

/**
 * Sms message.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class Sms extends Message
{
    /**
     * @var Phone[]
     */
    private array $cachePhones = [];

    /**
     * Add the from phone.
     *
     * @param Phone|string $phone The phone
     *
     * @return static
     */
    public function from($phone): self
    {
        return $this->setListPhoneHeaderBody('From', [$phone]);
    }

    /**
     * Get the from phone.
     */
    public function getFrom(): ?Phone
    {
        return $this->getPhoneFromListHeader('From');
    }

    /**
     * Add the to phones.
     *
     * @param Phone|Phone[]|string|string[] $phones The phones
     *
     * @return static
     */
    public function addTo(...$phones): self
    {
        return $this->addListPhoneHeaderBody('To', $phones);
    }

    /**
     * Set or replace the to phones.
     *
     * @param Phone|Phone[]|string|string[] $phones
     *
     * @return static
     */
    public function to(...$phones): self
    {
        return $this->setListPhoneHeaderBody('To', $phones);
    }

    /**
     * Get the to phones.
     *
     * @return Phone[]
     */
    public function getTo(): array
    {
        return $this->getPhonesFromListHeader('To');
    }

    /**
     * Set the text.
     *
     * @param string $body The text
     *
     * @return static
     */
    public function text(string $body): self
    {
        $this->setBody(new TextPart($body));

        return $this;
    }

    /**
     * Get the text.
     */
    public function getText(): ?string
    {
        $body = $this->getBody();

        return $body instanceof TextPart ? $body->getBody() : null;
    }

    /**
     * Add the header.
     *
     * @param HeaderInterface $header The header
     *
     * @return static
     */
    public function addHeader(HeaderInterface $header): self
    {
        $this->getHeaders()->add($header);

        return $this;
    }

    /**
     * Add the phones in the header.
     *
     * @param string           $name   The header name
     * @param Phone[]|string[] $phones The phones
     *
     * @return static
     */
    private function addListPhoneHeaderBody(string $name, array $phones): self
    {
        /** @var null|MailboxListHeader $to */
        if (null === $to = $this->getHeaders()->get($name)) {
            return $this->setListPhoneHeaderBody($name, $phones);
        }

        $to->addAddresses(Phone::createAddressArray($this->setCachePhones($phones)));

        return $this;
    }

    /**
     * Set or replace the phones in the header.
     *
     * @param string           $name   The header name
     * @param Phone[]|string[] $phones The phones
     *
     * @return static
     */
    private function setListPhoneHeaderBody(string $name, array $phones): self
    {
        $phoneAddresses = Phone::createAddressArray($this->setCachePhones($phones));
        $headers = $this->getHeaders();

        /** @var null|MailboxListHeader $to */
        if (null !== $to = $headers->get($name)) {
            $to->setAddresses($phoneAddresses);
        } else {
            $headers->addMailboxListHeader($name, $phoneAddresses);
        }

        return $this;
    }

    /**
     * Get the phones from the header.
     *
     * @param string $name The header name
     *
     * @return Phone[]
     */
    private function getPhonesFromListHeader(string $name): array
    {
        $header = $this->getHeaders()->get($name);
        $phones = [];

        if ($header instanceof MailboxListHeader
                && null !== ($body = $header->getBody())
                && \count($body) > 0) {
            foreach ($body as $value) {
                if ($value instanceof Address && Phone::isAddressPhone($value)) {
                    $phones[] = $this->getCachePhone(Phone::create($value));
                }
            }
        }

        return $phones;
    }

    /**
     * Get the phone form the header.
     *
     * @param string $name The header name
     */
    private function getPhoneFromListHeader(string $name): ?Phone
    {
        $phones = $this->getPhonesFromListHeader($name);

        return \count($phones) > 0 ? $phones[0] : null;
    }

    /**
     * Set the phone instance in cache.
     *
     * @param Phone|string $phone The phone instance
     */
    private function setCachePhone($phone): Phone
    {
        $phone = Phone::create($phone);
        $this->cachePhones[$phone->getAddress()] = $phone;

        return $phone;
    }

    /**
     * Set the phone instances in cache.
     *
     * @param Phone[]|string[] $phones The phone instances
     *
     * @return Phone[]
     */
    private function setCachePhones(array $phones): array
    {
        $res = [];

        foreach ($phones as $phone) {
            $res[] = $this->setCachePhone($phone);
        }

        return $res;
    }

    private function getCachePhone(Phone $phone): Phone
    {
        return $this->cachePhones[$phone->getAddress()] ?? $phone;
    }
}
