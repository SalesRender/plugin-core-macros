<?php

namespace Leadvertex\External\Export\Core\Components;


use Leadvertex\External\Export\Core\Formatter\Type;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class GenerateParamsTest extends TestCase
{
    /** @var MockObject */
    private $type;
    /** @var MockObject */
    private $storedConfig;
    /** @var MockObject */
    private $batchParams;
    /** @var MockObject */
    private $chunkedIds;
    /** @var GenerateParams */
    private $generateParams;

    public function setUp()
    {
        parent::setUp();

        $this->type = $this->createMock(Type::class);
        $this->storedConfig = $this->createMock(StoredConfig::class);
        $this->batchParams = $this->createMock(BatchParams::class);
        $this->chunkedIds = $this->createMock(ChunkedIds::class);

        $this->generateParams = new GenerateParams(
            $this->type,
            $this->storedConfig,
            $this->batchParams,
            $this->chunkedIds
        );
    }

    public function testGetType()
    {
        $this->assertEquals($this->type, $this->generateParams->getType());
    }

    public function testGetChunkedIds()
    {
        $this->assertEquals($this->storedConfig, $this->generateParams->getConfig());
    }

    public function testGetBatchParams()
    {
        $this->assertEquals($this->batchParams, $this->generateParams->getBatchParams());
    }

    public function testGetConfig()
    {
        $this->assertEquals($this->chunkedIds, $this->generateParams->getChunkedIds());
    }
}
