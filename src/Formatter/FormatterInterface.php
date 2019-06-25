<?php
/**
 * @author Timur Kasumov (aka XAKEPEHOK)
 * Datetime: 20.06.2019 17:43
 */

namespace Leadvertex\External\Export\Core\Formatter;


use Leadvertex\External\Export\Core\Components\ApiParams;
use Leadvertex\External\Export\Core\Components\BatchResult\BatchResultInterface;
use Leadvertex\External\Export\Core\Components\GenerateParams;
use Leadvertex\External\Export\Core\Components\StoredConfig;
use Leadvertex\External\Export\Core\Components\WebhookManager;

interface FormatterInterface
{

    public function __construct(ApiParams $apiParams, string $runtimeDir, string $outputDir);

    public function getScheme(): Scheme;

    public function isConfigValid(StoredConfig $config): bool;

    public function generate(GenerateParams $params);

    /**
     * Should be called after every chunk handled (not every id, chunk only)
     * @param WebhookManager $manager
     * @param array $ids
     * @return mixed
     */
    public function sendProgress(WebhookManager $manager, array $ids);

    /**
     * @param WebhookManager $manager
     * @param BatchResultInterface $batchResult
     * @return mixed
     */
    public function sendResult(WebhookManager $manager, BatchResultInterface $batchResult);

}