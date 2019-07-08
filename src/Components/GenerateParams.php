<?php
/**
 * @author Timur Kasumov (aka XAKEPEHOK)
 * Datetime: 20.06.2019 17:47
 */

namespace Leadvertex\Plugin\Export\Core\Components;


use Leadvertex\Plugin\Export\Core\Formatter\Type;

class GenerateParams
{
    /**
     * @var Type
     */
    private $type;
    /**
     * @var StoredConfig
     */
    private $config;
    /**
     * @var BatchParams
     */
    private $batchParams;
    /**
     * @var ChunkedIds
     */
    private $chunkedIds;

    public function __construct(
        Type $type,
        StoredConfig $config,
        BatchParams $batchParams,
        ChunkedIds $chunkedIds
    )
    {
        $this->type = $type;
        $this->config = $config;
        $this->batchParams = $batchParams;
        $this->chunkedIds = $chunkedIds;
    }

    /**
     * @return Type
     */
    public function getType(): Type
    {
        return $this->type;
    }

    /**
     * @return StoredConfig
     */
    public function getConfig(): StoredConfig
    {
        return $this->config;
    }

    /**
     * @return BatchParams
     */
    public function getBatchParams(): BatchParams
    {
        return $this->batchParams;
    }

    /**
     * @return ChunkedIds
     */
    public function getChunkedIds(): ChunkedIds
    {
        return $this->chunkedIds;
    }


}