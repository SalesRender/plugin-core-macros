<?php

namespace Leadvertex\External\Export\Core\FieldDefinitions;


use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class ArrayDefinitionTest extends TestCase
{
    /** @var array */
    private $names;
    /** @var array */
    private $descriptions;
    /** @var string */
    private $default;
    /** @var bool */
    private $required;
    /** @var ArrayDefinition */
    private $arrayDefinition;
    /** @var array */
    private $enum;

    public function setUp()
    {
        parent::setUp();

        $this->names = ['Mark','Bob','Frank'];
        $this->descriptions = ['description'];
        $this->default = 'DefaultValue';
        $this->required = true;
        $this->enum = ['enum'];

        $this->arrayDefinition = new ArrayDefinition(
            $this->names,
            $this->descriptions,
            $this->default,
            $this->required,
            $this->enum
        );
    }

    public function testInvalidEnum()
    {
        $this->expectException(InvalidArgumentException::class);
        $enum = ['enum', ['bla']];

        $this->arrayDefinition = new ArrayDefinition(
            $this->names,
            $this->descriptions,
            $this->default,
            $this->required,
            $enum
        );
    }

    public function testDefinition()
    {
        $this->assertEquals('array', $this->arrayDefinition->definition());
    }

    /**
     * @dataProvider dataProvider
     * @param bool $required
     * @param array $value
     * @param bool $expected
     */
    public function testValidateValue(bool $required, array $value, bool $expected)
    {
        $textDefinition = new ArrayDefinition(
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
            [true, [], false],
            [true, ['notEmpty'], true],
            [false, [], true],
            [false, ['notEmpty'], true],
        ];
    }

    public function testToArray()
    {
        $expected = [
            'definition' => 'array',
            'name' => $this->names,
            'description' => $this->descriptions,
            'default' => $this->default,
            'required' => $this->required,
            'enum' => $this->enum
        ];

        $this->assertEquals($expected, $this->arrayDefinition->toArray());
    }

    public function testGetEnum()
    {
        $this->assertEquals($this->enum, $this->arrayDefinition->getEnum());
    }
}
