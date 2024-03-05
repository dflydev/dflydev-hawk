<?php

namespace Dflydev\Hawk\Server;

use Dflydev\Hawk\Credentials\CredentialsInterface;
use Dflydev\Hawk\Credentials\CredentialsProviderInterface;
use Dflydev\Hawk\Crypto\Artifacts;
use Dflydev\Hawk\Crypto\Crypto;
use Dflydev\Hawk\Header\Header;
use Dflydev\Hawk\Header\HeaderFactory;
use Dflydev\Hawk\Nonce\NonceValidatorInterface;
use Dflydev\Hawk\Time\TimeProviderInterface;
use InvalidArgumentException;

/**
 * @see \Dflydev\Hawk\Server\ServerTest
 */
class Server implements ServerInterface
{
    public function __construct(
        private readonly Crypto $crypto,
        private readonly CredentialsProviderInterface $credentialsProvider,
        private readonly TimeProviderInterface $timeProvider,
        private readonly NonceValidatorInterface $nonceValidator,
        private int $timestampSkewSec,
        private int $localtimeOffsetSec
    ) {
    }

    public function authenticate(
        string $method,
        string $host,
        mixed $port,
        mixed $resource,
        string $contentType = null,
        mixed $payload = null,
        mixed $headerObjectOrString = null
    ): Response {
        if (null === $headerObjectOrString) {
            throw new UnauthorizedException("Missing Authorization header");
        }

        $header = HeaderFactory::createFromHeaderObjectOrString(
            'Authorization',
            $headerObjectOrString,
            function (): never {
                throw new UnauthorizedException("Invalid Authorization header");
            }
        );

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

        foreach (['id', 'ts', 'nonce', 'mac'] as $requiredAttribute) {
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

            throw new UnauthorizedException('Stale timestamp', ['ts' => $ts, 'tsm' => $tsm]);
        }

        return new Response($credentials, $artifacts);
    }

    public function createHeader(CredentialsInterface $credentials, Artifacts $artifacts, array $options = []): Header
    {
        if (isset($options['payload']) || isset($options['content_type'])) {
            if (isset($options['payload']) && isset($options['content_type'])) {
                $payload = $options['payload'];
                $contentType = $options['content_type'];
                $hash = $this->crypto->calculatePayloadHash($payload, $credentials->algorithm(), $contentType);
            } else {
                throw new InvalidArgumentException(
                    "If one of 'payload' and 'content_type' are specified, both must be specified."
                );
            }
        } else {
            $payload = null;
            $contentType = null;
            $hash = null;
        }

        $ext = $options['ext'] ?? null;

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

        $attributes = ['mac' => $this->crypto->calculateMac('response', $credentials, $responseArtifacts)];

        if ($hash !== null) {
            $attributes['hash'] = $hash;
        }

        if ($ext) {
            $attributes['ext'] = $ext;
        }

        return HeaderFactory::create('Server-Authorization', $attributes);
    }

    public function authenticatePayload(
        CredentialsInterface $credentials,
        mixed $payload,
        string $contentType,
        string $hash
    ): bool {
        $calculatedHash = $this->crypto->calculatePayloadHash($payload, $credentials->algorithm(), $contentType);

        return $this->crypto->fixedTimeComparison($calculatedHash, $hash);
    }

    public function authenticateBewit(
        string $host,
        int $port,
        mixed $resource
    ): Response {
        // Measure now before any other processing
        $now = $this->timeProvider->createTimestamp() + $this->localtimeOffsetSec;

        if (
            !preg_match(
                '/^(\/.*)([?&])bewit=([^&$]*)(?:&(.+))?$/',
                (string) $resource,
                $resourceParts
            )
        ) {
            // TODO: Should this do something else?
            throw new UnauthorizedException('Malformed resource or does not contan bewit');
        }

        $bewit = base64_decode(str_replace(
            ['-', '_', '', ''],
            ['+', '/', '=', "\n"],
            $resourceParts[3]
        ));

        [$id, $exp, $mac, $ext] = explode('\\', $bewit);

        if ((int)$exp < $now) {
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
            (int)$exp,
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
