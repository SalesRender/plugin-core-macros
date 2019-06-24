<?php
/**
 * Created for lv-export-core
 * Datetime: 02.07.2018 16:59
 * @author Timur Kasumov aka XAKEPEHOK
 */

namespace Leadvertex\External\Export\Core\Components;


use Leadvertex\External\Export\Core\FieldDefinitions\FieldDefinition;
use TypeError;

class Scheme
{

    /** @var string[]  */
    protected $names = [];

    /** @var string[]  */
    protected $descriptions = [];

    /** @var FieldDefinition[] */
    protected $fields = [];

    /** @var Type */
    private $type;

    /**
     * ConfigDefinition constructor.
     * @param Type $type
     * @param string[] $names . Export name in different languages. If array, first value are default if language
     * undefined. For example array('en' => 'Organization name', 'ru' => 'Название организации') - default en.
     * @param string[] $descriptions . Export description in different languages. Same behavior, as $names
     * @param FieldDefinition[] $fieldDefinitions
     */
    public function __construct(Type $type, array $names, array $descriptions, array $fieldDefinitions)
    {
        $this->type = $type;
        $this->names = $names;
        $this->descriptions = $descriptions;

        foreach ($fieldDefinitions as $fieldName => $fieldDefinition) {
            if (!$fieldDefinition instanceof FieldDefinition) {
                throw new TypeError('Every item of $fieldsDefinitions should be instance of ' . FieldDefinition::class);
            }
            $this->fields[$fieldName] = $fieldDefinition;
        }
    }

    /**
     * @return Type
     */
    public function getType(): Type
    {
        return $this->type;
    }

    /**
     * Return property name in passed language. If passed language was not defined, will return name in default language
     * @return string
     */
    public function getName(): string
    {
        return $this->names;
    }

    /**
     * Return property description in passed language. If passed language was not defined, will return description in default language
     * @return string
     */
    public function getDescription(): string
    {
        return $this->descriptions;
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

    /**
     * @return array
     */
    public function toArray(): array
    {
        $array = [
            'type' => $this->type->get(),
            'name' => $this->names,
            'description' => $this->descriptions,
            'fields' => [],
        ];

        foreach ($this->getFields() as $fieldName => $fieldDefinition) {
            $array['fields'][$fieldName] = $fieldDefinition->toArray();
        }

        return $array;
    }

}