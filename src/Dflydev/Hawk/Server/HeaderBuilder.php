<?php

namespace Dflydev\Hawk\Server;

use Dflydev\Hawk\Credentials\CredentialsInterface;
use Dflydev\Hawk\Crypto\Artifacts;
use Dflydev\Hawk\Crypto\Crypto;
use Dflydev\Hawk\Header\HeaderFactory;

class HeaderBuilder
{
    private $credentials;
    private $crypto;
    private $ext;
    private $payload;
    private $hash;
    private $contentType;

    public function __construct(Crypto $crypto, CredentialsInterface $credentials, Artifacts $artifacts)
    {
        $this->crypto = $crypto;
        $this->credentials = $credentials;
        $this->artifacts = $artifacts;
    }

    public function setExt($ext = null)
    {
        $this->ext = $ext;

        return $this;
    }

    public function setPayload($payload, $contentType)
    {
        $this->payload = $payload;
        $this->contentType = $contentType;
        $this->hash = $this->crypto->calculatePayloadHash(
            $payload,
            $this->credentials->algorithm(),
            $contentType
        );

        return $this;
    }

    public function build()
    {
        $artifacts = new Artifacts(
            $this->artifacts->method(),
            $this->artifacts->host(),
            $this->artifacts->port(),
            $this->artifacts->resource(),
            $this->artifacts->timestamp(),
            $this->artifacts->nonce(),
            $this->ext,
            $this->payload,
            $this->contentType,
            $this->hash,
            $this->artifacts->app(),
            $this->artifacts->dlg()
        );

        $attributes = array(
            'mac' => $this->crypto->calculateMac('response', $this->credentials, $artifacts),
        );

        if ($this->hash) {
            $attributes['hash'] = $this->hash;
        }

        if ($this->ext) {
            $attributes['ext'] = $this->ext;
        }

        return HeaderFactory::create('Server-Authorization', $attributes);
    }
}
