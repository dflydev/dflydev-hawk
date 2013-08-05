<?php

namespace Dflydev\Hawk\Crypto;

class ArtifactsTest extends \PHPUnit_Framework_TestCase
{
    /** @test */
    public function shouldReturnCorrectValuesForAllFields()
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

        $this->assertEquals('testmethod', $artifacts->method());
        $this->assertEquals('testhost', $artifacts->host());
        $this->assertEquals('testport', $artifacts->port());
        $this->assertEquals('testresource', $artifacts->resource());
        $this->assertEquals('testtimestamp', $artifacts->timestamp());
        $this->assertEquals('testnonce', $artifacts->nonce());
        $this->assertEquals('testext', $artifacts->ext());
        $this->assertEquals('testpayload', $artifacts->payload());
        $this->assertEquals('testcontenttype', $artifacts->contentType());
        $this->assertEquals('testhash', $artifacts->hash());
        $this->assertEquals('testapp', $artifacts->app());
        $this->assertEquals('testdlg', $artifacts->dlg());
    }
}
