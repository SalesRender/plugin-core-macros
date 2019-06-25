<?php
/**
 * Created for lv-export-core
 * Datetime: 25.06.2019 11:20
 * @author Timur Kasumov aka XAKEPEHOK
 */

namespace Leadvertex\External\Export\Core\Components\BatchResult;


class BatchResultSuccess implements BatchResultInterface
{

    /**
     * @var string
     */
    private $compiledUrl;

    public function __construct(string $compiledUrl)
    {
        $this->compiledUrl = $compiledUrl;
    }

    public function get(): array
    {
        return [
            'compiledUrl' => $this->compiledUrl
        ];
    }
}