<?php

namespace Dflydev\Hawk\Client;

use Dflydev\Hawk\Credentials\CredentialsInterface;
use Dflydev\Hawk\Header\FieldValueParserException;
use Dflydev\Hawk\Header\NotHawkAuthorizationException;

interface ClientInterface
{
    /**
     * @param array{
     *     timestamp?: int,
     *     nonce?: string,
     *     payload?: mixed,
     *     content_type?: string,
     *     ext?: string,
     *     app?: string,
     *     dlg?: string,
     * } $options
     */
    public function createRequest(
        CredentialsInterface $credentials,
        string $uri,
        string $method,
        array $options = []
    ): Request;

    /**
     * @param array{
     *     payload?: mixed,
     *     content_type?: string,
     * } $options
     * @throws FieldValueParserException
     * @throws NotHawkAuthorizationException
     */
    public function authenticate(
        CredentialsInterface $credentials,
        Request $request,
        mixed $headerObjectOrString,
        array $options = []
    ): bool;
}
