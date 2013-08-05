<?php

namespace Dflydev\Hawk\Crypto;

use Dflydev\Hawk\Credentials\Credentials;
use Dflydev\Hawk\Credentials\CredentialsInterface;

class CryptoTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @dataProvider payloadDataProvider
     */
    public function shouldCalculatePayloadHash(
        $expectedHash,
        $payload,
        $algorithm,
        $contentType
    ) {
        $crypto = new Crypto;

        $calculatedHash = $crypto->calculatePayloadHash(
            $payload,
            $algorithm,
            $contentType
        );

        $this->assertEquals($expectedHash, $calculatedHash);
    }

    public function payloadDataProvider()
    {
        return array(
            array(
                'neQFHgYKl/jFqDINrC21uLS0gkFglTz789rzcSr7HYU=',
                '{"type":"https://tent.io/types/status/v0#"}',
                'sha256',
                'application/vnd.tent.post.v0+json'
            )
        );
    }

    /**
     * @test
     * @dataProvider macDataProvider
     */
    public function shouldCalculateMac(
        $expectedMac,
        $type,
        CredentialsInterface $credentials,
        Artifacts $artifacts
    ) {
        $crypto = new Crypto;

        $calculatedMac = $crypto->calculateMac($type, $credentials, $artifacts);

        $this->assertEquals($expectedMac, $calculatedMac);
    }

    public function macDataProvider()
    {
        $tentTestVectorsCredentials = new Credentials(
            'HX9QcbD-r3ItFEnRcAuOSg',
            'sha256',
            'exqbZWtykFZIh2D7cXi9dA'
        );

        $tentTestVectorsAttributes = array(
            'method' => 'POST',
            'host' => 'example.com',
            'port' => 443,
            'resource' => '/posts',
            'timestamp' => 1368996800,
            'nonce' => '3yuYCD4Z',
            'payload' => '{"type":"https://tent.io/types/status/v0#"}',
            'content_type' => 'application/vnd.tent.post.v0+json',
            'hash' => 'neQFHgYKl/jFqDINrC21uLS0gkFglTz789rzcSr7HYU=',
        );

        return array(
            array(

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
            ),
            array(

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
            ),
            array(

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
            ),
            array(

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
            ),
            array(

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
            ),
        );
    }

    /**
     * @test
     * @dataProvider tsMacDataProvider
     */
    public function shouldCalculateTsMac(
        $expectedTsMac,
        $ts,
        CredentialsInterface $credentials
    ) {
        $crypto = new Crypto;

        $calculatedTsMac = $crypto->calculateTsMac($ts, $credentials);

        $this->assertEquals($expectedTsMac, $calculatedTsMac);
    }

    public function tsMacDataProvider()
    {
        $tentTestVectorsCredentials = new Credentials(
            'HX9QcbD-r3ItFEnRcAuOSg',
            'sha256',
            'exqbZWtykFZIh2D7cXi9dA'
        );

        $tentTestVectorsAttributes = array(
            'method' => 'POST',
            'host' => 'example.com',
            'port' => 443,
            'resource' => '/posts',
            'timestamp' => 1368996800,
            'nonce' => '3yuYCD4Z',
            'payload' => '{"type":"https://tent.io/types/status/v0#"}',
            'content_type' => 'application/vnd.tent.post.v0+json',
            'hash' => 'neQFHgYKl/jFqDINrC21uLS0gkFglTz789rzcSr7HYU=',
        );

        return array(
            array(
                'HPDcD5S3Kw7LM/oyoXKcgv2Z30RnOLAI5ebXpYDGfo4=',

                $tentTestVectorsAttributes['timestamp'],
                $tentTestVectorsCredentials,
            ),
        );
    }
}
