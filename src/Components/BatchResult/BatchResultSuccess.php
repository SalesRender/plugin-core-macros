<?php
/**
 * Created for plugin-export-core
 * Datetime: 25.06.2019 11:20
 * @author Timur Kasumov aka XAKEPEHOK
 */

namespace Leadvertex\Plugin\Export\Core\Components\BatchResult;


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