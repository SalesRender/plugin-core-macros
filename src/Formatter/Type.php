<?php
/**
 * Created for plugin-export-core
 * Datetime: 24.06.2019 15:10
 * @author Timur Kasumov aka XAKEPEHOK
 */

namespace Leadvertex\Plugin\Export\Core\Formatter;


use InvalidArgumentException;

class Type
{

    const ORDERS = 'ORDERS';

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

    public function isEquals(self $type): bool
    {
        return $type->get() === $this->get();
    }

    private function guardType(string $type)
    {
        $types = [self::ORDERS];
        if (!in_array($type, $types)) {
            throw new InvalidArgumentException("Invalid type '{$type}'. Should be one of [" . implode(', ', $types) . "]");
        }
    }

}