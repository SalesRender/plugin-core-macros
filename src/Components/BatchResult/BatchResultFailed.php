<?php
/**
 * Created for plugin-export-core
 * Datetime: 25.06.2019 11:24
 * @author Timur Kasumov aka XAKEPEHOK
 */

namespace Leadvertex\Plugin\Export\Core\Components\BatchResult;


class BatchResultFailed implements BatchResultInterface
{

    /**
     * @var string
     */
    private $failedCause;

    public function __construct(string $failedCause)
    {
        $this->failedCause = $failedCause;
    }

    public function get(): array
    {
        return [
            'failedCause' => $this->failedCause
        ];
    }
}