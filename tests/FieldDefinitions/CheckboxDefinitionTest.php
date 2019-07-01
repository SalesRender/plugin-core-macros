<?php

namespace Leadvertex\External\Export\Core\FieldDefinitions;


use Exception;
use Leadvertex\External\Export\Core\Components\MultiLang;
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

    /**
     * @throws Exception
     */
    public function setUp()
    {
        parent::setUp();

        $this->names = new MultiLang(array('en' => 'Organization name', 'ru' => 'Название организации'));
        $this->description = new MultiLang(array('en' => 'Description', 'ru' => 'Описание'));
        $this->default = 'Test value for default param';
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
     * @dataProvider dataProviderForValidate
     * @param bool $required
     * @param $value
     * @param bool $expected
     * @throws Exception
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

    /**
     * @return array
     * @throws Exception
     */
    public function dataProviderForValidate()
    {
        return [
            ['required' => true, 'value' => false, 'expected' => false],
            ['required' => true, 'value' => true, 'expected' => true],

            ['required' => false, 'value' => false, 'expected' => true],
            ['required' => false, 'value' => null, 'expected' => false],
            ['required' => false, 'value' => random_int(1,100), 'expected' => false],
            ['required' => false, 'value' => [], 'expected' => false],
            ['required' => false, 'value' => 'string', 'expected' => false],
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
