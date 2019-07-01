<?php

namespace Leadvertex\External\Export\Core\FieldDefinitions;


use Exception;
use Leadvertex\External\Export\Core\Components\MultiLang;
use PHPUnit\Framework\TestCase;

class EnumDefinitionTest extends TestCase
{

    /** @var MultiLang */
    private $nameMultiLang;
    /** @var MultiLang */
    private $descriptions;
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

        $this->nameMultiLang = new MultiLang(array('en' => 'Organization name', 'ru' => 'Название организации'));
        $this->descriptions = new MultiLang(array('en' => 'Description', 'ru' => 'Описание'));
        $this->enum = [
            'jan' => new MultiLang(['en' => 'January', 'ru' => 'Январь']),
            'feb' => new MultiLang(['en' => 'February', 'ru' => 'Февраль'])
        ];
        $this->default = 'Test value for default param';
        $this->required = true;

        $this->enumDefinition = new EnumDefinition(
            $this->nameMultiLang,
            $this->descriptions,
            $this->enum,
            $this->default,
            $this->required
        );
    }

    public function testToArray()
    {
        $expected = [
            'definition' => 'enum',
            'name' => $this->nameMultiLang->getTranslations(),
            'description' => $this->descriptions->getTranslations(),
            'default' => $this->default,
            'required' => $this->required,
            'enum' => MultiLang::toArray($this->enum)
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
            $this->nameMultiLang,
            $this->descriptions,
            $this->enum,
            $this->default,
            $required
        );

        $this->assertEquals($expected, $definition->validateValue($value));
    }

    public function dataProviderForValidate()
    {
        return [
            ['required' => true, 'value' => null, 'expected' => false],
            ['required' => true, 'value' => 1, 'expected' => false],
            ['required' => true, 'value' => 'feb', 'expected' => true],
            ['required' => true, 'value' => 'sen', 'expected' => false],

            ['required' => false, 'value' => 'jan', 'expected' => true],
        ];
    }

}
