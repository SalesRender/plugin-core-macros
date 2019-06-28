<?php
/**
 * Created for lv-export-core.
 * Datetime: 02.07.2018 16:07
 * @author Timur Kasumov aka XAKEPEHOK
 */

namespace Leadvertex\External\Export\Core\FieldDefinitions;


class TextDefinition extends StringDefinition
{
    /**
     * @return string
     */
    public function definition(): string
    {
        return 'text';
    }
}