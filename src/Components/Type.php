<?php
/**
 * Created for lv-export-core
 * Datetime: 24.06.2019 15:10
 * @author Timur Kasumov aka XAKEPEHOK
 */

namespace Leadvertex\External\Export\Core\Components;


use InvalidArgumentException;

class Type
{

    const ORDERS = 'orders';

    /**
     * @var string
     */
    private $type;

    public function __construct(string $type)
    {
        $this->guardType($type);
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function get(): string
    {
        return $this->type;
    }

    private function guardType(string $type)
    {
        $types = [];
        if (!in_array($type, $types)) {
            throw new InvalidArgumentException("Invalid type '{$type}'. Should be one of [" . implode(', ', $types) . "]");
        }
    }

}