<?php
/**
 * Created for lv-export-core.
 * Datetime: 02.07.2018 16:54
 * @author Timur Kasumov aka XAKEPEHOK
 */

namespace Leadvertex\External\Export\Core\FieldDefinitions;


use Leadvertex\External\Export\Core\Components\MultiLang;

class FloatDefinition extends FieldDefinition
{

    public function __construct(MultiLang $name, MultiLang $description, $default, bool $required)
    {
        $default = (float) $default;
        parent::__construct($name, $description, $default, $required);
    }

    /**
     * @return string
     */
    public function definition(): string
    {
        return 'float';
    }

    /**
     * @param float|int $value
     * @return bool
     */
    public function validateValue($value): bool
    {
        if ($this->isRequired() && is_null($value)) {
            return false;
        }

        return is_numeric($value);
    }
}