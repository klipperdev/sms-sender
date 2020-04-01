Getting Started With Klipper SMS Sender
===================================

## Prerequisites

### Download the library using composer

Tell composer to download the library by running the command:

```bash
$ composer require klipper/sms-sender
```

Composer will install the library to your project's `vendor/klipper` directory.

SMS are delivered via a `transport`. By default, only the NULL transport are available. To
send a message with a real transport, you must install a 3rd Party Transport.

### Using a 3rd Party Transport

To send a SMS, a 3rd party provider is required. SMS Sender supports several - install
whichever you want:

Service    | Install with
-----------|---------------------------------------
Amazon SNS | composer require klipper/amazon-sms-sender
Twilio     | composer require klipper/twilio-sms-sender
