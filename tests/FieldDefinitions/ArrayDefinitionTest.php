<?php

namespace Leadvertex\Plugin\Export\Core\FieldDefinitions;


use Exception;
use InvalidArgumentException;
use Leadvertex\Plugin\Export\Core\Components\MultiLang;
use PHPUnit\Framework\TestCase;

class ArrayDefinitionTest extends TestCase
{

    /** @var MultiLang */
    private $label;

    /** @var MultiLang */
    private $description;

    /** @var mixed */
    private $default;

    /** @var bool */
    private $required;

    /** @var ArrayDefinition */
    private $arrayDefinition;

    /** @var array */
    private $enum;

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

        $this->default = [];

        $this->required = true;

        $this->enum = [
            'jan' => new MultiLang([
                'en' => 'January',
                'ru' => 'Январь',
            ]),
            'feb' => new MultiLang([
                'en' => 'February',
                'ru' => 'Февраль',
            ]),
        ];

        $this->arrayDefinition = new ArrayDefinition(
            $this->label,
            $this->description,
            $this->enum,
            $this->default,
            $this->required
        );
    }

    /**
     * @throws Exception
     */
    public function testInvalidEnum()
    {
        $this->expectException(InvalidArgumentException::class);
        $enum = [
            'enum',
            ['invalid text for Enum'],
        ];

        $this->arrayDefinition = new ArrayDefinition(
            $this->label,
            $this->description,
            $enum,
            $this->default,
            $this->required
        );
    }

    public function testDefinition()
    {
        $this->assertEquals('array', $this->arrayDefinition->definition());
    }

    /**
     * @dataProvider dataProviderForValidate
     * @param bool $required
     * @param array $value
     * @param bool $expected
     * @throws Exception
     */
    public function testValidateValue(bool $required, $value, bool $expected)
    {
        $textDefinition = new ArrayDefinition(
            $this->label,
            $this->description,
            $this->enum,
            $this->default,
            $required
        );

        $this->assertEquals($expected, $textDefinition->validateValue($value));
    }

    public function dataProviderForValidate()
    {
        $data = [
            [
                'required' => true,
                'value' => [],
                'expected' => false,
            ],
            [
                'required' => true,
                'value' => null,
                'expected' => false,
            ],
            [
                'required' => true,
                'value' => [
                    ['test value in array'],
                    ['two'],
                ],
                'expected' => false,
            ],
            [
                'required' => true,
                'value' => [''],
                'expected' => false,
            ],

            [
                'required' => false,
                'value' => [1],
                'expected' => false,
            ],
            [
                'required' => false,
                'value' => [
                    'jan',
                    'feb',
                ],
                'expected' => true,
            ],
        ];

        $result = [];
        foreach ($data as $item) {
            $result[json_encode($item['value'])] = $item;
        }
        return $result;
    }

    public function testToArray()
    {
        $expected = [
            'definition' => 'array',
            'label' => $this->label->getTranslations(),
            'description' => $this->description->getTranslations(),
            'default' => $this->default,
            'required' => $this->required,
            'enum' => MultiLang::toArray($this->enum),
        ];

        $this->assertEquals($expected, $this->arrayDefinition->toArray());
    }

    public function testGetEnum()
    {
        $this->assertEquals($this->enum, $this->arrayDefinition->getEnum());
    }
}
