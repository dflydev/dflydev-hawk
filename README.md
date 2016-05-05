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

// A complete example
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
    $request->header()->fieldName(), // 'Authorization'
    $request->header()->fieldValue() // 'Hawk id="12345", mac="ad8c9f', ...'
);

```

#### The Client Request Object

The `Request` represents everything the client needs to know about a request
including a header and the artifacts that were used to create the request.

 * **header()**: A `Header` instance that represents the request
 * **artifacts()**: An `Artifacts` instance that contains the values that were
   used in creating the request

The **header** is required to be able to get the properly formatted Hawk
authorization header to send to the server. The **artifacts** are useful in the
case that authentication will be done on the server response.


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
$isAuthenticatedResponse = $client->authenticate(
    $credentials,
    $request,
    $header,
    array(
        'payload' => '{"message": "good day, sir!"}',
        'content_type' => 'application/json',
    )
);
```

### Complete Client Example

```php
<?php

// Create a set of Hawk credentials
$credentials = new Dflydev\Hawk\Credentials\Credentials(
    'afe89a3x',  // shared key
    'sha256',    // default: sha256
    '12345'      // identifier, default: null
);

// Create a Hawk client
$client = Dflydev\Hawk\Client\ClientBuilder::create()
    ->build();

// Create a Hawk request based on making a POST request to a specific URL
// using a specific user's credentials. Also, we're expecting that we'll
// be sending a payload of 'hello world!' with a content-type of 'text/plain'.
$request = $client->createRequest(
    $credentials,
    'http://example.com/foo/bar?whatever',
    'POST',
    array(
        'payload' => 'hello world!',
        'content_type' => 'text/plain',
    )
);

// Ask a really useful fictional user agent to make a request; note that the
// request we are making here matches the details that we told the Hawk client
// about our request.
$response = Fictional\UserAgent::makeRequest(
    'POST',
    'http://example.com/foo/bar?whatever',
    array(
        'content_type' => 'text/plain',
        $request->header()->fieldName() => $request->header()->fieldValue(),
    ),
    'hello world!'
);

// This part is optional but recommended! At this point if we have a successful
// response we could just look at the content and be done with it. However, we
// are given the tools to authenticate the response to ensure that the response
// we were given came from the server we were expecting to be talking to.
$isAuthenticatedResponse = $client->authenticate(
    $credentials,
    $request,
    $response->headers->get('Server-Authorization'),
    array(
        'payload' => $response->getContent(),
        'content_type' => $response->headers->get('content-type'),
    )
);

if (!$isAuthenticatedResponse) {
    die("The server did a very bad thing...");
}

// Huzzah!
```


### Bewit

Hawk supports a method for granting third-parties temporary access to individual
resources using a query parameter called bewit.

The return value is a string that represents the bewit. This string should be
added to a requested URI by appending it to the end of the URI. If the URI has
query parameters already, the bewit should have `&bewit=` appended to the front
of it. If the URI does not have query parameters already, the bewit should
have `?bewit=` appended to the front of it.

#### Client Bewit Example

```php
<?php

$bewit = $client->createBewit(
    $credentials,
    'https://example.com/posts?foo=bar',
    300 // ttl in seconds
);
```

Server
------

### Building a Server

The `Server` has a few required dependencies. It is generally easier to
construct a `Server` by using the `ServerBuilder`. A `Server` can be built
without setting anything but the credentials provider to get sane defaults.

#### Simple ServerBuilder Example

```php
<?php

$credentialsProvider = function ($id) {
    if ('12345' === $id) {
        return new Dflydev\Hawk\Credentials\Credentials(
            'afe89a3x',  // shared key
            'sha256',    // default: sha256
            '12345'      // identifier, default: null
        );
    }
};

// Simple example
$server = Dflydev\Hawk\Server\ServerBuilder::create($credentialsProvider)
    ->build()
```

#### Complete ServerBuilderExample

```php
<?php

$credentialsProvider = function ($id) {
    if ('12345' === $id) {
        return new Dflydev\Hawk\Credentials\Credentials(
            'afe89a3x',  // shared key
            'sha256',    // default: sha256
            '12345'      // identifier, default: null
        );
    }

    throw new UnauthorizedException("Unknown credentials");
};

// A complete example
$server = Dflydev\Hawk\Server\ServerBuilder::create($credentialsProvider)
    ->setCrypto($crypto)
    ->setTimeProvider($timeProvider)
    ->setNonceValidator($nonceValidator)
    ->setTimestampSkewSec($timestampSkewSec)
    ->setLocaltimeOffsetSec($localtimeOffsetSec)
    ->build()
```

### Authenticating a Request

In order for a server to be able to authenticate a request, it needs to be able
to build the same MAC that the client did. It does this by getting the same
information about the request that the client knew about when it signed the
request.

In particular, the authorization header should include the ID. This ID is used
to retrieve the credentials (notably the key) in order to calculate the MAC
based on the rest of the request information.

#### Authenticate Example

