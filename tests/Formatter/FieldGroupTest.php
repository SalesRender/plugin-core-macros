<?php
/**
 * Created for lv-export-core
 * Datetime: 04.07.2019 16:49
 * @author Timur Kasumov aka XAKEPEHOK
 */

namespace Leadvertex\Plugin\Export\Core\Formatter;

use Leadvertex\Plugin\Export\Core\Components\MultiLang;
use Leadvertex\Plugin\Export\Core\FieldDefinitions\BooleanDefinition;
use Leadvertex\Plugin\Export\Core\FieldDefinitions\FieldDefinition;
use PHPUnit\Framework\TestCase;

class FieldGroupTest extends TestCase
{

    /** @var MultiLang */
    private $label;

    /** @var FieldDefinition[] */
    private $fields;

    /** @var FieldGroup */
    private $group;

    protected function setUp()
    {
        parent::setUp();

        $this->label = new MultiLang([
            'en' => 'Main settings',
            'ru' => 'Основные настройки',
        ]);

        $this->fields = [
            'use' => new BooleanDefinition(
                new MultiLang([
                    'en' => 'Use this format',
                    'ru' => 'Использовать этот формат',
                ]),
                new MultiLang([
                    'en' => 'Include this type in export',
                    'ru' => 'Включать этот тип в выгрузку',
                ]),
                true,
                false
            ),
            'printCaption' => new BooleanDefinition(
                new MultiLang([
                    'en' => 'Print caption',
                    'ru' => 'Печатать заголовок',
                ]),
                new MultiLang([
                    'en' => 'Print caption at first page',
                    'ru' => 'Печатает заголовок на первой странице',
                ]),
                true,
                false
            ),
        ];

        $this->group = new FieldGroup($this->label, $this->fields);
    }

    public function testGetLabel()
    {
        $this->assertEquals($this->label, $this->group->getLabel());
    }

    public function testGetField()
    {
        $this->assertEquals(
            $this->fields['use'],
            $this->group->getField('use')
        );
    }

    public function testGetFields()
    {
        $this->assertEquals($this->fields, $this->group->getFields());
    }

    public function testToArray()
    {
        $this->assertEquals([
            'label' => $this->label->getTranslations(),
            'fields' => [
                'use' => $this->fields['use']->toArray(),
                'printCaption' => $this->fields['printCaption']->toArray(),
            ],
        ], $this->group->toArray());
    }

}
