<?php
/**
 * Created for lv-export-core.
 * Datetime: 02.07.2018 15:33
 * @author Timur Kasumov aka XAKEPEHOK
 */

namespace Leadvertex\Plugin\Export\Core\FieldDefinitions;


use Exception;
use Leadvertex\Plugin\Export\Core\Components\MultiLang;

abstract class FieldDefinition
{

    protected $label = [];
    protected $description = [];
    protected $default;
    protected $required;

    /**
     * ConfigDefinition constructor.
     * @param MultiLang $label
     * @param MultiLang $description
     * @param string|int|float|bool|array|null $default value
     * @param bool $required is this field required
     * @throws Exception
     */
    public function __construct(MultiLang $label, MultiLang $description, $default, bool $required)
    {
        $this->label = $label;
        $this->description = $description;
        $this->default = $default;
        $this->required = $required;
    }

        /**
     * Value, witch will be used as default
     * @return string|int|float|bool|array|null
     */
    public function getDefaultValue()
    {
        return $this->default;
    }

    /**
     * Does this field will be required
     * @return bool
     */
    public function isRequired(): bool
    {
        return $this->required;
    }

    /**
     * @return string
     */
    abstract public function definition(): string;

    /**
     * @param $value
     * @return bool
     */
    abstract public function validateValue($value): bool;

    public function toArray(): array
    {
        return [
            'definition' => $this->definition(),
            'label' => $this->label->getTranslations(),
            'description' => $this->description->getTranslations(),
            'default' => $this->default,
            'required' => (bool) $this->required,
        ];
    }

}