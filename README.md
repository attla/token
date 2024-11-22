# Web Token

<p align="center">
<a href="LICENSE"><img src="https://img.shields.io/badge/license-MIT-lightgrey.svg" alt="License"></a>
<a href="https://packagist.org/packages/attla/token"><img src="https://img.shields.io/packagist/v/attla/token" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/attla/token"><img src="https://img.shields.io/packagist/dt/attla/token" alt="Total Downloads"></a>
</p>

## Installation

```bash
composer require attla/token
```

## Usage

#### Creating and managing a token:

```php

use Attla\Token\Factory as Token;
use Attla\Token\Facade as TokenFacade;

// create token on PHP projects
$token = Token::create();
// on laravel projects
$token = TokenFacade::create();
// or with global alias on laravel projects
$token = \Token::create();

// set a payload
$token->body('token value..');

// get the token value
$tokenEncoded = $token->get();
```

#### Configure the token instance:

```php
$token = Token::create()->secret('your secret phrase');
// changing the secret on exist instance
$token->secret('your secret phrase');

// secret aliases
$token->phrase('your secret phrase');
$token->passphrase('your secret phrase');

// Set token body type when it can be converted (array, stdClass, object)
$token->associative(); // set token payload as associative array
$token->asObject();    // set payload as stdClass object

// defines that it will always generate the same result
$token->same();
```

By default the `secret` key is empty, but on laravel projects the default as `env('APP_KEY')` or `config('app.key')`

When token body as `string`, `integer`, `float`, `bool`, and `null`, it cant be converted to associative or object equivalent

#### Setup token claims:

Set the `expiration` time in seconds after which the JWT MUST NOT be accepted for processing:

```php
use Carbon\Carbon;

$time = strtotime('+1 hour');

$token->exp($time);
$token->expiration((new \DateTime())->setTimeStamp($time));
$token->expiresAt(Carbon::createFromTimestamp($time));
```

Set the time at which the JWT was issued (`iat`):

```php
use Carbon\Carbon;

$time = strtotime('-1 day');

$token->iat($time);
$token->issuedAt((new \DateTime())->setTimeStamp($time));
$token->issuedBefore(Carbon::createFromTimestamp($time));
```

Set the time before (`nbf`) which the JWT MUST NOT be accepted for processing

```php
use Carbon\Carbon;

$time = strtotime('+30 day');

$token->nbf($time);
$token->notBefore((new \DateTime())->setTimeStamp($time));
$token->canOnlyBeUsedAfter(Carbon::createFromTimestamp($time));
```

Set the `audience` that the JWT is intended for:

```php
$token->aud('https://example.com');
$token->audience('https://example.com', 'https://example.app');
$token->permittedFor(['https://example.net', 'https://example.org']);
```

Set the principal `subject` of the JWT:

```php
$token->relatedTo('exampl@e.com');
$token->sub('exampl@e.com');
```

Set the principal that issued (`iss`) the JWT:

```php
$token->issuedBy('https://example.com');
$token->iss('https://example.net');
```

Set the unique identifier (`jti`) for the JWT:

```php
$jti = hash('sha256', uniqid(mt_rand(), true));

$token->jti($jti);
$token->identifiedBy($jti);
```

#### Custom validation claims:

Lock the token by `browser` user agent:

```php
// current browser
$token->bwr();
$token->broser();

// setup a user agent by string
$token->browser('Mozilla/5.0 (U; Linux x86_64; en-US) Gecko/20100101 Firefox/50.9');
```

Lock the token by `ip` address:

```php
// current request ip address
$token->ip();

// setup a ip address by string
$token->ip('1.1.1.1');
$token->ip('1.1.1.1', '2001:db8:0:0:0:0:2:1');
$token->ip(['1.1.1.1', '8.8.8.8']);
```

Lock the token by geographic coordinates (`loc`):

```php
// setup a location by coordinate string
$token->loc('-44.05964,77.10679,5');
```

#### Setup custom claim:

```php
// set a custom claim "uid"
$token->withClaim('uid', 1);
$token->with('uid', 1); // alias

// on parse validate using:
$token->with('uid', 1);
```

All claim values as inserted on token header, to be retrieved on body use:

```php
// insert the payload as array or object
$token->payload(['uid' => 1]);

// on parse validate use:
$token->with('uid', 1);
```

Verifying if a value is present on token:

```php
$hasUid = $token->has('uid'); // isset(uid)
$hasUidWithValue = $token->has('uid', 1); // isset(uid) && uid === 1
```

#### Parse a token:

```php
$tokenValue = Token::parse($tokenEncoded)
    ->associative()
    ->get();
```

#### Real world example:

```php
// Creating
$token = Token::create()
    ->secret('your secret phrase')            // secret key
    ->iss($_SERVER['HTTP_HOST'])              // Set 'issuer' claim
    ->aud('e.com', $_SERVER['HTTP_HOST'])     // Set 'audience' claim
    ->sub('7urkg6uDkMISjZBuFGdeySokAIrSuWAB') // Set 'subject' claim
    ->iat(time()) // Set 'issued' date in seconds
    ->exp(7200)   // Set 'expiration' in seconds (2 hours)
    ->bwr()       // Lock the token by user agent of browser
    ->ip()        // Lock the token with IP (v6 or v4)
    ->payload([   // Set the token payload
        'name' => 'Acme LLC',
        'email' => 'acme@e.com',
    ]);

// Get the token
$tokenEncoded = $token->get();
echo $tokenEncoded . PHP_EOL;

$tokenParse = Token::parse($tokenEncoded)
    ->iss($_SERVER['HTTP_HOST']) // Set the issuer claim for validate
    ->validAt(time() - 3600)     // Rewrites the current date for 'exp', 'iat', 'nbf' validations
    ->associative();

if ($tokenParse->isValid()) {
    echo 'Subject: '. $tokenParse->sub() . PHP_EOL;
    echo 'Audience: '. implode(',', $tokenParse->audience()) . PHP_EOL;
    echo $tokenParse->get() . PHP_EOL;
} else {
    echo "Token as invalid!" . PHP_EOL;
}

```

## License

This package is licensed under the [MIT license](LICENSE) © [Zunq](https://zunq.com).
