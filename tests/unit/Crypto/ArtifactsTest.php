<?php

namespace Dflydev\Hawk\unit\Crypto;

use Dflydev\Hawk\Crypto\Artifacts;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ArtifactsTest extends TestCase
{
    #[Test]
    public function shouldReturnCorrectValuesForAllFields(): void
    {
        $artifacts = new Artifacts(
            'testmethod',
            'testhost',
            'testport',
            'testresource',
            'testtimestamp',
            'testnonce',
            'testext',
            'testpayload',
            'testcontenttype',
            'testhash',
            'testapp',
            'testdlg'
        );

        $this->assertSame('testmethod', $artifacts->method());
        $this->assertSame('testhost', $artifacts->host());
        $this->assertSame('testport', $artifacts->port());
        $this->assertSame('testresource', $artifacts->resource());
        $this->assertSame('testtimestamp', $artifacts->timestamp());
        $this->assertSame('testnonce', $artifacts->nonce());
        $this->assertSame('testext', $artifacts->ext());
        $this->assertSame('testpayload', $artifacts->payload());
        $this->assertSame('testcontenttype', $artifacts->contentType());
        $this->assertSame('testhash', $artifacts->hash());
        $this->assertSame('testapp', $artifacts->app());
        $this->assertSame('testdlg', $artifacts->dlg());
    }
}
