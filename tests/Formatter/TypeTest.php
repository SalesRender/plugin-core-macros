<?php

namespace Leadvertex\External\Export\Core\Formatter;


use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class TypeTest extends TestCase
{

    /** @var Type */
    private $type;
    /** @var string */
    private $typeName;

    public function setUp()
    {
        parent::setUp();

        $this->typeName = Type::ORDERS;

        $this->type = new Type($this->typeName);
    }

    public function testInvalidCreateType()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->type = new Type('invalidName');
    }

    public function testIsEquals()
    {
        $this->assertTrue($this->type->isEquals($this->type));
    }

    public function testGet()
    {
        $this->assertEquals($this->typeName, $this->type->get());
    }
}
