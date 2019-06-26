<?php

namespace Leadvertex\External\Export\Core\FieldDefinitions;


use PHPUnit\Framework\TestCase;

class TextDefinitionTest extends TestCase
{

    /** @var array */
    private $names;
    /** @var array */
    private $descriptions;
    /** @var string */
    private $default;
    /** @var bool */
    private $required;
    /** @var TextDefinition */
    private $textDefinition;

    public function setUp()
    {
        parent::setUp();

        $this->names = [];
        $this->descriptions = [];
        $this->default = 'DefaultValue';
        $this->required = true;

        $this->textDefinition = new TextDefinition(
            $this->names,
            $this->descriptions,
            $this->default,
            $this->required
        );

    }

    public function testDefinition()
    {
        $this->assertEquals('text', $this->textDefinition->definition());
    }

    /**
     * @dataProvider dataProvider
     * @param bool $required
     * @param string $value
     * @param bool $expected
     */
    public function testValidateValue(bool $required, string $value, bool $expected)
    {
        $textDefinition = new TextDefinition(
            $this->names,
            $this->descriptions,
            $this->default,
            $required
        );

        $this->assertEquals($expected, $textDefinition->validateValue($value));
    }

    public function dataProvider()
    {
        return [
          [true, '   ', false],
          [true, 'notEmpty', true],
          [false, '   ', true],
          [false, 'notEmpty', true],
        ];
    }
}
