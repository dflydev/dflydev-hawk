<?php

namespace Dflydev\Hawk\Client;

use Dflydev\Hawk\Nonce\NonceProviderInterface;
use Dflydev\Hawk\Time\TimeProviderInterface;

class ClientTest extends \PHPUnit_Framework_TestCase
{
    public function createMockCredentials($id = 1234, $key = 'asdf!!!!', $algorithm = 'sha256')
    {
        $credentials = $this->getMockBuilder('Dflydev\Hawk\Credentials\CredentialsInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $credentials
            ->expects($this->any())
            ->method('id')
            ->will($this->returnValue(1234));

        $credentials
            ->expects($this->any())
            ->method('key')
            ->will($this->returnValue('asdf!!!'));

        $credentials
            ->expects($this->any())
            ->method('algorithm')
            ->will($this->returnValue('sha256'));

        return $credentials;
    }

    public function createMockCrypto()
    {
        $crypto = $this->getMock('Dflydev\Hawk\Crypto\Crypto');

        return $crypto;
    }

    /** @test */
    public function builderShouldBuildClient()
    {
        $client = ClientBuilder::create()->build();
    }

    /** @test */
    public function shouldCreateRequest()
    {
        $crypto = $this->createMockCrypto();
        $credentials = $this->createMockCredentials();

        $crypto
            ->expects($this->once())
            ->method('calculateMac')
            ->will($this->returnValue('macasdf'));

        $mockTimeProvider = new MockTimeProvider(123456789);
        $mockNonceProvider = new MockNonceProvider('asdf1234');

        $client = ClientBuilder::create()
            ->setCrypto($crypto)
            ->setTimeProvider($mockTimeProvider)
            ->setNonceProvider($mockNonceProvider)
            ->build();

        $request = $client->createRequest($credentials, 'http://example.com/sample.json', 'GET');
        $this->assertEquals(1234, $request->header()->attribute('id'));
        $this->assertEquals(123456789, $request->header()->attribute('ts'));
        $this->assertEquals('asdf1234', $request->header()->attribute('nonce'));
        $this->assertEquals('macasdf', $request->header()->attribute('mac'));
    }

    /** @test */
    public function shouldOffsetTs()
    {
        $credentials = $this->createMockCredentials();

        $mockTimeProvider = new MockTimeProvider(123456789);

        $client = ClientBuilder::create()
            ->setTimeProvider($mockTimeProvider)
            ->setLocaltimeOffset(100)
            ->build();

        $request = $client->createRequest($credentials, 'http://example.com/sample.json', 'GET');
        $this->assertEquals(123456789 + 100, $request->header()->attribute('ts'));
    }
}

class MockNonceProvider implements NonceProviderInterface
{
    private $nonce;

    public function __construct($nonce)
    {
        $this->nonce = $nonce;
    }

    public function createNonce()
    {
        return $this->nonce;
    }
}

class MockTimeProvider implements TimeProviderInterface
{
    private $timestamp;

    public function __construct($timestamp)
    {
        $this->timestamp = $timestamp;
    }

    public function createTimestamp()
    {
        return $this->timestamp;
    }
}
