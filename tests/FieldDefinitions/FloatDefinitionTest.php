<?php

namespace Leadvertex\Plugin\Export\Core\FieldDefinitions;


use Exception;
use Leadvertex\Plugin\Export\Core\Components\MultiLang;
use PHPUnit\Framework\TestCase;

class FloatDefinitionTest extends TestCase
{
    /** @var MultiLang */
    private $label;

    /** @var MultiLang */
    private $description;

    /** @var string */
    private $default;

    /** @var bool */
    private $required;

    /** @var StringDefinition */
    private $floatDefinition;

    /**
     * @throws Exception
     */
    public function setUp()
    {
        parent::setUp();

        $this->label = new MultiLang([
            'en' => 'Your weight',
            'ru' => 'Ваш вес',
        ]);

        $this->description = new MultiLang([
            'en' => 'Description',
            'ru' => 'Описание',
        ]);

        $this->default = 2.95;
        $this->required = true;

        $this->floatDefinition = new FloatDefinition(
            $this->label,
            $this->description,
            $this->default,
            $this->required
        );

    }

    public function testDefinition()
    {
        $this->assertEquals('float', $this->floatDefinition->definition());
    }

    /**
     * @dataProvider dataProviderForValidate
     * @param bool $required
     * @param $value
     * @param bool $expected
     * @throws Exception
     */
    public function testValidateValue(bool $required, $value, bool $expected)
    {
        $floatDefinition = new FloatDefinition(
            $this->label,
            $this->description,
            $this->default,
            $required
        );

        $this->assertEquals($expected, $floatDefinition->validateValue($value));
    }

    /**
     * @return array
     * @throws Exception
     */
    public function dataProviderForValidate()
    {
        return [
            [
                'required' => true,
                'value' => 'invalidText',
                'expected' => false,
            ],
            [
                'required' => true,
                'value' => (float)random_int(1, 100),
                'expected' => true,
            ],
            [
                'required' => true,
                'value' => random_int(1, 100),
                'expected' => true,
            ],
            [
                'required' => true,
                'value' => 5.95,
                'expected' => true,
            ],
            [
                'required' => true,
                'value' => null,
                'expected' => false,
            ],
            [
                'required' => true,
                'value' => [5.14],
                'expected' => false,
            ],

            [
                'required' => false,
                'value' => 5.65,
                'expected' => true,
            ],
            [
                'required' => false,
                'value' => '5.65',
                'expected' => true,
            ],
            [
                'required' => false,
                'value' => '5.65text',
                'expected' => false,
            ],
            [
                'required' => false,
                'value' => null,
                'expected' => false,
            ],
        ];
    }
}
