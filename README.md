Klipper SMS Sender Component
============================

The SMS Sender component is is a powerful system for creating and sending SMS. Like
[Symfony Mailer](https://symfony.com/doc/current/mailer.html) for emails, this library
use the [Symfony Mime](https://symfony.com/doc/current/components/mime.html) to build the
messages. It uses the same logic to create and send the message via several transports.

Features include:

- Create simply a SMS message like an email for Symfony Mailer
- Available transports:
  - Null transport (to not really send the SMS)
  - Failover transport
  - Round Robin transport
- Available 3rd party transports:
  - Amazon AWS with [Klipper Amazon SMS Sender](https://github.com/klipperdev/amazon-sms-sender)
  - Twilio with [Klipper Twilio SMS Sender](https://github.com/klipperdev/twilio-sms-sender)
- Added possibility to register custom transport for dsn by implementing
  `Klipper\Component\SmsSender\Transport\TransportFactoryInterface`
- Render the SMS body with the Twig template with [Klipper Twig SMS Sender](https://github.com/klipperdev/twig-sms-sender)


Resources
---------

- [Documentation](https://doc.klipper.dev/components/sms-sender)
- [Report issues](https://github.com/klipperdev/klipper/issues)
  and [send Pull Requests](https://github.com/klipperdev/klipper/pulls)
  in the [main Klipper repository](https://github.com/klipperdev/klipper)
