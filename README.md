Hawk — A PHP Implementation
===========================

> Hawk is an HTTP authentication scheme using a message authentication code
> (MAC) algorithm to provide partial HTTP request cryptographic verification.
> — [hawk README][0]


Installation
------------

Through [Composer][1] as [dflydev/hawk][2].


Client
------

### Building a Client

The `Client` has a few required dependencies. It is generally easier to
construct a `Client` by using the `ClientBuilder`. A `Client` can be built
without setting anything to get sane defaults.

#### Simple ClientBuilder Example

```php
<?php

// Simple example
$client = Dflydev\Hawk\Client\ClientBuilder::create()
    ->build()
```

#### Complete ClientBuilderExample

```php
<?php

// Simple example
$client = Dflydev\Hawk\Client\ClientBuilder::create()
    ->setCrypto($crypto)
    ->setTimeProvider($timeProvider)
    ->setNonceProvider($nonceProvider)
    ->setLocaltimeOffset($localtimeOffset)
    ->build()
```

### Creating a Request

In order for a client to be able to sign a request, it needs to know the
credentials for the user making the request, the URL, method, and optionally
payload and content type of the request.

All available options include:

 * **payload**: The body of the request
 * **content_type**: The content-type for the request
 * **nonce**: If a specific nonce should be used in favor of one being generated
   automatically by the nonce provider.
 * **ext**: An ext value specific for this request
 * **app**: The app for this request ([Oz][3] specific)
 * **dlg**: The delegated-by value for this request ([Oz][3] specific)


#### Create Request Example

```php
<?php

$request = $client->createRequest(
    $credentials,
    'http://example.com/foo/bar?whatever',
    'POST',
    array(
        'payload' => 'hello world!',
        'content_type' => 'text/plain',
    )
);

// Assuming a hypothetical $headers object that can be used to add new headers
// to an outbound request, we can add the resulting 'Authorization' header
// for this Hawk request by doing:
$headers->set(
    $request->header()->fieldName(),
    $request->header()->fieldValue()
);

```

#### The Client Request Object

The `Request` represents everything the client needs to know about a request
including a header and the artifacts that were used to create the request.

 * **header()**: A `Header` instance that represents the request
 * **artifacts()**: An `Artifacts` instance that contains the values that were
   used in creating the request


### Authenticate Server Response

Hawk provides the ability for the client to authenticate a server response to
ensure that the response sent back is from the intended target.

All available options include:

 * **payload**: The body of the response
 * **content_type**: The content-type for the response


#### Authenticate Response Example

```php
<?php

// Assuming a hypothetical $headers object that can be used to get headers sent
// back as the response of a user agent request, we can get the value for the
// 'Server-Authorization' header.
$header = $headers->get('Server-Authorization');

// We need to use the original credentials, the original request, the value
// for the 'Server-Authorization' header, and optionally the payload and
// content type of the response from the server.
$authenticatedResponse = $client->authenticate(
    $credentials,
    $request,
    $header,
    array(
        'payload' => '{"message": "good day, sir!"}',
        'content_type' => 'application/json',
    )
);
```

Crypto
------

### Dflydev\Hawk\Crypto\Crypto

Tools for calculation of and comparison of MAC values.

 * **calculatePayloadHash($payload, $algorithm, $contentType)**
 * **calculateMac($type, CredentialsInterface $credentials, Artifacts $attributes)**
 * **calculateTsMac($ts, CredentialsInterface $credentials)**
 * **fixedTimeComparison($a, $b)**<br>
   Used to ensure that the comparing two strings will always take the same amount
   of time regardless of whether they are the same or not.


### Dflydev\Hawk\Crypto\Artifacts

A container for all of the pieces of data that may go into the creation of a
MAC.


Credentials
-----------

### Dflydev\Hawk\Credentials\CredentialsInterface

Represents a valid set of credentials.

 * **key()**: Used to calculate the MAC
 * **algorithm()**: The algorithm used to calculate hashes
 * **id()**: An identifier (e.g. username) for whom the key belongs

In some contexts only the key may be known.

### new Dflydev\Hawk\Credentials\Credentials

A simple implementation of `CredentialsInterface`.

```php
<?php

$credentials = new Dflydev\Hawk\Credentials\Credentials(
    $key,        // shared key
    $algorithm,  // default: sha256
    $id          // identifier, default: null
);

```


Header
------

### Dflydev\Hawk\Header\Header

 * **fieldName()**: The name for the header field
 * **fieldValue()**: The value for the header field
 * **attributes()**: The attributes used to build the field value


License
-------

MIT, see LICENSE.


Community
---------

If you have questions or want to help out, join us in **#dflydev** on
**irc.freenode.net**.


[0]: https://github.com/hueniverse/hawk
[1]: http://getcomposer.org/
[2]: http://packagist.org/packages/dflydev/hawk
[3]: https://github.com/hueniverse/oz
