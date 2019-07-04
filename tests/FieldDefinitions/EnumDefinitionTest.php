<?php

namespace Leadvertex\External\Export\Core\FieldDefinitions;


use Exception;
use Leadvertex\External\Export\Core\Components\MultiLang;
use PHPUnit\Framework\TestCase;

class EnumDefinitionTest extends TestCase
{

    /** @var MultiLang */
    private $label;

    /** @var MultiLang */
    private $description;

    /** @var array */
    private $enum;

    /** @var string */
    private $default;

    /** @var bool */
    private $required;

    /** @var EnumDefinition */
    private $enumDefinition;

    /**
     * @throws Exception
     */
    public function setUp()
    {
        parent::setUp();

        $this->label = new MultiLang([
            'en' => 'Month',
            'ru' => 'Месяц',
        ]);

        $this->description = new MultiLang([
            'en' => 'Description',
            'ru' => 'Описание',
        ]);

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

        $this->default = 'Test value for default param';
        $this->required = true;

        $this->enumDefinition = new EnumDefinition(
            $this->label,
            $this->description,
            $this->enum,
            $this->default,
            $this->required
        );
    }

    public function testToArray()
    {
        $expected = [
            'definition' => 'enum',
            'label' => $this->label->getTranslations(),
            'description' => $this->description->getTranslations(),
            'default' => $this->default,
            'required' => $this->required,
            'enum' => MultiLang::toArray($this->enum),
        ];

        $this->assertEquals($expected, $this->enumDefinition->toArray());
    }

    public function testDefinition()
    {
        $this->assertEquals('enum', $this->enumDefinition->definition());
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
        $definition = new EnumDefinition(
            $this->label,
            $this->description,
            $this->enum,
            $this->default,
            $required
        );

        $this->assertEquals($expected, $definition->validateValue($value));
    }

    public function dataProviderForValidate()
    {
        return [
            [
                'required' => true,
                'value' => null,
                'expected' => false,
            ],
            [
                'required' => true,
                'value' => 1,
                'expected' => false,
            ],
            [
                'required' => true,
                'value' => 'feb',
                'expected' => true,
            ],
            [
                'required' => true,
                'value' => 'sen',
                'expected' => false,
            ],
            [
                'required' => false,
                'value' => 'jan',
                'expected' => true,
            ],
        ];
    }

}
