<?php
/**
 * Created for plugin-export-core
 * Datetime: 02.07.2018 16:59
 * @author Timur Kasumov aka XAKEPEHOK
 */

namespace Leadvertex\Plugin\Export\Core\Formatter;


use Leadvertex\Plugin\Export\Core\Components\Developer;
use Leadvertex\Plugin\Export\Core\Components\MultiLang;
use TypeError;

class Scheme
{

    /** @var Type */
    private $type;

    /** @var MultiLang  */
    protected $name;

    /** @var MultiLang  */
    protected $description;

    /** @var FieldGroup[] */
    protected $groups = [];

    /** @var Developer */
    private $developer;


    /**
     * Scheme constructor.
     * @param Developer $developer
     * @param Type $type
     * @param MultiLang $name
     * @param MultiLang $description
     * @param FieldGroup[] $fieldGroups
     */
    public function __construct(Developer $developer, Type $type, MultiLang $name, MultiLang $description, array $fieldGroups)
    {
        $this->developer = $developer;
        $this->type = $type;
        $this->name = $name;
        $this->description = $description;

        foreach ($fieldGroups as $groupName => $fieldsGroup) {
            if (!$fieldsGroup instanceof FieldGroup) {
                throw new TypeError('Every item of $fieldsDefinitions should be instance of ' . FieldGroup::class);
            }
            $this->groups[$groupName] = $fieldsGroup;
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
     * @return FieldGroup
     */
    public function getGroup(string $name): FieldGroup
    {
        return $this->groups[$name];
    }

    /**
     * @return FieldGroup[]
     */
    public function getGroups(): array
    {
        return $this->groups;
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
            'groups' => [],
        ];

        foreach ($this->getGroups() as $groupName => $fieldDefinition) {
            $array['groups'][$groupName] = $fieldDefinition->toArray();
        }

        return $array;
    }

}