```php
<?php

// Get the authorization header for the request; it should be in the form
// of 'Hawk id="...", mac="...", [...]'
$authorization = $headers->get('Authorization');

try {
    $response = $server->authenticate(
        'POST',
        'example.com',
        80,
        '/foo/bar?whatever',
        'text/plain',
        'hello world!'
        $authorization
    );
} catch(Dflydev\Hawk\Server\UnauthorizedException $e) {
    // If authorization is incorrect (invalid mac, etc.) we can catch an
    // unauthorized exception.
    throw $e;
}

// The credentials associated with this request. This is where one could access
// the ID for the user that made this request.
$credentials = $response->credentials();

// The artifacts associated with this request. This is where one could access
// things like the 'ext', 'app', and 'dlg' values sent with the request.
$artifacts = $response->artifacts();
```

#### The Server Response Object

The `Response` represents everything the server needs to know about a request
including the credentials and artifacts that are associated with the request.

 * **credentials()**
 * **artifacts()**


### Creating a Response Header

Hawk provides the ability for the server to sign the response to provide the
client with a way to authenticate a server response.

All available options include:

 * **payload**: The body of the request
 * **content_type**: The content-type for the request
 * **ext**: An ext value specific for this request


#### Create Response Header Example

```php
<?php

// Using the same credentials and artifacts from the server authenticate
// response, we can create a 'Server-Authorization' header.
$header = $server->createHeader($credentials, $artifacts, array(
    'payload' => '{"message": "good day, sir!"}',
    'content_type' => 'application/json',
));

// Set the header using PHP's header() function.
header(sprintf("%s: %s", $header->fieldName(), $header->fieldValue()));
```

### Complete Server Example

```php
<?php

// Create a simple credentials provider
$credentialsProvider = function ($id) {
    if ('12345' === $id) {
        return new Dflydev\Hawk\Credentials\Credentials(
            'afe89a3x',  // shared key
            'sha256',    // default: sha256
            '12345'      // identifier, default: null
        );
    }
};

// Create a Hawk server
$server = Dflydev\Hawk\Server\ServerBuilder::create($credentialsProvider)
    ->build()

// Get the authorization header for the request; it should be in the form
// of 'Hawk id="...", mac="...", [...]'
$authorization = $headers->get('Authorization');

try {
    $response = $server->authenticate(
        'POST',
        'example.com',
        80,
        '/foo/bar?whatever',
        'text/plain',
        'hello world!'
        $authorization
    );
} catch(Dflydev\Hawk\Server\UnauthorizedException $e) {
    // If authorization is incorrect (invalid mac, etc.) we can catch an
    // unauthorized exception.
    throw $e;
}

// Huzzah! Do something at this point with the request as we now know that
// it is an authenticated Hawk request.
//
// ...
//
// Ok we are done doing things! Assume based on what we did we ended up deciding
// the following payload and content type should be used:

$payload = '{"message": "good day, sir!"}';
$contentType = 'application/json';

// Create a Hawk header to sign our response
$header = $server->createHeader($credentials, $artifacts, array(
    'payload' => $payload,
    'content_type' => $contentType,
));

// Send some headers
header(sprintf("%s: %s", 'Content-Type', 'application/json'));
header(sprintf("%s: %s", $header->fieldName(), $header->fieldValue()));

// Output our payload
print $payload;
```


### Bewit

Hawk supports a method for granting third-parties temporary access to individual
resources using a query parameter called bewit.

Bewit authentication should only occur for `GET` and `HEAD` requests. The return
value of an authenticated bewit is a Server Response object.

#### Server Bewit Example

```php
<?php

$response = $server->authenticateBewit(
    'example.com',
    443,
    '/posts?bewit=ZXhxYlpXdHlrRlpJaDJEN2NYaTlkQVwxMzY4OTk2ODAwXE8wbWhwcmdvWHFGNDhEbHc1RldBV3ZWUUlwZ0dZc3FzWDc2dHBvNkt5cUk9XA'
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

### Dflydev\Hawk\Credentials\Credentials

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

### Dflydev\Hawk\Header\HeaderFactory

 * **create($fieldName, array $attributes = null)**<br>
   Creates a Hawk header for a given field name for a set of attributes.
 * **createFromString($fieldName, $fieldValue, array $requiredKeys = null)**<br>
   Creates a Hawk header for a given field name from a Hawk value string. For
   example, 'Hawk id="foo", mac="1234"' would be an example of a Hawk value
   string. This is useful for converting a header value coming in off the wire.

   Throws:

    * **Dflydev\Hawk\Header\FieldValueParserException**
    * **Dflydev\Hawk\Header\NotHawkAuthorizationException**

### Dflydev\Hawk\Header\HeaderParser

 * **parseFieldValue($fieldValue, array $requiredKeys = null)**<br>
   Parses a field value string into an associative array of attributes.

   Throws:

    * **Dflydev\Hawk\Header\FieldValueParserException**
    * **Dflydev\Hawk\Header\NotHawkAuthorizationException**

### Dflydev\Hawk\Header\FieldValueParserException

Indicates that a string claims to be a Hawk string but it cannot be completely
parsed. This is mostly a sign of a corrupted or malformed header value.

### Dflydev\Hawk\Header\NotHawkAuthorizationException

Indicates that the string has nothing to do with Hawk. Currently means that the
string does not start with 'Hawk'.


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
