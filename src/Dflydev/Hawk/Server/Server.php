<?php

namespace Dflydev\Hawk\Server;

use Dflydev\Hawk\Credentials\CallbackCredentialsProvider;
use Dflydev\Hawk\Credentials\CredentialsInterface;
use Dflydev\Hawk\Credentials\CredentialsProviderInterface;
use Dflydev\Hawk\Crypto\Artifacts;
use Dflydev\Hawk\Crypto\Crypto;
use Dflydev\Hawk\Header\Header;
use Dflydev\Hawk\Header\HeaderFactory;
use Dflydev\Hawk\Nonce\CallbackNonceValidator;
use Dflydev\Hawk\Nonce\NonceValidatorInterface;
use Dflydev\Hawk\Time\TimeProviderInterface;

class Server implements ServerInterface
{
    private $crypto;
    private $credentialsProvider;
    private $timeProvider;
    private $nonceValidator;
    private $timestampSkewSec;
    private $localtimeOffsetSec;

    public function __construct(
        Crypto $crypto,
        $credentialsProvider,
        TimeProviderInterface $timeProvider,
        $nonceValidator,
        $timestampSkewSec,
        $localtimeOffsetSec
    ) {
        if (!$credentialsProvider instanceof CredentialsProviderInterface) {
            if (is_callable($credentialsProvider)) {
                $credentialsProvider = new CallbackCredentialsProvider($credentialsProvider);
            } else {
                throw new \InvalidArgumentException(
                    "Credentials provider must implement CredentialsProviderInterface or must be callable"
                );
            }
        }

        if (!$nonceValidator instanceof NonceValidatorInterface) {
            if (is_callable($nonceValidator)) {
                $nonceValidator = new CallbackNonceValidator($nonceValidator);
            } else {
                throw new \InvalidArgumentException(
                    "Nonce validator must implement NonceValidatorInterface or must be callable"
                );
            }
        }

        $this->crypto = $crypto;
        $this->credentialsProvider = $credentialsProvider;
        $this->timeProvider = $timeProvider;
        $this->nonceValidator = $nonceValidator;
        $this->timestampSkewSec = $timestampSkewSec;
        $this->localtimeOffsetSec = $localtimeOffsetSec;
    }

    public function authenticate(
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

        // Measure now before any other processing
        $now = $this->timeProvider->createTimestamp() + $this->localtimeOffsetSec;

        $artifacts = new Artifacts(
            $method,
            $host,
            $port,
            $resource,
            $header->attribute('ts'),
            $header->attribute('nonce'),
            $header->attribute('ext'),
            $payload,
            $contentType,
            $header->attribute('hash'),
            $header->attribute('app'),
            $header->attribute('dlg')
        );

        foreach (array('id', 'ts', 'nonce', 'mac') as $requiredAttribute) {
            if (null === $header->attribute($requiredAttribute)) {
                throw new UnauthorizedException('Missing attributes');
            }
        }

        $credentials = $this->credentialsProvider->loadCredentialsById($header->attribute('id'));

        $calculatedMac = $this->crypto->calculateMac('header', $credentials, $artifacts);

        if (!$this->crypto->fixedTimeComparison($calculatedMac, $header->attribute('mac'))) {
            throw new UnauthorizedException('Bad MAC');
        }

        if (null !== $artifacts->payload()) {
            if (null === $artifacts->hash()) {
                // Should this ever happen? Difficult to get a this far if
                // hash is missing as the MAC will probably be wrong anyway.
                throw new UnauthorizedException('Missing required payload hash');
            }

            $calculatedHash = $this->crypto->calculatePayloadHash(
                $artifacts->payload(),
                $credentials->algorithm(),
                $artifacts->contentType()
            );

            if (!$this->crypto->fixedTimeComparison($calculatedHash, $artifacts->hash())) {
                throw new UnauthorizedException('Bad payload hash');
            }
        }

        if (!$this->nonceValidator->validateNonce($artifacts->nonce(), $artifacts->timestamp())) {
            throw new UnauthorizedException('Invalid nonce');
        }

        if (abs($header->attribute('ts') - $now) > $this->timestampSkewSec) {
            $ts = $this->timeProvider->createTimestamp() + $this->localtimeOffsetSec;
            $tsm = $this->crypto->calculateTsMac($ts, $credentials);

            throw new UnauthorizedException('Stale timestamp', array('ts' => $ts, 'tsm' => $tsm));
        }

        return new Response($credentials, $artifacts);
    }

    public function createHeader(CredentialsInterface $credentials, Artifacts $artifacts, array $options = array())
    {
        if (isset($options['payload']) || isset($options['content_type'])) {
            if (isset($options['payload']) && isset($options['content_type'])) {
                $payload = $options['payload'];
                $contentType = $options['content_type'];
                $hash = $this->crypto->calculatePayloadHash($payload, $credentials->algorithm(), $contentType);
            } else {
                throw new \InvalidArgumentException(
                    "If one of 'payload' and 'content_type' are specified, both must be specified."
                );
            }
        } else {
            $payload = null;
            $contentType = null;
            $hash = null;
        }

        $ext = isset($options['ext']) ? $options['ext'] : null;

        $responseArtifacts = new Artifacts(
            $artifacts->method(),
            $artifacts->host(),
            $artifacts->port(),
            $artifacts->resource(),
            $artifacts->timestamp(),
            $artifacts->nonce(),
            $ext,
            $payload,
            $contentType,
            $hash,
            $artifacts->app(),
            $artifacts->dlg()
        );

        $attributes = array(
            'mac' => $this->crypto->calculateMac('response', $credentials, $responseArtifacts),
        );

        if ($hash) {
            $attributes['hash'] = $hash;
        }

        if ($ext) {
            $attributes['ext'] = $ext;
        }

        return HeaderFactory::create('Server-Authorization', $attributes);
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

    public function authenticateBewit(
        $host,
        $port,
        $resource
    ) {
        // Measure now before any other processing
        $now = $this->timeProvider->createTimestamp() + $this->localtimeOffsetSec;

        if (!preg_match(
            '/^(\/.*)([\?&])bewit\=([^&$]*)(?:&(.+))?$/',
            $resource,
            $resourceParts
        )) {
            // TODO: Should this do something else?
            throw new UnauthorizedException('Malformed resource or does not contan bewit');
        }

        $bewit = base64_decode(str_replace(
            array('-', '_', '', ''),
            array('+', '/', '=', "\n"),
            $resourceParts[3]
        ));

        list ($id, $exp, $mac, $ext) = explode('\\', $bewit);

        if ($exp < $now) {
            throw new UnauthorizedException('Access expired');
        }

        $resource = $resourceParts[1];
        if (isset($resourceParts[4])) {
            $resource .= $resourceParts[2] . $resourceParts[4];
        }

        $artifacts = new Artifacts(
            'GET',
            $host,
            $port,
            $resource,
            $exp,
            '',
            $ext
        );

        $credentials = $this->credentialsProvider->loadCredentialsById($id);

        $calculatedMac = $this->crypto->calculateMac(
            'bewit',
            $credentials,
            $artifacts
        );

        if (!$this->crypto->fixedTimeComparison($calculatedMac, $mac)) {
            throw new UnauthorizedException('Bad MAC');
        }

        return new Response($credentials, $artifacts);
    }
}
