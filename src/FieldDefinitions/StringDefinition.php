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

    public function __construct(MultiLang $name, MultiLang $description, $default, bool $required)
    {
        $default = (string) $default;
        parent::__construct($name, $description, $default, $required);
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
        $value = trim((string) $value);
        return $this->required === false || strlen($value) > 0;
    }
}