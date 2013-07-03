<?php

namespace Dflydev\Hawk\Server;

use Dflydev\Hawk\Credentials\CredentialsInterface;
use Dflydev\Hawk\Crypto\Artifacts;
use Dflydev\Hawk\Crypto\Crypto;
use Dflydev\Hawk\Header\Header;
use Dflydev\Hawk\Header\HeaderFactory;

class Server implements ServerInterface
{
    private $crypto;

    public function __construct(Crypto $crypto)
    {
        $this->crypto = $crypto;
    }

    public function createRequest(
        $method,
        $host,
        $port,
        $resource,
        $contentType = null,
        $payload = null,
        $headerObjectOrString = null
    ) {
        if (null === $headerObjectOrString) {
            throw new UnauthorizedException("Missing Authorization header");
        }

        if (is_string($headerObjectOrString)) {
            $header = HeaderFactory::createFromString('Authorization', $headerObjectOrString);
        } elseif ($headerObjectOrString instanceof Header) {
            $header = $headerObjectOrString;
        } else {
            throw new UnauthorizedException("Invalid Authorization header");
        }

        return new Request(
            $method,
            $host,
            $port,
            $resource,
            $contentType,
            $payload,
            $header
        );
    }

    public function createAuthenticatorBuilder($credentialsCallback)
    {
        return new AuthenticatorBuilder($this->crypto, $credentialsCallback);
    }

    public function createHeaderBuilder(CredentialsInterface $credentials, Artifacts $artifacts)
    {
        return new HeaderBuilder($this->crypto, $credentials, $artifacts);
    }

    public function authenticatePayload(
        CredentialsInterface $credentials,
        $payload,
        $contentType,
        $hash
    ) {
        $calculatedHash = $this->crypto->calculatePayloadHash($payload, $credentials->algorithm(), $contentType);

        return $this->crypto->fixedTimeComparison($calculatedHash, $hash);
    }
}
