<?php
/**
 * Created for lv-export-core.
 * Datetime: 02.07.2018 15:37
 * @author Timur Kasumov aka XAKEPEHOK
 */

namespace Leadvertex\External\Export\Core\FieldDefinitions;


use Leadvertex\External\Export\Core\Components\MultiLang;

class StringDefinition extends FieldDefinition
{

    public function __construct(MultiLang $label, MultiLang $description, $default, bool $required)
    {
        $default = (string) $default;
        parent::__construct($label, $description, $default, $required);
    }

    /**
     * @return string
     */
    public function definition(): string
    {
        return 'string';
    }

    /**
     * @param string $value
     * @return bool
     */
    public function validateValue($value): bool
    {
        if (is_string($value)) {
            $value = trim($value);
        }

        $isEmpty = is_null($value) || (is_string($value) && empty($value));
        if ($this->isRequired() && $isEmpty) {
            return false;
        }

        return is_string($value);
    }
}