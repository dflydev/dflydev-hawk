<?php

namespace Dflydev\Hawk\Server;

use Dflydev\Hawk\Credentials\CredentialsInterface;
use Dflydev\Hawk\Crypto\Artifacts;
use Dflydev\Hawk\Crypto\Crypto;
use Dflydev\Hawk\Header\Header;
use Dflydev\Hawk\Header\HeaderFactory;
use Dflydev\Hawk\Time\TimeProviderInterface;

class Server implements ServerInterface
{
    private $crypto;
    private $credentialsCallback;
    private $timeProvider;
    private $nonceCallback;
    private $timestampSkewSec;
    private $localtimeOffsetSec;

    public function __construct(
        Crypto $crypto,
        $credentialsCallback,
        TimeProviderInterface $timeProvider,
        $nonceCallback,
        $timestampSkewSec,
        $localtimeOffsetSec
    ) {
        $this->crypto = $crypto;
        $this->credentialsCallback = $credentialsCallback;
        $this->timeProvider = $timeProvider;
        $this->nonceCallback = $nonceCallback;
        $this->timestampSkewSec = $timestampSkewSec;
        $this->localtimeOffsetSec = $localtimeOffsetSec;
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

    public function authenticate(Request $request)
    {
        // Measure now before any other processing
        $now = $this->timeProvider->createTimestamp() + $this->localtimeOffsetSec;

        $artifacts = new Artifacts(
            $request->method(),
            $request->host(),
            $request->port(),
            $request->resource(),
            $request->header()->attribute('ts'),
            $request->header()->attribute('nonce'),
            $request->header()->attribute('ext'),
            $request->payload(),
            $request->contentType(),
            $request->header()->attribute('hash'),
            $request->header()->attribute('app'),
            $request->header()->attribute('dlg')
        );

        $credentials = call_user_func_array(
            $this->credentialsCallback,
            array($request->header()->attribute('id'))
        );

        $calculatedMac = $this->crypto->calculateMac('header', $credentials, $artifacts);

        if (!$this->crypto->fixedTimeComparison($calculatedMac, $request->header()->attribute('mac'))) {
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

        if (!call_user_func_array(
            $this->nonceCallback,
            array(
                $artifacts->nonce(),
                $artifacts->timestamp(),
            )
        )) {
            throw new UnauthorizedException('Invalid nonce');
        }

        if (abs($request->header()->attribute('ts') - $now) > $this->timestampSkewSec) {
            $ts = $this->timeProvider->createTimestamp() + $this->localtimeOffsetSec;
            $tsm = $this->crypto->calculateTsMac($ts, $credentials);

            throw new UnauthorizedException('Stale timestamp', array('ts' => $ts, 'tsm' => $tsm));
        }

        return array($credentials, $artifacts);
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
        $app = isset($options['app']) ? $options['app'] : null;
        $dlg = isset($options['dlg']) ? $options['dlg'] : null;

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
            $app,
            $dlg
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
}
