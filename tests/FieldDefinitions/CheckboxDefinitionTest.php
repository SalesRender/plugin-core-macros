<?php

namespace Leadvertex\External\Export\Core\FieldDefinitions;


use PHPUnit\Framework\TestCase;

class CheckboxDefinitionTest extends TestCase
{

    /** @var CheckboxDefinition */
    private $checkboxDefinition;
    /** @var array */
    private $names;
    /** @var array */
    private $description;
    /** @var string */
    private $default;
    /** @var bool */
    private $required;

    public function setUp()
    {
        parent::setUp();

        $this->names = ['Bob','Martin','Lesly'];
        $this->description = ['lorem'];
        $this->default = 'defaultValue';
        $this->required = true;

        $this->checkboxDefinition = new CheckboxDefinition(
            $this->names,
            $this->description,
            $this->default,
            $this->required
        );
    }

    public function testDefinition()
    {
        $this->assertEquals('checkbox', $this->checkboxDefinition->definition());
    }

    /**
     * @dataProvider dataProvider
     * @param bool $required
     * @param $value
     * @param bool $expected
     */
    public function testValidateValue(bool $required, $value, bool $expected)
    {
        $definition = new CheckboxDefinition(
            $this->names,
            $this->description,
            $this->default,
            $required
        );

        $this->assertEquals($expected, $definition->validateValue($value));
    }

    public function dataProvider()
    {
        return [
            [true, false, false],
            [true, 'value', true],
            [false, null, true],
            [false, random_int(1,100), true],
        ];
    }

    public function testGetDefaultValue()
    {
        $this->assertEquals($this->default, $this->checkboxDefinition->getDefaultValue());
    }

    public function testIsRequired()
    {
        $this->assertEquals($this->required, $this->checkboxDefinition->isRequired());
    }
}
