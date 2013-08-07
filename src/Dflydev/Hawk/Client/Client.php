<?php

namespace Dflydev\Hawk\Client;

use Dflydev\Hawk\Credentials\CredentialsInterface;
use Dflydev\Hawk\Crypto\Artifacts;
use Dflydev\Hawk\Crypto\Crypto;
use Dflydev\Hawk\Header\Header;
use Dflydev\Hawk\Header\HeaderFactory;
use Dflydev\Hawk\Nonce\NonceProviderInterface;
use Dflydev\Hawk\Time\TimeProviderInterface;

class Client implements ClientInterface
{
    private $crypto;

    public function __construct(
        Crypto $crypto,
        TimeProviderInterface $timeProvider,
        NonceProviderInterface $nonceProvider,
        $localtimeOffset
    ) {
        $this->crypto = $crypto;
        $this->timeProvider = $timeProvider;
        $this->nonceProvider = $nonceProvider;
        $this->localtimeOffset = $localtimeOffset;
    }

    public function createRequest(CredentialsInterface $credentials, $uri, $method, array $options = array())
    {
        $timestamp = isset($options['timestamp']) ? $options['timestamp'] : $this->timeProvider->createTimestamp();
        if ($this->localtimeOffset) {
            $timestamp += $this->localtimeOffset;
        }

        $parsed = parse_url($uri);
        $host = $parsed['host'];
        $resource = isset($parsed['path']) ? $parsed['path'] : '';

        if (isset($parsed['query'])) {
            $resource .= '?'.$parsed['query'];
        }

        $port = isset($parsed['port']) ? $parsed['port'] : ($parsed['scheme'] === 'https' ? 443 : 80);

        $nonce = isset($options['nonce']) ? $options['nonce'] : $this->nonceProvider->createNonce();

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

        $artifacts = new Artifacts(
            $method,
            $host,
            $port,
            $resource,
            $timestamp,
            $nonce,
            $ext,
            $payload,
            $contentType,
            $hash,
            $app,
            $dlg
        );

        $attributes = array(
            'id' => $credentials->id(),
            'ts' => $artifacts->timestamp(),
            'nonce' => $artifacts->nonce(),
        );

        if (null !== $hash) {
            $attributes['hash'] = $hash;
        }

        if (null !== $ext) {
            $attributes['ext'] = $ext;
        }

        $attributes['mac'] = $this->crypto->calculateMac('header', $credentials, $artifacts);

        if (null !== $app) {
            $attributes['app'] = $app;
        }

        if (null !== $dlg) {
            $attributes['dlg'] = $dlg;
        }

        return new Request(HeaderFactory::create('Authorization', $attributes), $artifacts);
    }

    public function authenticate(
        CredentialsInterface $credentials,
        Request $request,
        $headerObjectOrString,
        array $options = array()
    ) {
        if (is_string($headerObjectOrString)) {
            $header = HeaderFactory::createFromString('Server-Authorization', $headerObjectOrString);
        } elseif ($headerObjectOrString instanceof Header) {
            $header = $headerObjectOrString;
        } else {
            throw new \InvalidArgumentException(
                "Header must either be a string or an instance of 'Dflydev\Hawk\Header\Header'"
            );
        }

        if (isset($options['payload']) || isset($options['content_type'])) {
            if (isset($options['payload']) && isset($options['content_type'])) {
                $payload = $options['payload'];
                $contentType = $options['content_type'];
            } else {
                throw new \InvalidArgumentException(
                    "If one of 'payload' and 'content_type' are specified, both must be specified."
                );
            }
        } else {
            $payload = null;
            $contentType = null;
        }

        if ($ts = $header->attribute('ts')) {
            // do something with ts
        }

        $artifacts = new Artifacts(
            $request->artifacts()->method(),
            $request->artifacts()->host(),
            $request->artifacts()->port(),
            $request->artifacts()->resource(),
            $request->artifacts()->timestamp(),
            $request->artifacts()->nonce(),
            $header->attribute('ext'),
            $payload,
            $contentType,
            $header->attribute('hash'),
            $request->artifacts()->app(),
            $request->artifacts()->dlg()
        );

        $mac = $this->crypto->calculateMac('response', $credentials, $artifacts);
        if ($header->attribute('mac') !== $mac) {
            return false;
        }

        if (!$payload) {
            return true;
        }

        if (!$artifacts->hash()) {
            return false;
        }

        $hash = $this->crypto->calculatePayloadHash($payload, $credentials->algorithm(), $contentType);
        return $artifacts->hash() === $hash;
    }

    public function createBewit(CredentialsInterface $credentials, $uri, $ttlSec, array $options = array())
    {
        $timestamp = isset($options['timestamp']) ? $options['timestamp'] : $this->timeProvider->createTimestamp();
        if ($this->localtimeOffset) {
            $timestamp += $this->localtimeOffset;
        }

        $parsed = parse_url($uri);
        $host = $parsed['host'];
        $resource = isset($parsed['path']) ? $parsed['path'] : '';

        if (isset($parsed['query'])) {
            $resource .= '?'.$parsed['query'];
        }

        $port = isset($parsed['port']) ? $parsed['port'] : ($parsed['scheme'] === 'https' ? 443 : 80);

        $ext = isset($options['ext']) ? $options['ext'] : null;

        $exp = $timestamp + $ttlSec;

        $artifacts = new Artifacts(
            'GET',
            $host,
            $port,
            $resource,
            $exp,
            '',
            $ext
        );

        $bewit = implode('\\', array(
            $credentials->id(),
            $exp,
            $this->crypto->calculateMac('bewit', $credentials, $artifacts),
            $ext,
        ));

        return str_replace(
            array('+', '/', '=', "\n"),
            array('-', '_', '', ''),
            base64_encode($bewit)
        );
    }
}
