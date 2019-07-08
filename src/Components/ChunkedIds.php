<?php
/**
 * @author Timur Kasumov (aka XAKEPEHOK)
 * Datetime: 21.06.2019 22:04
 */

namespace Leadvertex\Plugin\Export\Core\Components;


class ChunkedIds
{

    /**
     * @var array
     */
    private $ids;

    public function __construct(array $ids)
    {
        $this->ids = $ids;
    }

    /**
     * @return array
     */
    public function getChunks(): array
    {
        return $this->ids;
    }

}