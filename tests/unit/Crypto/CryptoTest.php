<?php

namespace Dflydev\Hawk\unit\Crypto;

use Dflydev\Hawk\Credentials\Credentials;
use Dflydev\Hawk\Credentials\CredentialsInterface;
use Dflydev\Hawk\Crypto\Artifacts;
use Dflydev\Hawk\Crypto\Crypto;
use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class CryptoTest extends TestCase
{
    #[Test]
    #[DataProvider('payloadDataProvider')]
    public function shouldCalculatePayloadHash(
        $expectedHash,
        $payload,
        $algorithm,
        $contentType
    ) {
        $crypto = new Crypto();

        $calculatedHash = $crypto->calculatePayloadHash(
            $payload,
            $algorithm,
            $contentType
        );

        $this->assertEquals($expectedHash, $calculatedHash);
    }

    public static function payloadDataProvider(): Generator
    {
        yield [
            'neQFHgYKl/jFqDINrC21uLS0gkFglTz789rzcSr7HYU=',
            '{"type":"https://tent.io/types/status/v0#"}',
            'sha256',
            'application/vnd.tent.post.v0+json'
        ];
    }

    #[Test]
    #[DataProvider('macDataProvider')]
    public function shouldCalculateMac(
        $expectedMac,
        $type,
        CredentialsInterface $credentials,
        Artifacts $artifacts
    ) {
        $crypto = new Crypto();

        $calculatedMac = $crypto->calculateMac($type, $credentials, $artifacts);

        $this->assertEquals($expectedMac, $calculatedMac);
    }

    public static function macDataProvider(): Generator
    {
        $tentTestVectorsCredentials = new Credentials(
            'HX9QcbD-r3ItFEnRcAuOSg',
            'sha256',
            'exqbZWtykFZIh2D7cXi9dA'
        );

        $tentTestVectorsAttributes = [
            'method' => 'POST',
            'host' => 'example.com',
            'port' => 443,
            'resource' => '/posts',
            'timestamp' => 1368996800,
            'nonce' => '3yuYCD4Z',
            'payload' => '{"type":"https://tent.io/types/status/v0#"}',
            'content_type' => 'application/vnd.tent.post.v0+json',
            'hash' => 'neQFHgYKl/jFqDINrC21uLS0gkFglTz789rzcSr7HYU='
        ];
        yield [
            //
            // App request w/hash
            //
            '2sttHCQJG9ejj1x7eCi35FP23Miu9VtlaUgwk68DTpM=',
            'header',
            $tentTestVectorsCredentials,
            new Artifacts(
                $tentTestVectorsAttributes['method'],
                $tentTestVectorsAttributes['host'],
                $tentTestVectorsAttributes['port'],
                $tentTestVectorsAttributes['resource'],
                $tentTestVectorsAttributes['timestamp'],
                $tentTestVectorsAttributes['nonce'],
                null,
                $tentTestVectorsAttributes['payload'],
                $tentTestVectorsAttributes['content_type'],
                $tentTestVectorsAttributes['hash'],
                'wn6yzHGe5TLaT-fvOPbAyQ'
            ),
        ];
        yield [
            //
            // Server Response (App request w/hash)
            //
            'lTG3kTBr33Y97Q4KQSSamu9WY/mOUKnZzq/ho9x+yxw=',
            'response',
            $tentTestVectorsCredentials,
            new Artifacts(
                $tentTestVectorsAttributes['method'],
                $tentTestVectorsAttributes['host'],
                $tentTestVectorsAttributes['port'],
                $tentTestVectorsAttributes['resource'],
                $tentTestVectorsAttributes['timestamp'],
                $tentTestVectorsAttributes['nonce'],
                null,
                null,
                null,
                null,
                'wn6yzHGe5TLaT-fvOPbAyQ'
            ),
        ];
        yield [
            //
            // Relationship Request
            //
            'OO2ldBDSw8KmNHlEdTC4BciIl8+uiuCRvCnJ9KkcR3Y=',
            'header',
            $tentTestVectorsCredentials,
            new Artifacts(
                $tentTestVectorsAttributes['method'],
                $tentTestVectorsAttributes['host'],
                $tentTestVectorsAttributes['port'],
                $tentTestVectorsAttributes['resource'],
                $tentTestVectorsAttributes['timestamp'],
                $tentTestVectorsAttributes['nonce']
            ),
        ];
        yield [
            //
            // Server Response w/ hash (Relationship Request)
            //
            'LvxASIZ2gop5cwE2mNervvz6WXkPmVslwm11MDgEZ5E=',
            'response',
            $tentTestVectorsCredentials,
            new Artifacts(
                $tentTestVectorsAttributes['method'],
                $tentTestVectorsAttributes['host'],
                $tentTestVectorsAttributes['port'],
                $tentTestVectorsAttributes['resource'],
                $tentTestVectorsAttributes['timestamp'],
                $tentTestVectorsAttributes['nonce'],
                null,
                $tentTestVectorsAttributes['payload'],
                $tentTestVectorsAttributes['content_type'],
                $tentTestVectorsAttributes['hash']
            ),
        ];
        yield [
            //
            // Bewit (GET /posts)
            //
            'O0mhprgoXqF48Dlw5FWAWvVQIpgGYsqsX76tpo6KyqI=',
            'bewit',
            $tentTestVectorsCredentials,
            new Artifacts(
                'GET',
                $tentTestVectorsAttributes['host'],
                $tentTestVectorsAttributes['port'],
                $tentTestVectorsAttributes['resource'],
                $tentTestVectorsAttributes['timestamp'],
                ''
            ),
        ];
    }

    #[Test]
    #[DataProvider('tsMacDataProvider')]
    public function shouldCalculateTsMac(
        $expectedTsMac,
        $ts,
        CredentialsInterface $credentials
    ) {
        $crypto = new Crypto();

        $calculatedTsMac = $crypto->calculateTsMac($ts, $credentials);

        $this->assertEquals($expectedTsMac, $calculatedTsMac);
    }

    public static function tsMacDataProvider(): Generator
    {
        $tentTestVectorsCredentials = new Credentials(
            'HX9QcbD-r3ItFEnRcAuOSg',
            'sha256',
            'exqbZWtykFZIh2D7cXi9dA'
        );

        $tentTestVectorsAttributes = [
            'method' => 'POST',
            'host' => 'example.com',
            'port' => 443,
            'resource' => '/posts',
            'timestamp' => 1368996800,
            'nonce' => '3yuYCD4Z',
            'payload' => '{"type":"https://tent.io/types/status/v0#"}',
            'content_type' => 'application/vnd.tent.post.v0+json',
            'hash' => 'neQFHgYKl/jFqDINrC21uLS0gkFglTz789rzcSr7HYU='
        ];

        yield [
            'HPDcD5S3Kw7LM/oyoXKcgv2Z30RnOLAI5ebXpYDGfo4=',
            $tentTestVectorsAttributes['timestamp'],
            $tentTestVectorsCredentials
        ];
    }
}
