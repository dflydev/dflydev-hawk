<?php

namespace Dflydev\Hawk\Server;

use Dflydev\Hawk\Crypto\Artifacts;
use Dflydev\Hawk\Crypto\Crypto;
use Dflydev\Hawk\Time\TimeProviderInterface;

class Authenticator
{
    private $crypto;
    private $timeProvider;
    private $credentialsCallback;
    private $nonceCallback;
    private $timestampSkewSec;

    public function __construct(
        Crypto $crypto,
        TimeProviderInterface $timeProvider,
        $credentialsCallback,
        $nonceCallback,
        $timestampSkewSec,
        $localtimeOffsetMsec
    ) {
        $this->crypto = $crypto;
        $this->timeProvider = $timeProvider;
        $this->credentialsCallback = $credentialsCallback;
        $this->nonceCallback = $nonceCallback;
        $this->timestampSkewSec = $timestampSkewSec;
        $this->localtimeOffsetMsec = $localtimeOffsetMsec;
    }

    public function authenticate(Request $request)
    {
        // Measure now before any other processing
        $now = $this->timeProvider->createTimestamp() + $this->localtimeOffsetMsec;

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
            $ts = $this->timeProvider->createTimestamp() + $this->localtimeOffsetMsec;
            $tsm = $this->crypto->calculateTsMac($ts, $credentials);

            throw new UnauthorizedException('Stale timestamp', array('ts' => $ts, 'tsm' => $tsm));
        }

        return array($credentials, $artifacts);
    }
}
