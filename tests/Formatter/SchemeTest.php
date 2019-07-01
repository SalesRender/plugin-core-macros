<?php

namespace Leadvertex\External\Export\Core\Formatter;


use Exception;
use Leadvertex\External\Export\Core\Components\Developer;
use Leadvertex\External\Export\Core\Components\MultiLang;
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
    private $fieldDefinitions;
    /** @var Scheme */
    private $scheme;
    /** @var MultiLang */
    private $nameDefinition;
    /** @var MultiLang */
    private $description;
    /** @var MultiLang */
    private $defaultMultiLang;

    /**
     * @throws Exception
     */
    public function setUp()
    {
        parent::setUp();

        $this->developer = $this->createMock(Developer::class);
        $this->type = $this->createMock(Type::class);

        $this->nameDefinition = new MultiLang(array('en' => 'Organization name', 'ru' => 'Название организации'));
        $this->description = new MultiLang(array('en' => 'Description', 'ru' => 'Описание'));

        $this->defaultMultiLang = new MultiLang(array('en' => 'default test field', 'ru' => 'Дефолтное тестовое поле'));

        $this->fieldDefinitions = [
            'name1' => new IntegerDefinition($this->defaultMultiLang,$this->defaultMultiLang,1,true),
            'name2' => new StringDefinition($this->defaultMultiLang,$this->defaultMultiLang,'default value for test',true),
        ];

        $this->scheme = new Scheme(
            $this->developer,
            $this->type,
            $this->nameDefinition,
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
            $this->nameDefinition,
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
        $this->assertEquals($this->description, $this->scheme->getDescription());
    }

    public function testToArray()
    {
        $expected = [
            'developer' => $this->developer->toArray(),
            'type' => $this->type->get(),
            'name' => $this->nameDefinition->getTranslations(),
            'description' => $this->description->getTranslations(),
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
        $this->assertEquals($this->nameDefinition, $this->scheme->getName());
    }
}
