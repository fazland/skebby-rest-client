Skebby Rest Client
==================
Fazland's Skebby Rest Client is an unofficial PHP Rest Client for the italian SMS GatewayProvider [Skebby](http://www.skebby.it). 

Requirements
------------
- php >= 5.5.0
- symfony/options-resolver >= 2.7

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
- `sender_number`
- `method`

Just create a `Client` object passing to the constructor the parameters as an array:
```php
$this->skebbyRestClient = new Client([
    'username' => 'your_username',
    'password' => 'your_password',
    'sender_number' => '+393333333333',
    'method' => SendMethods::CLASSIC
]);
```

In that array you can also specify the optional configuration parameters:
- `delivery_start`
- `validity_period`
- `encoding_scheme`
- `charset`

You can even specify to which REST endpoint you want to connect with the parameter `endpoint_uri`

A part from integer or string parameters, some can have only a set of values. You can find those in the `Fazland\SkebbyRestClient\Constant` namespace.

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

Contributing
------------
Contributions are welcome. Feel free to open a PR or file an issue here on GitHub!

License
-------
Skebby Rest Client is licensed under the MIT License - see the [LICENSE](https://github.com/fazland/Notifire/blob/master/LICENSE) file for details
