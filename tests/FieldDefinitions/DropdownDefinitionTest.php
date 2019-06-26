<?php

namespace Leadvertex\External\Export\Core\FieldDefinitions;


use PHPUnit\Framework\TestCase;

class DropdownDefinitionTest extends TestCase
{
    /** @var array */
    private $names;
    /** @var array */
    private $descriptions;
    /** @var string */
    private $default;
    /** @var bool */
    private $required;
    /** @var StringDefinition */
    private $dropdownDefinition;
    /** @var array */
    private $dropdownItems;

    public function setUp()
    {
        parent::setUp();

        $this->names = [];
        $this->descriptions = [];
        $this->dropdownItems = [
            '01' => array('en' => 'January', 'ru' => 'Январь'),
            '02' => array('en' => 'February', 'ru' => 'Февраль')
        ];
        $this->default = 'DefaultValue';
        $this->required = true;

        $this->dropdownDefinition = new DropdownDefinition(
            $this->names,
            $this->descriptions,
            $this->dropdownItems,
            $this->default,
            $this->required
        );

    }

    /**
     * @dataProvider dataProvider
     * @param bool $required
     * @param $value
     * @param bool $expected
     */
    public function testValidateValue(bool $required, $value, bool $expected)
    {
        $floatDefinition = new DropdownDefinition(
            $this->names,
            $this->descriptions,
            $this->dropdownItems,
            $this->default,
            $required
        );

        $this->assertEquals($expected, $floatDefinition->validateValue($value));
    }

    public function dataProvider()
    {
        return [
            [true, 'invalidValue', false],
            [true, '01', true],
            [false, 'invalidValue', true],
            [false, '02', true],
        ];
    }

    public function testToArray()
    {
        $expected = [
            'definition' => 'dropdown',
            'name' => $this->names,
            'description' => $this->descriptions,
            'default' => $this->default,
            'required' => $this->required,
            'dropdownItems' => $this->dropdownItems
        ];

        $this->assertEquals($expected, $this->dropdownDefinition->toArray());
    }

    public function testDefinition()
    {
        $this->assertEquals('dropdown', $this->dropdownDefinition->definition());
    }
}
