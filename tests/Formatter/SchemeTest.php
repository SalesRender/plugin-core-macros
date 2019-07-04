<?php

namespace Leadvertex\External\Export\Core\Formatter;


use Exception;
use Leadvertex\External\Export\Core\Components\Developer;
use Leadvertex\External\Export\Core\Components\MultiLang;
use Leadvertex\External\Export\Core\FieldDefinitions\IntegerDefinition;
use Leadvertex\External\Export\Core\FieldDefinitions\StringDefinition;
use PHPUnit\Framework\TestCase;
use TypeError;

class SchemeTest extends TestCase
{
    /** @var Developer */
    private $developer;

    /** @var Type */
    private $type;

    /** @var FieldGroup[] */
    private $fieldGroups;

    /** @var Scheme */
    private $scheme;

    /** @var MultiLang */
    private $label;

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

        $this->developer = new Developer(
            'Tony Stark',
            'tony@starkindustries.com',
            'starkindustries.com'
        );

        $this->type = new Type(Type::ORDERS);

        $this->label = new MultiLang([
            'en' => 'Organization name',
            'ru' => 'Название организации',
        ]);

        $this->description = new MultiLang([
            'en' => 'Description',
            'ru' => 'Описание',
        ]);

        $this->defaultMultiLang = new MultiLang([
            'en' => 'default test field',
            'ru' => 'Дефолтное тестовое поле',
        ]);

        $this->fieldGroups = [
            'main' => new FieldGroup(
                new MultiLang([
                    'en' => 'Main settings',
                    'ru' => 'Основные настройки',
                ]),
                [
                    'field_1' => new IntegerDefinition($this->defaultMultiLang, $this->defaultMultiLang, 1, true),
                    'field_2' => new StringDefinition($this->defaultMultiLang, $this->defaultMultiLang, 'default value for test', true),
                ]
            ),
            'additional' => new FieldGroup(
                new MultiLang([
                    'en' => 'Additional settings',
                    'ru' => 'Дополнительные настройки',
                ]),
                [
                    'field_3' => new IntegerDefinition($this->defaultMultiLang, $this->defaultMultiLang, 1, true),
                    'field_4' => new StringDefinition($this->defaultMultiLang, $this->defaultMultiLang, 'default value for test', true),
                ]
            ),
        ];

        $this->scheme = new Scheme(
            $this->developer,
            $this->type,
            $this->label,
            $this->description,
            $this->fieldGroups
        );

    }

    public function testCreateWithInvalidFieldDefinition()
    {
        $this->expectException(TypeError::class);
        $fieldDefinitions = [
            5,
            10,
        ];

        $this->scheme = new Scheme(
            $this->developer,
            $this->type,
            $this->label,
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
        $this->assertEquals($this->fieldGroups, $this->scheme->getGroups());
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
            'label' => $this->label->getTranslations(),
            'description' => $this->description->getTranslations(),
            'groups' => [],
        ];
        foreach ($this->fieldGroups as $groupName => $fieldGroup) {
            $expected['groups'][$groupName] = $fieldGroup->toArray();
        }

        $this->assertEquals($expected, $this->scheme->toArray());
    }

    public function testGetGroup()
    {
        $this->assertEquals(
            $this->fieldGroups['main'],
            $this->scheme->getGroup('main')
        );

        $this->assertEquals(
            $this->fieldGroups['additional'],
            $this->scheme->getGroup('additional')
        );
    }

    public function testGetGroups()
    {
        $this->assertEquals($this->fieldGroups, $this->scheme->getGroups());
    }

    public function testGetName()
    {
        $this->assertEquals($this->label, $this->scheme->getLabel());
    }
}
