<?php
/**
 * Created for lv-export-core.
 * Datetime: 02.07.2018 15:37
 * @author Timur Kasumov aka XAKEPEHOK
 */

namespace Leadvertex\External\Export\Core\FieldDefinitions;


use InvalidArgumentException;

class ArrayDefinition extends FieldDefinition
{

    /**
     * @var array
     */
    protected $enum;

    public function __construct(array $names, array $descriptions, $default, bool $required, array $enum = [])
    {
        parent::__construct($names, $descriptions, $default, $required);
        $this->guardFlatArray($enum);
        $this->enum = array_values($enum);
    }

    /**
     * @return string
     */
    public function definition(): string
    {
        return 'array';
    }

    /**
     * @param array $value
     * @return bool
     */
    public function validateValue($value): bool
    {
        return $this->required === false || count($value) > 0;
    }

    /**
     * @return array
     */
    public function getEnum(): array
    {
        return $this->enum;
    }

    public function toArray(): array
    {
        $array = parent::toArray();
        $array['enum'] = $this->getEnum();
        return $array;
    }

    private function guardFlatArray($array)
    {
        if (count($array) !== count($array, COUNT_RECURSIVE)) {
            throw new InvalidArgumentException('Array enum should be flat');
        }
    }
}