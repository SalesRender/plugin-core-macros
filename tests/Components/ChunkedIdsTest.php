<?php

namespace Leadvertex\External\Export\Core\Components;


use PHPUnit\Framework\TestCase;

class ChunkedIdsTest extends TestCase
{

    /** @var array */
    private $ids;
    /** @var ChunkedIds */
    private $chunkedIds;

    public function setUp()
    {
        parent::setUp();

        $this->ids = [1,2];

        $this->chunkedIds = new ChunkedIds($this->ids);
    }

    public function testGetChunks()
    {
        $this->assertEquals($this->ids, $this->chunkedIds->getChunks());
    }
}
