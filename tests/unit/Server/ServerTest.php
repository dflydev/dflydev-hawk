<?php

namespace Dflydev\Hawk\unit\Server;

use Dflydev\Hawk\Credentials\Credentials;
use Dflydev\Hawk\Server\ServerBuilder;
use Dflydev\Hawk\Time\ConstantTimeProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ServerTest extends TestCase
{
    #[Test]
    public function shouldAuthenticateBewit()
    {
        $credentialsProvider = fn($id) => new Credentials(
            'HX9QcbD-r3ItFEnRcAuOSg',
            'sha256',
            'exqbZWtykFZIh2D7cXi9dA'
        );

        $server = ServerBuilder::create($credentialsProvider)
            ->setTimeProvider(new ConstantTimeProvider(1368996800))
            ->build();

        $response = $server->authenticateBewit(
            'example.com',
            443,
            '/posts?bewit=ZXhxYlpXdHlrRlpJaDJEN2NYaTlkQVwxMzY4OTk2' .
            'ODAwXE8wbWhwcmdvWHFGNDhEbHc1RldBV3ZWUUlwZ0dZc3FzWDc2dHBvNkt5cUk9XA'
        );

        $this->assertSame('/posts', $response->artifacts()->resource());
    }
}
