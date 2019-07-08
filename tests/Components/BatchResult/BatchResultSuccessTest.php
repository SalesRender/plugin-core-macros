<?php

namespace Leadvertex\Plugin\Export\Core\Components\BatchResult;


use PHPUnit\Framework\TestCase;

class BatchResultSuccessTest extends TestCase
{
    /** @var string */
    private $compiledUrl;
    /** @var BatchResultSuccess */
    private $batchResult;

    public function setUp()
    {
        parent::setUp();

        $this->compiledUrl = 'https://example.ru';

        $this->batchResult = new BatchResultSuccess($this->compiledUrl);
    }

    public function testGet()
    {
        $expected = ['compiledUrl' => $this->compiledUrl];

        $this->assertEquals($expected, $this->batchResult->get());
    }
}
