<?php
/**
 * Created for plugin-exporter-core
 * Datetime: 05.08.2019 16:10
 * @author Timur Kasumov aka XAKEPEHOK
 */

namespace Leadvertex\Plugin\Exporter\Core\Components;


use Leadvertex\Plugin\Components\ApiClient\ApiClient;
use Leadvertex\Plugin\Exporter\Core\ExporterInterface;

class ExporterFactory
{

    public static function create(string $name, ApiClient $client): ExporterInterface
    {
        $classname = "\Leadvertex\Plugin\Exporter\Handler\\{$name}\\{$name}";
        return new $classname(
            $client,
            constant('LV_EXPORT_RUNTIME_DIR'),
            constant('LV_EXPORT_PUBLIC_DIR'),
            constant('LV_EXPORT_PUBLIC_URL')
        );
    }

}