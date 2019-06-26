<?php

namespace Leadvertex\External\Export\Core\Formatter;


use Leadvertex\External\Export\Core\Components\Developer;
use Leadvertex\External\Export\Core\FieldDefinitions\IntegerDefinition;
use Leadvertex\External\Export\Core\FieldDefinitions\StringDefinition;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use TypeError;

class SchemeTest extends TestCase
{
    /** @var MockObject */
    private $developer;
    /** @var MockObject */
    private $type;
    /** @var array */
    private $names;
    /** @var array */
    private $description;
    /** @var array */
    private $fieldDefinitions;
    /** @var Scheme */
    private $scheme;

    public function setUp()
    {
        parent::setUp();

        $this->developer = $this->createMock(Developer::class);
        $this->type = $this->createMock(Type::class);
        $this->names = ['Bob','Martin','Lesly'];
        $this->description = ['textDescription'];
        $this->fieldDefinitions = [
            'name1' => new IntegerDefinition([],[],'defaultValue',true),
            'name2' => new StringDefinition([],[],'defaultValue',true),
        ];

        $this->scheme = new Scheme(
            $this->developer,
            $this->type,
            $this->names,
            $this->description,
            $this->fieldDefinitions
        );

    }

    public function testCreateWithInvalidFieldDefinition()
    {
        $this->expectException(TypeError::class);
        $fieldDefinitions = [5, 10];

        $this->scheme = new Scheme(
            $this->developer,
            $this->type,
            $this->names,
            $this->description,
            $fieldDefinitions
        );
    }

    public function testGetType()
    {
        $this->assertEquals($this->type, $this->scheme->getType());
    }

    public function testGetFields()
    {
        $this->assertEquals($this->fieldDefinitions, $this->scheme->getFields());
    }

    public function testGetDeveloper()
    {
        $this->assertEquals($this->developer, $this->scheme->getDeveloper());
    }

    public function testGetDescription()
    {
        $this->assertEquals($this->description, $this->scheme->getDescriptions());
    }

    public function testToArray()
    {
        $expected = [
            'developer' => $this->developer->toArray(),
            'type' => $this->type->get(),
            'name' => $this->names,
            'description' => $this->description,
            'fields' => [],
        ];
        foreach ($this->fieldDefinitions as $fieldName => $fieldDefinition) {
            $expected['fields'][$fieldName] = $fieldDefinition->toArray();
        }

        $this->assertEquals($expected, $this->scheme->toArray());
    }

    public function testGetField()
    {
        foreach ($this->fieldDefinitions as $key => $fieldDefinition) {
            $this->assertEquals($fieldDefinition, $this->scheme->getField($key));
        }
    }

    public function testGetName()
    {
        $this->assertEquals($this->names, $this->scheme->getNames());
    }
}
