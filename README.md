Skebby Rest Client
==================
[![Build Status](https://travis-ci.org/fazland/skebby-rest-client.svg?branch=master)](https://travis-ci.org/fazland/skebby-rest-client) [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/fazland/skebby-rest-client/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/fazland/skebby-rest-client/?branch=master) [![Code Coverage](https://scrutinizer-ci.com/g/fazland/skebby-rest-client/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/fazland/skebby-rest-client/?branch=master)

Fazland's Skebby Rest Client is an unofficial PHP Rest Client for the italian SMS GatewayProvider [Skebby](http://www.skebby.it). 

Requirements
------------
- `php` >= 7.0
- `php curl extension`
- `symfony/options-resolver` >= 2.7
- `giggsey/libphonenumber-for-php` >= 7.0

Installation
------------
The suggested installation method is via [composer](https://getcomposer.org/):

```sh
$ composer require fazland/skebby-rest-client
```

Using Skebby Rest Client
------------------------
It's really simple. First of all, configuration!

### Configuration
The mandatory configuration parameters are:
- `username`
- `password`
- `sender`
- `method`

Just create a `Client` object passing to the constructor the parameters as an array:

```php
$this->skebbyRestClient = new Client([
    'username' => 'your_username',
    'password' => 'your_password',
    'sender' => '+393333333333',
    'method' => SendMethods::CLASSIC,
    'encoding_scheme' => EncodingSchemas::NORMAL,  // Optional
    'charset' => Charsets::UTF8,                   // Optional
    'endpoint_uri' => 'https://gateway.skebby.it/api/send/smseasy/advanced/rest.php' // (default)
]);
```

You can also set default values for `delivery_start` and `validity_period`, thus they can be overridden by the Sms object

### Creating SMS:
To create an SMS just follow the example:

```php
Sms::create()
    ->setRecipients([
        '+393473322444',
        '+393910000000'
    ])
    ->setRecipientVariables('+393473322444', [
        'name' => 'Mario',
        'quest' => 'Go and rescue Peach, Bowser kidnapped her!'
    ])
    ->setRecipientVariables('+393910000000', [
        'name' => 'Luigi',
        'quest' => 'Help Mario, Bowser is really bad!!'
    ])
    ->setText('Hey ${name}! ${quest}')
;
```
### Send SMS!
Just use the `Client::send(Sms $sms)` method to send sms!
```php
$client->send($sms);
```

### Note:
A single client will send SMS through the method you specified in configuration. If you want to send it through another method, just create a new client.

### Events

You can leverage your preferred event system, as long as it implements [PSR-14](https://www.php-fig.org/psr/psr-14/).
Just pass your dispatcher as third argument of Client constructor:

```php
$dispatcher = new EventDispatcher();    // any dispatcher implementing EventDispatcherInterface
$options = [/* .. */];  // see above for detailed options
$this->skebbyRestClient = new Client($options, null, $dispatcher);
```

Each time an SMS is sent, a `\Fazland\SkebbyRestClient\Event\SmsMessageEvent` will be dispatched.

Test
----
Run 
```sh
$ vendor/bin/phpunit
```

Contributing
------------
Contributions are welcome. Feel free to open a PR or file an issue here on GitHub!

License
-------
Skebby Rest Client is licensed under the MIT License - see the [LICENSE](https://github.com/fazland/skebby-rest-client/blob/master/LICENSE) file for details
