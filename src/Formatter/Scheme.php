<?php
/**
 * Created for lv-export-core
 * Datetime: 02.07.2018 16:59
 * @author Timur Kasumov aka XAKEPEHOK
 */

namespace Leadvertex\External\Export\Core\Formatter;


use Leadvertex\External\Export\Core\Components\Developer;
use Leadvertex\External\Export\Core\Components\MultiLang;
use Leadvertex\External\Export\Core\FieldDefinitions\FieldDefinition;
use TypeError;

class Scheme
{

    /** @var Type */
    private $type;

    /** @var MultiLang  */
    protected $name;

    /** @var MultiLang  */
    protected $description;

    /** @var FieldDefinition[] */
    protected $fields = [];

    /** @var Developer */
    private $developer;


    /**
     * Scheme constructor.
     * @param Developer $developer
     * @param Type $type
     * @param MultiLang $name
     * @param MultiLang $description
     * @param array $fieldDefinitions
     */
    public function __construct(Developer $developer, Type $type, MultiLang $name, MultiLang $description, array $fieldDefinitions)
    {
        $this->developer = $developer;
        $this->type = $type;
        $this->name = $name;
        $this->description = $description;

        foreach ($fieldDefinitions as $fieldName => $fieldDefinition) {
            if (!$fieldDefinition instanceof FieldDefinition) {
                throw new TypeError('Every item of $fieldsDefinitions should be instance of ' . FieldDefinition::class);
            }
            $this->fields[$fieldName] = $fieldDefinition;
        }
    }

    /**
     * @return Developer
     */
    public function getDeveloper(): Developer
    {
        return $this->developer;
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
     * @return MultiLang
     */
    public function getName(): MultiLang
    {
        return $this->name;
    }

    /**
     * Return property description in passed language. If passed language was not defined, will return description in default language
     * @return MultiLang
     */
    public function getDescription(): MultiLang
    {
        return $this->description;
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
            'developer' => $this->developer->toArray(),
            'type' => $this->type->get(),
            'name' => $this->name->getTranslations(),
            'description' => $this->description->getTranslations(),
            'fields' => [],
        ];

        foreach ($this->getFields() as $fieldName => $fieldDefinition) {
            $array['fields'][$fieldName] = $fieldDefinition->toArray();
        }

        return $array;
    }

}