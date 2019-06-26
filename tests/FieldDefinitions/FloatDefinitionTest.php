<?php

namespace Leadvertex\External\Export\Core\FieldDefinitions;


use PHPUnit\Framework\TestCase;

class FloatDefinitionTest extends TestCase
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
    private $floatDefinition;

    public function setUp()
    {
        parent::setUp();

        $this->names = [];
        $this->descriptions = [];
        $this->default = 'DefaultValue';
        $this->required = true;

        $this->floatDefinition = new FloatDefinition(
            $this->names,
            $this->descriptions,
            $this->default,
            $this->required
        );

    }

    public function testDefinition()
    {
        $this->assertEquals('float', $this->floatDefinition->definition());
    }

    /**
     * @dataProvider dataProvider
     * @param bool $required
     * @param $value
     * @param bool $expected
     */
    public function testValidateValue(bool $required, $value, bool $expected)
    {
        $floatDefinition = new FloatDefinition(
            $this->names,
            $this->descriptions,
            $this->default,
            $required
        );

        $this->assertEquals($expected, $floatDefinition->validateValue($value));
    }

    public function dataProvider()
    {
        return [
            [true, 'invalidText', false],
            [true, (float) random_int(1,100), true],
            [false, null, true],
            [false, (float) random_int(1,100), true],
        ];
    }
}
