<?php

namespace Leadvertex\Plugin\Export\Core\Components\BatchResult;


use PHPUnit\Framework\TestCase;

class BatchResultFailedTest extends TestCase
{
    /** @var string */
    private $failedCause;
    /** @var BatchResultSuccess */
    private $batchResult;

    public function setUp()
    {
        parent::setUp();

        $this->failedCause = 'TestCause';

        $this->batchResult = new BatchResultFailed($this->failedCause);
    }

    public function testGet()
    {
        $expected = ['failedCause' => $this->failedCause];

        $this->assertEquals($expected, $this->batchResult->get());
    }
}
