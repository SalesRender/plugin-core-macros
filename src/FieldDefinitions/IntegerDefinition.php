<?php
/**
 * Created for lv-export-core.
 * Datetime: 02.07.2018 16:53
 * @author Timur Kasumov aka XAKEPEHOK
 */

namespace Leadvertex\External\Export\Core\FieldDefinitions;


use Leadvertex\External\Export\Core\Components\MultiLang;

class IntegerDefinition extends FieldDefinition
{

    public function __construct(MultiLang $name, MultiLang $description, $default, bool $required)
    {
        $default = (int) $default;
        parent::__construct($name, $description, $default, $required);
    }

    /**
     * @return string
     */
    public function definition(): string
    {
        return 'integer';
    }

    /**
     * @param int $value
     * @return bool
     */
    public function validateValue($value): bool
    {
        if (!$this->isRequired() && is_null($value)) {
            return true;
        }

        return is_int($value);
    }
}