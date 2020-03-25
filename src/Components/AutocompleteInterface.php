<?php
/**
 * Created for plugin-core
 * Datetime: 27.02.2020 14:21
 * @author Timur Kasumov aka XAKEPEHOK
 */

namespace Leadvertex\Plugin\Core\Macros\Components;


interface AutocompleteInterface
{

    public function query(string $query): array;

    public function values(array $values): array;

    public function validate(array $values): bool;

}