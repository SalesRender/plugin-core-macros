<?php

namespace Leadvertex\External\Export\Core\FieldDefinitions;


use Exception;
use Leadvertex\External\Export\Core\Components\MultiLang;
use PHPUnit\Framework\TestCase;

class StringDefinitionTest extends TestCase
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
    private $stringDefinition;

    /**
     * @throws Exception
     */
    public function setUp()
    {
        parent::setUp();

        $this->label = new MultiLang([
            'en' => 'Organization name',
            'ru' => 'Название организации',
        ]);

        $this->description = new MultiLang([
            'en' => 'Description',
            'ru' => 'Описание',
        ]);

        $this->default = 'Test value for default param';
        $this->required = true;

        $this->stringDefinition = new StringDefinition(
            $this->label,
            $this->description,
            $this->default,
            $this->required
        );

    }

    public function testDefinition()
    {
        $this->assertEquals('string', $this->stringDefinition->definition());
    }

    /**
     * @dataProvider dataProviderForValidate
     * @param bool $required
     * @param string $value
     * @param bool $expected
     * @throws Exception
     */
    public function testValidateValue(bool $required, $value, bool $expected)
    {
        $stringDefinition = new StringDefinition(
            $this->label,
            $this->description,
            $this->default,
            $required
        );

        $this->assertEquals($expected, $stringDefinition->validateValue($value));
    }

    public function dataProviderForValidate()
    {
        return [
            [
                'required' => true,
                'value' => '   ',
                'expected' => false,
            ],
            [
                'required' => true,
                'value' => 'notEmpty',
                'expected' => true,
            ],
            [
                'required' => true,
                'value' => 1,
                'expected' => false,
            ],
            [
                'required' => true,
                'value' => [],
                'expected' => false,
            ],

            [
                'required' => false,
                'value' => '   ',
                'expected' => true,
            ],
            [
                'required' => false,
                'value' => 'notEmpty',
                'expected' => true,
            ],
            [
                'required' => false,
                'value' => 1,
                'expected' => false,
            ],
        ];
    }
}
