<?php

namespace Dflydev\Hawk\unit\Client;

use Dflydev\Hawk\Client\ClientBuilder;
use Dflydev\Hawk\Credentials\Credentials;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ClientTest extends TestCase
{
    #[Test]
    public function shouldCreateBewit(): void
    {
        $client = ClientBuilder::create()->build();

        $tentTestVectorsCredentials = new Credentials(
            'HX9QcbD-r3ItFEnRcAuOSg',
            'sha256',
            'exqbZWtykFZIh2D7cXi9dA'
        );

        $this->assertSame(
            'ZXhxYlpXdHlrRlpJaDJEN2NYaTlkQVwxMzY4OTk2ODAwXE8wbWhwcmdv' .
            'WHFGNDhEbHc1RldBV3ZWUUlwZ0dZc3FzWDc2dHBvNkt5cUk9XA',
            $client->createBewit(
                $tentTestVectorsCredentials,
                'https://example.com/posts',
                0,
                ['timestamp' => 1368996800]
            )
        );
    }
}
