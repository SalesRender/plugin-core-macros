<?php

namespace Leadvertex\External\Export\Core\FieldDefinitions;


use Exception;
use Leadvertex\External\Export\Core\Components\MultiLang;
use PHPUnit\Framework\TestCase;

class TextDefinitionTest extends TestCase
{

    /** @var array */
    private $name;
    /** @var array */
    private $descriptions;
    /** @var string */
    private $default;
    /** @var bool */
    private $required;
    /** @var TextDefinition */
    private $textDefinition;

    /**
     * @throws Exception
     */
    public function setUp()
    {
        parent::setUp();

        $this->name = new MultiLang(array('en' => 'Organization name', 'ru' => 'Название организации'));
        $this->descriptions = new MultiLang(array('en' => 'Description', 'ru' => 'Описание'));
        $this->default = 'Test value for default param';
        $this->required = true;

        $this->textDefinition = new TextDefinition(
            $this->name,
            $this->descriptions,
            $this->default,
            $this->required
        );

    }

    public function testDefinition()
    {
        $this->assertEquals('text', $this->textDefinition->definition());
    }

}
