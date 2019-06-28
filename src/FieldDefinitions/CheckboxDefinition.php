<?php
/**
 * Created for lv-export-core.
 * Datetime: 02.07.2018 16:52
 * @author Timur Kasumov aka XAKEPEHOK
 */

namespace Leadvertex\External\Export\Core\FieldDefinitions;


class CheckboxDefinition extends FieldDefinition
{

    /**
     * @return string
     */
    public function definition(): string
    {
        return 'checkbox';
    }

    /**
     * @param bool $value
     * @return bool
     */
    public function validateValue($value): bool
    {
        return is_bool($value) && ($this->required === false || $value === true);
    }
}