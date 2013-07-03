<?php

namespace Dflydev\Hawk\Client;

use Dflydev\Hawk\Crypto\NonceProviderInterface;
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
    public function shouldReturnHeaderBuilder()
    {
        $credentials = $this->createMockCredentials();
        $crypto = $this->createMockCrypto();

        $client = new Client($crypto);

        $headerBuilder = $client->createHeaderBuilder($credentials, 'http://example.com', 'GET');

        $this->assertInstanceOf('Dflydev\Hawk\Client\HeaderBuilder', $headerBuilder);
    }

    /** @test */
    public function shouldBeAbleToBuildHeader()
    {
        $credentials = $this->createMockCredentials();
        $crypto = $this->createMockCrypto();

        $client = new Client($crypto);

        $header = $client
            ->createHeaderBuilder($credentials, 'http://example.com', 'GET')
            ->build();

        $this->assertInstanceOf('Dflydev\Hawk\Header\Header', $header);
    }

    /** @test */
    public function shouldCreateExpectedHeader()
    {
        $credentials = $this->createMockCredentials();
        $crypto = $this->createMockCrypto();

        $artifacts = new \Dflydev\Hawk\Crypto\Artifacts(
            'GET',
            'example.com',
            '80',
            '',
            12345,
            'mocked-nonce'
        );

        $crypto
            ->expects($this->once())
            ->method('calculateMac')
            ->with('header', $credentials, $artifacts)
            ->will($this->returnValue('ab12ccff'));

        $client = new Client($crypto);

        $header = $client
            ->createHeaderBuilder($credentials, 'http://example.com', 'GET')
            ->setNonceProvider(new MockNonceProvider('mocked-nonce'))
            ->setTimeProvider(new MockTimeProvider('12345'))
            ->build();

        // String based assertions
        $this->assertEquals("Authorization", $header->fieldName());
        $this->assertEquals(
            'Hawk id="1234", ts="12345", nonce="mocked-nonce", mac="ab12ccff"',
            $header->fieldValue()
        );

        // Attribute based assertions
        $this->assertEquals('1234', $header->attribute('id'));
        $this->assertEquals('12345', $header->attribute('ts'));
        $this->assertEquals('mocked-nonce', $header->attribute('nonce'));
        $this->assertEquals('ab12ccff', $header->attribute('mac'));
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
