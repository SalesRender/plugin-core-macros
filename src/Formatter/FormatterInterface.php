<?php
/**
 * @author Timur Kasumov (aka XAKEPEHOK)
 * Datetime: 20.06.2019 17:43
 */

namespace Leadvertex\Plugin\Export\Core\Formatter;


use Leadvertex\Plugin\Export\Core\Components\ApiParams;
use Leadvertex\Plugin\Export\Core\Components\BatchResult\BatchResultInterface;
use Leadvertex\Plugin\Export\Core\Components\GenerateParams;
use Leadvertex\Plugin\Export\Core\Components\StoredConfig;
use Leadvertex\Plugin\Export\Core\Components\WebhookManager;
use Leadvertex\Plugin\Scheme\Components\i18n;
use Leadvertex\Plugin\Scheme\Scheme;

interface FormatterInterface
{

    public function __construct(ApiParams $apiParams, string $runtimeDir, string $publicDir, string $publicUrl);

    /**
     * Should return human-friendly name of this exporter
     * @return i18n
     */
    public static function getName(): i18n;

    /**
     * Should return human-friendly description of this exporter
     * @return i18n
     */
    public static function getDescription(): i18n;

    /**
     * @return Type of entities, that can be exported by plugin
     */
    public function getType(): Type;

    /**
     * Should return scheme of exporter configs
     * @return Scheme
     */
    public function getScheme(): Scheme;

    /**
     * Validator of stored exporter config
     * @param StoredConfig $config
     * @return bool
     */
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