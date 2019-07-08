<?php

namespace Leadvertex\Plugin\Export\Core\FieldDefinitions;


use Exception;
use Leadvertex\Plugin\Export\Core\Components\MultiLang;
use PHPUnit\Framework\TestCase;

class TextDefinitionTest extends TestCase
{

    /** @var MultiLang */
    private $label;

    /** @var MultiLang */
    private $description;

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

        $this->label = new MultiLang([
            'en' => 'Organization name',
            'ru' => 'Название организации'
        ]);

        $this->description = new MultiLang([
            'en' => 'Description',
            'ru' => 'Описание'
        ]);

        $this->default = 'Test value for default param';
        $this->required = true;

        $this->textDefinition = new TextDefinition(
            $this->label,
            $this->description,
            $this->default,
            $this->required
        );

    }

    public function testDefinition()
    {
        $this->assertEquals('text', $this->textDefinition->definition());
    }

}
