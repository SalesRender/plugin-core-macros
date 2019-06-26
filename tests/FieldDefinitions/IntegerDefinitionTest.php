<?php

namespace Leadvertex\External\Export\Core\FieldDefinitions;


use PHPUnit\Framework\TestCase;

class IntegerDefinitionTest extends TestCase
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
    private $integerDefinition;

    public function setUp()
    {
        parent::setUp();

        $this->names = [];
        $this->descriptions = [];
        $this->default = 'DefaultValue';
        $this->required = true;

        $this->integerDefinition = new IntegerDefinition(
            $this->names,
            $this->descriptions,
            $this->default,
            $this->required
        );

    }

    public function testDefinition()
    {
        $this->assertEquals('integer', $this->integerDefinition->definition());
    }

    /**
     * @dataProvider dataProvider
     * @param bool $required
     * @param $value
     * @param bool $expected
     */
    public function testValidateValue(bool $required, $value, bool $expected)
    {
        $integerDefinition = new IntegerDefinition(
            $this->names,
            $this->descriptions,
            $this->default,
            $required
        );

        $this->assertEquals($expected, $integerDefinition->validateValue($value));
    }

    public function dataProvider()
    {
        return [
            [true, 'invalidText', false],
            [true, random_int(1,100), true],
            [false, null, true],
            [false, random_int(1,100), true],
        ];
    }
}
