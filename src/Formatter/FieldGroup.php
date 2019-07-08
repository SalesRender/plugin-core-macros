<?php
/**
 * Created for plugin-export-core
 * Datetime: 04.07.2019 16:18
 * @author Timur Kasumov aka XAKEPEHOK
 */

namespace Leadvertex\Plugin\Export\Core\Formatter;


use Leadvertex\Plugin\Export\Core\Components\MultiLang;
use Leadvertex\Plugin\Export\Core\FieldDefinitions\FieldDefinition;

class FieldGroup
{

    /**
     * @var MultiLang
     */
    private $label;
    /**
     * @var FieldDefinition[]
     */
    private $fields;

    /**
     * FieldsGroup constructor.
     * @param MultiLang $label
     * @param FieldDefinition[] $fields
     */
    public function __construct(MultiLang $label, array $fields)
    {
        $this->label = $label;
        $this->fields = $fields;
    }

    /**
     * @return MultiLang
     */
    public function getLabel(): MultiLang
    {
        return $this->label;
    }

    /**
     * @param string $name
     * @return FieldDefinition
     */
    public function getField(string $name): FieldDefinition
    {
        return $this->fields[$name];
    }

    /**
     * @return FieldDefinition[]
     */
    public function getFields(): array
    {
        return $this->fields;
    }

    public function toArray(): array
    {
        $array = [
            'label' => $this->label->getTranslations(),
            'fields' => [],
        ];
        foreach ($this->getFields() as $fieldName => $fieldDefinition) {
            $array['fields'][$fieldName] = $fieldDefinition->toArray();
        }

        return $array;
    }

}